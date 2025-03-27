<?php
namespace AFIP;

use AFIP\Services\AfipAuthService;
use Exception;
use stdClass;

spl_autoload_register(function ($class) {

    $request = $class;

    $class = str_replace("\\"  , "/", $class);
    $class = str_replace("AFIP/", "", $class);

    $path = __DIR__ .'/'. $class . '.php';

    if(!file_exists($path)) throw new Exception("Class {$request} can't loaded");

    require_once($path);
});

use AFIP\Services\AfipEnvironmentService;
use AFIP\Entities\TokenAuthorization;
use AFIP\Exceptions\AfipServiceException; 

/**
 * SDK for AFIP's webservices
 * 
 * This library help to consume weservice methods
 * 
 * @inherits AfipEnvironmentService
 */
class Afip extends AfipEnvironmentService {

    const TAG = 'Afip';

    /**
     * Service to connect
     *
     * @var string 
     */
    private string $service = "";

    /**
     * Stop execution process
     *
     * @var boolean 
     */
    private bool $stopOnError = FALSE;
    
    /**
     * Object ticket authoization
     *
     * @var TokenAuthorization 
     */
    private TokenAuthorization $tokenAuthorization; 

    /**
     * Store dynamically instancie services
     *
     * @var stdClass 
     */
    private stdClass $bindInstancie; 

    /**
     * Load process (two way):
     * 
     * 1. by JSON (static) mode = FALSE
     * 2. by Array (dynamic) mode = TRUE
     *
     * @param boolean $mode
     */
    function __construct(bool $mode=FALSE){
        parent::__construct($mode); 
        $this->bindInstancie = new stdClass;

        $this->logger->log(self::TAG, "--------------------");
        $this->logger->log(self::TAG, "AFIP mode: ". ( $this->PRODUCTION ? 'PRODUCCION' : 'HOMOLOGACION' ) );
        
    } 

    /**
     * Set onError value
     *
     * @param boolean $value
     * @return void
     */
    public function setOnError(bool $value){
        $this->stopOnError = $value;
    }

    /**
     * Get Afip access token
     *
     * @return TokenAuthorization
     */
    public function getTokenAuthorization(): TokenAuthorization {
        return $this->tokenAuthorization;
    }

    /**
     * Service to use
     *
     * @param string $value
     * @return Afip
     */
    public function service(string $value) : Afip {
        $this->service = $value;
        return $this;
    }

    /**
     * WSAA webservice login
     *
     * @param string $service name of service to authorize
     * @return void
     */
    public function login(){

        $this->logger->log(self::TAG, "login");
        
        $auth = new AfipAuthService( $this->toArray () ); 
        $auth->setLogger($this->logger);
        $this->tokenAuthorization = $auth->authenticate($this->service, $this->stopOnError );
    }

    /**
     * Call to service
     *
     * @return object
     */
    public function factory() : object {
 
        if( !isset($this->SERVICES->{$this->service}) )
            throw new AfipServiceException("Service {$this->service} not found");

        if( isset( $this->bindInstancie->{$this->service} ) )
            return $this->bindInstancie->{$this->service} ;

        $property  = $this->SERVICES->{$this->service};  

        $this->bindInstancie->{$this->service} = new $property($this); 
        
        return $this->bindInstancie->{$this->service};
    }
}