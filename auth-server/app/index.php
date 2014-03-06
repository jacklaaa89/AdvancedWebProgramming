<?php

use \Phalcon\Db\Adapter\Pdo\Mysql,
    \Phalcon\Mvc\View,
    \Phalcon\Filter;

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
            'host' => 'localhost',
            'username' => 'root',
            'password' => '',
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

//set a view variable to the app so we can use views.
$app['view'] = function() {
    $view = new View();
    $view->setViewsDir('../app/views/');
    return $view;
};

/**
 * This route is the gateway to start the authorisation process, the client
 * will redirect to this page with the params defined in a query string.
 * the parameters that are used in this route are: client_id (required), state (required),
 * scope (required), and redirect_uri (optional).
 */
$app->get('/oauth/authorize', function() use ($app) {
    //format params
    $params = array();
    $filter = new Filter();
    foreach($_GET as $key => $value) {
        //sanitize the input.
        $params[strtolower($key)] = $filter->sanitize($value, 'string');
    }
    
    //check for required keys.
    $keys = array(
        'client_id',
        'state',
        'scope'
        //more keys if required at a later date.
    );
    
    //if the request is not valid.
    if(!\Models\BaseModel::validateInput($params, $keys)) {$app->response->setStatusCode(400, "Bad Request")->send();return;}
    
    $redirect_uri = (array_key_exists('redirect_uri', $params)) ? $params['redirect_uri'] : null;
    $scope = (array_key_exists('scope', $params)) ? explode(',',$params['scope']) 
            : array(); 
    
    //build a request and store it.
    $request = \Models\Request::requestBuilder($params['client_id'], $scope, $params['state'], $redirect_uri);
    
    if(!$request) {$app->response->setStatusCode(400, "Bad Request")->send();return;}
    
    //the request is valid, check if this user is already logged in.
    $userID = ($app->cookies->has('userID')) ? $app->cookies->get('userID')->getValue() : false;
    
    //render the correct view, injecting the request object into it.
    $view = array('oauth', '', array('request' => $request, 'userID' => ''));
    if(!$userID) {
        //show the log in form.
        $view[1] = 'login';
    } else {
        //show authorise page.
        $view[1] = 'authorize';
        $view[2]['userID'] = $userID; 
    }
    
    $app->response->setStatusCode(200, "OK")
                  ->setContent($app['view']->getRender($view[0], $view[1], $view[2]))
                  ->send();
    
});

/**
 * This route is defined to handle the authentication process on the actual login/auth pages, 
 * the reason why I used ajax is as because you can only process same-host requests this way, which
 * adds another layer of security to the process. This route is also only valid when a TYPE and a valid
 * requestID are provided.
 */
