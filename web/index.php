<?php

// Params
define ( 'DEBUG', true );
// route prefix
define ( 'PREFIX', "/ws/v1" );
// duration in seconds
define ( 'DEFAULT_DURATION', 3600 ); // 1h

require_once __DIR__ . "/../vendor/autoload.php";

// we load a new Silex app
$app = new Silex\Application ();

// we link it to a DB
$dbParams = array (
		'dbname' => 'abws',
		'user' => 'abws',
		'password' => 'password',
		'host' => 'localhost',
		'driver' => 'pdo_mysql' 
);
$app->register ( new Silex\Provider\DoctrineServiceProvider (), array (
		'db.options' => $dbParams 
) );

// debug
$app ['debug'] = DEBUG;
$params = array ();

// Get some credentials with predef duration
$app->get ( PREFIX . "/getCredentials", function () use($app, $params) {
	return getCredentials ( $app );
} );

$app->get ( PREFIX . "/getCredentials/{duration}", function ($duration) use($app, $params) {
	return getCredentials ( $app, $duration );
} );

$app->run ();

// functions
function getCredentials($app, $duration = -1) {
	$cp = new CredentialsProvider ( $app );
	$creds = $cp->createCredentials ( $duration );
	return $app->json ( $creds );
}

// entity Credentials
class Credentials {
	public $date, $heureDeb, $heureFin, $login, $mdp, $mdpSSHA;
	function __construct($l, $p, $d) {
		$this->login = $l;
		$this->mdp = $p;
		$this->mdpSSHA = CredentialsProvider::makeSshaPassword($p);
		
		// in the now
		$now = new \DateTime ();
		$this->date = $now->format ( 'Y-m-d' );
		$this->heureDeb = $now->getTimestamp ();
		$this->heureFin = $now->getTimestamp () + $d;
		/*echo "De ".date("Y-m-d H:i:s", $this->heureDeb)." à "
				.date("Y-m-d H:i:s", $this->heureFin);*/
	}
}
class CredentialsProvider {
	private $_app;
	private $_table = "users";
	function __construct($app) {
		$this->_app = $app;
	}
	function createCredentials($duration) {
		// get duration if -1
		if ($duration == -1) {
			$sql = "SELECT bail FROM parametres WHERE idParam=1"; // c moche
			$query = $this->_app['db']->query($sql);
			$duration = $query->fetch()['bail'];
		}
		
		
		$creds = new Credentials ( self::makeRandom(6, true), self::makeRandom(10, false), $duration );
		$this->persist ( $creds );
		return $creds;
	}
	function persist(Credentials $creds) {
		$sql = "INSERT INTO users (date, heureDeb, heureFin, login, mdp, mdpSSHA) 
				VALUES('" . $creds->date . "', " . $creds->heureDeb . ",
						  " . $creds->heureFin . ", '" . $creds->login . "',
						  '" . $creds->mdp . "', '" . $creds->mdpSSHA . "')";
		$this->_app ['db']->exec ( $sql );
	}
	
	// SSHA functions
	static function makeSshaPassword($password) {
		mt_srand ( ( double ) microtime () * 1000000 );
		$salt = pack ( "CCCC", mt_rand (), mt_rand (), mt_rand (), mt_rand () );
		$hash = "{SSHA}" . base64_encode ( pack ( "H*", sha1 ( $password . $salt ) ) . $salt );
		return $hash;
	}
	static function sshaPasswordVerify($hash, $password) {
		// Verify SSHA hash
		$ohash = base64_decode ( substr ( $hash, 6 ) );
		$osalt = substr ( $ohash, 20 );
		$ohash = substr ( $ohash, 0, 20 );
		$nhash = pack ( "H*", sha1 ( $password . $osalt ) );
		if ($ohash == $nhash) {
			return True;
		} else {
			return False;
		}
	}
	
	// generate random password, easy is for login
	static function makeRandom($length = 8, $easy = false) {
		// generation functions
		$alpha = "abcdefghijklmnopqrstuvwxyz";
		$alpha_upper = strtoupper ( $alpha );
		$numeric = "0123456789";
		// $special = ".-+=_,!@$#*%<>[]{}";;
		
		// default [a-zA-Z0-9]{9}
		if ($easy)
			$chars = $alpha;
		else
			$chars = $alpha . $alpha_upper . $numeric;
		
		$len = strlen ( $chars );
		$pw = '';
		
		for($i = 0; $i < $length; $i ++)
			$pw .= substr ( $chars, rand ( 0, $len - 1 ), 1 );
			
			// the finished password
		$pw = str_shuffle ( $pw );
		
		return $pw;
	}
}