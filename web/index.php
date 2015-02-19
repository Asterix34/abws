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

// we link it to a DB
$dbParams = array(
	'dbname' => 'abws',
	'user' => 'abws',
	'password' => 'password',
	'host' => 'localhost',
	'driver' => 'pdo_mysql'
);
$app->register(new Silex\Provider\DoctrineServiceProvider(), array('db.options' => $dbParams));


// debug
$app['debug'] = DEBUG;
$params = array();

// Get some credentials with predef duration
$app->get(PREFIX."/getCredentials", function () use ($app, $params) {
	return getCredentials($app);
});

$app->get(PREFIX."/getCredentials/{duration}", function ($duration) use ($app, $params) {
	return getCredentials($app, $duration);
});

$app->run();

// functions
function getCredentials($app, $duration = DEFAULT_DURATION) {
	$cp = new CredentialsProvider();
	$creds = new Credentials("michel", "1234", time() + $duration);
	return $app->json($creds);

}

// entity Credentials
class Credentials {
	public $login, $password, $validUntil;
	function __construct($l, $p, $v) {
		$this->login = $l;
		$this->password = $p;
		$this->validUntil = $v;
	}
}