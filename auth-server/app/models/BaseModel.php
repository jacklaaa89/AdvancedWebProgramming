<?php

namespace Models;

use \Phalcon\Mvc\Model;

class BaseModel extends Model {

    /**
     * This function just generates a random string of a given length by
     * shuffling a alphanumeric string, then hashing it using sha256 and
     * finally base64_encoding it.
     *
     * @param $length [optional] <p>the length of the string required, default is 10 characters.</p>
     * @return string a random string.
     */
    public static function generateID($length = 10) {
        $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
        return substr(base64_encode(hash('sha256', $randomString)), 0, $length);
    }
    
    public static function validateInput($params, $keys) {
        
        //check all of the required keys are in the params.
        if(count(array_intersect($keys, array_keys($params))) != count($keys)) {
            return false;
        }

        foreach($keys as $key) {
            if(!is_string($params[$key]) || strlen($params[$key]) == 0) {
                return false;
            }
        }
        
        return true;
    }
    
    public static function array_equal($a1, $a2) {
        return !array_diff($a1, $a2) && !array_diff($a2, $a1);
    }

}
