<?php

require_once("../src/pushthis.php");

// PUSHTHIS_SECRET
// PUSHTHIS_SERVER
// PUSHTHIS_SERVERAUTH
$please = new Pushthis(); // Get from Laravel ENV System

$please->add(new Event(
  "home",
  "new_messge",
  array("Noah", "Hello")
));


?>
