<?php 

namespace AFIP\Services;
 
use SimpleXMLElement;
use DateTime; 
use SoapFault;
use AFIP\Exceptions\AfipLoginException;
use AFIP\Entities\TokenAuthorization;
use AFIP\Services\AfipSoapService;

/**
 * Afip Authorization Service
 * 
 * Process afip's user login
 */
class AfipAuthService {

    const TAG = 'AfipAuthService';

    private string $loginTicketRequest = '<?xml version="1.0" encoding="UTF-8"?><loginTicketRequest version="1.0"></loginTicketRequest>';

    private string $fileXMLRequest  = '{file}.xml'; 
    private string $fileXMLResponse = '{file}.xml'; 
 
    private string $fileTMP      = '{file}.tmp'; 
    private string $fileCRT      = '' ; 
    private string $fileKEY      = '' ; 
    private string $phrase       = '' ; 
    private string $WSAA_WSDL    = '' ;
    private string $WSAA_URL     = '' ;
    private mixed $path          = FALSE;  
    private mixed $resPath       = FALSE;  
    private bool $exceptions     = FALSE;
    private string $ticketFormat = 'AUTH-{CUIT}-{ENV}';
    private string $ticketFormatIn = 'REQUEST-AUTH-{CUIT}-{ENV}';
    private string $ticketFormatOut = 'RESPONSE-AUTH-{CUIT}-{ENV}';
    private float $startTime = 0.0;
    private float $endTime = 0.0;

    private $logger;
    
    const READ_FILE = 'r';
    const EXPIRE    =  60;

    function __construct(array $option)
    {
        $this->config($option);
    }

    public function setLogger(AfipLogService $value){
        $this->logger = $value;
    }
    
    /**
     * Replace array data into string template
     *
     * @param string $template
     * @param array $vars
     * @return string
     */
    private function _replace(string $template, array $vars) : string {
        foreach($vars as $key=>$value){
            $template = str_replace("{{$key}}",$value, $template);
        }
        return $template;
    }
 
    /**
     * Get name of ticket
     *
     * @param string $cuit
     * @param string $env
     * @return string
     */
    private function getNameTicket(string $cuit, string $env) : string {
        return $this->_replace($this->ticketFormat, ['CUIT'=> $cuit, 'ENV'=> $env]);
    }

    /**
     * Setup configuration by array
     *
     * @param array $option
     * @return void
     */
    public function config( array $option ){

        $this->resPath      = $option['RES_FOLDER'  ]; 
        $this->path         = $option['TA_FOLDER'   ]; 
        $this->phrase       = $option['PASSPHRASE'  ];
        $this->WSAA_WSDL    = $option['WSAA_WSDL'   ];
        $this->WSAA_URL     = $option['WSAA_URL'    ];     
        $this->exceptions   = $option['EXCEPTIONS'  ];     
        $this->fileCRT      = $option['CERT'        ];
        $this->fileKEY      = $option['PRIVATEKEY'  ];  

        $this->fileTMP      = $this->path .'/'. str_replace('{file}', $this->getNameTicket($option['CUIT'], $option['PRODUCTION'] ? 'PROD' : 'DEV' ), $this->fileTMP);
        
        $this->fileXMLRequest  = $this->path .'/'. str_replace('{file}', $this->_replace($this->ticketFormatIn , ['CUIT'=> $option['CUIT'], 'ENV'=> $option['PRODUCTION'] ? 'PROD' : 'DEV'  ]) , $this->fileXMLRequest);
        $this->fileXMLResponse = $this->path .'/'. str_replace('{file}', $this->_replace($this->ticketFormatOut, ['CUIT'=> $option['CUIT'], 'ENV'=> $option['PRODUCTION'] ? 'PROD' : 'DEV'  ]) , $this->fileXMLResponse);

    }

    /**
     * Create XML Token Request file
     *
     * @param string $service
     * @return void
     */
    private function create(string $service) {

        $this->logger->log(self::TAG, "create {$service}");

        if( !$this->path ) throw new AfipLoginException("Path TA not defined");

        $epochTime = date('U');

        $TRA = new SimpleXMLElement($this->loginTicketRequest);
        $TRA->addChild('header');
        $TRA->header->addChild('uniqueId'      , $epochTime                );
        $TRA->header->addChild('generationTime', date('c', $epochTime - self::EXPIRE ));
        $TRA->header->addChild('expirationTime', date('c', $epochTime + self::EXPIRE ));
        $TRA->addChild('service', $service);
        $TRA->asXML($this->fileXMLRequest);

        $this->logger->info(self::TAG, "File {$this->fileXMLRequest} writed");
        $this->logger->info(self::TAG, "Epoch Base Time: {$epochTime }");
    }

    /**
     * Open XML Response and check expiration time
     *
     * @param boolean $continue
     * @return TokenAuthorization
     */
    private function read(bool $continue = TRUE) : TokenAuthorization 
    {
        $this->logger->log(self::TAG, "read {$this->fileXMLResponse}");

        $TA = new SimpleXMLElement(file_get_contents($this->fileXMLResponse));

        $actual_time 		= new DateTime(date('c', date('U') + self::EXPIRE ));
        $expiration_time 	= new DateTime($TA->header->expirationTime);

        if ($actual_time < $expiration_time) {
            $this->logger->info(self::TAG, "actual_time < expiration_time : Return TokenAuthorization ");
            return new TokenAuthorization($TA->credentials->token, $TA->credentials->sign, $expiration_time);
        }
        else if ($continue === FALSE){
            throw new AfipLoginException("Error Getting TA. Posible expiration: ".$expiration_time->format('d/m/Y H:i:s')." in current time: ".$actual_time->format('d/m/Y H:i:s'), $this->logger);
            unlink($this->fileXMLResponse);
        }
    }

