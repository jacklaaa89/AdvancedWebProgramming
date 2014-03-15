<?php

namespace Models\Data;

class User extends \Phalcon\Mvc\Model {
    
    /**
     * this userID maps to the userID in the auth-server.
     * @var string
     */
    private $userID;
    
    /**
     * the email address of this user.
     * @var string 
     */
    private $emailAddress;
    
    /**
     * the name of this user.
     * @var string
     */
    private $name;
    
    /**
     * overrides getSource in Model.
     * returns the table name that this model maps to.
     * @return string
     */
    public function getSource() {
        return 'user';
    }
    
    /**
     * overrides initialize in Model.
     * sets up a virtual foreign key mapping.
     */
    public function initialize() {
        $this->hasMany('userID', 'Message', 'userID');
    }
    
    /**
     * returns the ID of this user.
     * @return string
     */
    public function getUserID() {
        return $this->userID;
    }
    
    /**
     * returns the email of this user
     * @return string
     */
    public function getEmailAddress() {
        return $this->emailAddress;
    }
    
    /**
     * sets the name of this user.
     * @param string $name the name of this user.
     * @return \Models\Data\User
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }
    
    /**
     * returns the name of this user.
     * @return string
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * sets the ID of this user.
     * @param string $userID the ID of this user.
     * @return \Models\Data\User
     */
    public function setUserID($userID) {
        $this->userID = $userID;
        return $this;
    }
    
    /**
     * sets the email of this user.
     * @param string $emailAddress a valid email address.
     * @return \Models\Data\User
     */
    public function setEmailAddress($emailAddress) {
        $this->emailAddress = $emailAddress;
        return $this;
    }
    
    /**
     * returns this User Object as an associative array representation.
     * @return array
     */
    public function toArray() {
        return array(
            'userID' => $this->getUserID(),
            'emailAddress' => $this->getEmailAddress(),
            'name' => $this->getName()
        );
    }
}

