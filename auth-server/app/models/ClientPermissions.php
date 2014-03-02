<?php

namespace Models;

use \Phalcon\Mvc\Model,
    \Phalcon\Mvc\Model\Validator\Uniqueness,
    \Phalcon\Db\Column;

/**
 * This class is an encapulation of a single entry for
 * all of the previous permissions that a user has allowed
 * for a client. This is so we dont ask for same permissions
 * if the user has already allowed it.
 * 
 * @Author Jack Timblin - u1051575
 */
class ClientPermissions extends Model {
    
    //the unique clientID.
    private $clientID;
    
    //the unique userID this client has previous 
    //permissions for.
    private $userID;
    
    //the previous permissions.
    private $scope;
    
    /**
     * called by the model to set the database table name to map to this model.
     * @return string the database table name.
     */
    public function getSource() {
        return 'clientpermissions';
    }
    
    public function validation() {
        
        $this->validate(new Uniqueness(
            array(
                'field' => array('userID', 'clientID'),
                'message' => 'user/client ID needs to be unique'
            )
        ));
        
        if($this->validationHasFailed()) {
            return false;
        }
        
    }
    
    /**
     * called by model before database operations are performed.
     * converts the permissions array to a string.
     */
    public function beforeSave() {
        $this->scope = join(',', $this->scope);
    }
    
    /**
     * called by the model after a model has been created.
     * converts the permissions into an array.
     */
    public function afterFetch() {
        $this->scope = explode(',', $this->scope);
    }
    
    /**
     * sets the permissions array.
     * @param array $scope the permissions array
     * @return \Models\ClientPermissions returns itself for method chaining.
     */
    public function setScope($scope) {
        $this->scope = $scope;
        return $this;
    }
    
    /**
     * sets the clientID for this model.
     * @param string $clientID the clientID.
     * @return \Models\ClientPermissions returns itself for method chaining.
     */
    public function setClientID($clientID) {
        $this->clientID = $clientID;
        return $this;
    }
    
    /**
     * Sets the userID for this model.
     * @param string $userID the unique userID.
     * @return \Models\ClientPermissions returns itself for method chaining.
     */
    public function setUserID($userID) {
        $this->userID = $userID;
        return $this;
    }
    
    /**
     * returns the array of permissions
     * @return array the array of permissions.
     */
    public function getScope() {
        return $this->scope;
    }
    
    /**
     * retruns the clientID for this instance.
     * @return string the unique clientID.
     */
    public function getClientID() {
        return $this->clientID;
    }
    
    /**
     * returns the userID for this instance.
     * @return string the userID.
     */
    public function getUserID() {
        return $this->userID;
    }
    
    /**
     * checks to see if a request has already gained a single permission.
     * @param string $permission the permission to check.
     * @return TRUE if permission has already been given, FALSE if not.
     */
    public function hasPermissions($permission) {
        return in_array(strtolower($permission), array_map('strtolower', $this->scope));
    }
    
    /**
     * adds more valid perissions to this model.
     * @param array $permissions the list of permissions.
     */
    public function addPermissions($permissions) {
        foreach(array_map('strtolower', $permissions) as $permission) {
            if(!$this->hasPermissions($permission)) {
                if(!in_array($permission, $this->scope)) {
                    $this->scope[] = $permission;
                }
            }
        }
        
        //save this model.
        $this->save();
    }
    
    public static function findPermissions($clientID, $userID) {
        $previousPermissions = \Models\ClientPermissions::findFirst(
             array(
                 'conditions' => 'userID = ?1 AND clientID = ?2',
                 'bind' => array(
                     1 => $userID,
                     2 => $clientID
                 ),
                 'bindTypes' => array(
                     1 => Column::BIND_TYPE_STR,
                     2 => Column::BIND_TYPE_STR
                 )
             )
        );
        
        return (!is_bool($previousPermissions)) ? $previousPermissions : null;
    }
}

