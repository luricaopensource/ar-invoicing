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
class AfipFcredService extends AfipRequestService {

    const TAG = 'AfipFcredService';

    const WSDL_DEV   = 'https://fwshomo.afip.gov.ar/wsfecred/FECredService?wsdl';
    const WSDL_PROD  = 'https://serviciosjava.afip.gob.ar/wsfecred/FECredService?wsdl';

    const URL_DEV   = 'https://fwshomo.afip.gov.ar/wsfecred/FECredService';
    const URL_PROD  = 'https://serviciosjava.afip.gob.ar/wsfecred/FECredService';

    const FILE_SPEC = 'FacturaDeCredito.xml'; 

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
            'token'             => $TA->getToken() ,
            'sign'              => $TA->getSign() ,
            'cuitRepresentada'  => $this->CUIT_REPRESENTED ? $this->CUIT_REPRESENTED : $this->afip->getCUIT()
        ];
    }

    private function afterRequest(&$params){
        $params['authRequest']= $this->authRequest();
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

        if( isset($rawResult->{$keyResultHandle}->arrayErrores->codigoDescripcion) ){
            $result = $rawResult->{$keyResultHandle}->arrayErrores->codigoDescripcion;

            throw new AfipResultException($result->descripcion, $this->logger);
        }

        if( isset($rawResult->{$keyResultHandle}->arrayErroresFormato->codigoDescripcion) ){
            $result = $rawResult->{$keyResultHandle}->arrayErroresFormato->codigoDescripcion;

            throw new AfipResultException($result->descripcion, $this->logger);
        }

        if( !isset($rawResult->{$keyResultHandle}) ) 
            throw new AfipResultException("Unknown content response: {$keyResultHandle}", $this->logger);
    }


    /**
     * Método que permite obtener información sobre los comprobates Emitidos y Recibidos
     *
     * @param array $params
     * @return mixed
     */
    public function consultarComprobantes(array $params =[], $timeout = FALSE, $raw = FALSE)
    { 
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params);
        
        $this->checkArgs($params, [ 'rolCUITRepresentada' ]);

        $rawResult =  $this->request(__FUNCTION__, $params, $timeout);


        if($raw) return $rawResult;

        $this->setPaginateData(
            isset($rawResult->consultarCmpReturn->nroPagina ) ?  $rawResult->consultarCmpReturn->nroPagina : 0,
            isset($rawResult->consultarCmpReturn->hayMas    ) ? ($rawResult->consultarCmpReturn->hayMas != 'N' ) : false,
        );

        if(isset($rawResult->consultarCmpReturn->arrayComprobantes->comprobante))
            return $rawResult->consultarCmpReturn->arrayComprobantes->comprobante;

        $this->handleErrors($rawResult, "consultarCmpReturn"); 
    }
 
    /**
     * Método que permite al Comprador Rechazar la Cta. Cte. de una Factura de Crédito debiendo indicar el motivo del rechazo.
     *
     * @param array $params
     * @return void
     */
    public function rechazarFECred(array $params=[], $timeout = FALSE, $raw = FALSE)
    {  
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params);

        $this->checkArgs($params, [ 'idCtaCte', 'arrayMotivosRechazo' ]);

        $rawResult =  $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        $this->handleErrors($rawResult, "operacionFECredReturn"); 

        return $rawResult->operacionFECredReturn;
    }

    /**
     * Método que permite obtener el detalle y composición de una cuenta corriente.
     *
     * @param array $params
     * @return void
     */
    public function consultarCtaCte(array $params=[], $timeout = FALSE, $raw = FALSE)
    { 
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params);

        $this->checkArgs($params, [ 'idCtaCte' ]);

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        if( isset($rawResult->consultarCtaCteReturn->ctaCte))
            return $rawResult->consultarCtaCteReturn->ctaCte;

        $this->handleErrors($rawResult, "consultarCtaCteReturn"); 
    }

    /**
     * Método que permite al Comprador rechazar Notas de Débito / Crédito individualmente para desafectarlas del cálculo del saldo de la Cta. Cte. vinculada.
     *
     * @param array $params
     * @return mixed
     */
    public function rechazarNotaDC(array $params=[], $timeout = FALSE, $raw = FALSE)
    { 
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params);

        $this->checkArgs($params, ['idComprobante', 'arrayMotivosRechazo' ]);

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        $this->handleErrors($rawResult, "rechazarNotaDCReturn"); 

        return $rawResult->rechazarNotaDCReturn;
    }

    /**
     * Método que permite al Vendedor solicitar la transeferencia (al Agente de Depósito Colectivo) de la factura de crédito con el saldo resultante de la cuenta corriente vinculada aceptada por el comprador, debiendo indicar la Cuenta del Agente de Deposito Colectivo.
     *
     * @param array $params
     * @return void
     */
    public function informarFacturaAgtDptoCltv(array $params=[], $timeout = FALSE, $raw = FALSE)
    {   
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params);

        $this->checkArgs($params, ['idCtaCte', 'ctaAgente']);

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        $this->handleErrors($rawResult, "operacionFECredReturn"); 

        return $rawResult->operacionFECredReturn;
    }

    /**
     * Método que permite al Vendedor consultar sus cuentas en sus Agentes de Deposito Colectivo
     *
     * @param array $params
     * @return void
     */
    public function consultarCuentasEnAgtDptoCltv(array $params=[], $timeout = FALSE, $raw = FALSE)
    {   
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params); 

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;
 
        if(isset($rawResult->consultarCuentasEnAgtDptoCltvReturn->arrayCuentasEnAgente))
            return $rawResult->consultarCuentasEnAgtDptoCltvReturn->arrayCuentasEnAgente;

        $this->handleErrors($rawResult, "consultarCuentasEnAgtDptoCltvReturn");  
    }

    /**
     * Método que permite consultar los tipos de retenciones habilitadas con sus respectivos porcentajes.
     *
     * @param array $params
     * @return void
     */
    public function consultarTiposRetenciones(array $params=[], $timeout = FALSE, $raw = FALSE)
    {   
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params);

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;
         
        if(isset($rawResult->consultarTiposRetencionesReturn->arrayTiposRetenciones))
            return $rawResult->consultarTiposRetencionesReturn->arrayTiposRetenciones;

        $this->handleErrors($rawResult, "consultarTiposRetencionesReturn");
    }

    /**
     * Método que permite obtener las cuentas corrientes que fueron generadas a partir de la facturación, que coinciden con los parámetros de búsqueda.
     *
     * @param array $params
     * @return void
     */
    public function ConsultarCtasCtes(array $params=[], $timeout = FALSE, $raw = FALSE)
    {   
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params);

        $this->checkArgs($params, [ 'rolCUITRepresentada' ]);

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        if( isset($rawResult->consultarCtasCtesReturn->arrayInfosCtaCte->infoCtaCte) )
            return $rawResult->consultarCtasCtesReturn->arrayInfosCtaCte->infoCtaCte;

        $this->handleErrors($rawResult, "consultarCtasCtesReturn");  
    }

    /**
     * Método que permite a partir de una CUIT conocer su obligación respecto a la emisión o recepción de facturas de créditos 
     *
     * @param array $params
     * @return void
     */
    public function consultarObligadoRecepcion(array $params=[], $timeout = FALSE, $raw = FALSE)
    {   
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params);

        $this->checkArgs($params, [  'cuitConsultada' ]);

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        $this->handleErrors($rawResult, "consultarObligadoRecepcionReturn");  

        return $rawResult->consultarObligadoRecepcionReturn;
    }
    /**
     * Método que permite obtener información sobre las transeferencias realizadas al Agente de Depósito Colectivo.
     *
     * @param array $params
     * @return void
     */
    public function consultarFacturasAgtDptoCltv(array $params=[], $timeout = FALSE, $raw = FALSE)
    {   
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params);

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        if(isset($rawResult->consultarFacturasAgtDptoCltvReturn->arrayFacturasAgtDptoCltv))
            return $rawResult->consultarFacturasAgtDptoCltvReturn->arrayFacturasAgtDptoCltv;

        $this->handleErrors($rawResult, "consultarFacturasAgtDptoCltvReturn");  
    }

    /**
     * Método por el cual el Comprador informa que le ha cancelado (pagado) totalmente la deuda al vendedor, debiendo indicar la forma de pago.Solo puede cancelar las aceptadas dentros de los plazos establecidos
     *
     * @param array $params
     * @return void
     */
    public function informarCancelacionTotalFECred(array $params=[], $timeout = FALSE, $raw = FALSE)
    {   
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params);

        $this->checkArgs($params, [ 'idCtaCte', 'arrayFormasCancelacion', 'importeCancelacion' ]);

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;
        
        $this->handleErrors($rawResult, "operacionFECredReturn");  

        return $rawResult->operacionFECredReturn;
    }

    /**
     * Método que permite listar los tipos de motivos de rechazo habilitados para una cta. cte.
     *
     * @param array $params
     * @return void
     */
    public function consultarTiposMotivosRechazo(array $params=[], $timeout = FALSE, $raw = FALSE)
    {   
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params);

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;
        
        if(isset($rawResult->codigoDescripcionReturn->arrayCodigoDescripcion))
            return $rawResult->codigoDescripcionReturn->arrayCodigoDescripcion;

        $this->handleErrors($rawResult, "codigoDescripcionReturn");  
    }

    /**
     * Método que permite listar los tipos de formas de pago habilitados para una factura de crédito.
     *
     * @param array $params
     * @return void
     */
    public function consultarTiposFormasCancelacion(array $params=[], $timeout = FALSE, $raw = FALSE)
    {   
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params);

        $rawResult =  $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        if(isset($rawResult->codigoDescripcionReturn->arrayCodigoDescripcion))
            return $rawResult->codigoDescripcionReturn->arrayCodigoDescripcion;

        $this->handleErrors($rawResult, "codigoDescripcionReturn");  
    }

    /**
     * Método que permite al Comprador Aceptar el saldo actual de la Cta. Cte. de una Factura de Crédito pudiendo indicar: pagos parciales, retenciones y/o embargos. 
     *
     * @param array $params
     * @return void
     */
    public function aceptarFECred(array $params=[], $timeout = FALSE, $raw = FALSE)
    {   
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params);

        $this->checkArgs($params, [ 'idCtaCte', 'saldoAceptado', 'codMoneda', 'cotizacionMonedaUlt' ]);

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        $this->handleErrors($rawResult, "operacionFECredReturn");  

        return $rawResult->operacionFECredReturn;
    }

    public function ConsultarCodigoDescripcion(array $params=[], $timeout = FALSE, $raw = FALSE)
    {   
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params);

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        if(isset($rawResult->codigoDescripcionReturn->arrayCodigoDescripcion))
            return $rawResult->codigoDescripcionReturn->arrayCodigoDescripcion;

        $this->handleErrors($rawResult, "codigoDescripcionReturn");  
    }

    public function obtenerRemitos(array $params=[], $timeout = FALSE, $raw = FALSE)
    {   
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params);

        $this->checkArgs($params, ['idComprobante' ]);

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        if(isset( $rawResult->obtenerRemitosReturn->arrayIdsRemitos))
            return $rawResult->obtenerRemitosReturn->arrayIdsRemitos;

        $this->handleErrors($rawResult, "obtenerRemitosReturn");  
    }

    public function ConsultarHistorialEstadosComprobante(array $params=[], $timeout = FALSE, $raw = FALSE)
    {   
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params);

        $this->checkArgs($params, ['idComprobante' ]);

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        if(isset( $rawResult->consultarHistorialEstadosComprobanteReturn))
            return $rawResult->consultarHistorialEstadosComprobanteReturn;

        $this->handleErrors($rawResult, "consultarHistorialEstadosComprobanteReturn");  
    }

    public function consultarHistorialEstadosCtaCte(array $params=[], $timeout = FALSE, $raw = FALSE)
    {   
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params);

        $this->checkArgs($params, [ 'idCtaCte' ]);

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        $this->handleErrors($rawResult, "consultarHistorialEstadosCtaCteReturn");  

        return $rawResult->consultarHistorialEstadosCtaCteReturn;
    }

    public function consultarTiposAjustesOperacion(array $params=[], $timeout = FALSE, $raw = FALSE)
    {   
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params);

        $this->checkArgs($params, [ 'idCtaCte' ]);

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        if(isset( $rawResult->codigoDescripcionReturn->arrayCodigoDescripcion))
            return $rawResult->codigoDescripcionReturn->arrayCodigoDescripcion;

        $this->handleErrors($rawResult, "codigoDescripcionReturn");  
    }

    public function consultarMontoObligadoRecepcion(array $params=[], $timeout = FALSE, $raw = FALSE)
    {   
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params);

        $this->checkArgs($params, [ 'cuitConsultada', 'fechaEmision' ]);

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        $this->handleErrors($rawResult, "consultarMontoObligadoRecepcionReturn");  

        return $rawResult->consultarMontoObligadoRecepcionReturn;
    }

    public function ModificarOpcionTransferencia(array $params=[], $timeout = FALSE, $raw = FALSE)
    {   
        $this->logger->info(self::TAG, __FUNCTION__);

        $this->afterRequest($params);

        $this->checkArgs($params, [ 'idCtaCte' ]);

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        $this->handleErrors($rawResult, "operacionFECredReturn");  

        return $rawResult->operacionFECredReturn;
    }

    public function dummy(array $params=[], $timeout = FALSE, $raw = FALSE)
    {
        $this->logger->info(self::TAG, __FUNCTION__);
        
        $this->afterRequest($params);

        $rawResult = $this->request(__FUNCTION__, $params, $timeout);

        if($raw) return $rawResult;

        $this->handleErrors($rawResult, "dummyReturn");  

        return $rawResult->dummyReturn; 
    }
}