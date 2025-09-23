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
     * WSAA webservice login with credentials from database
     *
     * @param string $certContent Certificate content as string
     * @param string $keyContent Private key content as string
     * @param string $passphrase Passphrase for private key (optional)
     * @return TokenAuthorization
     */
    public function loginWithCredentials(string $certContent, string $keyContent, string $passphrase = '') {
        $this->logger->log(self::TAG, "loginWithCredentials");
        
        // Validar que los certificados no estén vacíos
        if (empty($certContent) || empty($keyContent)) {
            $this->logger->err(self::TAG, "Los certificados no pueden estar vacíos");
            throw new Exception("Los certificados no pueden estar vacíos");
        }
        
        // Validar que contengan el formato PEM básico
        if (strpos($certContent, '-----BEGIN') === false || strpos($keyContent, '-----BEGIN') === false) {
            $this->logger->err(self::TAG, "Los certificados deben estar en formato PEM válido");
            throw new Exception("Los certificados deben estar en formato PEM válido");
        }
        
        // Validar que los certificados sean válidos usando OpenSSL
        $certResource = openssl_x509_read($certContent);
        if ($certResource === false) {
            $this->logger->err(self::TAG, "El certificado no es válido");
            throw new Exception("El certificado no es válido");
        }
        //openssl_x509_free($certResource);
        
        $keyResource = openssl_pkey_get_private($keyContent, $passphrase);
        if ($keyResource === false) {
            $this->logger->err(self::TAG, "La clave privada no es válida o la passphrase es incorrecta");
            throw new Exception("La clave privada no es válida o la passphrase es incorrecta");
        }
        //openssl_pkey_free($keyResource);
        
        // Crear configuración temporal con los certificados proporcionados
        $tempConfig = $this->toArray();
        $tempConfig['CERT'] = $certContent;
        $tempConfig['PRIVATEKEY'] = $keyContent;
        $tempConfig['PASSPHRASE'] = $passphrase;
        
        $auth = new AfipAuthService($tempConfig); 
        $auth->setLogger($this->logger);
        $this->tokenAuthorization = $auth->authenticate($this->service, $this->stopOnError);
        
        return $this->tokenAuthorization;
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