<?php

use \Phalcon\Loader,
    \Phalcon\DI\FactoryDefault as Di,
    \Phalcon\Mvc\View,
    \Phalcon\Mvc\Application as App;

try {
    
    $loader = new Loader();
    
    $loader->registerDirs(array(
        '../app/controllers/'
    ));
    
    $loader->registerNamespaces(array(
        'Model' => '../app/models/',
    ))->register();
    
    $di = new Di();
    
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
            'notice' => 'alert alert-info',
        ));
        return $flash;
    });
    
    $app = new App($di);
    
    echo $app->handle()->getContent();
    
} catch (Exception $ex) {
    echo 'PhalconException: ' . $ex->getTraceAsString();
}

