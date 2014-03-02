<?php

namespace Models;

use Models\BaseModel,
    Phalcon\Db\Column,
    Phalcon\Mvc\Model\Validator\Uniqueness;

/**
 * This class is an encapulation of an single request
 * that is made to this authorisation server. A request is saved 
 * in the database as a reference for when the authorisation URL requires
 * it.
 * It is deleted after this to remove redundant entries.
 *
 * @Author Jack Timblin - U1051575
 */
class Request extends BaseModel {
   
    //the unique ID of this request.
    private $requestID;
    //the unique ID of the client 
    //performing the request.
    private $clientID;
    //the redirect URI to use in this
    //request as it may be provided.
    private $redirectURI;
    //the timestamp of when this request was generated.
    private $timestamp;
    //an array of permissions requested.
    private $scope;

    /**
     * This function overrides the getSource function in Model.
     * It returns the database table that this Model is mapped to.
     */
    public function getSource() {
        return "request";
    }

    public function initialize() {
        $this->belongsTo('clientID', 'Client', 'clientID');
    }
    
    /**
     * Called by the model just before the database operation is executed.
     * Glues the permissions array as a string for db storage.
     */
    public function beforeSave() {
        $this->scope = join(',', $this->scope);
    }
    
    /**
     * Called just after the create operation is executed.
     * explodes the db string to permissions array.
     */
    public function afterFetch() {
        $this->scope = explode(",", $this->scope);
    }

    /**
     * This method is called before the model is saved to the database
     * i.e when model->save() is called. This validates that the 
     * requestID generated for this request is unique, and fails if not.
     */
    public function validation() {
        //perform validation on this model before saving/updating.
        //the requestID must be unique
        $this->validate(new Uniqueness(
                array(
            'field' => 'requestID',
            'message' => 'The requestID must be unique.'
                )
        ));

        //check if a validation message has been produced.
        if ($this->validationHasFailed()) {
            return false;
        }
    }

    /**
     * This function validates that this request is valid.
     * i.e that all of the parameters in this request are valid.
     * 
     * @return boolean <b>TRUE</b> if request is valid, <b>FALSE</b> otherwise.
     */
    public function isValidRequest() {

        //this request is treated as valid by default.
        $valid = true;

        //check the client.
        if (is_bool($this->getClient())) {
            $valid = false;
        }

        //check the timestamp.
        if (!date('Y-m-d', $this->timestamp)) {
            $valid = false;
        }

        //check the redirectURI
        if (!filter_var($this->redirectURI, FILTER_VALIDATE_URL)) {
            $valid = false;
        }

        //check the requestID.
        if (!isset($this->requestID)) {
            $valid = false;
        }

        return $valid;
    }

    public function getRequestID() {
        return $this->requestID;
    }
    
    /**
     * returns the permissions array made with this request.
     * @return array the permission array.
     */
    public function getScope() {
        return $this->scope;
    }

    /**
     * Returns the client object associated with this  
     * request or false if a client was not found.
     * 
     * @return \Models\Client - the client that made the request.
     */
    public function getClient() {

        //first check that the ID isset.
        if (!isset($this->clientID)) {
            return false;
        }

        //findFirst of the Model class finds the first entry
        //matching the column(s) to value(s). binding is optional,
        //but forcing placeholders and types on columns 
        //reduces the risk of SQL injection.
        $client = \Models\Client::findFirst(
            array(
                'conditions' => 'clientID = ?1',
                'bind' => array(1 => $this->clientID),
                'bindTypes' => array(1 => Column::BIND_TYPE_STR)
            )
        );

        //just return the client as if one was not found,
        //false will be returned anyway.
        return $client;
    }

    /**
     * Returns the redirect URI to use.
     * Obviously we cannot return the one supplied
     * by the client as a different one may have been
     * provided.
     *
     * @return RedirectURI - the redirectURI. 
     */
    public function getRedirectURI() {
        return $this->redirectURI;
    }

    /**
     * Returns the UNIX timestamp of
     * when this request was made.
     *
     * @return INT the UNIX timestamp.
     */
    public function getTimestamp() {
        return $this->timestamp;
    }
    
    /**
     * set the permissions required in this request.
     * @param array $scope the permissions array.
     * @return $this returns itself for method 'chaining'
     */
    public function setScope($scope) {
        $this->scope = $scope;
        return $this;
    }

