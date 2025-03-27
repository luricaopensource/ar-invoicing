<?php

namespace AFIP\Services;

use AFIP\Exceptions\AfipEnvException;

use stdClass;

class AfipEnvironmentService {

    private string $configFile = "config/env.json";
    protected string $WSAA_WSDL = "";
    protected string $WSAA_URL = "";
    protected string $CERT = "";
    protected string $PRIVATEKEY = "";
    protected string $PASSPHRASE = "";
    protected string $RES_FOLDER = "";
    protected string $TA_FOLDER = "";
    protected string $CUIT = "";
    protected bool $PRODUCTION = FALSE;
    protected bool $EXCEPTIONS = FALSE;
    protected stdClass $SOAP_CONF ;
    protected stdClass $SERVICES ;

    private string $ORIGIN_RES_FOLDER = '';
    private string $ORIGIN_TA_FOLDER = '';

    protected AfipLogService $logger;
    
    const PHP_SETUP_VARS = TRUE;

    const WSAA_URL_DEV   = 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms';
    const WSAA_URL_PROD  = 'https://wsaa.afip.gov.ar/ws/services/LoginCms'    ;

    const CURRENT_PATH   = 'src/Services';
    const RELATIVE_PATH  = 'src/Services';
    
    function __construct(bool|array $mode)
    {
        $this->SOAP_CONF = new stdClass;
        $this->SERVICES  = new stdClass;
        $this->loadConfig($mode);
    }

    public function isProduction(){
        return $this->PRODUCTION;
    }

    public function enableExceptions(){
        return $this->EXCEPTIONS;
    }

    public function getCUIT(){
        return $this->CUIT;
    }

    public function getLogger(){
        return $this->logger;
    }

    public function getResourceFolder($env = false){
        if($env == false)
        {
            return $this->RES_FOLDER;
        }
        else{
            $ORIGIN_RES_FOLDER = str_replace("{BASEPATH}", $this->_basepath(), $this->ORIGIN_RES_FOLDER);
            
            switch($env){
                case 'dev' : return str_replace("{ENV}", 'dev' , $ORIGIN_RES_FOLDER); break;
                case 'prod': return str_replace("{ENV}", 'prod', $ORIGIN_RES_FOLDER); break;
            }
        }
    }

    /**
     * Load config
     * 
     * mode = FALSE, load by JSON file
     * mode = ARRAY, load by array
     *
     * @param boolean|array $mode
     * @return void
     */
    private function loadConfig(bool|array $mode)
    {
        if(!$mode) 
            $this->loadConfigFile();
        else
            if(is_array($mode))
                $this->loadConfigOption($mode);
            else
                throw new AfipEnvException("No estan definidas las configuraciones.");

        $this->afterLoadConfig();
    } 

    /**
     * Open JSON file and load vars
     *
     * @return void
     */
    private function loadConfigFile() : void
    {
        $file = $this->_relativepath().$this->configFile;

        if( !file_exists($file) ) throw new AfipEnvException("El archivo de configuraciones ({$file}) no esta definido.");

        $rawJsonData = file_get_contents($file);

        if( !$rawJsonData || $rawJsonData == "" ) throw new AfipEnvException("El contenido del archivo de configuraciones (env.json) esta vacio o es invalido.");

        $jsonData = json_decode($rawJsonData);

        foreach((array)$jsonData as $key=>$value ){
            if(isset($this->{$key}))
                $this->{$key}=$value;
        
        }  
    }

    /**
     * Load vars by array
     *
     * @param array $option
     * @return void
     */
    private function loadConfigOption(array $option) : void 
    {
        foreach($option as $key=>$value ){
            if(isset($this->{$key}))
                $this->{$key}=$value;
        }
    }

    protected function _basepath()
    {
        return str_replace(self::CURRENT_PATH, "",__DIR__);
    }

    protected function _relativepath()
    {
        return str_replace(self::RELATIVE_PATH, "",__DIR__);
    }

    /**
     * Check if is valid CUIT format
     *
     * @param string $cuit
     * @return boolean
     */
    protected function isValidCUIT(string $cuit)
	{
		if (strlen($cuit) < 10) return false;
		
		$rv = false;
		$resultado = 0;
		$cuit_nro = str_replace("-", "", $cuit);
		
		$codes = "6789456789";
		$cuit_long = intVal($cuit_nro);
		$verificador = intVal($cuit_nro[strlen($cuit_nro)-1]);
        
		$x = 0;
		
		while ($x < 10)
		{
			$digitoValidador = intVal(substr($codes, $x, 1));
			$digito = intVal(substr($cuit_nro, $x, 1));
			$digitoValidacion = $digitoValidador * $digito;
			$resultado += $digitoValidacion;
			$x++;
		}
		$resultado = intVal($resultado) % 11;
		$rv = $resultado == $verificador;
		return $rv;
	}  

