<?php

namespace Model\Data;

class User extends \Phalcon\Mvc\Model {
    
    private $emailAddress;
    
    private $userID;
    
    public function getSource() {
        return 'user';
    }
    
    public function validation() {
        
        $this->validate(new \Phalcon\Mvc\Model\Validator\Uniqueness(array(
            'field' => array('userID', 'emailAddress'),
            'message' => 'UserID/Email has to be unique.'
        )));
        
        $this->validate(new \Phalcon\Mvc\Model\Validator\Email(array(
            'field' => 'emailAddress',
            'message' => 'The email Address has to be valid.'
        )));
        
        if($this->validationHasFailed()) {
            return false;
        }
    }
    
    public function getEmail() {
        return $this->emailAddress;
    }
    
    public function setUserID($userID) {
        $this->userID = $userID;
        return $this;
    }
    
    public function setEmail($email) {
        $this->emailAddress = $email;
        return $this;
    }
    
}