    /**
     * Sets the requestID for this request.
     * This value is unique, but the validation
     * for this is carried out in validation();
     *
     * @param $requestID the requestID for this request.
     * @return $this returns itself for method 'chaining'
     * @see /Models/Request->validation();
     */
    public function setRequestID($requestID) {

        //all validation on the ID are carried out
        //in model validation.
        $this->requestID = $requestID;
        return $this;
    }

    /**
     * Sets the RedirectURI to use for this request.
     * @param $redirectURI the redirectURI to redirect to.
     * @return $this returns itself for method 'chaining'
     */
    public function setRedirectURI($redirectURI) {
        $this->redirectURI = $redirectURI;
        return $this;
    }

    /**
     * sets the clientID for this request.
     * This needs to be called before we can call
     * $this->getClient() as this searches the db for
     * a /Models/Client entry that matches the ID.
     *
     * @param $clientID the id of the client this request is for.
     * @return $this returns itself for method 'chaining'
     */
    public function setClientID($clientID) {
        if (is_numeric($clientID)) {
            $this->clientID = $clientID;
        }
        return $this;
    }

    /**
     * Sets the timestamp for when this request was made.
     * 
     * @param $timestamp the UNIX timestamp.
     * @return $this returns itself for method 'chaining'
     */
    public function setTimestamp($timestamp) {
        $this->$timestamp = $timestamp;
        return $this;
    }

    /**
     * Generates a new Request object, this function also carrys out all of the required
     * validation that is needed to be performed on a Request before it is deemed valid.
     * @param string $clientID <p>The unique ID of the client making the request.</p>
     * @param string|int $timestamp [optional] <p>The UNIX timestamp of when this request was made, 
     * can either be a string function call i.e 'time()' or the int timestamp. Default is 'time()'</p>
     * @param string $redirectURI [optional] <p>The redirectURI, default is null (use the clients default).</p>
     * @return \Models\Request|boolean the request object on success, <b>FALSE</b> otherwise.
     */
    public static function requestBuilder($clientID, $scope, $timestamp = 'time', $redirectURI = null) {
        //generate a requestID
        $requestID = \Models\Request::generateID();

        //generate a new request.
        $request = new \Models\Request();
        $request->setRequestID($requestID)
                ->setClientID($clientID)
                ->setScope($scope);
        
        //check if the timestamp is a int or a function call.
        if(!is_numeric($timestamp)) {
            $timestamp = (function_exists($timestamp)) ? $timestamp() : time();
        }
        
        $request->setTimestamp($timestamp);

        $client = $request->getClient();

        if (is_bool($client)) {
            return false; //could not find client.
        }

        //check if a redirect URI was provided and that it is valid.
        if (isset($redirectURI)) {
            if (filter_var($redirectURI, FILTER_VALIDATE_URL)) {
                //check that the hostname is the same as the 
                //one supplied for the client. (prevents XSS Attacks)
                //parse the urls into associative array.
                $url = parse_url($redirectURI);
                $curl = parse_url($client->getDefaultRedirectURI());

                //if the hosts are the same, regardless of case.
                if (strtolower($curl['host']) == strtolower($url['host'])) {
                    $request->setRedirectURI($redirectURI);
                }
            }
        }

        //check if the redirect uri was set from the parameter, if not
        //use the default.
        $ru = $request->getRedirectURI();
        if (!isset($ru)) {
            $request->setRedirectURI($client->getDefaultRedirectURI());
        }

        if (!$request->isValidRequest()) {
            return false; //invalid request.
        }

        //attempt to save this request.
        if (!$request->save()) {

            //validation error, try again, generating a new ID.
            \Models\Request::requestBuilder($clientID, $redirectURI, $timestamp);
        }

        //return the request object.
        return $request;
    }

    /**
     * Attempts to find a Request object by its requestID.
     * This function also deletes the model from the database.
     * 
     * @return mixed the Request object on success, FALSE otherwise.
     */
    public static function findRequestByID($requestID) {

        $request = \Models\Request::findFirst(
            array(
                'conditions' => 'requestID = ?1',
                'bind' => array(1 => $requestID),
                'bindTypes' => array(1 => Column::BIND_TYPE_STR)
            )
        );

        if (!is_bool($request)) {
            $request->delete();
        }

        return $request;
    }
}
