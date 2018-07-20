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

/**
 * Allow a Connection to a private Channel
 */
	$auth_request = $GitOdin->authorize(true, "demoChannel", "FadKJfypim1apPVBAAAJ");
	echo $auth_request;

print_r($GitOdin->errors); // Show the Tracked the Errors
?>
