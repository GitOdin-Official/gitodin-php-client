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
	$pushthis->setPem("cacert.pem"); // Enable SSL Verification

/** 
 * Set some Default Data for Express Request
 *
 * Using this you can make requests with less info to put in!
 */
	$pushthis->set_channel("demoChannel");
	$pushthis->set_event("newMessages");

/**
 * Express Request
 *
 * Using the Info Defined above, set_channel and set_event, make a request.
 */
	$express_response = $pushthis->send(array(
		'username' => 'bob dole',
		'message'  => 'omg soo cool'
	));
	echo $express_response;

/**
 * Bundled Request
 *
 * Using the Bundeled Request you can send many events at once.
 * If you have set the Defaults of set_channel and set_event, they will be used.
 */
	$bundled_response = $pushthis->send(array(
		array(
			'event' => 'example-even0t',
			'channel' => 'example-channel00',
			'data' => [
				'username' => 'bob dole',
				'message'  => 'omg soo cool'
			]
		),
		array(
			'event' => 'example-event4',
			'channel' => 'example-channel41',
			'data' => [
				'username' => 'bob dole',
				'message'  => 'omg soo cool'
			]
		)
	));
	echo $bundled_response;


/**
 * Queued Request
 *
 * Using the Message Queue you can add as many payloads
 *  you want to the request.
 * 
 * If the Request Fails you may be reaching the Limit of the Post Size.
 * Please refer to the Docs for Help.
 * @link http://pushthis.io/documentation
 */
	$pushthis->add(array(
		'event' => 'event',
		'channel' => 'python',
		'data' => [
			'username' => 'john_doe',
			'message'  => 'FREE MONEY'
		]
	));
	$pushthis->add(array(
		'event' => 'broadcast',
		'channel' => 'bitcoin',
		'data' => [
			'username' => 'bob_dynl',
			'message'  => 'FREE BITCOIN'
		]
	));
	$queue_response = $pushthis->send();
	echo $queue_response;
	
print_r($pushthis->errors); // Show the Tracked the Errors
?>