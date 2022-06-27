<?php

namespace App\Models;

use PDO;
use \App\Token;
use \App\Mail;

class User extends \Core\Model{

  public $errors = [];

  public function __construct($data = []){ //optional argument
    
    foreach ($data as $key => $value) {
      $this->$key = $value;
    }
  }

  public function save()
  {
    $this->validateName();
    $this->validateEmail();
    $this->validatePassword();

    $token = new Token();
    $this->hashedToken = $token->getHashedToken();

    if(empty($this->errors)){

      $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
      $sql = "INSERT INTO users (name, email, password, activation_token) 
              VALUES (:name, :email, :password, :activation_token)";
  
      $db = static::Conn();
      $stmt = $db->prepare($sql);
  
      $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
      $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
      $stmt->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
      $stmt->bindValue('activation_token', $this->hashedToken, PDO::PARAM_STR);
  
      return $res = $stmt->execute();
    }
    return false;
  }
  public function sendActivationToken(){
    $url = 'http://' . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $this->hashedToken;

    $text = "Please click on the following URL to reset your password: $url";
    $html = "Please click <a href=\"$url\">here</a> to reset your password.";
    echo $this->hashedToken;
    Mail::send($this->email, 'Account activation', $text, $html);
  }

  protected function validateName(){
     if($this->name == ""){
      $this->errors[] = "Please Provide a name";
    } else if(strlen($this->name) < 4){
      $this->errors[] = "Name can't be less than 4 characters";
    } else if(strlen($this->name) > 50){
      $this->errors[] = "Name can't be more than 50 characters";
    }
  }

  protected function validateEmail(){
    if(filter_var($this->email, FILTER_VALIDATE_EMAIL) === false){
      $this->errors[] = "Please Provide a valid email adress";
    } else if(static::findByEmail($this->email)){
      $this->errors[] = "Email adress already taken";
    }
  }

  protected function validatePassword(){
    //password
    if(preg_match('/.*[a-z]+.*/i', $this->password) == 0){
      $this->errors[] = "Please Provide a Password of letters, Capital letters and numbers";
    } else if(strlen($this->password) < 12){
      $this->errors[] = "Password must at least contain 12 character";
    } else if (preg_match('/.*\d+.*/i', $this->password) == 0) {
      $this->errors[] = 'Password must contain at least one number';
    } else if(preg_match('/.*[A-Z]+.*/', $this->password) == 0){
    $this->errors[] = 'Password must contain at least one CAPITAL LETTER';
    } else if(strlen($this->password) > 50){
    $this->errors[] = "Please Provide a smaller Password";
    }

  //Password confrmation:
  if($this->password !== $this->passwordConf){
    $this->errors[] = "Password must match confirmation";
    }
  }

  //static, to use it during validation with json
  public static function findByEmail($email){
    $sql = "SELECT * FROM users WHERE email = :email";
    $db = static::conn();
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    //return the data as an object of the class 'user'
    $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class()); //->class namespace
    $stmt->execute();

    return $stmt->fetch();
  }

  public static function authenticate($email, $password){
    $user = static::findByEmail($email);
    if($user){
      if(password_verify($password, $user->password) && $user->is_active){
        return $user;
      }
    }
    return false;
  }

  public static function findById($id){
    $sql = "SELECT * FROM users WHERE id = :id";
    
    $db = static::conn();
    $stmt = $db->prepare($sql);

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    //return the data as an object of the class 'user'
    $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class()); //->class namespace

    $stmt->execute();

    return $stmt->fetch();
  }

  public function rememberLogin(){
    $token = new Token();
    $this->hashedToken = $token->getHashedToken();

    $this->expireDate = time() + 60 * 60 * 24 * 30; //30 days

    $sql = "INSERT INTO remembered_logins (token_hash, user_id, expires_at)
    VALUES (:token_hash, :user_id, :expires_at)";
    
    $db = static::conn();
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':token_hash', $this->hashedToken, PDO::PARAM_STR);
    $stmt->bindValue(':user_id', $this->id, PDO::PARAM_INT);
    $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $this->expireDate), PDO::PARAM_STR);

    return $stmt->execute();
  }

  public static function resetingPasswordP1($email){
    $user = static::findByEmail($email);

    if($user){
      if($user->resetingPasswordP2()){
        $user->sendPasswordResetEmail();
      }
    }
  }

  protected function resetingPasswordP2(){
    $token = new Token();
    $hashed_token = $token->getHashedToken();
    $this->passwordResetToken = $token->getvalue();

    $expiry_timestamp = time() + 60 * 60;  //  1 hour from now

    $sql = 'UPDATE users
            SET password_reset_token = :token_hash,
            password_reset_token_expires = :expires_at
            WHERE id = :id';

    $db = static::Conn();
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
    $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expiry_timestamp), PDO::PARAM_STR);
    $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

    return $stmt->execute();

  }

  protected function sendPasswordResetEmail(){
    $url = 'http://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->passwordResetToken;

    $text = "Please click on the following URL to reset your password: $url";
    $html = "Please click <a href=\"$url\">here</a> to reset your password.";
    echo $this->passwordResetToken;
    Mail::send($this->email, 'Password reset', $text, $html);
  }

  public static function resetingPasswordP3($token){
    $sql = 'SELECT * FROM users
            WHERE password_reset_token = :token';

    $db = static::Conn();
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':token', $token, PDO::PARAM_STR);
    $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
    
    $stmt->execute();

    $user = $stmt->fetch();

    if($user){
      //Check password reset token hasn't expired
      if(strtotime($user->password_reset_token_expires) > time()){
        return $user;
      }
    }
  }

  public function resetingPasswordP4($password, $passwordConf){
    $this->password = $password;
    $this->passwordConf = $passwordConf;
    $this->validatePassword();
    if(empty($this->errors)){
      
      $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

      $sql = 'UPDATE users
              SET password = :password_hash,
                  password_reset_token = NULL,
                  password_reset_token_expires = NULL
              WHERE id = :id';

      $db = static::Conn();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
      $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);

      return $stmt->execute();
    }
    return false;
  }

  public static function activateAccount($token){
    $sql = 'UPDATE users
            SET is_active = 1, activation_token = null
            WHERE activation_token = :token';

    $db = static::Conn();
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':token', $token, PDO::PARAM_STR);
    
    return $stmt->execute();
  }

  public function updateProfile($data){
    if(isset($data['name'])){
      $this->name = $data['name'];

      $this->validateName();

      if(empty($this->errors)){
        $sql = 'UPDATE users SET name = :name WHERE id = :id';
        $db = static::Conn();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        return $stmt->execute();
      }
  } else if(isset($data['email'])){
    $this->email = $data['email'];

    $this->validateEmail();

    if(empty($this->errors)){
      $sql = 'UPDATE users SET email = :email WHERE id = :id';
      $db = static::Conn();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
      $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
      return $stmt->execute();
    }

  } else if(isset($data['password']) && isset($data['passwordConf'])){
    echo "password";
    $this->password = $data['password'];
    $this->passwordConf = $data['passwordConf'];
    $this->validatePassword();

    if(empty($this->errors)){
      $sql = 'UPDATE users SET password = :password WHERE id = :id';
      $db = static::Conn();
      $stmt = $db->prepare($sql);

      $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);

      $stmt->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
      $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
      
      return $stmt->execute();
    }
    return false;
    }
  }
}