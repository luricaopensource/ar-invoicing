<?php 

namespace AFIP\Services;

use AFIP\Afip;
use AFIP\Exceptions\AfipResultException;
use AFIP\Services\AfipRequestService;
use stdClass;

/**
 * Afip Credit Invoice Service
 * 
 */
class AfipVoucherService extends AfipRequestService {

    const TAG = 'AfipVoucherService';

    const WSDL_DEV   = 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx?wsdl';
    const WSDL_PROD  = 'https://servicios1.afip.gov.ar/wsfev1/service.asmx?wsdl';

    const URL_DEV   = 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx';
    const URL_PROD  = 'https://servicios1.afip.gov.ar/wsfev1/service.asmx';

    const FILE_SPEC = 'wsfe.wsdl'; 

    const SOAP_VERSION = SOAP_1_1;

    private string $CUIT_REPRESENTED = '';

    private stdClass $paginate;

    function __construct(Afip $afip)
    {
        parent::__construct($afip);

        $this->setPaginateData(1, false);

        $this->configService([
            'WSDL_PROD'=> file_exists( $afip->getResourceFolder('prod').'/'.self::FILE_SPEC ) ? $afip->getResourceFolder('prod').'/'.self::FILE_SPEC  :  self::WSDL_PROD,
            'URL_PROD'=> self::URL_PROD,
            'WSDL_DEV'=> file_exists( $afip->getResourceFolder('dev').'/'.self::FILE_SPEC ) ? $afip->getResourceFolder('dev').'/'.self::FILE_SPEC  :  self::WSDL_DEV,
            'URL_DEV'=> self::URL_DEV
        ]);

        $this->setSoapVersion(self::SOAP_VERSION);
    }

    public function setCuitRepresented(string $value){
        $this->logger->info(self::TAG, __FUNCTION__ . " to {$value}");

        $this->CUIT_REPRESENTED = $value;
    }

    public function getCuitRepresented(){ 

        return $this->CUIT_REPRESENTED;
    } 

    private function authRequest()
    {
        $TA = $this->getTokenAuthorization();

        return [
            'Token' => $TA->getToken() ,
            'Sign'  => $TA->getSign() ,
            'Cuit'  => $this->CUIT_REPRESENTED ? $this->CUIT_REPRESENTED : $this->afip->getCUIT()
        ];
    }

    private function afterRequest(&$params){
        $params['Auth']= $this->authRequest();
    }

    public function getPaginateData() : stdClass {
        return $this->paginate;
    }

    private function setPaginateData(int $page, bool $hasMore){
        $output = new stdClass;
        $output->page = $page;
        $output->hasMore = $hasMore;

        $this->paginate = $output;
    }

    private function handleErrors($rawResult, $keyResultHandle){
     
        if( isset($rawResult->{$keyResultHandle}->arrayObservaciones->codigoDescripcion) ){
            $result = $rawResult->{$keyResultHandle}->arrayObservaciones->codigoDescripcion;

            throw new AfipResultException($result->descripcion, $this->logger);
        }

        if( isset($rawResult->{$keyResultHandle}->Errors->Err) ){
            $result = $rawResult->{$keyResultHandle}->Errors->Err;

            throw new AfipResultException($result->Msg, $this->logger);
        }

        if( isset($rawResult->{$keyResultHandle}->arrayErroresFormato->codigoDescripcion) ){
            $result = $rawResult->{$keyResultHandle}->arrayErroresFormato->codigoDescripcion;

            throw new AfipResultException($result->descripcion, $this->logger);
        }

        if( !isset($rawResult->{$keyResultHandle}) ) 
            throw new AfipResultException("Unknown content response: {$keyResultHandle}", $this->logger);
   
    }

    public function FECompUltimoAutorizado(array $params =[], $timeout = FALSE, $raw = FALSE)
    { 
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params); 

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        if(isset($rawResult->FECompUltimoAutorizadoResult))
            return $rawResult->FECompUltimoAutorizadoResult;

