<?php declare(strict_types = 1);

namespace PwPop\SlimApp\controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PwPop\SlimApp\models\Category;
use PwPop\SlimApp\models\Product;
use PwPop\SlimApp\models\ProductImageModel;
use PwPop\SlimApp\models\User;

final class UploadController {
    
    private const UPLOADS_DIR = __DIR__ . '/../../public/uploads';
    private const UNEXPECTED_ERROR = "An unexpected error occurred uploading the file '%s'...";
    private const INVALID_EXTENSION_ERROR = "The received file extension '%s' is not valid";
    private const INVALID_SIZE = 'Please upload images that are less than 1MB!';
    private const ALLOWED_EXTENSIONS = ['jpg', 'png'];
    private const TITLE_ERROR = 'You need a title to the product!';
    private const SAME_TITLE_ERROR = 'You already have a product with that title!';
    private const DESC_ERROR = 'The description should be at least 20 characters long!';
    private const PRICE_ERROR = 'Please enter a valid price in euros!';
    private const INVALID_CATEGORY_ERROR = "Please choose a valid category!";
    private const INVALID_FORM = "Please fill all the fields!";

    /** @var ContainerInterface */
    private $container;

       /**
     * HelloController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function loadView(Request $request, Response $response): Response {
        $categories = Category::all();
        return $this->container->get('view')->render($response, 'upload.html.twig', [
            'categories' => $categories,
        ]);
    }

    public function productAction(Request $request, Response $response): Response {
        $errors = [];
        $data = $request->getParsedBody();
        $categories = Category::all();
        //VALIDATIONS
        $allValid = true;
        if($data === null){
            $errors[] = self::INVALID_FORM;
            $allValid = false;
        }
        if(! isset($data['title']) || $data['title'] === ''){
            $errors[] = self::TITLE_ERROR;
            $allValid = false;
        }
        if(! isset($data['desc']) || $data['desc'] === ''){
            $errors[] = self::DESC_ERROR;
            $allValid = false;
        }
        if(! isset($data['price']) || $data['price'] === ''){
            $errors[] = self::PRICE_ERROR;
            $allValid = false;
        }
        if(! isset($data['cat']) || $data['cat'] === ''){
            $errors[] = self::INVALID_CATEGORY_ERROR;
            $allValid = false;
        }
        if($allValid === false){
            return $this->container->get('view')->render($response, 'upload.html.twig', [
                'errors' => $errors,
                'data' => $data,
                'categories' => $categories,
            ]);
        }
        $title = $data['title'];
        $desc = $data['desc'];
        $price = $data['price'];
        $cat = $data['cat'];

        $session_id = $_SESSION['session_id'];
        $dataUser = User::where('session_id', $session_id)->first();
        $user = $dataUser->id;
        $isOkay = true;
        $errors = $this->isValid($title, $desc, (float) $price, (int) $cat, $user);
        if(sizeof($errors) !== 0){
            return $this->container->get('view')->render($response, 'upload.html.twig', [
                'errors' => $errors,
                'data' => $data,
                'categories' => $categories,
            ]);
        }
        //FINISHED VALIDATIONS
        $uploadedFiles = $request->getUploadedFiles();
        //FORM POST
        /** @var UploadedFileInterface $uploadedFile */
        foreach ($uploadedFiles['files'] as $uploadedFile) {
            if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
                $errors[] = sprintf(self::UNEXPECTED_ERROR, $uploadedFile->getClientFilename());
                continue;
            }
            $name = $uploadedFile->getClientFilename();
            $fileInfo = pathinfo($name);
            $format = $fileInfo['extension'];
            if (! $this->isValidFormat($format)) {
                $errors[] = sprintf(self::INVALID_EXTENSION_ERROR, $format);
                $isOkay = false;
            }

            if(! $this->isValidSize($uploadedFile->getSize())){
                $errors[] = self::INVALID_SIZE;
                $isOkay = false;
            }
        }
        if(! $isOkay){
            return $this->container->get('view')->render($response, 
                    'upload.html.twig', [
                        'errors' => $errors, 
                        'data' => $data, 
                        'categories' => $categories, 
                    ]);
        }
        $product = new Product;
        $product->title = $title;
        $product->description = $desc;
        $product->price = $price;
        $product->category_id = $cat;
        $product->user_id = $user;
        $product->save();
        foreach ($uploadedFiles['files'] as $uploadedFile) {
            $name = uniqid() .'.' . $format;
            $uploadedFile
                ->moveTo(self::UPLOADS_DIR . DIRECTORY_SEPARATOR . $name);
            $productImages = new ProductImageModel();
            $productImages->product_id = $product->id;
            $productImages->image = $name;
            $productImages->save();
        }
        $this->container->flash->addMessage('addedSuccess', 
                                "The product was sucessfully added");
        return $response->withRedirect($this->container->router->pathFor('myProducts'), 303);
    }

    private function isValid(string $title, string $desc,
                             float $price, int $cat, int $user): array {
        $errors = [];
        if($title === ""){
            $errors[] = self::TITLE_ERROR;
        }

        if(Product::where(['user_id' => $user, 'title' => $title])->count() > 0){
            $errors[] = self::SAME_TITLE_ERROR;
        }
        if(strlen($desc) < 20){
            $errors[] = self::DESC_ERROR;
        }
        if(is_numeric($price) === false){
            $errors[] = self::PRICE_ERROR;
        }
        $categoriesCount = Category::count();
        if($cat <= 0 || $cat > $categoriesCount){
             $errors[] = self::INVALID_CATEGORY_ERROR;
        }
        return $errors;
    }

    private function isValidFormat(string $extension): bool{
        return in_array($extension, self::ALLOWED_EXTENSIONS, true);
    }

    private function isValidSize(int $size):bool{
        if($size > 1000000) {
            return false;
        }
        else {
            return true;
        }
    }
}
