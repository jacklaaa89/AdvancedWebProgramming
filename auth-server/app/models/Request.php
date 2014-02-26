<?php

namespace Models;

use Phalcon\Mvc\Model,
    Phalcon\Db\Column,
    Phalcon\Mvc\Model\Message,
    Phalcon\Mvc\Model\Validator\InclusionIn,
    Phalcon\Mvc\Model\Validator\Uniqueness;

/*
 * This class is an encapulation of an single request
 * that is made to this authorisation server. A request is saved 
 * in the database as a reference for when the authorisation URL requires
 * it.
 * It is deleted after this to remove redundant entries.
 *
 * @Author Jack Timblin - U1051575
 */
class Request extends Model {

	/**************/
	/* Parameters */

	//the unique ID of this request.
	private $requestID;

	//the unique ID of the client 
	//performing the request.
	private $clientID;

	//the redirect URI to use in this
	//request as it may be provided.
	private $redirectURI;
	
	/* End Parameters */
	/******************/

	/*
	 * This function overrides the getSource function in Model.
	 * It returns the database table that this Model is mapped to.
	 */
	public function getSource() {
		return "request";
	}

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
		if($this->validationHasFailed()) {
			return false;
		}
	}

	/*********************/
	/* Getters & Setters */

	public function getRequestID() {
		return $this->requestID;
	}

	/*
	 * Returns the client object associated with this  
	 * request or false if a client was not found.
	 * 
	 *@return Client - the client that made the request.
	 */
	public function getClient() {

		//first check that the ID isset.
		if(!isset($clientID)) {
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
				'bindTypes' => array(1 => Column::BIND_TYPE_INT)
			)
		);

		//just return the client as if one was not found,
		//false will be returned anyway.
		return $client;
	}

	/*
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

	public function setRequestID($requestID) {

		//all validation on the ID are carried out
		//in model validation.
		$this->requestID = $requestID;
		return $this;
	}

	public function setRedirectURI($redirectURI) {
		$this->redirectURI = $redirectURI;
		return $this;
	}

	public function setClientID($clientID) {
		if(is_numeric($clientID)) {
			$this->clientID = $clientID;
		}
		return $this;
	}

	/* End Getters & Setters */
	/*************************/

}