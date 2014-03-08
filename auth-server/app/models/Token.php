<?php

namespace Models;

use \Models\BaseModel,
    \Phalcon\Db\Column,
    \Phalcon\Mvc\Model\Validator\Uniqueness;

/**
 * This is an encapsulation of an access token as provided by the auth-server.
 * While the user has allowed access, this token will be used, so emulate 'persistant' authentication.
 * Obivously if this token is deleted, the user has revoked permissions, also the scope can be modified.
 */
class Token extends BaseModel {
    
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
    
    public function getUpdated() {
        return $this->updated;
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
    
    public function setUpdated($updated) {
        $this->updated = $updated;
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
              ->setUpdated(time())
              ->setScope($scope)
              ->setClientID($clientID)
              ->setUserID($userID);
        
        if(!$token->save()) {
            \Models\Token::generateToken($clientID, $userID, $scope);
        }
        return $token;
    }
    
    public static function findToken($clientID, $userID) {
        return \Models\Token::findFirst(array(
            'conditions' => 'clientID = ?1 AND userID = ?2',
            'bind' => array(
                1 => $clientID, 2 => $userID
            ),
            'bindTypes' => array(
                1 => Column::BIND_PARAM_STR, 2 => Column::BIND_PARAM_STR
            )
        ));
    } 
    
}

