<?php

namespace Utilities;

/**
 * This class has some helper functions that deal with the request
 * like getting the token from the request (as this can be set in either
 * the Authorization header or using the param access_token in the query string.)
 * @author Jack Timblin - U1051575
 */
class RequestHelper {
    
    /**
     * tries to get the Token object from the request headers.
     * @param array $headers the array of request headers.
     * @return mixed the Token object on success, FALSE otherwise.
     */
    public static function getToken($headers, $tokenParam) {
        //check if the tokenParam has been set.
        if(isset($tokenParam) && !is_bool($tokenParam) && strlen($tokenParam) != 0) {
            return \Models\Auth\Token::findToken($tokenParam);
        }
        //check if the auth header has been set.
        if(!array_key_exists(strtoupper('authorization'), $headers)) {
            return false;
        }
        //basic auth is not supported, so the valud should be "token OAUTH_TOKEN"
        if(!preg_match('/^token/i', $headers[strtoupper('authorization')])) {
            return false;
        }
        $t = explode(' ', trim($headers[strtoupper('authorization')]));
        return \Models\Auth\Token::findToken(trim($t[1]));
    }
    
    /**
     * sets the headers in the response.
     * @param \Phalcon\Mvc\Micro $app the web application instance.
     * @param array $headers an associative array of headers to set.
     */
    public static function setHeaders($app, $headers) {
        //set headers in response.
        foreach($headers as $tag => $value) {
            $app->response->setHeader($tag, $value);
        }
    }
}

