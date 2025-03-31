<?php 

namespace AFIP\Services;
 
use SoapClient; 

/**
 * Afip wrap for soap 
 */
class AfipSoapService extends SoapClient{

    function __construct(?string $wsdl, array $options = []){
        parent::__construct($wsdl, $options);  
    } 
    
    public function __doRequest( string $request, string $location, string $action, int $version, bool $oneWay = false ) :  ?string {
  
        return parent::__doRequest($request, $location, $action, $version, $oneWay);
    }
}