<?php

namespace Core;

class View{

  public static function render($view, $args = []){
    extract($args, EXTR_SKIP); //#1
    $file = "../App/Views/$view";

    if(is_readable($file)){
      require $file;
    } else{
      throw new \Exception("$file not found ya negm!");
    }
  }

  //renedring using twig
  public static function renderTemplate($template, $args = [])
    {
        static $twig = null;

        if ($twig === null) {
          $loader = new \Twig_Loader_Filesystem(dirname(__DIR__) . '/App/Views');
          $twig = new \Twig_Environment($loader);
          $twig->addGlobal('session', $_SESSION);
          //$twig->addGlobal('is_logged_in', \App\Auth::isLoggedIn());
          $twig->addGlobal('current_user', \App\Auth::getUser());
          $twig->addGlobal('flash_messages', \App\FlashMessages::getMessages());
        }

        echo $twig->render($template, $args);
    }

}

// 1: transform the associative array to multiple variables