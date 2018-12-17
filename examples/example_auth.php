<?php

require_once("../vendor/autoload.php"); // Composer Method, Loading by PSR4
//require_once("../src/GitOdin_load.php"); // Manual Load, no PSR4 Autoload

use GitOdin\GitOdin;
use GitOdin\Request\Authentication;

/**
 * Setup
 *
 * You need to Start GitOdin and give it your key to Connect with.
 */
$GitOdin = GitOdin::summon('*', 'Server', 'Auth Gateway');

/**
 * Allow a Connection to a private Channel
 */
$auth_request = $GitOdin->authorize(
 new Authentication(
	 "FadKJfypim1apPVBAAAJ",
	 "demoChannel",
	 Authentication::Allow /* This is the same thing as True */
 )
);
echo $auth_request;

print_r($GitOdin->errors); // Show the Tracked the Errors
?>
