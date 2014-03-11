<?php

namespace Model\Auth;

class Application extends \Phalcon\Mvc\Model {
    
    //the actual token, this will be unique.
    private $token;
    
    //the type of token, this will always be 'bearer'
    private $token_type;
    
    //the timestamp of when this token was created.
    private $created;
    
    //the timestamp of when this token is validTo.
    private $updated;
    
    //the permissions this token has over the
    //users information, these can be modified by the user.
    private $scope;
    
    //the client that can use this token.
    private $clientID;
    
    //the user that has authorised the use of this token.
    private $userID;
    
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
    
    public function getClient() {
        $client = \Model\Auth\Client::findFirst(array(
            'conditions' => 'clientID = ?1',
            'bind' => array(1 => $this->clientID),
            'bindTypes' => array(1 => \Phalcon\Db\Column::BIND_PARAM_STR)
        ));
        if(!is_bool($client)) {
            return $client;
        }
    }
    
    public function getClientID() {
        return $this->clientID;
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
    
    public function removeScope($permission) {
        $scope = $this->getScope();
        for($i = 0; $i < count($scope); $i++) {
            if(strtolower($permission) == strtolower($scope[$i])) {
                unset($scope[$i]);
            }
        }
        return $this;
    }
    
    public static function findApplication($clientID, $userID){
        return \Model\Auth\Application::findFirst(array(
            'conditions' => 'userID = ?1 AND clientID = ?2',
            'bind' => array(1 => $userID, 2 => $clientID),
            'bindTypes' => array(1 => \Phalcon\Db\Column::BIND_PARAM_STR,
                                 2 => \Phalcon\Db\Column::BIND_PARAM_STR)
        ));
    }
    
}

