<?php

use \Phalcon\Db\Adapter\Pdo\Mysql;

//use loader class to auto-load required classes.
$loader = new \Phalcon\Loader();

$loader->registerDirs(
	array(
		'models/'
	)
)->register();

//create a dependency injector, which is used to inject modules,
//we shall be using the factory default one which injects a lot
//of the standard modules automatically.
$di = new \Phalcon\DI\FactoryDefault();

//set up a database connection service for the models to use.
$di->set('db', function() {
	return new Mysql(
		array(
			'host' => 'locahost',
			'username' => 'root',
			'password' => 'root',
			'dbname' => 'oauth'
		)
	);
});

//create a new new micro application
$app = new \Phalcon\Mvc\Micro($di);

//define the routes used in this application. Each route is handled here rather than
//using controllers, as all this dummy server provides is authorisation.

$app->post('/oauth/token', function() use ($app) {

	//get the json request body, i.e the client secret, id,
	//timstamp and authorization code to request an access token.
	$params = (array) $app->request->getJsonRawBody();

	$response = new \Phalcon\Http\Response();

	//format params.
	$data = array();
	//make all the keys in params lower.
	foreach($params as $key => $value) {
		$data[strtolower($key)] = $value;
	}

	//check that all of the required params have been set.
	$keys = array(
		'clientid',
		'clientsecret',
		'timestamp',
		'signature'
	);

	//by default the request is treated as valid
	//until proven wrong.
	$validRequest = true;

	//check that the same keys are supplied, regardless of case and whatever
	//params were provided by the user.
	if(count(array_intersect($keys,array_keys($data))) != 4) {
		$validRequest = false;
	}

	//check that the variables are of the required type, i.e
	//the clientID/secret/signature are strings and timestamp can be
	//parsed as a valid datetime.

	if(!date('Y-m-d', $data['timestamp'])) {
		$validRequest = false;
	}

	foreach(array($data['clientid'], $data['clientsecret'], $data['signature']) as $value) {
		if(!isset($value) && !is_string($value)) {
			$validRequest = false;
		}
	}

	//if any of the request tests failed, then send 400 - Bad Request.
	if(!$validRequest) {

		//set the status code to 400 - Bad Request
		$response->setStatusCode(400, "Bad Request");

		//send the response.
		$response->send();
	}

	//after request is correctly parsed, try to determine if
	//client exists.

	$redirectURI = (isset($data['redirecturi'])) ? $data['redirecturi'] : null;

	if(!\Models\Client::checkIsValidClient($data['clientid'], $data['clientsecret'])) {

		//set the status code to 403 - Forbidden
		$response->setStatusCode(403, "Forbidden");

		//send the response.
		$response->send();
	}

	$request = \Models\Request::requestBuilder($data['clientid'], $data['timestamp'], $redirectURI);
	if(!$request) {

		//set the status code to 403 - Forbidden
		$response->setStatusCode(403, "Forbidden");

		//send the response.
		$response->send();
	}
	

	//start authorization process, create new request instance and send
	//to /oauth/authorisation.

	$response->redirect('/oauth/authorisation/'.$request->getRequestID());


});

$app->get('/oauth/authorisation/{ri:[0-9A-Za-z]+}', function($ri) use ($app) {
	die(var_dump(\Models\Request::findRequestByID($ri)));
});

//handle the request.
$app->handle();