    public function renderEnvironment(){

        $options = [
            "WSAA_WSDL" ,
            "CERT" ,
            "PRIVATEKEY", 
            "CUIT" ,
            "PASSPHRASE",
            "RES_FOLDER", 
            "TA_FOLDER" 
        ];

        foreach($options as $key){
            $value = $this->{$key};
            if(strpos($value, "![") > -1 ) {
                $value = preg_replace('/\W+/', '', $value); 
                if(isset($_ENV[$value])) $this->{$key}= $_ENV[$value];
            } 
        }

        $this->EXCEPTIONS = isset($_ENV['ENVIRONMENT']) ? ( $_ENV['ENVIRONMENT'] == 'dev') : FALSE ;
        $this->PRODUCTION = isset($_ENV['ENVIRONMENT']) ? ( $_ENV['ENVIRONMENT'] != 'dev') : FALSE ;
    }

    /**
     * Validate vars options
     *
     * @return void
     */
    private function afterLoadConfig()
    {
        $this->renderEnvironment();

        $this->ORIGIN_RES_FOLDER = $this->RES_FOLDER;
        $this->ORIGIN_TA_FOLDER  = $this->TA_FOLDER;

        $this->RES_FOLDER  = str_replace("{BASEPATH}", $this->_basepath(), $this->RES_FOLDER);
        $this->TA_FOLDER   = str_replace("{BASEPATH}", $this->_basepath(), $this->TA_FOLDER);
        $this->RES_FOLDER  = str_replace("{ENV}", $this->PRODUCTION ? 'prod' : 'dev' , $this->RES_FOLDER);
        $this->TA_FOLDER   = str_replace("{ENV}", $this->PRODUCTION ? 'prod' : 'dev' , $this->TA_FOLDER);

        $this->logger = new AfipLogService($this->PRODUCTION, $this->RES_FOLDER );

        if ($this->PRODUCTION === TRUE) {
			$this->WSAA_URL = self::WSAA_URL_PROD;
		} else {
			$this->WSAA_URL = self::WSAA_URL_DEV;
		}

        $this->CERT         = $this->RES_FOLDER . '/' . $this->CERT      ;
        $this->PRIVATEKEY   = $this->RES_FOLDER . '/' . $this->PRIVATEKEY;
        $this->WSAA_WSDL    = $this->RES_FOLDER . '/' . $this->WSAA_WSDL ;

        if (!is_dir($this->RES_FOLDER)) 
            throw new AfipEnvException("Failed to check path to resources: ".$this->RES_FOLDER, $this->logger);
        if (!is_dir($this->TA_FOLDER)) 
            throw new AfipEnvException("Failed to check path to ticket access: ".$this->TA_FOLDER, $this->logger);

        if (!file_exists($this->CERT)) 
            throw new AfipEnvException("Failed to open CERT file: {$this->CERT}", $this->logger);
        if (!file_exists($this->PRIVATEKEY)) 
            throw new AfipEnvException("Failed to open KEY file: {$this->PRIVATEKEY}", $this->logger);
        if (!file_exists($this->WSAA_WSDL)) 
            throw new AfipEnvException("Failed to open WSAA_WSDL file: {$this->WSAA_WSDL}", $this->logger);
        
        if(!$this->isValidCUIT($this->CUIT))
            throw new AfipEnvException("CUIT number invalid: {$this->CUIT}", $this->logger);
        
        if(empty($this->PASSPHRASE))
            throw new AfipEnvException("PASSPHRASE can't be empty", $this->logger);

        if (self::PHP_SETUP_VARS) {
            if (isset($this->SOAP_CONF->{"soap.wsdl_cache_dir"})) {
                $this->SOAP_CONF->{"soap.wsdl_cache_dir"} = str_replace("{BASEPATH}", $this->_basepath(), $this->SOAP_CONF->{"soap.wsdl_cache_dir"});
            }

            foreach ((array)$this->SOAP_CONF as $key=>$value) {
                ini_set($key, $value);
            }
        }
    }
 
    /**
     * Return properties in array mode
     *
     * @return array
     */
    protected function toArray() : array {
        return [
            'WSAA_WSDL'  => $this->WSAA_WSDL    ,
            'WSAA_URL'   => $this->WSAA_URL     ,
            'CERT'       => $this->CERT         ,
            'PRIVATEKEY' => $this->PRIVATEKEY   ,
            'PASSPHRASE' => $this->PASSPHRASE   ,
            'RES_FOLDER' => $this->RES_FOLDER   ,
            'TA_FOLDER'  => $this->TA_FOLDER    ,
            'CUIT'       => $this->CUIT         ,
            'PRODUCTION' => $this->PRODUCTION   ,
            'EXCEPTIONS' => $this->EXCEPTIONS   ,
            'SOAP_CONF'  => $this->SOAP_CONF    ,
            'SERVICES'   => $this->SERVICES
        ];
    }
}