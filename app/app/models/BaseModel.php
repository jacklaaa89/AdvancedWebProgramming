<?php

namespace Model;

class BaseModel extends \Phalcon\Mvc\Model {
    
    /**
     * This function just generates a random string of a given length by
     * shuffling a alphanumeric string, then hashing it using sha256 and
     * finally base64_encoding it.
     *
     * @param $length [optional] <p>the length of the string required, default is 10 characters.</p>
     * @return string a random string.
     */
    public function generateID($length = 10) {
        $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
        return substr(base64_encode(hash('sha256', $randomString)), 0, $length);
    }
    
}

