<?php

namespace Models;

use \Phalcon\Mvc\Model,
    \Phalcon\Db\Column,
    \Phalcon\Mvc\Model\Validator\Uniqueness;

class User extends Model {
    
    private $userID;
    
    private $passwordHash;
    
    private $email;
    
    public function getSource() {
        return 'user';
    }
    
    public function validation() {
        //userID && email has to be unique.
        $this->validate(new Uniqueness(
                array(
                   'field' => array('userID', 'email'),
                    'message' => 'userID and Email address have to be unique.'
                )
        ));
        
        //check if a validation message has been produced.
        if ($this->validationHasFailed()) {
            return false;
        }
    }
    
    public function getUserID() {
        return $this->userID;
    }
    
    public function getPasswordHash() {
        return $this->passwordHash;
    }
    
    public function getEmail() {
        return $this->email;
    }
    
    public function setUserID($userID) {
        $this->userID = $userID;
        return $this;
    }
    
    public function setPasswordHash($passwordHash) {
        $this->passwordHash = $passwordHash;
        return $this;
    }
    
    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }
    
    public static function findUserByCredentials($email, $passwordHash) {
        return \Models\User::findFirst(array(
            'conditions' => 'email = ?1 AND passwordHash = ?2',
            'bind' => array(1 => $email, 2 => $passwordHash),
            'bindTypes' => array(
                1 => Column::BIND_PARAM_STR,
                2 => Column::BIND_PARAM_STR
            )
        ));
    }
    
}

