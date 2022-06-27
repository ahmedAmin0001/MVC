<?php

namespace App\Controllers;
use Core\View;
use App\Models\User;
use Core\Router;

class PasswordReset extends \Core\Controller{

  protected function forgetPasswordAction(){
    View::renderTemplate('Password/forget.html');
  }

  public function requestResetAction(){
    User::resetingPasswordP1($_POST['email']);
    View::renderTemplate('Password/message.html');
  }
  public function resetAction(){
    $url = explode('/', $_SERVER['QUERY_STRING']);
    @$token = $url[2];
    $user = User::resetingPasswordP3($token);
    if($user){
      View::renderTemplate('Password/reset.html', ['token' => $token]);
    } else{
      echo "Invalid Token or Expired token Please request a new one!";
    }
  }

  protected function resetPasswordAction(){
    @$token = $_POST['token'];
    $user = User::resetingPasswordP3($token);
    if($user){
      if($user->resetingPasswordP4($_POST['password'], $_POST['passwordConf'])){
        View::renderTemplate('Password/reset_success.html');
      } else{
        View::renderTemplate('Password/reset.html', ['token' => $token, 'user' => $user]);

      }
    } else{
      echo "Invalid Token or Expired token Please request a new one!";
    }
  }

  public function before(){

  }
  public function after(){
    
  }
}