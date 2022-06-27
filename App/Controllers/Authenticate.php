<?php

namespace App\Controllers;

abstract class Authenticate extends \Core\Controller{

  protected function before(){
    $this->requireLogin();
  }

}