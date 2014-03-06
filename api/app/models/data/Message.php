<?php

namespace Models\Data;

use \Phalcon\Mvc\Model,
    \Phalcon\Mvc\Model\Validator\Uniqueness;

class Message extends Model {
    
    //the unique ID of this message.
    private $messageID;
    //the userID that this message belongs to.
    private $userID;
    //the actual message.
    private $message;
    //the date this message was added.
    private $dateAdded;
    
    public function getSource() {
        return 'message';
    }
    
    public function initialize() {
        $this->belongsTo('userID', 'User', 'userID');
    }
    
    public function validation() {
        $this->validate(new Uniqueness(array(
            'field' => 'messageID',
            'message' => 'The messageID has to be unique.'
        )));
        if($this->validationHasFailed()) {
            return false;
        }
    }
    
    public function getMessageID() {
        return $this->messageID;
    }
    
    public function getUserID() {
        return $this->userID;
    }
    
    public function getMessage() {
        return $this->message;
    }
    
    public function getDateAdded() {
        return $this->dateAdded;
    }
    
    public function setMessageID($messageID) {
        $this->messageID = $messageID;
        return $this;
    }
    
    public function setUserID($userID) {
        $this->userID = $userID;
        return $this;
    }
    
    public function setMessage($message) {
        $this->message = $message;
        return $this;
    }
    
    public function setDateAdded($dateAdded) {
        $this->dateAdded = $dateAdded;
        return $this;
    }
    
    public function toArray() {
        return array(
            'messageID' => $this->getMessageID(),
            'message' => $this->getMessage(),
            'dateAdded' => $this->getDateAdded()
        );
    }
    
}

