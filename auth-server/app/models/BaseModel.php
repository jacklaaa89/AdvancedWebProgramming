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
        if (count(array_intersect($keys, array_keys($params))) != count($keys)) {
            return false;
        }

        foreach ($keys as $key) {
            if (!is_string($params[$key]) || strlen($params[$key]) == 0) {
                return false;
            }
        }

        return true;
    }

    public static function hasNewPermissions($requestScope, $tokenScope) {
        for ($i = 0; $i < count($tokenScope); $i++) {
            if (in_array($tokenScope[$i], $requestScope)) {
                unset($requestScope[$i]);
            }
        }
        sort($requestScope);
        if (count($requestScope) > 0) {
            return $requestScope;
        }
        return false;
    }

    public static function checkTokenPermissions($request, $token) {
        if (!is_bool($token)) {
            //check if the request has additional parameters, i.e we only need to authorize a
            //request if it requires additional scopes.

            $perms = self::hasNewPermissions($request->getScope(), $token->getScope());
            //TODO - hyperthetical if request scope is user, and token has user,message scope
            //then the token already covers all of the permissions needed, and thus auth is not
            //required.
            if (!$perms) {
                //delete the request object.
                $r_uri = $request->getRedirectURI();
                $request->delete();
                $link = $r_uri . '?'
                        . http_build_query(array(
                            'error' => 'auth_error',
                            'error_description' => 'Auth completed with no new permissions'
                ));
                return $link;
            }

            //the client needs additional permissions, but we only
            //want to display the new permissions required.
            $request->setScope($perms)
                    ->save();
        }
        return true;
    }

    public static function parsePermission($name) {
        switch (strtoupper($name)) {
            case 'USER':
                return 'Personal information, including name and email address.';
            case 'MESSAGES':
                return 'Messages that you have sent.';
            default:
                return 'Could not parse permission';
        }
    }

}
