<?php

namespace App;

class Token{
  protected $token;


  /*
    the optional argiment below, is used to deal with an existing token,,, NOT VERY understood
  */
  public function __construct($tokenValue = null){
    if($tokenValue){
      $this->token = $tokenValue;
    } else{
      $this->token = bin2hex(random_bytes(16)); 
    }
  }

  public function getToken(){
    return $this->token;
  }

  public function getHashedToken(){
    return hash_hmac('sha256', $this->token, \App\Config::SECRET_KEY);
  }

  public function getValue(){
    return $this->getHashedToken();
  }
}