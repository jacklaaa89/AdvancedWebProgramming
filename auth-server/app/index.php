<?php

//create a new new micro application
$app = new \Phalcon\Mvc\Micro();

//define the routes used in this application. Each route is handled here rather than
//using controllers, as all this dummy server provides is authorisation.

$app->post('/oauth/token', function() use ($app) {

	//get the json request body, i.e the client secret, id,
	//timstamp and authorization code to request an access token.
	$params = (array) $app->request->getJsonRawBody();

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
		//some of the required fields have not been provided.
		$response = new \Phalcon\Http\Response();

		//set the status code to 400 - Bad Request
		$response->setStatusCode(400, "Bad Request");

		//send the response.
		$response->send();
	}

	//after request is correctly parsed, try to determine if
	//client exists.

	$request = new \Models\Request();
	$request->setClientID($data['clientid'])
			->setRequestID(/* generate random ID. */);

	//check if a redirect URI was supplied, if so, check its from the
	//same host that initialised the request. If not supplied or not 
	//same host, use Redirect URI defined in the client.
	if(isset($data['redirecturi'])) {
		if(filter_var($data['redirecturi'], FILTER_VALIDATE_URL)) {
			//check that the hostname is the same as the 
			//one supplied for the client. (prevents XSS Attacks)
		}
	}

	//start authorization process, create new request instance and send
	//to /oauth/authorisation.


});

//handle the request.
$app->handle();