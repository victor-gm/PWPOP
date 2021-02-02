<?php declare(strict_types = 1);

namespace PwPop\SlimApp\controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PwPop\SlimApp\models\Product;
use PwPop\SlimApp\models\ProductImageModel;
use PwPop\SlimApp\models\User;

final class MyProductsController {

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function formAction(Request $request, Response $response): Response {

        $session_id = $_SESSION['session_id'];
        $user = User::where('session_id', $session_id)->first();
        $id = $user->id;
        $FlashMsgs = $this->container->get('flash')->getMessages();

        $products = Product::where(['user_id' => $id, 'sold_out' => 0])
            ->orderBy('id','desc')
            ->get();

        foreach($products as $product){
            $image = ProductImageModel::where('product_id', $product->id)->first();
            if($image !== null){
                $product->image = $image->image;
            }
        }
        return $this->container->get('view')->render($response, 'my-products.html.twig', [
            'products' => $products,
            'FlashMsgs' => $FlashMsgs,
        ]);
    }
}
