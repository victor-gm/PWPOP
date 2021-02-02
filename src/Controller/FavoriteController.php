<?php declare(strict_types = 1);

namespace PwPop\SlimApp\controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PwPop\SlimApp\models\Favorite;
use PwPop\SlimApp\models\Product;
use PwPop\SlimApp\models\ProductImageModel;
use PwPop\SlimApp\models\User;

final class FavoriteController {

    /** @var ContainerInterface */
    private $container;

    /**
    * HelloController constructor.
    * @param ContainerInterface $container
    */

  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }


  public function formAction(Request $request, Response $response): Response {
    $session_id = $_SESSION['session_id'];
    $user = User::where('session_id', $session_id)->first();
    $user = $user->id;

    $favorites = Favorite::where(['user_id' => $user])->get();
    $products = [];
    foreach ($favorites as $favorite) {
      $product = Product::where(['id' => $favorite->product_id])->first();
      if (isset($product['sold_out'])  && $product['sold_out'] !== 1) {
        array_push($products,$product);
      }
    }
    if(sizeof($products) > 0){
      foreach($products as $product){
        $image = ProductImageModel::where('product_id', $product->id)->first();
        if($image !== null){
            $product->image = $image->image;
        }
      }
    }
    return $this->container->get('view')->render($response, 'my-favorites.html.twig', [
        'products' => $products,
    ]);
  }

  public function favoriteAction(Request $request, Response $response, array $args) : string {
    
    $session_id = $_SESSION['session_id'];
    $user = User::where('session_id', $session_id)->first();
    $user = $user->id;
    $product = $args['product_id'];

    $exists = Favorite::where(['user_id' => $user, 'product_id' => $product])->first();
    $json = new \stdClass;
    if($exists !== null) {

      $exists->delete();
      $json->fav = false;
    } else {

      $favorite = new Favorite();
      $favorite->user_id = $user;
      $favorite->product_id = $product;
      $favorite->save();
      $json->fav = true;
    }
    $json = json_encode($json);
    return $json;
  }
}
