<?php

namespace Model\Auth;

class Application extends \Phalcon\Mvc\Model {
    
    private $userID;
    
    private $scope;
    
    private $clientID;
    
    public function initialize() {
        $this->setConnectionService('authdb');
        $this->hasOne('clientID', 'Client', 'clientID');
    }
    
    public function getSource() {
        return 'token';
    }
    
    public function beforeSave() {
        $this->scope = join(',',$this->scope);
    }
    
    public function afterFetch() {
        $this->scope = explode(',',$this->scope);
    }
    
    public function getClientName() {
        $client = $this->getClient();
        if(!is_bool($client)) {
            return $client->getName();
        }
    }
    
    public function getUserID() {
        return $this->userID;
    }
    
    public function getScope() {
        if(!is_array($this->scope)) {
            $this->scope = explode(',', $this->scope);
        }
        return $this->scope;
    }
    
}

