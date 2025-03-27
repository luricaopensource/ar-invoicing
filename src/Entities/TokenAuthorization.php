<?php 

namespace AFIP\Entities;

/**
 * Token Access Structure
 * 
 * Properties: token, sign, expirationTime
 */
class TokenAuthorization {
	/**
	 * Authorization and authentication web service Token
	 *
	 * @var string
	 **/
	private $token;

	/**
	 * Authorization and authentication web service Sign
	 *
	 * @var string
	 **/
	private $sign;
    
	/**
	 * Expiration time
	 *
	 * @var string
	 **/
	private $expirationTime;

	function __construct($token, $sign, $expirationTime)
	{
		$this->token 	        = $token;
		$this->sign 	        = $sign;
		$this->expirationTime 	= $expirationTime;
	}

	public function getToken()
	{
		return $this->token;
	}

	public function getSign()
	{
		return $this->sign;
	}
}