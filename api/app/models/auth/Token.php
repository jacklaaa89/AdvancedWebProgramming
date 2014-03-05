<?php

namespace Models\Auth;

use \Phalcon\Db\Column;

/**
 * this class will encapsulate a Token saved on the Auth Server,
 * except it does not need functions to generate them.
 * 
 * @Author - Jack Timblin - U1051575
 */
class Token extends \Phalcon\Mvc\Model {
    
    public function initialize() {
        $this->setConnectionService('authdb');
    }
    
    public static function findToken($token) {
        
        return \Models\Auth\Token::findFirst(array(
            'conditions' => 'token = ?1',
            'bind' => array(1 => $token),
            'bindTypes' => array(1 => Column::BIND_TYPE_STR)
        ));
        
    }
    
}

