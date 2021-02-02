<?php declare(strict_types = 1);

namespace PwPop\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PwPop\SlimApp\models\Category;
use PwPop\SlimApp\models\Favorite;
use PwPop\SlimApp\models\Product;
use PwPop\SlimApp\models\ProductImageModel;
use PwPop\SlimApp\models\User;

final class MoreProductsController
{
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args) : string
    {
      $offset = $args['offset'];
      $products = (new Product)->newQuery();
      if(isset($_SESSION['session_id'])) {
         $user = User::where('session_id', $_SESSION['session_id'])->first();
         $products = $products->where('user_id', '<>', $user->id);
      }
      $products = $products->where('sold_out', 0);
      $products = Product::skip($offset)->take(5)->orderBy('id','desc')->get();
      if(isset($user['id'])){
        foreach ($products as $product) {
          $isFavorite = Favorite::where(['product_id' => $product->id, 'user_id' => $user->id])->first();
          if ($isFavorite !== null) {
            $product->favorite = true;
          }
        }
      }
      foreach($products as $product){
        $image = ProductImageModel::where('product_id', $product->id)->first();
        if($image !== null){
            $product->image = $image->image;
        }
      }
      $json = $products->toJson();
      
      if($products->count() < 5){
        $arrne = [];
        $arrne['limit'] = true;
        $tempArray = json_decode($json);
        array_push($tempArray, $arrne);
        $json = json_encode($tempArray);
      }
      return $json;
    }

    public function getCategories() : string
    {
      $categories = Category::all();
      return $categories->toJson();
    }
}
