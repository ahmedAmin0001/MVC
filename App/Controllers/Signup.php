<?php

namespace App\Controllers;
use Core\View;
use \App\Models\User;

class Signup extends \Core\Controller{

  public function before(){

  }
  public function after(){
    
  }
  protected function newAction(){
    View::renderTemplate('Signup/signup.html');
  }

  protected function createAction(){
    $user = new User($_POST); //passing the post array to the constructor.
    if($user->save()){
      $user->sendActivationToken();
      $this->redirect('/Signup/success');
    } else{
      View::renderTemplate('Signup/signup.html', ['user' => $user]);
    }

  }
  protected function successAction()
    {
      View::renderTemplate('Signup/success.html');
    }

    protected function activateAction(){
      $url = explode('/', $_SERVER['QUERY_STRING']);
      $token = $url[2];
      
      if(User::activateAccount($token)){
        $this->redirect('/signup/activated');
      } else{
        echo "Invalide token";
      } //edit it
    }

    protected function activatedAction(){
      View::renderTemplate('Signup/activated.html');
    }

}