<?php

namespace Model\Auth;

use Model\BaseModel;

class User extends BaseModel {
    
    private $userID;
    
    private $email;
    
    private $passwordHash;
    
    public function initialize() {
        $this->setConnectionService('authdb');
    }
    
    public function getSource() {
        return 'user';
    }
    
    public function validation() {
        $this->validate(new \Phalcon\Mvc\Model\Validator\Uniqueness(array(
            'field' => array('userID', 'email'),
            'message' => 'The userID/email has to be unique.'
        )));
        $this->validate(new \Phalcon\Mvc\Model\Validator\Email(array(
            'field' => 'email',
            'message' => 'The email has to be valid.'
        )));
        
        //check if validation has failed.
        if($this->validationHasFailed()) {
            return false;
        }
    }
    
    public function getEmail() {
        return $this->email;
    }
    
    public function getUserID() {
        return $this->userID;
    }
    
    public function getPasswordHash() {
        return $this->passwordHash;
    }
    
    public function setUserID($userID) {
        $this->userID = $userID;
        return $this;
    }
    
    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }
    
    public function getDataUser() {
        if(!isset($this->userID)) {
            return false;
        }
        
        return \Model\Data\User::findFirst(array(
            'conditions' => 'userID = ?1',
            'bind' => array(1 => $this->userID),
            'bindTypes' => array(1 => \Phalcon\Db\Column::BIND_PARAM_STR)
        ));
    }
    
    public function setPasswordHash($passwordHash) {
        $this->passwordHash = $passwordHash;
        return $this;
    }
    
    public static function addNewUser($emailAddress, $passwordHash) {
        //generate new auth user.
        if(is_bool(self::checkUserExists($emailAddress)) && filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            $user = new \Model\Auth\User();
            $user->setEmail($emailAddress)
                 ->setPasswordHash($passwordHash)
                 ->setUserID($user->generateID());
            
            //make a new data user.
            $datauser = new \Model\Data\User();
            $datauser->setEmail($emailAddress)
                     ->setUserID($user->getUserID());
            
            if(!$user->save() || !$datauser->save()) {
                //try again as userID failed.
                \Model\Auth\User::addNewUser($emailAddress, $passwordHash);
            }
            return $user;
        }
        return false; //a user exists or the email was not valid.
    }
    
    public static function checkUserExists($emailAddress) {
        return \Model\Auth\User::findFirst(array(
            'conditions' => 'email = ?1',
            'bind' => array(1 => $emailAddress),
            'bindTypes' => array(1 => \Phalcon\Db\Column::BIND_PARAM_STR)
        ));
    }
    
}

