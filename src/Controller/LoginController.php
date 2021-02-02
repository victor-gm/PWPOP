<?php declare(strict_types = 1);

namespace PwPop\SlimApp\Controller;

use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PwPop\SlimApp\models\User;

final class LoginController
{
    private const USERNAME_OR_EMAIL_EMPTY = "You need to introduce an username or an email";
    private const USERNAME_ERROR_ALPHA = 'You need to introduce an alphanumeric username';
    private const USERNAME_ERROR_LENGHT = "Username can't exceed 20 characters";
    private const USERNAME_EXISTS = "Username doesn't exist";
    private const VALID_EMAIL = "The provided email is not valid";
    private const PASSWORD_ERROR_LENGHT = "Password must have at least 6 characters";
    private const PASSWORD_EMPTY = "Password can't be empty";
    private const DATA_WRONG = "Username or password are not correct, 
                                please try again with a different account or password.";
    private const ACCOUNT_DELETED = "This user has deleted the account";
    /** @var ContainerInterface */
    private $container;

    /**
     * LoginController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function logout(Request $request, Response $response, array $args) : Response {
        unset($_SESSION['session_id']);
        $response = FigResponseCookies::expire($response, 'session_id');
        return $response->withRedirect($this->container->router->pathFor('home'), 303);
    }

    public function __invoke(Request $request, Response $response, array $args) : Response {
        $data = $request->getParsedBody();
        $username_or_email = $data['username_or_email'];
        $password = $data['password'];
        $remember_me = 0;
        if(isset($data['rememberme'])){
            $remember_me = 1;
        }
        $errors = $this->checkData($data);
        if($errors){
            return $this->container->get('view')->render($response, 'homepage.html.twig', [
                'errorslogin' => $errors,
                'data' => $data,
            ]);
        }
        $result = $this->login($username_or_email, $password, $remember_me);
        if(! is_numeric($result[0])){
            $errors[] = $result;
            return $this->container->get('view')->render($response, 'homepage.html.twig', [
                'errorslogin' => $errors,
                'data' => $data,
            ]);
        }
        if($remember_me === 1) {
            $response = $this->createSessionCookie($response, $result[0]);
        }
        return $response->withRedirect($this->container->router->pathFor('home'), 303);
    }


    function checkData(array $data) : array {
        $errors = [];
        if(! isset($data['username_or_email']) || $data['username_or_email'] === null){
            $errors[] = sprintf(self::USERNAME_OR_EMAIL_EMPTY);
            return $errors;
        }
        if(preg_match("/@/", $data['username_or_email'])) //if it's an email
        {
            if (filter_var($data['username_or_email'], FILTER_VALIDATE_EMAIL) === false) {
                $errors[] = sprintf(self::VALID_EMAIL);
            }
        } else {
            if (ctype_alnum($data['username_or_email']) === false) {
                $errors[] = sprintf(self::USERNAME_ERROR_ALPHA);
            }
            if (strlen($data['username_or_email']) > 20) {
                $errors[] = sprintf(self::USERNAME_ERROR_LENGHT);
            }
        }
        //password
        if (! isset($data['password']) || $data['password'] === '') {
            $errors[] = sprintf(self::PASSWORD_EMPTY);
        }
        if (strlen($data['password']) < 6) {
            $errors[] = sprintf(self::PASSWORD_ERROR_LENGHT);
        }
        return $errors;
    }

    function login(string $username_or_email, string $password, int $rememberme) : array {
        $errors = [];
        $exists = $this->userExists($username_or_email);
        $password = md5($password);
        if(! $exists){
            $errors[] = self::USERNAME_EXISTS;
            return $errors;
        }
        $user = User::where(['username' => $username_or_email, 'password' => $password])->first();
        if($user === null) {
            $user = User::where(['email' => $username_or_email, 'password' => $password])->first();
        }
        if($user !== null)
        {
            if($user->is_active === 0){
                $errors[] = self::ACCOUNT_DELETED;
                return $errors;
            }
            //we create a session ID
            $session_id = rand(00000000, 99999999);
            //We create a session with that ID;
            $_SESSION['session_id'] = $session_id;
            $user->session_id = $session_id;
            $user->save();
            $errors[] = $session_id;
            return $errors;
        }else{
            $errors[] = self::DATA_WRONG;
        }
        return $errors;
    }

    //Auxiliar functions
    function userExists(string $username_or_email) : bool {
        $exists = false;
        //check if exists by user
        $user = User::where('username', $username_or_email)->first();
        if($user !== null) {
            $exists=true;
        }
        //check if exists by email
        $user = User::where('email', $username_or_email)->first();
        if($user !== null) {
            $exists=true;
        }
        return $exists;
    }

    private function createSessionCookie(Response $response,int $session_id): Response {
        return FigResponseCookies::set(
            $response,
            SetCookie::create('session_id')
                ->withMaxAge(3600)
                ->withValue((string) $session_id)
        );
    }
}
