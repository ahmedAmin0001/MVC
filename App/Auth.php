<?php

namespace App;

use App\Models\User;
use App\Models\CookieLogin;

class Auth{

  public static function login($user, $rememberMe){

    session_regenerate_id(true); //to avoid session fixition attacks, true means that the old session will be deleted.
    $_SESSION['user_id'] = $user->id;

    if($rememberMe){

      if($user->rememberLogin()){
        setrawcookie('remember_me', $user->hashedToken,
         $user->expireDate, '/');
      }
    }
  }

  public static function logout(){

    //logout the cookie
    static::logoutCookie();     
      // Unset all of the session variables
      $_SESSION = [];

      // Delete the session cookie
      if (ini_get('session.use_cookies')) {
          $params = session_get_cookie_params();

          setcookie(
              session_name(),
              '',
              time() - 42000,
              $params['path'],
              $params['domain'],
              $params['secure'],
              $params['httponly']
          );
      }

    // Finally destroy the session
    session_destroy();

  }

  
  public static function isLoggedin(){
    return isset($_SESSION['user_id']);
  }
  
  public static function rememberRequestedPage()
    {
        $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
    }

    /**
     * Get the originally-requested page to return to after requiring login, or default to the homepage
     *
     * @return void
     */
    public static function getReturnToPage()
    {
        return $_SESSION['return_to'] ?? '/';
    }

    public static function getUser(){
      if(isset($_SESSION['user_id'])){
        return User::findById($_SESSION['user_id']);
      } else{
        return static::loginWithCookies();
      }
    }

    protected static function loginWithCookies(){
      $cookie = $_COOKIE['remember_me'] ?? false;

      if($cookie){
        // object from the class 'CookieLogin'
        $findByToken = CookieLogin::findByToken($_COOKIE['remember_me']);

        if($findByToken && ! $findByToken->hasExpired()){
          
          $user = $findByToken->getUser();

          static::login($user, false);
          
          return $user;
        }
      }
    }

    protected static function logoutCookie(){
    $cookie = $_COOKIE['remember_me'] ?? false;
   
    if($cookie){
      //Created object so we can run it's methods
      $rememberedLogin = CookieLogin::findByToken($cookie); 

      if($rememberedLogin){
        $rememberedLogin->deleteCookieFromDB($cookie);
      }
      setcookie('remember_me', '', time() - 3600);
    }
    }
}