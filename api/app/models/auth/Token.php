<?php

namespace Models\Auth;

use \Phalcon\Db\Column;

/**
 * this class will encapsulate a Token saved on the Auth Server,
 * except it does not need functions to generate them, this class
 * also has no set methods, as it is read only in this scope.
 * 
 * @Author - Jack Timblin - U1051575
 */
class Token extends \Phalcon\Mvc\Model {
    /**
     * the permissions that this token has.
     * @var string|array 
     */
    private $scope;
    
    /**
     * the userID that this token is authorized for.
     * @var string 
     */
    private $userID;
    
    /**
     * Overrides getSource in \Phalcon\Mvc\Model
     * sets the table this model should map to.
     * @return string the table this model should map to.
     */
    public function getSource() {
        return 'token';
    }
    
    /**
     * Override afterFetch in Model, 
     * when this event is triggered this is called.
     */
    public function afterFetch() {
        $this->scope = explode(',', $this->scope);
    }
    
    /**
     * Override initialize in Model
     * sets the connection service.
     */
    public function initialize() {
        $this->setConnectionService('authdb');
    }
    
    /**
     * finds a token, by the action token value.
     * @param string $token the token value.
     * @return \Model\Auth\Token|boolean the token object of false if one was not found.
     */
    public static function findToken($token) {
        
        return \Models\Auth\Token::findFirst(array(
            'conditions' => 'token = ?1',
            'bind' => array(1 => $token),
            'bindTypes' => array(1 => Column::BIND_PARAM_STR)
        ));
        
    }
    
    /**
     * returns an array of string permissions that
     * this token is valid for.
     * @return array an array of permissions.
     */
    public function getScope() {
        if(!is_array($this->scope)) {
            $this->scope = explode(',', $this->scope);
        }
        return $this->scope;
    }
    
    /**
     * gets the userID that is associated with this token.
     * @return string the userID.
     */
    public function getUserID() {
        return $this->userID;
    }
}

