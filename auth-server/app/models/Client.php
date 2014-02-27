<?php

namespace Models;

use \Phalcon\Mvc\Model,
	Phalcon\Mvc\Model\Message,
	Phalcon\Db\Column,
    Phalcon\Mvc\Model\Validator\Uniqueness;

/*
 * This class is an encapulation of an single client.
 *
 * @Author Jack Timblin - U1051575
 */
class Client extends BaseModel {

	/**************/
	/* Parameters */

	//this is the ID for this client, it is
	//unique to this system.
	private $clientID;

	//this is the clients secret, which is used
	//in combination with the clientID to make
	//requests, should only be known to the 
	//client and this system.
	private $clientSecret;

	//this is the default redirect URI to 
	//redirect to when auth is complete.
	private $defaultRedirectURI;

	/* End Parameters */
	/******************/

	/*
	 * This function overrides the getSource function in Model.
	 * It returns the database table that this Model is mapped to.
	 */
	public function getSource() {
		return "client";
	}

	public function initialize() {
		$this->hasMany('clientID', 'Request', 'clientID');
	}

	/* 
	 * This function is an event method called by the model.
	 * It is called before the save operation is performed on the
	 * database. It checks to see that this client is valid before 
	 * saving.
	 */
	public function beforeSave() {
		//check that the client is valid before inserting.
		if(!$this->isValidClient()) {
			return false;
		}
	}

	/* 
	 * This function validates that this client is valid.
	 * i.e that all of the parameters in this client are valid.
	 * 
	 * @return bool TRUE if client is valid, FALSE otherwise.
	 */
	public function isValidClient() {

		$array = array( //class may inherit variables from parent.
			'clientid',
			'clientsecret',
			'defaultredirecturi'
		);
		
		foreach(get_object_vars($this) as $id => $value) {

			if(in_array(strtolower($id), $array)) {

				if(!isset($value)) {
					return false;
				}

				if(strtolower($id) == 'defaultredirecturi') {
					if(!filter_var($value, FILTER_VALIDATE_URL)) {
						return false;
					}
				}
			}
		}
	}

	/* 
	 * This method is called before the model is saved to the database
	 * i.e when model->save() is called. This validates that the 
	 * requestID generated for this client is unique, and fails if not.
	 */
	public function validation() {
		//perform validation on this model before saving/updating.

		//the clientID must be unique
		$this->validate(new Uniqueness(
			array(
				'field' => 'clientID',
				'message' => 'The clientID must be unique.'
			)
		));

		//the defaultRedirectUri has to be a valid URL.
		if(!filter_var($this->defaultRedirectUri, FILTER_VALIDATE_URL)) {
			$this->appendMessage(new Message("The defaultRedirectURI has to be a valid URL"));
		}

		//check if a validation message has been produced.
		if($this->validationHasFailed()) {
			return false;
		}
	}

	/*********************/
	/* Getters & Setters */

	public function getClientID() {
		return $this->clientID();
	}

	public function getClientSecret() {
		return $this->clientSecret;
	}

	public function getDefaultRedirectURI() {
		return $this->defaultRedirectURI;
	}

	public function setClientID($clientID) {
		$this->clientID = $clientID;
		return $this;
	}

	public function setClientSecret($clientSecret) {
		$this->clientSecret = $clientSecret;
		return $this;
	}

	public function setDefaultRedirectURI($defaultRedirectURI) {
		$this->defaultRedirectURI = $defaultRedirectURI;
		return $this;
	}

	/* End Getters & Setters */
	/*************************/

	/********************/
	/* Static Functions */

	public static function clientBuilder($defaultRedirectURI) {

		//generate an ID for this client.
		$clientID = \Models\Client::generateID(30);
		$clientSecret = \Models\Client::generateID(30);

		if($clientID == $clientSecret) {
			//retry.
			\Models\Client::clientBuilder($defaultRedirectURI);
		}

		//generate a new client object.
		$client = new \Models\Client();
		$client->setClientID($clientID)
			   ->setClientSecret($clientSecret)
			   ->setDefaultRedirectURI($defaultRedirectURI);

		if($client->isValidClient()) {
			if(!$client->save()) {
				//validation error, try again.
				\Models\Client::clientBuilder($defaultRedirectURI);
			}
			//return the client object.
			return $client;
		}

		//return false if it is not valid.
		return false;
	}

	/*
	 * Checks to see if a matching entry can be found based on
	 * the clientID and the clientSecret.
	 *
	 * @return bool TRUE if a client was found, FALSE otherwise.
	 */
	public static function checkIsValidClient($clientID, $clientSecret) {

		$client = \Models\Client::findFirst(
			array(
				'conditions' => 'clientID = ?1 AND clientSecret = ?2',
				'bind' => array(
					1 => $clientID,
					2 => $clientSecret
				),
				'bindTypes' => array(
					1 => Column::BIND_TYPE_STR,
					2 => Column::BIND_TYPE_STR
				)
			)
		);

		return !is_bool($client);

	} 

	/* End Static Functions */
	/************************/

}