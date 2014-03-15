<?php

use \Phalcon\Db\Adapter\Pdo\Mysql,
    \Phalcon\Loader,
    \Phalcon\DI\FactoryDefault as Di,
    \Phalcon\Mvc\Micro,
    \Phalcon\Db\Column;

$loader = new Loader();

//register namespaces.
$loader->registerNamespaces(
    array(
        "Models" => "../app/models/",
        "Utilities" => '../app/utilities/',
        "Models\Auth" => '../app/models/auth/',
        "Models\Data" => '../app/models/data/'
    )
)->register();

$di = new Di();

//set auth database connection.
$di->set('authdb', function() {
    return new Mysql(array(
        'host' => 'localhost', //in reality this would be on a seperate server.
        'username' => 'root',
        'password' => '',
        'dbname' => 'oauth'
    ));
});

//set normal api database connection.
$di->set('db', function(){
    return new Mysql(array(
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'dbname' => 'data'
    ));
});

$app = new Micro($di);

//this sample api will have two basic functions, to retrieve a users data, and to retrieve messages
//all of this data is obviously dummy data, this is just a proof of concept.
$app->get('/user', function() use ($app) {
    //try to get the token object from this requests header.
    $token = \Utilities\RequestHelper::getToken($app->request->getHeaders(), $app->request->getQuery('access_token', 'alphanum'));
    
    //if the token could not be found.
    if(!$token) {
        $app->response->setStatusCode(403, "Forbidden")->send();
        return;
    }
    
    //set headers.
    \Utilities\RequestHelper::setHeaders($app, array(
        'X-OAuth-Accepted-Scopes' => 'user',
        'X-OAuth-Scopes' => join(',', $token->getScope())));
    
    //check if this token has permission to use this function.
    if(!in_array('user', $token->getScope())) {
        $app->response->setStatusCode(403, "Forbidden")->send();
        return;
    }
    
    //return data on user.
    $user = \Models\Data\User::findFirst(array(
        'conditions' => 'userID = ?1',
        'bind' => array(1 => $token->getUserID()),
        'bindTypes' => array(1 => Column::BIND_PARAM_STR)
    ));
    
    //merge user details to array.
    $data = array();
    if(!is_bool($user)) {
        $data[] = $user->toArray();
    }
    
    //send the data.
    $app->response->setStatusCode(200, "OK")->setContentType('application/json')
        ->setJsonContent($data)->send();
});

$app->get('/messages', function() use ($app) {
    //try to get the token object from this requests header.
    $token = \Utilities\RequestHelper::getToken($app->request->getHeaders(), $app->request->getQuery('access_token', 'alphanum'));
    
    //if the token could not be found.
    if(!$token) {
        $app->response->setStatusCode(403, "Forbidden")->send();
        return;
    }
    
    //set headers.
    \Utilities\RequestHelper::setHeaders($app, array(
        'X-OAuth-Accepted-Scopes' => 'messages',
        'X-OAuth-Scopes' => join(',', $token->getScope())));
    
    //check if this token has permission to use this function.
    if(!in_array('messages', $token->getScope())) {
        $app->response->setStatusCode(403, "Forbidden")->send();
        return;
    }
    
    //return messages.
    $messages = \Models\Data\Message::find(array(
        'conditions' => 'userID = ?1',
        'bind' => array(1 => $token->getUserID()),
        'bindTypes' => array(1 => Column::BIND_PARAM_STR)
    ));
    
    $data = array();
    foreach($messages as $message) {
        $data[] = $message->toArray();
    }
    
    //send the data.
    $app->response->setStatusCode(200, "OK")->setContentType('application/json')
        ->setJsonContent($data)->send();
});

//handle not found requests.
$app->notFound(function() use ($app){
    $app->response->setStatusCode(404, "Not Found")->send();
});

//handle the request.
$app->handle();