<?php

use \Phalcon\Loader,
    \Phalcon\DI\FactoryDefault as Di,
    \Phalcon\Mvc\View,
    \Phalcon\Db\Adapter\Pdo\Mysql,
    \Phalcon\Mvc\Application as App,
    \Phalcon\Mvc\Dispatcher;

try {
    
    $loader = new Loader();
    
    $loader->registerDirs(array(
        '../app/controllers/'
    ));
    
    $loader->registerNamespaces(array(
        'Model' => '../app/models/',
        'Plugin' => '../app/plugins/',
        'Model/Auth' => '../app/models/auth/',
        'Model/Data' => '../app/models/data/'
    ))->register();
    
    $di = new Di();
    $di->set('db', function(){
        return new Mysql(array(
            'host' => 'localhost',
            'username' => 'root',
            'password' => '',
            'dbname' => 'data'
        ));
    });
    
    $di->set('authdb', function(){
        return new Mysql(array(
            'host' => 'localhost',
            'username' => 'root',
            'password' => '',
            'dbname' => 'oauth'
        ));
    });
    
    $di->set('view', function(){
        $view = new View();
        $view->setViewsDir('../app/views/');
        return $view;
    });
    
    $di->setShared('session', function(){
        $session = new \Phalcon\Session\Adapter\Files();
        $session->start();
        return $session;
    });
    
    $di->set('flashSession', function() {
        $flash = new \Phalcon\Flash\Session(array(
            'error' => 'alert alert-danger',
            'success' => 'alert alert-success',
            'notice' => 'alert alert-info'
        ));
        return $flash;
    });
    
    $di->set('flash', function() {
        $flash = new \Phalcon\Flash\Direct(array(
            'error' => 'alert alert-danger',
            'success' => 'alert alert-success',
            'notice' => 'alert alert-info'
        ));
        return $flash;
    });
    
    $di->set('router', function(){
        $router = new \Phalcon\Mvc\Router();
        $router->add('/dashboard/{userID:[A-Za-z0-9]+}', array(
            'controller' => 'dashboard',
            'action' => 'index'
        ));
        $router->add('/dashboard/{userID:[A-Za-z0-9]+}/permissions', array(
            'controller' => 'dashboard',
            'action' => 'permissions'
        ));
        $router->add('/dashboard/background', array(
            'controller' => 'dashboard',
            'action' => 'background'
        ));
        return $router;
    });
    
    $di->set('dispatcher', function() use($di) {
        $eventsManager = $di->getShared('eventsManager');
        
        $security = new \Plugin\Security($di);
        
        //attach ACL security plugin.
        $eventsManager->attach('dispatch', $security);
        
        //handle 404 - not found events.
        $eventsManager->attach('beforeException', function($event, $dispatcher, $exception){
            switch($exception->getCode()) {
                case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                    $dispatcher->forward(
                            array(
                                'controller' => 'error',
                                'action'     => 'index',
                            )
                        );
                        return false;
            }
        });
        
        $dispatcher = new Dispatcher();
        
        $dispatcher->setEventsManager($eventsManager);
        
        return $dispatcher;
    });
    
    $app = new App($di);
    
    echo $app->handle()->getContent();
    
} catch (Exception $ex) {
    echo 'PhalconException: ' . $ex->getTraceAsString();
}