    private function dropTmpFiles() {
        if (file_exists($this->fileTMP)) {
            $this->logger->info(self::TAG, "dropTmpFiles: {$this->fileTMP}");
            unlink($this->fileTMP);
        }
    }

    private function checkCertificate(){

        $this->logger->log(self::TAG, "checkCertificate: {$this->fileCRT}");

        $crtRawData = file_get_contents($this->fileCRT);

        $this->checkStreamCertificate($crtRawData);
    }

    private function checkStreamCertificate($text){
        $certData   = openssl_x509_parse($text);

        $actual_time 		= new DateTime(date('c', time() ));
        $expiration_time 	= new DateTime(date('c', $certData['validTo_time_t'] ));
 
        if($actual_time > $expiration_time)
        throw new AfipLoginException("Certificate Expired [".$certData['name']."]: ".$expiration_time->format('d/m/Y H:i:s')." in current time: ".$actual_time->format('d/m/Y H:i:s'), $this->logger);

        if(!$certData)
            throw new AfipLoginException("Invalid Certificate CRT {$this->fileCRT}", $this->logger);  

        $this->logger->info(self::TAG, "Certificate: ".$certData['name']);
    }

    /**
     * Sign XML by openSSL
     *
     * @return string
     */
    private function sign() : string
    {
        $this->logger->log(self::TAG, "sign pkcs7");

        if( !$this->path ) throw new AfipLoginException("Path not defined: {$this->path}", $this->logger);

        $i = 0;  $CMS = "";

        $this->checkCertificate(); 

        $status = openssl_pkcs7_sign( $this->fileXMLRequest, $this->fileTMP, "file://" .$this->fileCRT, array("file://" .$this->fileKEY, $this->phrase), array(), !PKCS7_DETACHED );

        if (!$status)  throw new AfipLoginException("Error generating PKCS#7 signature (status: false)", $this->logger);  

        $binTmp = fopen($this->fileTMP, self::READ_FILE);
        
        while (!feof($binTmp)) 
        {
            $buffer = fgets($binTmp);
            
            if ($i++ >= 4) $CMS .= $buffer; 
        }

        fclose($binTmp);

        $this->dropTmpFiles();

        return $CMS;
    }

	private function getRequestTime(){
		return round( $this->endTime - $this->startTime , 2);
	}

    /**
     * Connect to SOAP's Service Login
     *
     * @param string $CMS
     * @return boolean
     */
    private function connect(string $CMS ) : bool
    { 
        $this->logger->log(self::TAG, "connect {$CMS}");

        $WSAA_FILE = $this->resPath .'/'. $this->WSAA_WSDL ;
        $WSDL = file_exists($WSAA_FILE) ? $WSAA_FILE : $this->WSAA_WSDL;

        $this->logger->info(self::TAG, "SOAP WDSL: {$WSDL}");
        $this->logger->info(self::TAG, "SOAP VERSION: ".SOAP_1_2);
        $this->logger->info(self::TAG, "SOAP URL: {$this->WSAA_URL}");
        $this->logger->info(self::TAG, "SOAP TRACE: TRUE");
        $this->logger->info(self::TAG, "SOAP EXCEPTIONS: {$this->exceptions}");

		$client = new AfipSoapService($WSDL, array(
			'soap_version'   => SOAP_1_2,
			'location'       => $this->WSAA_URL,
			'trace'          => 1,
			'exceptions'     => $this->exceptions,
            'keep_alive'     => true,
			'stream_context' => stream_context_create(['ssl'=> ['ciphers'=> 'AES256-SHA','verify_peer'=> false,'verify_peer_name'=> false]])
		)); 

        $this->startTime = microtime(true);

        $results = null;

        try {
            $results=$client->loginCms(['in0'=>$CMS]);
        }
        catch(SoapFault $e){
            $this->logger->warn(self::TAG, $e->getMessage());
        }

        $this->endTime = microtime(true);

        $this->logger->info(self::TAG, "LoginCms Request Time: ".$this->getRequestTime().'s');
       
        if(is_null($results)){
            throw new AfipLoginException("LoginCms Invalid nullpoint", $this->logger);
        }

        if (is_soap_fault($results)) {
            throw new AfipLoginException("SOAP Fault (WSDL: {$this->WSAA_WSDL} , URL: {$this->WSAA_URL} ) : ".$results->faultcode." - ".$results->faultstring, $this->logger);
        }

        if(!isset($results->loginCmsReturn))
            throw new AfipLoginException("Response bad format", $this->logger);

		$TA = $results->loginCmsReturn;

		if (file_put_contents($this->fileXMLResponse, $TA)) 
			return TRUE;
		else
			throw new AfipLoginException("Error writing XML: {$this->fileXMLResponse}", $this->logger);
    }

    /**
     * Get Token Authorizacion
     *
     * @param string $service
     * @param boolean $continue
     * @return TokenAuthorization
     */
    public function authenticate(string $service, bool $continue = TRUE): TokenAuthorization
    {
        $this->logger->log(self::TAG, "authenticate {$service}"); 

        if( file_exists($this->fileXMLResponse) ){
            try {
                return $this->read($continue);
            }catch(AfipLoginException $ex){
                $this->dropTmpFiles();
            }
        } 
      
        $this->create($service);
        $CMS = $this->sign();

        $this->connect($CMS);
        return $this->read($continue);
    
    }
}