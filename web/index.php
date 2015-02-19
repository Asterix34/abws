<?php

// Params
define('DEBUG', true);
// route prefix
define('PREFIX', "/ws/v1");
// duration in seconds
define('DEFAULT_DURATION', 3600); // 1h

require_once __DIR__."/../vendor/autoload.php";

// we load a new Silex app
$app = new Silex\Application();

// debug
$app['debug'] = DEBUG;
$params = array();

// Get some credentials with predef duration
$app->get(PREFIX."/getCredentials", function () use ($params) {
	return getCredentials();
});

$app->get(PREFIX."/getCredentials/{duration}", function ($duration) use ($params) {
	return getCredentials($duration);
});

$app->run();

// functions
function getCredentials($duration = DEFAULT_DURATION) {
	return "Requested new credentials for ".$duration." seconds";

}