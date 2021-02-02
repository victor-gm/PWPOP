<?php declare(strict_types = 1);

use PwPop\SlimApp\models\User;
use Slim\Flash\Messages;

$container = $app->getContainer();

$container['view'] = function ($con) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../src/view/templates', [
        'cache' => false,
    ]);

    $router = $con->get('router');

    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));

    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
    $view->addExtension(new Knlv\Slim\Views\TwigMessages(
        new Messages()
    ));
    if(isset($_SESSION['session_id'])){
        $view->getEnvironment()->addGlobal('session', $_SESSION['session_id']);
        $user = User::where('session_id', $_SESSION['session_id'])->first();
        $view->getEnvironment()->addGlobal('loggedUser', $user);
    }
    return $view;
};

$container['flash'] = function () {
    return new Messages();
};

//PDO database library
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function ($container) use ($capsule){
  return $capsule;
};

// $container['db'] = function ($c) {
//
//
//     $settings = $c->get('settings')['db'];
//     $pdo = new PDO("mysql:host=" . $settings['host'] . ";dbname=" . $settings['dbname'],
//         $settings['user'], $settings['pass']);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//     $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
//     return $pdo;
// };
