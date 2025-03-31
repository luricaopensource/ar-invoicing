<?php 

namespace AFIP\Services; 

use AFIP\Afip;
use AFIP\Entities\TokenAuthorization;
use AFIP\Exceptions\AfipServiceException;
use AFIP\Services\AfipSoapService;

use SoapFault;

/**
 * Afip meta class for call webservices resources
 * 
 */
class AfipRequestService {

	const TAG = 'AfipRequestService';

    protected Afip $afip; 
	private string $WSDL =""; 
	private string $URL ="";
    private $SOAP_VERSION = SOAP_1_2;
    protected $soap_client;
	private float $startTime = 0.0;
	private float $endTime = 0.0;

	protected AfipLogService $logger;

	const LOG_RESPONSE = true;
	const TRACE = true;

	private $defaultTimeout = 1;

    function __construct(Afip $afip)
    {
        $this->afip = $afip; 
		$this->logger = $afip->getLogger();

		$this->defaultTimeout = (int) ini_get('default_socket_timeout');
    }

	public function setSoapVersion(int $value){
		$this->SOAP_VERSION = $value;
	}

	/**
	 * Switch WSDL between production or dev
	 *
	 * @param array $options
	 * @return void
	 */
    protected function configService(array $options){
        if($this->afip->isProduction()){
            $this->WSDL = $options['WSDL_PROD'];
            $this->URL = $options['URL_PROD'];
        }
        else{
            $this->WSDL = $options['WSDL_DEV'];
            $this->URL = $options['URL_DEV'];
        } 
    }

	/**
	 * Get the token authorization from afip
	 *
	 * @return TokenAuthorization
	 */
	public function getTokenAuthorization() : TokenAuthorization
	{
		return $this->afip->getTokenAuthorization();
	}

	/**
	 * Conect by SOAP to WDSL endpoint
	 *
	 * @return void
	 */
	protected function connect(){
		if (!isset($this->soap_client)) {

			$this->logger->info(self::TAG, "SOAP WDSL: {$this->WSDL}");
			$this->logger->info(self::TAG, "SOAP VERSION: {$this->SOAP_VERSION}");
			$this->logger->info(self::TAG, "SOAP URL: {$this->URL}");
			$this->logger->info(self::TAG, "SOAP TRACE: ".self::TRACE);
			$this->logger->info(self::TAG, "SOAP EXCEPTIONS: ".$this->afip->enableExceptions());

			$this->soap_client = new AfipSoapService($this->WSDL, array(
				'soap_version'   => $this->SOAP_VERSION,
				'location'       => $this->URL,
				'trace'          => self::TRACE,
				'exceptions'     => $this->afip->enableExceptions(), 
				'keep_alive'     => true,
				'stream_context' => stream_context_create(['ssl'=> ['ciphers'=> 'AES256-SHA','verify_peer'=> false,'verify_peer_name'=> false]])
			));
		}
	}

	private function enableTimeout(int $timeout){
		ini_set('default_socket_timeout', $timeout);
	}

	private function disableTimeout(){
		ini_set('default_socket_timeout', $this->defaultTimeout);
	}

	public function getRequestTime(){
		return round( $this->endTime - $this->startTime , 2);
	}

	/**
	 * Implement the WSDL Request
	 *
	 * @param string $operation
	 * @param array $params
	 * @return mixed
	 */
	public function request(string $operation, $params = array(), $timeout = FALSE)
	{
		$tmpForLog = $params;

		$represented = "";

		if (isset($tmpForLog['authRequest'])) {
			$represented = $tmpForLog['authRequest']['cuitRepresentada'];
			$tmpForLog['authRequest'] = "***[Hide for log]***";
		}
		$this->logger->info(self::TAG, "request {$operation} [REPRESENTED: {$represented}] \n".print_r($tmpForLog, true));

		$this->connect();

		$this->startTime = microtime(true);

		if( $timeout ) $this->enableTimeout($timeout);

		$results = null; 

		$exception = null;

		try {
			$results = $this->soap_client->{$operation}($params);
		}
		catch(SoapFault $e){
			$exception = $e;
			$this->logger->warn(self::TAG, "{$operation} Exception: ".$e->getMessage());
		}
		finally{
			if(!is_null($exception))
				throw $e;
		}

		if( $timeout ) $this->disableTimeout();

		$this->endTime = microtime(true);

		$this->logger->info(self::TAG, "{$operation} Request Time: ".$this->getRequestTime().'s');

		$this->checkErrors($operation, $results); 

		if(self::LOG_RESPONSE){
			$this->logger->info(self::TAG, "response {$operation}\n".print_r($results, true));
		}
		
		if(is_null($results)){
			throw new AfipServiceException("{$operation} nullpoint exception");
		}

		return $results;
	}

	/**
	 * Check if has errors
	 *
	 * @param string $operation
	 * @param mixed $results
	 * @return void
	 */
    private function checkErrors(string $operation, $results)
	{
		if (is_soap_fault($results)) { 
			throw new AfipServiceException("{$operation} SOAP Fault: \n".print_r($results, true), $this->logger);
		}
	}

	/**
	 * Check if has all param required
	 *
	 * @param array $params
	 * @param array $validArg
	 * @return void
	 */
	protected function checkArgs(array $params, array $validArg){
        foreach($validArg as $key){
            if(!isset($params[$key]))
                throw new AfipServiceException("Param {$key} not found", $this->logger);
        }
    }

	public function getlistMethods(){

        $this->connect();
        $list = $this->soap_client->__getFunctions();
        return $list;
    }

	public function getSoapClient(){

        return $this->soap_client;
    }
}