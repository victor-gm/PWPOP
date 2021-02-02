<?php declare(strict_types = 1);

namespace PwPop\SlimApp\Controller;

use PHPMailer\PHPMailer\PHPMailer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PwPop\SlimApp\models\User;

final class ActivationController
{
    /** @var ContainerInterface */
    private $container;

    /**
     * ActivationController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response,array $args) : Response {
        $activation_code = $request->getQueryParam('activation_code', $default = null);
        $success = $this->activate((int) $activation_code);
        if($success === false){
            return $this->container->get('view')->render($response, 'page404.html.twig', []);
        }
        $this->container->flash->addMessage('buySuccess', "Your account has been validated!");
        return $response->withRedirect($this->container->router->pathFor('home'), 303);
    }

    function activate(int $activation_code) : bool {
        $success = false;
        $user = User::where('validation_code', $activation_code)->first();
        if($user !== null)
        {
            $user->validated = 1;
            $user->save();
            $success = true;
        }
        return $success;
    }
    
    public function resendMail(Request $request, Response $response, array $args) : Response {
        if(isset($_SESSION['session_id'])){
            $user = User::where('session_id', $_SESSION['session_id'])->first();
            $validation_url_header = "pwpop.test/activate?activation_code=".$user->session_id;
            $data = [];
            $data['validation_url'] = $validation_url_header;
            $mail = new PHPMailer();
            try {
                //Server settings
                $mail->isSMTP();                                            // Set mailer to use SMTP
                $mail->Host       = 'smtp.gmail.com';  // Specify main and backup SMTP servers
                $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                $mail->Username   = 'pwpop.grupo.12@gmail.com';                     // SMTP username
                $mail->Password   = 'doQrew-qawkam-9rutwy';                               // SMTP password
                $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
                $mail->Port       = 587;                                    // TCP port to connect to

                //Recipients
                $loader = new \Twig\Loader\FilesystemLoader(__DIR__ .'/../View/templates');
                $twig = new \Twig\Environment($loader);
                $htmlBody = $twig->render("mail.html.twig", $data);
                $mail->setFrom('pwpop.grupo.12@gmail.com', 'PWPOP Grupo 12 WebMaster');
                $mail->addAddress($user->email);
                $mail->isHTML(true);
                $mail->Subject = 'Activate your PWPOP account!';
                $mail->AddEmbeddedImage('assets/img/logo.png', 'logo');
                $mail->Body    = $htmlBody;
                $mail->AltBody = $message;
                $mail->send();
                return $response->withRedirect($this->container->router->pathFor('home'), 303);
            } catch (Exception $err) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }
}
