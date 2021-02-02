<?php declare(strict_types = 1);

namespace PwPop\SlimApp\Controller;

use Dflydev\FigCookies\FigRequestCookies;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PwPop\SlimApp\models\Favorite;
use PwPop\SlimApp\models\Product;
use PwPop\SlimApp\models\ProductImageModel;
use PwPop\SlimApp\models\User;

final class HomePageController
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

    public function __invoke(Request $request, Response $response, array $args) : Response {
        $sessionCookie = FigRequestCookies::get($request, 'session_id');
        $buySuccess = $this->container->get('flash')->getFirstMessage('buySuccess');
        $registerSuccess = $this->container->get('flash')->getFirstMessage('registerSuccess');
        $products = (new Product)->newQuery();
        if($sessionCookie->getValue()){
          $_SESSION['session_id'] = $sessionCookie->getValue();
        }
        if(isset($_SESSION['session_id']))
        {
            $session_id = $_SESSION['session_id'];
            $user = User::where('session_id', $session_id)->first();
            $products = $products->where('user_id', '<>', $user->id);
        }
        $products = $products->where('sold_out', 0);
        $productCount = Product::where('sold_out', 0)->count();
        $products = $products->take(5)->orderBy('id','desc')->get();
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
        return $this->container->get('view')->render($response, 'homepage.html.twig', [
            'products' => $products,
            'offset' => 0,
            'totalProducts' => $productCount,
            'buySuccess' => $buySuccess,
            'registerSuccess' => $registerSuccess,
        ]);
    }
}
