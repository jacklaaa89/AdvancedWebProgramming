<?php

use \Phalcon\Db\Adapter\Pdo\Mysql;

//use loader class to not only auto-load required classes, but also
//register the namespaces.
$loader = new \Phalcon\Loader();

$loader->registerNamespaces(
    array(
        "Models" => "../app/models/"
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

//need to set a global key in the crypt for cookie management encryption.
$di->set('crypt', function() {
    $crypt = new Phalcon\Crypt();
    $crypt->setKey('89K%1m1K1cK38z^NxIG9SZ}L61!M)S');
    return $crypt;
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
    foreach ($params as $key => $value) {
        $data[strtolower($key)] = $value;
    }

    //check that all of the required params have been set.
    $keys = array(
        'clientid',
        'clientsecret',
        'timestamp',
        'signature',
        'scope'
    );

    //by default the request is treated as valid
    //until proven wrong.
    $validRequest = true;

    //check that the same keys are supplied, regardless of case and whatever
    //params were provided by the user.
    if (count(array_intersect($keys, array_keys($data))) != 4) {
        $validRequest = false;
    }

    //check that the variables are of the required type, i.e
    //the clientID/secret/signature are strings and timestamp can be
    //parsed as a valid datetime.
    if (!date('Y-m-d', $data['timestamp'])) {
        $validRequest = false;
    }
    
    //we need to check that permissions were set.
    if(!is_array($data['scope']) || count($data['scope']) == 0) {
        $validRequest = false;
    }

    foreach (array($data['clientid'], $data['clientsecret'], $data['signature']) as $value) {
        if (!isset($value) && !is_string($value)) {
            $validRequest = false;
        }
    }

    //if any of the request tests failed, then send 400 - Bad Request.
    if (!$validRequest) {
        //set the status code to 400 - Bad Request
        $response->setStatusCode(400, "Bad Request")->send();
    }

    //after request is correctly parsed, try to determine if
    //client exists.

    $redirectURI = (isset($data['redirecturi'])) ? $data['redirecturi'] : null;

    if (!\Models\Client::checkIsValidClient($data['clientid'], $data['clientsecret'])) {
        //set the status code to 403 - Forbidden
        $response->setStatusCode(403, "Forbidden")->send();
    }

    $request = \Models\Request::requestBuilder($data['clientid'], $data['scope'], $data['timestamp'], $redirectURI);
    if (!$request) {
        //set the status code to 403 - Forbidden
        $response->setStatusCode(403, "Forbidden")->send();
    }

    //start authorization process, create new request instance and send
    //to /oauth/authorisation.
    $response->redirect('/oauth/authorisation/' . $request->getRequestID());
});

$app->get('/oauth/authorisation/{ri:[0-9A-Za-z]+}', function($ri) use ($app) {
    
    //check that the Request is valid.
    $request = \Models\Request::findRequestByID($ri);
    
    //if the request is not found.
    if(!$request) {
        $app->response->setStatusCode(404, "Not Found")->send();
    }
    
    $userID = null;
    $previousPermissions = null;
    //Check if a session is already set, i.e a user has authenticated before
    //and asked to be remembered.
    
    //cookies on this auth site are not only encrypted but the only value that is
    //stored is the userID which is only applicable for this site.
    if($app->cookies->has('remember-me')) {
        $userID = $app->cookies->get('remember-me')->getValue();
    }
    
    //if a user has logged in before.
    if(isset($userID)) {
        //if the user has already authenticated, we need to check what
        //the permissions required have not changed.
        $previousPermissions = \Models\ClientPermissions::findPermissions($request->getClientID(), $userID);
        
        //check if the required permissions are the same.
        $newPermissions = array();
        foreach($request->getScope() as $permission) {
            if(!$previousPermissions->hasPermission($permission)) {
                $newPermissions[] = $permission;
            }
        }
    
        if(!empty($newPermissions)) {
            //show permissions page with new permissions.
        } else {
            //just supply token as user has been authenticated
            //before and all required permissions have been given permission.
        }
    }
    
    //show the log in page.
    
});

$app->post('oauth/background/', function() use ($app) {
    //this is used to perform ajax requests to move through the authenticated process.
    //this is used to reload the HTML on the page without having to reload the page, with the
    //only redirect being made is that to the redirectURI when the process is complete.
});

//Need to define a action which is fired if a route is used that is not found.
//It just fires a 404 Not Found request.
$app->notFound(function() use ($app) {
    $app->response->setStatusCode(404, "Not Found")->send();
});

//handle the request.
$app->handle();
