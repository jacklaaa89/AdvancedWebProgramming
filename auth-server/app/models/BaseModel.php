<?php

namespace Models;

use \Phalcon\Mvc\Model;

class BaseModel extends Model {


	/********************/
	/* Static Functions */

	/* 
	 * This function just generates a random string of a given length by
	 * shuffling a alphanumeric string, then hashing it using sha256 and
	 * finally base64_encoding it.
	 *
	 * @param $length [optional] the length of the string required, default
	 * 							 is 10 characters.
	 * @return string a random string.
	 */
	public static function generateID($length = 10) {
		$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
		return substr(base64_encode(hash('sha256', $randomString)), 0, $length);
	}

	/* End Static Functions */
	/************************/

}