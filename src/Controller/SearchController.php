<?php declare(strict_types = 1);

namespace PwPop\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PwPop\SlimApp\models\Favorite;
use PwPop\SlimApp\models\Product;
use PwPop\SlimApp\models\ProductImageModel;
use PwPop\SlimApp\models\User;

final class SearchController
{
   /** @var ContainerInterface */
   private $container;

   /**
    * HelloController constructor.
    * @param ContainerInterface $container
    */
   public function __construct(ContainerInterface $container) {
      $this->container = $container;
   }

   public function __invoke(Request $request, Response $response) : Response
   {
      $data = $request->getParsedBody();
      $products = (new Product)->newQuery();
      if($data['title']){
         $products = $products->where('title', 'like', '%' . $data['title'] . '%');
      }
      if($data['category_id']){
         $products = $products->where('category_id', $data['category_id']);
      }
      if($data['min_price']){
         $products = $products->where('price', '>=', $data['min_price']);
      }
      if($data['max_price']){
         $products = $products->where('price', '<=', $data['max_price']);
      }
      $productCount = $products->count();
      $products = $products->take(5)->get();
      if(isset($_SESSION['session_id'])){
         $user = User::where('session_id', $_SESSION['session_id'])->first();
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
      return $this->container->get('view')->render($response, 'homepage.html.twig', [
          'products' => $products,
          'offset' => 0,
          'withFilters' => true,
          'totalProducts' => $productCount, 
      ]);
   }

    public function moreProducts(array $args) : string {
      $offset = $args['offset'];
      $productCount = Product::where('sold_out', 0)->count();
      if($offset >= $productCount){
        $data = [];
        $data["limit"] = true;
        $json = (object) $data;
        return json_encode($json);
      }
      $products = Product::where('sold_out', 0)->skip($offset)->take(5)->get();
      return $products->toJson();
    }
}
