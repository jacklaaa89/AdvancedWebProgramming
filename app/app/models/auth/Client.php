<?php

namespace Model\Auth;

/**
 * this is a read-only version of a client instance.
 * @author Jack Timblin - U1051575
 */
class Client extends \Phalcon\Mvc\Model {
    /**
     * the name of the client
     * @var string 
     */
    private $name;
    
    /**
     * overrides initialize in Model
     * sets the connection to the auth-server server
     */
    public function initialize() {
        $this->setConnectionService('authdb');
    }
    
    /**
     * overrides getSource in Model
     * sets the table this Model should map to.
     * @return string
     */
    public function getSource() {
        return 'client';
    }
    
    /**
     * returns the name of the client
     * @return string
     */
    public function getName() {
        return $this->name;
    }
    
}

