<?php

require_once("../vendor/autoload.php"); // Composer Method, Loading by PSR4
//require_once("../src/GitOdin_load.php"); // Manual Load, no PSR4 Autoload

use GitOdin\GitOdin;
use GitOdin\Request\Event;
use GitOdin\Request\Authentication;

// GITODIN_SECRET
// GITODIN_SERVER
// GITODIN_SERVERAUTH
$please = GitOdin::summon("*", "https://na.gitodin.com", "https://na.gitodin.com/auth"); // Get from Laravel ENV System

$please
  ->add(new Event(
    "home",
    "new_messge",
    array("Noah", "Hello")
  ))
  ->add(new Authentication(
    "2390h23r09",
    "gitodin:test"
  ));
$please->send();

print_r($please->errors);


?>
