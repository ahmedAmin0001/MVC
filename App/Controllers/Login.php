<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
use \App\Auth;
use \App\FlashMessages;

class Login extends \Core\Controller{

  public function before(){

  }
  public function after(){

  }

  protected function newAction(){
    View::renderTemplate('Login/login.html');
  }

  protected function createAction(){
    $user = User::authenticate($_POST['email'], $_POST['password']);
    $rememberMe = isset($_POST['remember_me']);

    if($user){

      Auth::Login($user, $rememberMe);
      FlashMessages::addMessage('Login successfuly!', );
      $this->redirect(Auth::getReturnToPage());
      //unset($_SESSION['return_to']);

    } else{
      FlashMessages::addMessage('Incorrect email or password', FlashMessages::WARNING);
      FlashMessages::addMessage('If you didn\'t activate your account please check your email and try again', FlashMessages::INFO);
      View::renderTemplate('Login/login.html', [
       'email' => $_POST['email'],
       'remember_me' => $rememberMe
    ]);
    }
  }

  protected function destroyAction(){
    Auth::Logout();
    $this->redirect('/login/showlogoutmessage');
  }

  protected function showLogoutMessageAction(){
    FlashMessages::addMessage('Logout successful', FlashMessages::INFO);
    $this->redirect('/');
  }
}
