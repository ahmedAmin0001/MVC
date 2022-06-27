<?php

namespace Core;

class Router{

  protected $routes = [];    //contains routes added form front controller.
  protected $matches = [];   //contains matched results between routes & url.

  public function addRoutes($route , $params = []){   //#1
    // replacing backslash with escaped backslash
    $route = preg_replace('/\//', '\\/', $route);

    // converting any thing between {} to a capture group
    $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route);

    // Convert variables with custom regular expressions e.g. {id:\d+} #2
    $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);

    // adding start and end delimiters, case insensitive flag
    $route = '/^' . $route . '$/i';

    $this->routes[$route] = $params;
  }

  public function getRoutes(){
    echo "<pre>";
    print_r($this->routes);
    echo "</pre>";
  }

  public function match($url){
    foreach ($this->routes as $route => $params){
      if (preg_match($route, $url, $matches)){
        foreach ($matches as $key => $value) {
          if(is_string($key)){
            $params[$key] = $value;
          }
        }
        $this->params = $params;
        return true;
      }
    }
    return false;
  }

  //get the current matched parameters ex: action => pla
  public function getParams(){
    echo "<pre>";
    print_r($this->params);
    echo "</pre>";
  }

  //@@ Dispatching:

  //preparing controllers and actions as we named it in root folder

  public function prepareController($str){
    return str_replace(' ', '', ucwords(str_replace('-', '', $str)));
  }
  public function prepareAction($str){
    return lcfirst($this->prepareController($str));
  }

  public function getNamespace(){
    $namespace = "App\Controllers\\";

    if(array_key_exists("namespace", $this->params)){
      $namespace .= $this->params['namespace'] . "\\";
    }
    return $namespace;
  }

  //remove query string, ex: ?page=1&post=412
  public function removeQueryString($url){
    if($url != ""){
      $queries = explode ("&", $url, 2);
      if(strpos ($queries[0], "=") === false){
        $url = $queries[0];
      } else{
        $url = "";
      }
    }
    return $url;
  }

  public function dispatch($url){
    
    $url = $this->removeQueryString($url);

    if($this->match($url)){
      //Handling controller:
      $controller = $this->params['controller'];
      $controller = $this->prepareController($controller);
      $controller = $this->getNamespace() . $controller;

      if(class_exists($controller)){
        $controllerObj = new $controller($this->params); //#3

        //Handling action:
        $action = $this->params['action'];
        $action = $this->prepareAction($action);

        //filling a security hole(explained on section 4)
        if (preg_match('/action$/i', $action) == 0) {
          $controllerObj->$action();
      } else{
        throw new \Exception("Method $action (in controller $controller) not found ya negm!");
      }
      } else{
        throw new \Exception("Controller class $controller not found ya negm!");
      }
    } else{
      throw new \Exception('No route matched ya negm!', 404);
    }
  }
}


/*
3: passing matched routes array to the constructor of 'controller class' to be able to access it in all controllers.
*/
