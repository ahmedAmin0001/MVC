<?php

namespace App\Controllers;

use App\Models\User;

class EmailValidate extends \Core\Controller{
  public function before(){

  }
  public function after(){

  }
  //AJAX
  public function validateEmailAction(){
    $is_valid = ! User::findByEmail($_GET['email']);
    header('Content-Type: application/json');
    echo json_encode($is_valid);
  }
}