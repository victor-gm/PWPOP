<?php declare(strict_types = 1);

namespace PwPop\SlimApp\controller;

use Dflydev\FigCookies\FigResponseCookies;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PwPop\SlimApp\models\User;
use Slim\Http\UploadedFile;

final class ProfileController
{
    private const UPLOADS_DIR = __DIR__ . '/../../public/uploads';
    private const INVALID_FORM = "Please fill all the fields!";
    private const UNEXPECTED_ERROR = "An unexpected error occurred uploading the file '%s'...";
    private const INVALID_EXTENSION_ERROR = "The received file extension '%s' is not valid";
    private const INVALID_SIZE = 'Please upload images that are less than 500KB!';
    private const ALLOWED_EXTENSIONS = ['jpg', 'png'];
    private const NAME_ERROR = 'You need to introduce an alphanumeric name';
    private const VALID_EMAIL = "The provided email is not valid";
    private const VALID_PHONE = "The provided phone number is not valid";
    private const PASSWORD_ERROR_LENGHT = "Password must have at least 6 characters";
    private const PASSWORD_ERROR_MATCH = "Passwords don't match";
    private const BIRTHDATE_FORMAT = "The birthdate date must be in format of dd/mm/yyyy";

    /** @var ContainerInterface */
    private $container;

    /**
     * ProfileController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function loadProfile(Request $request, Response $response) : Response {
        $flashMsg = $this->container->flash->getFirstMessage('updateSuccess');
        if(isset($_SESSION['session_id'])){
            $user = User::where('session_id', $_SESSION['session_id'])->first();
            return $this->container->get('view')->render($response, 'profile.html.twig', [
                'user' => $user,
                'flashMsg' => $flashMsg,
            ]);
        }
        return $this->container->get('view')->render($response, 'page403.html.twig', []);
    }

    public function update(Request $request, Response $response, array $args) : Response
    {
        $uploadedFiles = $request->getUploadedFiles();
        $data = $request->getParsedBody();
        $data['id'] = $args['id'];
        $userToGetImg = User::where('id', $data['id'])->first();
        $data['image'] = $userToGetImg->image;
        if(! isset($_SESSION['session_id'])){
            return $this->container->get('view')->render($response, 'page404.html.twig', []);
        }
        $errors = $this->checkData($data, $uploadedFiles);
        if($errors){
            return $this->container->get('view')->render($response, 'profile.html.twig', [
                'errors' => $errors,
                'user' => $data,
            ]);
        }
        $saveChanges = $this->saveChanges($data, $request);
        if($saveChanges){
            $this->container->flash->addMessage('updateSuccess', "Your profile update has beeen successfull!");
            return $response->withRedirect($this->container->router->pathFor('user'), 303);
        }
        $errors[] = self::PASSWORD_ERROR_MATCH;
        return $this->container->get('view')->render($response, 'profile.html.twig', [
            'errors' => $errors,
            'user' => $data,
        ]);
    }

    function saveChanges(array $data, Request $request) : bool
    {
        $name = $data['name'];
        $email = $data['email'];
        $birthdate = $data['birthdate'];
        $phone = $data['phone'];
        $password = $data['password'];
        $confirm_password = $data['confirmPassword'];
        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['picture'];
        $new_pic = $uploadedFile->getClientFilename();
        if(isset($password) || $password !== '' || $password !== null){
            if(strcmp($password, $confirm_password) !== 0) { //If passwords don't match we don't save changes
                return false; 
            }
        }
        $user= User::where('id', $data['id'])->first(); // We retrieve the user
        $user->name = $name;
        $user->email = $email;
        if($birthdate){
            $birthdate = date('Y-m-d h:i:s', strtotime($birthdate));
            $user->birthdate = $birthdate;
        }
        $phone = str_replace("-", "", $phone);
        $user->phone = $phone;
        if(isset($password) || $password !== ''){
            $password = md5($password);
            $user->password = $password;
        }
        // We retrieve the name of the picture from the database
        $old_pic = $user->image;
        //If there's a new profile picture, we delete the current one
        if($new_pic){
            if(strcmp($old_pic, $new_pic) !== 0)
            {
                $filename = $this->moveUploadedFile(self::UPLOADS_DIR, $uploadedFile); // We upload the new file
                if($filename){
                    $user->image = $filename; // we update the image name into the data base
                }
                // we delete the old picture
                unlink(self::UPLOADS_DIR.'/'.$old_pic);
            }
        }
        $user->save();
        return true;
    }

    public function delete(Request $request, Response $response, array $args) : Response
    {
        if(! isset($args['id'])){
            return $this->container->get('view')->render($response, 'page404.html.twig', []);
        }
        $user = User::where('id', $args['id'])->first(); // We retrieve the user
        $user->is_active = 0;
        $user->favorites()->delete();
        $user->products()->delete();
        unlink(self::UPLOADS_DIR.'/'.$user->image);
        $user->save();
        $this->container->flash->addMessage('buySuccess', "Your account has beeen deleted successfully!");
        unset($_SESSION['session_id']);
        $response = FigResponseCookies::expire($response, 'session_id');
        return $response->withRedirect($this->container->router->pathFor('home'), 303);
    }

    function moveUploadedFile($directory, UploadedFile $uploadedFile) : string {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
        $filename = sprintf('%s.%0.8s', $basename, $extension);
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
        return $filename;
    }

    function checkData(array $data, array $uploadedFiles) : array {
        $errors = [];
        //name
        if($data === null){
            $errors[] = self::INVALID_FORM;
        }
        if(! isset($data['name'])){
            $errors[] = sprintf(self::NAME_ERROR);
        }elseif(! ctype_alnum($data['name'])){
            $errors[] = sprintf(self::NAME_ERROR);
        }
        //email
        if(! isset($data['email'])){
            $errors[] = sprintf(self::VALID_EMAIL);
        }elseif (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = sprintf(self::VALID_EMAIL);
        }
        //birthdate
        if(isset($data['birthdate']) && $data['birthdate'] !== null){
            if(! preg_match("/^\d{1,2}\-\d{1,2}\-\d{4}$/", $data['birthdate'])){
                $errors[] = sprintf(self::BIRTHDATE_FORMAT);
            }
        }
        //phone
        if(! isset($data['phone'])){
            $errors[] = sprintf(self::VALID_PHONE);
        }elseif ((strlen($data['phone']) !== 11)){
            $errors[] = sprintf(self::VALID_PHONE);
        }elseif (! preg_match("/^\d{3}\-\d{3}\-\d{3}/", $data['phone'])){
            $errors[] = sprintf(self::VALID_PHONE);
        }
        //password
        if($data['password'] !== ''){
            if(strlen($data['password']) < 6) {
                $errors[] = sprintf(self::PASSWORD_ERROR_LENGHT);
            }
        }
        //confirm_password
        if($data['confirmPassword'] !== ''){
            if($data['password'] !== $data['confirmPassword']){
                $errors[] = sprintf(self::PASSWORD_ERROR_MATCH);
            }
        }
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

    private function isValidFormat(string $extension): bool {
        return in_array($extension, self::ALLOWED_EXTENSIONS, true);
    }

    private function isValidSize(int $size):bool {
        if($size > 500000) {
            return false;
        }
        return true;
    }
}
