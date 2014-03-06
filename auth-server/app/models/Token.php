<?php

namespace Models;

use Models\BaseModel,
    \Phalcon\Db\Column,
    \Phalcon\Mvc\Model\Validator\Uniqueness;

class Token extends BaseModel {
    
    //the actual token, this will be unique.
    private $token;
    
    //the type of token, this will always be 'bearer'
    private $token_type;
    
    //the timestamp of when this token was created.
    private $created;
    
    //the timestamp of when this token is validTo.
    private $validTo;
    
    //the permissions this token has over the
    //users information.
    private $scope;
    
    //the client that can use this token.
    private $clientID;
    
    //the user that has authorised the use of this token.
    private $userID;
    
    public function getSource() {
        return 'token';
    }
    
    public function validation() {
        //token has to be unique.
        $this->validate(new Uniqueness(array(
            'field' => 'token',
            'message' => 'Token had to be unique.'
        )));
        
        if($this->validationHasFailed()) {
            return false;
        }
    }
    
    public function beforeSave() {
        $this->scope = join(',', $this->scope);
    }
    
    public function afterFetch() {
        $this->scope = explode(',', $this->scope);
    }
    
    public function getToken() {
        return $this->token;
    }
    
    public function getTokenType() {
        return $this->token_type;
    }
    
    public function getCreated() {
        return $this->created;
    }
    
    public function getValidTo() {
        return $this->validTo;
    }
    
    public function getClientID() {
        return $this->clientID;
    }
    
    public function getUserID() {
        return $this->userID;
    }
    
    public function getScope() {
        return $this->scope;
    }
    
    public function setScope($scope) {
        $this->scope = $scope;
        return $this;
    }
    
    public function setUserID($userID) {
        $this->userID = $userID;
        return $this;
    }
    
    public function setClientID($clientID) {
        $this->clientID = $clientID;
        return $this;
    }
    
    public function setValidTo($validTo) {
        $this->validTo = $validTo;
        return $this;
    }
    
    public function setCreated($created) {
        $this->created = $created;
        return $this;
    }
    
    public function setToken($token) {
        $this->token = $token;
        return $this;
    }
    
    public function setTokenType($token_type = 'bearer') {
        $this->token_type = $token_type;
        return $this;
    }
    
    public function toArray() {
        return array(
            'token' => $this->getToken(),
            'token_type' => $this->getTokenType(),
            'scope' => join(',', $this->getScope())
        );
    }
    
    public static function generateToken($clientID, $userID, $scope) {
        $token = new \Models\Token();
        $token->setToken(\Models\Token::generateID(30))
              ->setTokenType()
              ->setCreated(time())
              ->setValidTo(strtotime('+30 minutes'))
              ->setScope($scope)
              ->setClientID($clientID)
              ->setUserID($userID);
        
        if(!$token->save()) {
            \Models\Token::generateToken($clientID, $userID, $scope);
        }
    }
    
    public static function findToken($token) {
        return \Models\Tokens::findFirst(array(
            'conditions' => 'token = ?1',
            'bind' => array(1 => $token),
            'bindTypes' => array(1 => Column::BIND_TYPE_STR)
        ));
    }
    
}

