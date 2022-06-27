<?php

//composer autoloader to load packetgs
require '../vendor/autoload.php';


//autoloader
spl_autoload_register(function ($class) {
  $root = dirname(__DIR__);   // get the parent directory
  $file = $root . '/' . str_replace('\\', '/', $class) . '.php';
  if (is_readable($file)) {
    require $file;
  }
});

/**
 * Error and Exception handling
 */
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

//starting the session after errors handlers are done
session_start();

//Routing:
$url = $_SERVER['QUERY_STRING'];

$obj = new Core\Router();
$obj->addRoutes('', ['controller' => 'Home', 'action' => 'index']);
$obj->addRoutes("{controller}/{action}");
$obj->addRoutes("{namespace}/{controller}/{action}");
$obj->addRoutes('login', ['controller' => 'Login', 'action' => 'new']);
$obj->addRoutes('signup', ['controller' => 'Signup', 'action' => 'new']);
$obj->addRoutes('logout', ['controller' => 'Login', 'action' => 'destroy']);
//custom regex as the token in hexadecimal (nums, a-f)
$obj->addRoutes('passwordreset/reset/{token:[\da-f]+}', ['controller' => 'PasswordReset', 'action' => 'reset']);
$obj->addRoutes('signup/activate/{token:[\da-f]+}', ['controller' => 'signup', 'action' => 'activate']);

$obj->match($url);
//$obj->getParams();
$obj->dispatch($url);
