<?php declare(strict_types = 1);

namespace PwPop\SlimApp\Controller;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PwPop\SlimApp\models\User;
use Slim\Http\UploadedFile;

final class SignUpController
{
    //pictures
    private const UPLOADS_DIR = __DIR__ . '/../../public/uploads';
    private const INVALID_FORM = "Please fill all the fields!";
    private const UNEXPECTED_ERROR = "An unexpected error occurred uploading the file '%s'...";
    private const INVALID_EXTENSION_ERROR = "The received file extension '%s' is not valid";
    private const INVALID_SIZE = 'Please upload images that are less than 500KB!';
    private const ALLOWED_EXTENSIONS = ['jpg', 'png'];
    private const NAME_ERROR = 'You need to introduce an alphanumeric name';
    private const USERNAME_ERROR_ALPHA = 'You need to introduce an alphanumeric username';
    private const USERNAME_ERROR_LENGHT = "Username can't exceed 20 characters";
    private const UNIQUE_USERNAME = "Username already taken :(";
    private const VALID_EMAIL = "The provided email is not valid";
    private const VALID_PHONE = "The provided phone number is not valid";
    private const BIRTHDATE_FORMAT = "The birthdate date must be in format of dd/mm/yyyy";
    private const PASSWORD_ERROR_LENGHT = "Password must have at least 6 characters";
    private const PASSWORD_ERROR_EMPTY = "You must provide a password";
    private const PASSWORD_CONFIRM_EMPTY = "confirm password field can't be empty";
    private const PASSWORD_ERROR_MATCH = "Passwords don't match";

    /** @var ContainerInterface */
    private $container;

