<?php
//require_once("../src/GitOdin.php"); //Without Composer
require_once("../vendor/autoload.php"); //With Composer
use GitOdin\GitOdin;

/**
 * Setup
 *
 * You need to Start GitOdin and give it your key to Connect with.
 */
	$GitOdin = new GitOdin('key', 'secret', 'Access Point');

	$express_response = $GitOdin->send(new Event(
		"channelName",
		"eventName",
		"someData"
	));
	echo $express_response;

/**
 * Bundled Request
 *
 * Using the Bundeled Request you can send many events at once.
 * If you have set the Defaults of set_channel and set_event, they will be used.
 */
	$bundled_response = $GitOdin->send(new EventGroup(
		new Event(
			"server",
			"pageEvents",
			"reload"
		),
		new Event(
			"updates",
			"newData",
			array("Something In the chat");
		),
		new Authentication(
			"SOCKETID",
			"CHANNELID",
			Authentication::Allow /* This is the same thing as True */
		),
		new Authentication(
			"SOCKETID",
			"CHANNELID",
			true
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
 * @link http://GitOdin.io/documentation
 */
	$GitOdin->add(new Event(
		"server",
		"pageEvents",
		"reload"
	))->add(new Event(
		"updates",
		"newData",
		array("Something In the chat");
	));
	$queue_response = $GitOdin->send();
	echo $queue_response;

print_r($GitOdin->errors); // Show the Tracked the Errors
?>