        $this->handleErrors($rawResult, "FECompUltimoAutorizadoResult"); 
    }

    public function FECAESolicitar(array $params =[], $timeout = FALSE, $raw = FALSE)
    { 
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params); 

        return $this->request(__FUNCTION__, $params, $timeout);
    }

    public function FECompConsultar(array $params =[], $timeout = FALSE, $raw = FALSE)
    { 
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params); 

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        //if(isset($rawResult->FECompConsultarResult))
        //    return $rawResult->FECompConsultarResult;

        $this->handleErrors($rawResult, "FECompConsultarResult"); 
    }

    public function FEParamGetTiposCbte(array $params =[], $timeout = FALSE, $raw = FALSE)
    { 
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params); 

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        if(isset($rawResult->FEParamGetTiposCbteResult->ResultGet->CbteTipo))
            return $rawResult->FEParamGetTiposCbteResult->ResultGet->CbteTipo;

        $this->handleErrors($rawResult, "FEParamGetTiposCbteResult"); 
    }

    public function FEParamGetTiposConcepto(array $params =[], $timeout = FALSE, $raw = FALSE)
    { 
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params); 

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        if(isset($rawResult->FEParamGetTiposConceptoResult->ResultGet->ConceptoTipo))
            return $rawResult->FEParamGetTiposConceptoResult->ResultGet->ConceptoTipo;

        $this->handleErrors($rawResult, "FEParamGetTiposConceptoResult"); 
    }

    public function FEParamGetTiposDoc(array $params =[], $timeout = FALSE, $raw = FALSE)
    { 
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params); 

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        if(isset($rawResult->FEParamGetTiposDocResult->ResultGet->DocTipo))
            return $rawResult->FEParamGetTiposDocResult->ResultGet->DocTipo;

        $this->handleErrors($rawResult, "FEParamGetTiposDocResult"); 
    }

    public function FEParamGetTiposIva(array $params =[], $timeout = FALSE, $raw = FALSE)
    { 
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params); 

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        if(isset($rawResult->FEParamGetTiposIvaResult->ResultGet->IvaTipo))
            return $rawResult->FEParamGetTiposIvaResult->ResultGet->IvaTipo;

        $this->handleErrors($rawResult, "FEParamGetTiposIvaResult"); 
    }

    public function FEParamGetTiposMonedas(array $params =[], $timeout = FALSE, $raw = FALSE)
    { 
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params); 

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        if(isset($rawResult->FEParamGetTiposMonedasResult->ResultGet->Moneda))
            return $rawResult->FEParamGetTiposMonedasResult->ResultGet->Moneda;

        $this->handleErrors($rawResult, "FEParamGetTiposMonedasResult"); 
    }

    public function FEParamGetTiposOpcional(array $params =[], $timeout = FALSE, $raw = FALSE)
    { 
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params); 

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        if(isset($rawResult->FEParamGetTiposOpcionalResult->ResultGet->OpcionalTipo))
            return $rawResult->FEParamGetTiposOpcionalResult->ResultGet->OpcionalTipo;

        $this->handleErrors($rawResult, "FEParamGetTiposOpcionalResult"); 
    }

    public function FEParamGetTiposTributos(array $params =[], $timeout = FALSE, $raw = FALSE)
    { 
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params); 

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        if(isset($rawResult->FEParamGetTiposTributosResult->ResultGet->TributoTipo))
            return $rawResult->FEParamGetTiposTributosResult->ResultGet->TributoTipo;

        $this->handleErrors($rawResult, "FEParamGetTiposTributosResult"); 
    }
 
    public function FEDummy(array $params=[], $timeout = FALSE, $raw = FALSE)
    {
        $this->logger->info(self::TAG, __FUNCTION__);
        
        $this->afterRequest($params);

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        $this->handleErrors($rawResult, "FEDummyResult");  

        return $rawResult->FEDummyResult; 
    }

    public function FESeguienteComprobante($data)
    {
        $cbte = $this->FECompUltimoAutorizado(['PtoVta' => $data['PtoVta'], 'CbteTipo' 	=> $data['CbteTipo'] ]);

        $cbtenro = $cbte['CbteNro'] + 1;

        $data['CbteDesde'] = $cbtenro;
		$data['CbteHasta'] = $cbtenro;

        //llamar a FECAESolicitar
    }
}