    /**
     * SignUpController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response) : Response {
        $data = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();
        $errors = $this->checkData($data, $uploadedFiles);
        if(! $errors){
            $name = $data['name'];
            $username = $data['username'];
            $email = $data['email'];
            $birthdate = $data['birthdate'];
            $phone = $data['phone'];
            $password = $data['password'];
            $uploadedFile = $uploadedFiles['picture'];
            if(User::where('username', $username)->first()){
                $errors[] = self::UNIQUE_USERNAME;
                $data['username'] = null;
                return $this->container->get('view')->render($response, 'homepage.html.twig', [
                    'errors' => $errors,
                    'data' => $data,
                ]);
            }
            $register = $this->register($name, $username, $email, $birthdate, 
                                $phone, $password, $uploadedFile);
            if($register){
                $this->container->flash->addMessage('registerSuccess', "You are now registered!");
                return $response->withRedirect($this->container->router->pathFor('home'), 303);
            }
        }else {
            return $this->container->get('view')->render($response, 'homepage.html.twig', [
                'errors' => $errors,
                'data' => $data,
            ]);
        }
    }

    function checkData(array $data, array $uploadedFiles) : array
    {
        $errors = [];
        if($data === null){
            $errors[] = self::INVALID_FORM;
        }
        //name  
        if(! isset($data['name']) || $data['name'] === null){
            $errors[] = sprintf(self::NAME_ERROR);
        }elseif(! ctype_alnum($data['name'])){
            $errors[] = sprintf(self::NAME_ERROR);
        }
        //username
        if(! isset($data['username']) || $data['username'] === null){
            $errors[] = sprintf(self::USERNAME_ERROR_ALPHA);
        }elseif (! ctype_alnum($data['username'])) {
            $errors[] = sprintf(self::USERNAME_ERROR_ALPHA);
        }elseif (strlen($data['username']) > 20) {
            $errors[] = sprintf(self::USERNAME_ERROR_LENGHT);
        }
        //email
        if(! isset($data['email']) || $data['email'] === null){
            $errors[] = sprintf(self::VALID_EMAIL);
        }elseif (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = sprintf(self::VALID_EMAIL);
        }
        //birthdate
        if($data['birthdate'] !== ''){
            if(! preg_match("/^\d{1,2}\-\d{1,2}\-\d{4}$/", $data['birthdate'])){
                $errors[] = sprintf(self::BIRTHDATE_FORMAT); 
            }
        }
        //phone
        if(! isset($data['phone']) || $data['phone'] === ''){
            $errors[] = sprintf(self::VALID_PHONE);
        }elseif ((strlen($data['phone']) !== 11)){
            $errors[] = sprintf(self::VALID_PHONE);
        }elseif (! preg_match("/^\d{3}\-\d{3}\-\d{3}/", $data['phone'])){
            $errors[] = sprintf(self::VALID_PHONE);
        }
        //password
        if(! isset($data['password']) || $data['password'] === ''){  
            $errors[] = sprintf(self::PASSWORD_ERROR_EMPTY);
        }elseif (strlen($data['password']) < 6) {
            $errors[] = sprintf(self::PASSWORD_ERROR_LENGHT);
        }
        //confirm_password
        if(! isset($data['confirmPassword']) || $data['confirmPassword'] === ''){  
            $errors[] = sprintf(self::PASSWORD_CONFIRM_EMPTY);
        }elseif($data['password'] !== $data['confirmPassword']){
            $errors[] = sprintf(self::PASSWORD_ERROR_MATCH);
        }
        //picture
        $uploadedFile = $uploadedFiles['picture'];
        if(strlen($uploadedFile->getClientFilename()) !== 0){
            $uploadedFile = $uploadedFiles['picture'];
            if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
                $errors[] = sprintf(self::UNEXPECTED_ERROR, $uploadedFile->getClientFilename());
            }
            $name = $uploadedFile->getClientFilename();
            $fileInfo = pathinfo($name);
            $format = $fileInfo['extension'];
            if (! $this->isValidFormat($format)) {
                $errors[] = sprintf(self::INVALID_EXTENSION_ERROR, $format);
            }
            if(! $this->isValidSize($uploadedFile->getSize())){
                $errors[] = self::INVALID_SIZE;
            }
        }
        return $errors;
    }

    private function register(string $name, string $username, string $email,
                    string $birthdate, string $phone, string $password, 
                    UploadedFile $uploadedFile) : bool {
        $filename = null;
        if(strlen($uploadedFile->getClientFilename()) !== 0){
            $filename = $this->moveUploadedFile(self::UPLOADS_DIR, $uploadedFile);
        }
        //password encryption
        $pass = md5($password);
        $validation_code = rand(00000000, 99999999);
        $user = new User;
        $user->name = $name;
        $user->username = $username;
        $user->email = $email;
        //birthdate conversion
        if($birthdate){
            $birthdate = date('Y-m-d h:i:s', strtotime($birthdate));
            $user->birthdate = $birthdate;
        }
        $phone = str_replace("-", "", $phone);
        $user->phone = $phone;
        $user->password = $pass;
        $user->validated = 0; //validated status (not validated)
        if($filename){
            $user->image = $filename;
        }
        $user->validation_code = $validation_code;
        $user->save();
        return $this->sendActivationEmail($email, (string) $validation_code);
    }

    //Auxiliar functions
    private function isValidFormat(string $extension): bool {
        return in_array($extension, self::ALLOWED_EXTENSIONS, true);
    }

    private function isValidSize(int $size):bool {
        if($size > 500000) {
            return false;
        }
        return true;
    }

    function moveUploadedFile($directory, UploadedFile $uploadedFile) : string {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
        $filename = sprintf('%s.%0.8s', $basename, $extension);
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
        return $filename;
    }

    function sendActivationEmail(string $email, string $activation_code) : bool {
        $validation_url_header = "pwpop.test/activate?activation_code=".$activation_code;
        $mail = new PHPMailer();
        $data = [];
        $data['validation_url'] = $validation_url_header;
        try {
            $mail->isSMTP();                                 // Set mailer to use SMTP
            $mail->Host       = 'smtp.gmail.com';           // Specify main and backup SMTP servers
            $mail->SMTPAuth   = true;                       // Enable SMTP authentication
            $mail->Username   = 'pwpop.grupo.12@gmail.com'; // SMTP username
            $mail->Password   = 'doQrew-qawkam-9rutwy';     // SMTP password
            $mail->SMTPSecure = 'tls';                      // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = 587;                        // TCP port to connect to
            $loader = new \Twig\Loader\FilesystemLoader(__DIR__ .'/../View/templates');
            $twig = new \Twig\Environment($loader);
            $htmlBody = $twig->render("mail.html.twig", $data);
            $mail->setFrom('pwpop.grupo.12@gmail.com', 'PWPOP Grupo 12 WebMaster');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Activate your PWPOP account!';
            $mail->AddEmbeddedImage('assets/img/logo.png', 'logo');
            $mail->Body    = $htmlBody;
            $mail->AltBody = $message;
            $mail->send();
            return true;
        } catch (Exception $err) {
            echo "Message could not be sent. Mailer Error: { $mail->ErrorInfo }";
            return false;
        }
    }
}
