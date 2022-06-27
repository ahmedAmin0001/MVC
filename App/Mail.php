<?php

namespace App;
use App\Config;
use Mailgun\Mailgun;

class Mail{
  public static function send($to, $subject, $text){
    $mg = Mailgun::create('f791764017ae78271e13b682ced09b5f-443ec20e-39065917');
    $domain = "sandbox25296b84150b47d2a94274a32caae812.mailgun.org";
    # Make the call to the client.
    $mg->messages()->send($domain, array(
      'from'	=> 'http://localhost:7070/',
      'to'	=> $to,
      'subject' => $subject,
      'text'	=> $text
    ));
    }
}