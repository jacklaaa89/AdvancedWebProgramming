<?php

namespace Utilities;

class RequestHelper {
    
    /**
     * tries to get the Token object from the request headers.
     * @param array $headers the array of request headers.
     * @return mixed the Token object on success, FALSE otherwise.
     */
    public static function getToken($headers) {
        //check if the auth header has been set.
        if(!array_key_exists(strtoupper('authorization'), $headers)) {
            return false;
        }
        //basic auth is not supported, so the valud should be "token OAUTH_TOKEN"
        if(!preg_match('/^token/i', $headers[strtoupper('authorization')])) {
            return false;
        }
        $t = explode(' ', trim($headers[strtoupper('authorization')]));
        return \Models\Token::findToken(trim($t[1]));
    }
    
    public static function setHeaders($app, $headers) {
        //set headers in response.
        foreach($headers as $tag => $value) {
            $app->response->setHeader($tag, $value);
        }
        
    }
}

