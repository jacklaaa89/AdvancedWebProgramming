<?php

namespace Model\Auth;

class Client extends \Phalcon\Mvc\Model {
    
    private $name;
    
    private $clientID;
    
    public function initialize() {
        $this->setConnectionService('authdb');
        $this->hasOne('clientID', 'Application', 'clientID');
    }
    
    public function getSource() {
        return 'client';
    }
    
    public function getName() {
        return $this->name;
    }
    
}

