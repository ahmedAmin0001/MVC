<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\FlashMessages;

class Myprofile extends Authenticate{

  public function before(){
    Parent::before();
    $this->user = Auth::getUser();
  }
  public function after(){
    
  }
  public function indexAction()
    {
        View::renderTemplate('/profile/myprofile.html');
    }

    protected function editNameAction(){
      View::renderTemplate('/profile/editName.html');

    }

    
    protected function editEmailAction(){
      View::renderTemplate('/profile/editEmail.html');
    }

    protected function editPasswordAction(){
      View::renderTemplate('/profile/editPassword.html');
    }

    protected function saveAction(){
      //$user = Auth::getUser();
      if($this->user->updateProfile($_POST)){
        FlashMessages::addMessage('Changes saved');
        $this->redirect('/myprofile/index');
      } else{
        if(isset($_POST['name'])){
          View::renderTemplate('/profile/editName.html',['user' => $this->user]);
        } else if(isset($_POST['email'])){
          View::renderTemplate('/profile/editEmail.html',['user' => $this->user]);
        } else if(isset($_POST['password'])){
          View::renderTemplate('/profile/editPassword.html',['user' => $this->user]);
        }
      }
    }
}