<?php
namespace Core;

use \App\Auth;
use \App\FlashMessages;

abstract class Controller{
  
  //passing route parameters to all controllers inherts from here
  protected $routeParameters = [];
  public function __construct($routeParameters){
    $this->routeParameters = $routeParameters;
  }


  
  //run code before and after the excuting of a method:
   //ex: for cheking if the user is logged in
  public function __call($method, $args){
    $method .= "Action";
    if(method_exists($this, $method)){
      if($this->before() !== false){
        call_user_func([$this, $method], $args);
        $this->after();
      }
    } else{
      //echo "Method $method doesn't exists on Controller: " . get_class($this);
      throw new \Exception("Method $method not found in controller " .
      get_class($this));
    }
  }

  public function redirect($url){
    header('Location: http://' . $_SERVER['HTTP_HOST'] . $url, true, 303);
    exit;
  }

  public function requireLogin(){
    if(! Auth::isLoggedin()){
      FlashMessages::addMessage('Please login first');
      Auth::rememberRequestedPage();
      $this->redirect("/login");
    }
  }
}