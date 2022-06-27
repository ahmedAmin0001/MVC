<?php

namespace App\Controllers;
use Core\View;
class Home extends \Core\Controller{

  protected function indexAction(){
    View::renderTemplate('Home/index.html');
    //\App\Mail::send('ahmad.aminn7@gmail.com','Test Purpose', 'Hello From Localhost 2');
  }
  protected function before(){

  }
  protected function after(){
    
  }
}