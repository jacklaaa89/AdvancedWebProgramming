<?php

namespace Models\Data;

class User extends \Phalcon\Mvc\Model {
    
    private $userID;
    
    private $emailAddress;
    
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
            'emailAddress' => $this->getEmailAddress()
        );
    }
}

