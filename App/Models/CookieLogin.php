<?php

namespace App\Models;
use \App\Models\User;
use PDO;

class CookieLogin extends \Core\Model{
  public static function findByToken($token){
    $sql = "SELECT * FROM remembered_logins WHERE token_hash = :token_hash";
    
    $db = static::conn();
    $stmt = $db->prepare($sql);

    $stmt->bindParam(':token_hash', $token, PDO::PARAM_STR);

    //return the data as an object of the class 'CookieLogin'
    $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class()); //->class namespace

    $stmt->execute();

    return $stmt->fetch();
  }

  public function getUser(){
    return User::findById($this->user_id);
  }

  public function hasExpired(){
    return strtotime($this->expires_at) < time();
  }

  public function deleteCookieFromDB($token){
    $sql = "DELETE FROM remembered_logins
     WHERE token_hash = :token_hash";

    $db = static::conn();
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':token_hash', $token, PDO::PARAM_STR);
    
    $stmt->execute();
  }
}