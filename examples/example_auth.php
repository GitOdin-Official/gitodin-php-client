<?php
//require_once("../src/pushthis.php"); //Without Composer
require_once("../vendor/autoload.php"); //With Composer
use Pushthis\Pushthis;

/**
 * Setup
 *
 * You need to Start Pushthis and give it your key to Connect with.
 */
	$pushthis = new Pushthis('key', 'secret', 'Access Point');

/**
 * Allow a Connection to a private Channel
 */
	$auth_request = $pushthis->authorize(true, "demoChannel", "FadKJfypim1apPVBAAAJ");
	echo $auth_request;

print_r($pushthis->errors); // Show the Tracked the Errors
?>
