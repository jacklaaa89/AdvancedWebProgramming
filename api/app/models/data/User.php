<?php

namespace Models\Data;

class User extends \Phalcon\Mvc\Model {
    
    //this userID maps to the userID in the auth-server.
    private $userID;
    
    private $emailAddress;
    
    private $name;
    
    public function getSource() {
        return 'user';
    }
    
    public function initialize() {
        $this->hasMany('userID', 'Message', 'userID');
    }
    
    public function getUserID() {
        return $this->userID;
    }
    
    public function getEmailAddress() {
        return $this->emailAddress;
    }
    
    public function setName($name) {
        $this->name = $name;
        return $this;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function setUserID($userID) {
        $this->userID = $userID;
        return $this;
    }
    
    public function setEmailAddress($emailAddress) {
        $this->emailAddress = $emailAddress;
        return $this;
    }
    
    public function toArray() {
        return array(
            'userID' => $this->getUserID(),
            'emailAddress' => $this->getEmailAddress(),
            'name' => $this->getName()
        );
    }
}

