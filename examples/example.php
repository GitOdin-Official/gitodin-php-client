<?php
$start = microtime(true);
header("Content-Type: text/plain");
require_once("../pushthis.php");
use PushThis\PushThis;

## Ignore. Calling the Config File with the Data in it.
$c = json_decode(file_get_contents("config.json"), true);

// Setup
	$ports = new PushThis($c['key'], $c['secret']);

// Set some Default Infor for Express Request
	$ports->set_channel("pushthisChat");
	$ports->set_event("newMessages");

// Do Express Request
	echo $ports->send("MY STRING");
	echo "\n";
	echo $ports->send(array(
		'username' => 'bob dole',
		'message'  => 'omg soo cool'
	));
	echo PHP_EOL;

// Do a Full Request
	echo $ports->send(array(
		'event' => 'example-eventH',
		'channel' => 'example-channelC',
		'data' => [
			'username' => 'bob dole',
			'message'  => 'omg soo cool'
		]
	));
	echo PHP_EOL;
	echo $ports->send(array(
		'event' => 'example-event',
		'data' => [
			'message' => 'Hello World'
		]
	));
	echo PHP_EOL;

// Do a Bundle Request
	echo $ports->send(array(
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
	echo PHP_EOL;

// Do a Queue Request
	$ports->add(array(
		'event' => 'event',
		'channel' => 'python',
		'data' => [
			'username' => 'john_doe',
			'message'  => 'FREE MONEY'
		]
	));
	$ports->add(array(
		'event' => 'broadcast',
		'channel' => 'bitcoin',
		'data' => [
			'username' => 'bob_dynl',
			'message'  => 'FREE BITCOIN'
		]
	));
	echo $ports->send();


echo PHP_EOL.(microtime(true) - $start);
?>