$app->post('oauth/background/{type:[A-Za-z]+}/{requestID:[A-Za-z0-9]+}', function($type, $requestID) use ($app) {
    //this route is a ajax only route.
    if(!$app->request->isAjax()) {$app->response->setStatusCode(404, "Not Found")->send();return;}
    
    //get the request object.
    $request = \Models\Request::findRequestByID($requestID);
    
    if(!$request) {$app->response->setStatusCode(404, "Not Found")->send();} //request was not found.
    
    //get the type.
    switch(strtoupper($type)) {
        case 'LOGIN':
            //check if the users creds are valid.
            $email = $app->request->getPost('email', 'email');
            $pass = hash('sha256', $app->request->getPost('pass'));
            
            //see if a user exists with them credentials.
            $user = \Models\User::findUserByCredentials($email, $pass);
            
            $valid = (!is_bool($user));
            
            if($valid) {
                //check to see if this client/user combo has a token.
                $token = \Models\Token::findToken($request->getClientID(), $user->getUserID());
                if(!is_bool($token)) {
                    //check if the request has additional parameters, i.e we only need to authorize a
                    //request if it requires additional scopes.
                    if(\Models\BaseModel::array_equal($request->getScope(), $token->getScope())) {
                        //return to the redirect uri, with an error.
                        $app->response->setStatusCode(200, "OK")
                                      ->setContentType('application/json')
                                      ->setJsonContent(array(
                                          'valid' => false,
                                          'html' => '',
                                          'url' => $request->getRedirectURI() . '?'
                                                   . http_build_query(array(
                                                       'error' => 'auth_error',
                                                       'error_description' => 'Auth completed with no new permissions'
                                                   ))
                                      ))
                                      ->send();
                        //delete the request object.
                        $request->delete();
                        return;
                    }
                    
                    //the client needs additional permissions, but we only
                    //want to display the new permissions required.
                    $request->setScope(array_diff($request->getScope(), $token->getScope()))
                            ->save();
                }
            }
            
            //send the response as JSON.
            $app->response->setStatusCode(200, "OK")->setJsonContent(
                array(
                    'valid' => $valid, //check if user is valid.
                    'html' => $app['view']->getRender('oauth', 'authorize',
                            array(
                                'request' => $request,
                                'userID' => ($valid) ? $user->getUserID() : 1 //get userID.
                            )
                    ),
                    'url' => $request->getRedirectURI() . '?'
                            . http_build_query(array(
                                'error' => 'auth_error',
                                'error_description' => 'Invalid User.'
                            )) //render html.
                )
            )->send(); //send response.
            break;
        case 'AUTHORIZE':
            //see if the user accepted or declined the auth request.
            //if success, generate code for request.
            //redirect to redirect_uri either way.
            $auth = $app->request->getPost('auth', 'string');
            $userID = $app->request->getPost('userID', 'string');
            //get the permissions that were approved, json_decode returns
            //false if this string could not be parsed.
            $checked = json_decode($app->request->getPost('checked'));
            $data = array('url' => '');
            
            //if the auth value is not set or the user declined, 
            //or if the permissions array could not be parsed.
            if(!isset($auth) || preg_match('/(decline)/i', $auth) || !$checked) {
                //we need to send the URL to send to, to the browser
                //as we cannot redirect during ajax call.
                $data['url'] = $request->getRedirectURI().'?'
                        . http_build_query(array(
                            'error' => 'auth_error',
                            'error_description' => 'user declined permission request',
                            'state' => $request->getState()
                ));
            } else {
                //the user accepted.
                
                //check which permissions the user accepted, this will then be the
                //requests scope array.
                $permissions = array();
                foreach($checked as $scope => $isChecked) {
                    if($isChecked == '1') {
                        $permissions[] = $scope;
                    }
                }
                
                //generate code.
                $request->setScope($permissions)
                        ->setCode(\Models\Request::generateID(30))
                        ->setUserID($userID)->save();
                
                //redirect to redirect_uri.
                $data['url'] = $request->getRedirectURI().'?'
                        . http_build_query(array(
                            'code' => $request->getCode(),
                            'state' => $request->getState()
                ));
            }
            
            $app->response->setStatusCode(200, "OK")->setJsonContent($data)
                          ->setContentType('application/json')->send();
            break;
        default:
            //invalid request type.
            $app->response->setStatusCode(404, "Not Found")->send();
            break;
    }
});

/**
 * This is the route defined where a client can use the 'code' parameter returned from 
 * the initial authentication process (/oauth/authorize) in order to get an access token to use on the API.
 * This route takes the following parameters: code (required), client_id (required), client_secret (required).
 * This route takes a JSON array as data input.
 */
$app->post('oauth/access_token', function() use ($app) {
    //first check that the required params have been provided.
    $params = array();
    foreach( (array) $app->request->getJsonRawBody() as $key => $value) {
        $params[strtolower($key)] = $value;
    }
    
    $keys = array(
        'client_id',
        'client_secret',
        'code'
    );
    
    //if the request is not valid.
    if(!\Models\BaseModel::validateInput($params, $keys)) {$app->response->setStatusCode(400, "Bad Request")->send();}
    
    //then check that the client is valid and the request exists.
    if(!\Models\Client::checkIsValidClient($params['client_id'], $params['client_secret'])) {
        $app->response->setStatusCode(403, "Forbidden")->send();
    }
    
    //find the request based on the code.
    $request = \Models\Request::findRequestByCode($params['code'], $params['client_id']);
    
    if(!$request) {$app->response->setStatusCode(403, "Forbidden")->send();}
    
    //check if this client already has a token issued, and we need to modify permissions.
    $token = \Models\Token::findToken($request->getClientID(), $request->getUserID());
    
    if(!is_bool($token)) {
        //add the new permissions to the token, the request scope is the new permissions
        //that the user authorised.
        $token->setScope(array_merge($token->getScope(), $request->getScope()))
              ->setUpdated(time())
              ->save();
    } else {
        //generate a new token.
        $token = \Models\Token::generateToken($request->getClientID(), $request->getUserID(), $request->getScope());
    }
    
    //delete this request object.
    $request->delete();
    
    $app->response->setStatusCode(200, "OK")
                  ->setContentType('application/json')
                  ->setJsonContent($token->toArray())
                  ->send();
    
});

//Need to define a action which is fired if a route is used that is not found.
//It just fires a 404 Not Found request.
$app->notFound(function() use ($app) {
    $app->response->setStatusCode(404, "Not Found")->send();
});

//handle the request.
$app->handle();
