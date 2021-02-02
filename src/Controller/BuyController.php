<?php declare(strict_types = 1);

namespace PwPop\SlimApp\controller;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PwPop\SlimApp\models\Product;
use PwPop\SlimApp\models\User;

final class BuyController {

    private const BUY_SUCCESS = 'You have successfully bought a product!';

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

        $productId = $args['product_id'];
        $product = Product::where('id', $productId)->first();
        if ($product['sold_out'] === 1) {
            $response = new \Slim\Http\Response(404);
            return $this->container->get('view')->render($response, 'page404.html.twig', []);
        }
        $data = [];
        $owner = User::where('id', $product->user_id)->first();
        $data['email'] = $owner->email;
        $data['title'] = $product->title;
        $session_id = $_SESSION['session_id'];
        $buyer = User::where('session_id', $session_id)->first();
        $data['username'] = $buyer->username;
        $data['phone'] = $buyer->phone;
        $emailSent = $this->sendBoughtEmail($data);
        if($emailSent) {
            $product->sold_out = 1;
            $product->save();
            $this->container->flash->addMessage('buySuccess', self::BUY_SUCCESS);
            return $response->withRedirect($this->container->router->pathFor('home'), 303);
        }
        return $response->withRedirect($this->container->router->pathFor('home'), 303);
    }

    function sendBoughtEmail(array $data) : bool {

        $mail = new PHPMailer();
        try {
            //Server settings
            $mail->isSMTP();                                // Set mailer to use SMTP
            $mail->Host       = 'smtp.gmail.com';           // Specify main and backup SMTP servers
            $mail->SMTPAuth   = true;                       // Enable SMTP authentication
            $mail->Username   = 'pwpop.grupo.12@gmail.com'; // SMTP username
            $mail->Password   = 'doQrew-qawkam-9rutwy';     // SMTP password
            $mail->SMTPSecure = 'tls';                      // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = 587;                        // TCP port to connect to
            $loader = new \Twig\Loader\FilesystemLoader(__DIR__ .'/../View/templates');
            $twig = new \Twig\Environment($loader);
            $htmlBody = $twig->render("bought-mail.html.twig", ['data' => $data]);
            $mail->setFrom('pwpop.grupo.12@gmail.com', 'PWPOP Grupo 12 WebMaster');
            $mail->addAddress($data['email']);     // Add a recipient
            $mail->isHTML(true);
            $mail->Subject = 'One of your products has been sold!';
            $mail->AddEmbeddedImage('assets/img/logo.png', 'logo');
            $mail->Body    = $htmlBody;
            $mail->send();
            return true;
        } catch (Exception $err) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return false;
        }
    } 
}
