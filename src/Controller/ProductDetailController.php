<?php declare(strict_types = 1);

namespace PwPop\SlimApp\controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PwPop\SlimApp\models\Category;
use PwPop\SlimApp\models\Favorite;
use PwPop\SlimApp\models\Product;
use PwPop\SlimApp\models\ProductImageModel;
use PwPop\SlimApp\models\User;

final class ProductDetailController {
    
    private const UPLOADS_DIR = __DIR__ . '/../../public/uploads';
    private const TITLE_ERROR = 'You need a title to the product!';
    private const DESC_ERROR = 'The description should be at least 20 characters long!';
    private const PRICE_ERROR = 'Please enter a valid price in euros!';
    private const INVALID_CATEGORY_ERROR = "Please choose a valid category!";
    private const INVALID_FORM = "Please fill all the fields!";
    private const INVALID_PRODUCT = "We couldn't find the product, try again!";

    /** @var ContainerInterface */
    private $container;

       /**
     * HelloController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response {
        
        $productId = $args['id'];
        $session_id = $_SESSION['session_id'];
        $user = User::where('session_id', $session_id)->first();
        $currentId = $user->id;
        $product = Product::where('id', $productId)->first();
        $FlashMsgs = $this->container->get('flash')->getMessages();

        if($product === null){
            $response = new \Slim\Http\Response(404);
            return $this->container->get('view')->render($response, 'page404.html.twig', []);
        }

        if($product['sold_out'] === 1) {
            $response = new \Slim\Http\Response(404);
            return $this->container->get('view')->render($response, 'page404.html.twig', []);
        }
        
        $product = Product::where('id', $args['id'])->first()->toArray();
        if($currentId !== $product['user_id']){
            $buyer = true;
        }else{
            $buyer = false;
        }
        
        $categories = Category::all();
        $product['images'] = [];
        $images = ProductImageModel::where('product_id', $productId)->get();
        if($images !== null){
            foreach($images as $image){
                $product['images'][] = $image->image;
            }
        }
        $isFavorite = Favorite::where(['product_id' => $productId, 'user_id' => $currentId])->first();
        if ($isFavorite !== null) {
            $product['favorite'][] = true;
        }
        return $this->container->get('view')->render($response, 'detail-product.html.twig', [
            'categories' => $categories,
            'product' => $product,
            'buyer' => $buyer,
            'FlashMsgs' => $FlashMsgs,
        ]);
    }

    public function editProduct(Request $request, Response $response, array $args): Response {
        $productId = $args['id'];
        $productExists = Product::where('id', $productId)->exists();
        $allValid = true;
        if(! $productExists){
            $this->container->flash->addMessage('error', self::INVALID_PRODUCT);
            $allValid = false;
        }
        $product = Product::where('id', $productId)->first();
        $user = User::where('session_id', $_SESSION['session_id'])->first();
        if($user->id !== $product->user_id) {
            return $this->container->get('view')->render($response, 'page403.html.twig', []);
        }
        if($product->sold_out === 1) {
            $response = new \Slim\Http\Response(404);
            return $this->container->get('view')->render($response, 'page404.html.twig', []);
        }
        $data = $request->getParsedBody();
        //VALIDATIONS 
        if($data === null){
            $this->container->flash->addMessage('error', self::INVALID_FORM);
            $allValid = false;
        }
        if(! isset($data['title'])){
            $this->container->flash->addMessage('error', self::TITLE_ERROR);
            $allValid = false;
        }
        if(! isset($data['desc'])){
            $this->container->flash->addMessage('error', self::DESC_ERROR);
            $allValid = false;
        }
        if(! isset($data['price'])){
            $this->container->flash->addMessage('error', self::PRICE_ERROR);
            $allValid = false;
        }
        if(! isset($data['cat'])){
            $this->container->flash->addMessage('error', self::INVALID_CATEGORY_ERROR);
            $allValid = false;
        }
        if(! $allValid){
            return $response->withRedirect($this->container->router->pathFor('productDetail', [
                'id' => $productId,
            ]), 303);
        }else{
            $title = $data['title'];
            $desc = $data['desc'];
            $price = $data['price'];
            $cat = $data['cat'];
            if($this->isValid($title, $desc, (float) $price, (int) $cat) === false){
                return $response->withRedirect($this->container->router->pathFor('productDetail', [
                    'id' => $productId,
                ]), 303);
            }
        }
        //ENDED VALIDATIONS 

        $product->title = $title;
        $product->description = $desc;
        $product->price = $price;
        $product->category_id = $cat;
        $product->save();
        $this->container->flash->addMessage('deletedSuccess', "The edit of the product was sucessful");
        return $response->withRedirect($this->container->router->pathFor('myProducts'), 303);
    }

    public function deleteProduct(Request $request, Response $response, array $args): Response {
        $productId = $args['id'];
        $product = Product::where('id', $productId)->first();
        
        if($product){
            $images = ProductImageModel::where('product_id', $productId)->get();
            foreach($images as $image){
                echo self::UPLOADS_DIR.$image->image;
                if (file_exists(self::UPLOADS_DIR.'/'.$image->image)){
                    unlink(self::UPLOADS_DIR.'/'.$image->image);
                }
            }
            $product->images()->delete();
            $product->delete();
            $this->container->flash->addMessage('deletedSuccess', "The product was sucessfully deleted");
            $response = $response->withRedirect($this->container->router->pathFor('myProducts'), 303);
        }
        return $response;
    }

    private function isValid(string $title, string $desc, float $price, int $cat): bool {
        
        $isValid = true;
        if($title === ""){
            $this->container->flash->addMessage('error', self::TITLE_ERROR);
            $isValid = false;
        }

        if(strlen($desc) < 20){
            $this->container->flash->addMessage('error', self::DESC_ERROR);
            $isValid = false;
        } 
    
        if(is_numeric($price) === false){
            $this->container->flash->addMessage('error', self::PRICE_ERROR);
            $isValid = false;
        }

        $categoriesCount = Category::count();
        if($cat <= 0 || $cat > $categoriesCount){
            $this->container->flash->addMessage('error', self::INVALID_CATEGORY_ERROR);
            $isValid = false;
        }

        return $isValid;
    }
}
