<?php

namespace Model\Auth;

/**
 * this class in an encapsulation of an application that a user has authorised.
 * @author Jack Timblin - U1051575
 */
class Application extends \Phalcon\Mvc\Model {
    
    /* not used variables, but need to be set
     * to save the model. */
    
    //the actual token, this will be unique.
    private $token;
    
    //the type of token, this will always be 'bearer'
    private $token_type;
    
    //the timestamp of when this token was created.
    private $created;
    
    //the timestamp of when this token is validTo.
    private $updated;
    
    /* end not used variables. */
    
    //the permissions this token has over the
    //users information, these can be modified by the user.
    private $scope;
    
    //the client that can use this token.
    private $clientID;
    
    //the user that has authorised the use of this token.
    private $userID;
    
    /**
     * overrides initialize in Model.
     * sets the connection service to auth-local server
     * and sets virtual key mapping.
     */
    public function initialize() {
        $this->setConnectionService('authdb');
        $this->hasOne('clientID', 'Client', 'clientID');
    }
    
    /**
     * sets what table this model maps to.
     * @return string
     */
    public function getSource() {
        return 'token';
    }
    
    /**
     * overrides beforeSave in Model
     * joins the scope array to a string ready
     * to be stored in the Db.
     */
    public function beforeSave() {
        $this->scope = join(',',$this->scope);
    }
    
    /**
     * overrides afterFetch in Model
     * explodes the string representation 
     * of the scope values back to an array.
     */
    public function afterFetch() {
        $this->scope = explode(',',$this->scope);
    }
    
    /**
     * tries to get the client this application is associated with.
     * @return \Model\Auth\Client|boolean the client on success, false on failure.
     */
    public function getClient() {
        $client = \Model\Auth\Client::findFirst(array(
            'conditions' => 'clientID = ?1',
            'bind' => array(1 => $this->clientID),
            'bindTypes' => array(1 => \Phalcon\Db\Column::BIND_PARAM_STR)
        ));
        if(!is_bool($client)) {
            return $client;
        }
    }
    
    /**
     * gets the ID of the client this app is 
     * associated with.
     * @return string
     */
    public function getClientID() {
        return $this->clientID;
    }
    
    /**
     * gets the ID of the user this app 
     * is associated with.
     * @return string
     */
    public function getUserID() {
        return $this->userID;
    }
    
    /**
     * returns an array of permission values this
     * application has permission to use.
     * @return array
     */
    public function getScope() {
        if(!is_array($this->scope)) {
            $this->scope = explode(',', $this->scope);
        }
        return $this->scope;
    }
    
    /**
     * removes a single permission from the scope array.
     * @param string $permission the permission to remove.
     * @return \Model\Auth\Application
     */
    public function removeScope($permission) {
        $scope = $this->getScope();
        for($i = 0; $i < count($scope); $i++) {
            if(strtolower($permission) == strtolower($scope[$i])) {
                unset($scope[$i]);
            }
        }
        return $this;
    }
    
    /**
     * find an application instance from the clientID and userID.
     * @param string $clientID the ID of the client.
     * @param string $userID the ID of the user.
     * @return \Model\Auth\Application|boolean
     */
    public static function findApplication($clientID, $userID){
        return \Model\Auth\Application::findFirst(array(
            'conditions' => 'userID = ?1 AND clientID = ?2',
            'bind' => array(1 => $userID, 2 => $clientID),
            'bindTypes' => array(1 => \Phalcon\Db\Column::BIND_PARAM_STR,
                                 2 => \Phalcon\Db\Column::BIND_PARAM_STR)
        ));
    }
    
}

