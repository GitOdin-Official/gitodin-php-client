<?php

require_once("../vendor/autoload.php"); // Composer Method, Loading by PSR4
//require_once("../src/GitOdin_load.php"); // Manual Load, no PSR4 Autoload

use GitOdin\GitOdin;
use GitOdin\Request\Event;
use GitOdin\Request\Authentication;

/**
 * @link http://php.net/manual/en/function.sleep.php#118635
 */
function msleep($time) {
    usleep($time * 1000000);
}


// GITODIN_SECRET
// GITODIN_SERVER
// GITODIN_SERVERAUTH
$please = GitOdin::summon("*", "https://na.gitodin.com", "https://na.gitodin.com/auth"); // Get from Laravel ENV System

$load = 1555200; // Max

for($x = 0; $x <= $load; $x++){

  //msleep(0.05); // Delay for Half Second

  $please
    ->add(new Event(
      "gitodin:gitodin_demo",
      "demo",
      array(
        "username" =>"DEV",
        "message" => "1234"
      )
    ));
  $w = $please->send();

  echo $x.": ".$please->lastHTTPCode." (". $please->lastRequestRoundTime .") ". $please->lastRequestTime ." sec\n";
}


print_r($please->errors);


?>
