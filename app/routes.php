<?php declare(strict_types = 1);

use PwPop\SlimApp\Controller\ActivationController;
use PwPop\SlimApp\Controller\BuyController;
use PwPop\SlimApp\Controller\FavoriteController;
use PwPop\SlimApp\Controller\HomePageController;
use PwPop\SlimApp\Controller\LoginController;
use PwPop\SlimApp\controller\middleware\SessionMiddleware;
use PwPop\SlimApp\Controller\MoreProductsController;
use PwPop\SlimApp\Controller\MyProductsController;
use PwPop\SlimApp\controller\ProductDetailController;
use PwPop\SlimApp\Controller\ProfileController;
use PwPop\SlimApp\Controller\SearchController;
use PwPop\SlimApp\Controller\SignUpController;
use PwPop\SlimApp\controller\UploadController;

$app->get('/', HomePageController::class)
    ->setName('home');

$app->get('/getProducts/{offset}', MoreProductsController::class);

$app->get('/getCategories', MoreProductsController::class.':getCategories');

$app->post('/search', SearchController::class)
    ->setName('globalSearch');

$app->post('/signup', SignUpController::class)
    ->setName('signUp');

$app->get('/upload', UploadController::class . ":loadView");

$app->post('/upload', UploadController::class . ':productAction')
    ->setName('uploadProduct');

$app->get('/my-products', MyProductsController::class . ':formAction')
    ->setName('myProducts');

$app->get('/product/{id}', ProductDetailController::class)
    ->setName('productDetail');

$app->put('/editProduct/{id}', ProductDetailController::class . ':editProduct')
    ->setName('editProduct');

$app->delete('/deleteProduct/{id}', ProductDetailController::class . ':deleteProduct')
    ->setName('deleteProduct');

$app->get('/activate', ActivationController::class);

$app->get('/sendactivationmail', ActivationController::class .':resendMail');

$app->post('/login', LoginController::class)
    ->setName('login');

$app->get('/logout', LoginController::class.':logout')
    ->setName('logout');

$app->get('/buy/{product_id}', BuyController::class)
    ->setName('buy');

$app->post('/favorite/{product_id}', FavoriteController::class . ':favoriteAction')
    ->setName('favorite');

$app->get('/my-favorites', FavoriteController::class . ':formAction')
    ->setName('myFavorites');

$app->get('/user', ProfileController::class . ':loadProfile')
    ->setName('user');

$app->put('/user/{id}', ProfileController::class . ':update')
    ->setName('editProfile');

$app->delete('/deleteUser/{id}', ProfileController::class . ':delete')
    ->setName('deleteProfile');

$app->add(SessionMiddleware::class);
