<?php
//require_once("../src/pushthis.php"); //Without Composer
require_once("../vendor/autoload.php"); //With Composer
use Pushthis\Pushthis;

/**
 * Setup
 * 
 * You need to Start Pushthis and give it your key to Connect with.
 */
	$pushthis = new Pushthis('key', 'secret');
	$pushthis->setPem("cacert.pem"); // Enable SSL Verification

/** 
 * Set some Default Data for Express Request
 *
 * Using this you can make requests with less info to put in!
 */
	$pushthis->set_channel("demoChannel");
	$pushthis->set_event("newMessages");

/**
 * Allow a Connection to a private Channel
 */
	$auth_request = $pushthis->authorize(true, "demoChannel", "FadKJfypim1apPVBAAAJ");
	echo $auth_request;
?>