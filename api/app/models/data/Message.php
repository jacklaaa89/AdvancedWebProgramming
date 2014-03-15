<?php

namespace Models\Data;

use \Phalcon\Mvc\Model,
    \Phalcon\Mvc\Model\Validator\Uniqueness;

class Message extends Model {
    
    /**
     * the unique ID of this message.
     * @var string
     */
    private $messageID;
    
    /*
     * the userID that this message belongs to.
     * @var string
     */
    private $userID;
    
    /**
     * the actual message.
     * @var string
     */
    private $message;
    
    /**
     * -the date this message was added
     * stored as a UNIX timestamp.
     * @var int
     */
    private $dateAdded;
    
    /**
     * overrides getSource in Model.
     * returns the table name that this model maps to.
     * @return string
     */
    public function getSource() {
        return 'message';
    }
    
    /**
     * overrides initialize in Model.
     * sets up a virtual foreign key mapping.
     */
    public function initialize() {
        $this->belongsTo('userID', 'User', 'userID');
    }
    
    /**
     * overrides validation in Model.
     * this is called before DB operation is completed, 
     * checks that the messageID is unique.
     * @return boolean false if not unique.
     */
    public function validation() {
        $this->validate(new Uniqueness(array(
            'field' => 'messageID',
            'message' => 'The messageID has to be unique.'
        )));
        if($this->validationHasFailed()) {
            return false;
        }
    }
    
    /**
     * returns the messageID.
     * @return string
     */
    public function getMessageID() {
        return $this->messageID;
    }
    
    /**
     * returns the UserID that this message
     * 'belongs' to.
     * @return string
     */
    public function getUserID() {
        return $this->userID;
    }
    
    /**
     * returns the actual message.
     * @return string
     */
    public function getMessage() {
        return $this->message;
    }
    
    /**
     * get the date this message was added to the system
     * as a UNIX timestamp.
     * @return int
     */
    public function getDateAdded() {
        return $this->dateAdded;
    }
    
    /**
     * sets the messageID.
     * @param string $messageID the messageID.
     * @return \Models\Data\Message
     */
    public function setMessageID($messageID) {
        $this->messageID = $messageID;
        return $this;
    }
    
    /**
     * sets the UserID for this message.
     * @param string $userID the userID that this message 'belongs' to.
     * @return \Models\Data\Message
     */
    public function setUserID($userID) {
        $this->userID = $userID;
        return $this;
    }
    
    /**
     * sets the actual message.
     * @param string $message the message
     * @return \Models\Data\Message
     */
    public function setMessage($message) {
        $this->message = $message;
        return $this;
    }
    
    /**
     * sets the date this message was added
     * this should be a UNIX timestamp.
     * @param int $dateAdded the timestamp of when this
     * message was added.
     * @return \Models\Data\Message
     */
    public function setDateAdded($dateAdded) {
        $this->dateAdded = $dateAdded;
        return $this;
    }
    
    /**
     * returns this message object as an array.
     * @return array
     */
    public function toArray() {
        return array(
            'messageID' => $this->getMessageID(),
            'message' => $this->getMessage(),
            'dateAdded' => $this->getDateAdded()
        );
    }
    
}

