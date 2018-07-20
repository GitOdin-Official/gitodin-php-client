<?php

require_once("../src/GitOdin.php");

use GitOdin\GitOdin;
use GitOdin\Event;

// GITODIN_SECRET
// GITODIN_SERVER
// GITODIN_SERVERAUTH
$please = new GitOdin("*", "https://na.gitodin.com", "https://auth.gitodin.com"); // Get from Laravel ENV System

$please->add(new Event(
  "home",
  "new_messge",
  array("Noah", "Hello")
));
$please->send();

print_r($please->errors);


?>
