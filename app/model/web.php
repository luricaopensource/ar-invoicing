<?php if ( !defined('BASEPATH')) exit('No direct script access allowed');
 
$App = core::getInstance();  

require BASEPATH."app/third_party/afip/src/AFIP.php";

use AFIP\Afip;
 
$App->get('index', function ()
{
    $this->data->set("rand",rand(111,999) );
    $this->parser->parse(BASEPATH."ui/index.html", $this->data->get());
});

$App->get('home.stats', function(){

    $factura =  new stdClass;
    $factura->tipo              = 1;
    $factura->punto_venta       = "00001";
    $factura->nro               = "";
    $factura->concepto          = 2;
    $factura->tipo_doc          = "80";
    $factura->receptor          = "305000100842";
    $factura->emisor            = "33716282819";
    $factura->tipo_agente       = "ADC";
    $factura->importe_neto      = "1000000";
    $factura->fecha_vto         = "2025-06-15";
    $factura->iva               = "210000";
    $factura->iva_porc          = 5;
    $factura->total             = "1210000";
    $factura->moneda            = "PES";
    $factura->tyc               = 1;
    $factura->cbu               = "2850590940090418135201";
    $factura->alias             = "ALIAS.CBU.TEST";
    $factura->cae               = "";
    $factura->orden_compra      = "";
    $factura->cbte_asoc         = "";
    $factura->pto_vta_cbte_asoc = "";
    $factura->fecha_cbte_asoc   = "";
    $factura->cond_iva_receptor = "1";
    $factura->es_anulacion      = "";

    die(json_encode($factura));
});

$App->get('tipo_factura.combo', function(){

    $AFIP = new Afip();

    $AFIP->service('wsfe')->login();

    $tipo = $AFIP->service('wsfe')->factory()->FEParamGetTiposCbte(); 

    $data = [];

    foreach($tipo as $row){
        
        $item = new stdClass;
        $item->id = $row->Id;
        $item->value = str_pad($row->Id, 3, 0, STR_PAD_LEFT)." - {$row->Desc}";
        $data[]=$item;
    }

    die(json_encode($data));
});

$App->get('tipo_doc.combo', function(){

    $AFIP = new Afip();

    $AFIP->service('wsfe')->login();

    $tipoDoc = $AFIP->service('wsfe')->factory()->FEParamGetTiposDoc();

    $data = [];

    foreach($tipoDoc as $doc){
        
        $item = new stdClass;
        $item->id = $doc->Id;
        $item->value = str_pad($doc->Id, 2, 0, STR_PAD_LEFT)." - {$doc->Desc}";
        $data[]=$item;
    }

    die(json_encode($data));
});


$App->get('pto_vta.combo', function(){

    $AFIP = new Afip();

    $AFIP->service('wsfe')->login();

    $tipo = $AFIP->service('wsfe')->factory()->FEParamGetPtosVenta(); 

    $data = [];

    foreach($tipo as $row){
        
        $item = new stdClass;
        $item->id = $row->Id;
        $item->value = str_pad($row->Id, 3, 0, STR_PAD_LEFT)." - {$row->Desc}";
        $data[]=$item;
    }

    die(json_encode($data));
});

$App->get('iva.combo', function(){

    $AFIP = new Afip();

    $AFIP->service('wsfe')->login();

    $tipoDoc = $AFIP->service('wsfe')->factory()->FEParamGetTiposIva();

    $data = [];

    foreach($tipoDoc as $doc){
        
        $item = new stdClass;
        $item->id = (int) $doc->Id;
        $item->value = $doc->Desc;
        $data[]=$item;
    }

    die(json_encode($data));
});

$App->get('moneda.combo', function(){

    $AFIP = new Afip();

    $AFIP->service('wsfe')->login();

    $tipoDoc = $AFIP->service('wsfe')->factory()->FEParamGetTiposMonedas();

    $data = [];

    foreach($tipoDoc as $doc){
        
        $item = new stdClass;
        $item->id = $doc->Id;
        $item->value = $doc->Desc;
        $data[]=$item;
    }

    die(json_encode($data));
});


$App->get('home.facturacion', function(){

    $post = $this->input->post(); 

    $data = array(
        'CantReg' 		=> 1, // Cantidad de comprobantes a registrar
        'PtoVta' 		=> $post->punto_venta, // Punto de venta
        'CbteTipo' 		=> $post->tipo, // Tipo de comprobante (ver tipos disponibles) 
        'Concepto' 		=> $post->concepto, // Concepto del Comprobante: (1)Productos, (2)Servicios, (3)Productos y Servicios
        'DocTipo' 		=> $post->tipo_doc, // Tipo de documento del comprador (ver tipos disponibles)
        'DocNro' 		=> $post->receptor, // Numero de documento del comprador
        'CbteDesde' 	=> 1, // Numero de comprobante o numero del primer comprobante en caso de ser mas de uno
        'CbteHasta' 	=> 1, // Numero de comprobante o numero del ultimo comprobante en caso de ser mas de uno
        'CbteFch' 		=> intval(date('Ymd', time())), // (Opcional) Fecha del comprobante (yyyymmdd) o fecha actual si es nulo
        'ImpTotal' 		=> $post->total, // Importe total del comprobante
        'ImpTotConc' 	=> 0, // Importe neto no gravado
        'ImpNeto' 		=> $post->importe_neto, // Importe neto gravado
        'ImpOpEx' 		=> 0, // Importe exento de IVA
        'ImpIVA' 		=> $post->iva, //Importe total de IVA
        'ImpTrib' 		=> 0, //Importe total de tributos
        'FchServDesde' 	=> NULL, // (Opcional) Fecha de inicio del servicio (yyyymmdd), obligatorio para Concepto 2 y 3
        'FchServHasta' 	=> NULL, // (Opcional) Fecha de fin del servicio (yyyymmdd), obligatorio para Concepto 2 y 3
        'FchVtoPago' 	=> intval(date('Ymd', strtotime($post->fecha_vto))), // (Opcional) Fecha de vencimiento del servicio (yyyymmdd), obligatorio para Concepto 2 y 3
        'MonId' 		=> $post->moneda, //Tipo de moneda usada en el comprobante (ver tipos disponibles)('PES' para pesos argentinos) 
        'MonCotiz' 		=> 1, // Cotización de la moneda usada (1 para pesos argentinos)  
        'CbtesAsoc' 	=> array(),
        'Tributos' 		=> array(), 
        'Iva' 			=> array( // (Opcional) Alícuotas asociadas al comprobante
            array(
                'Id' 		=> $post->iva_porc, // Id del tipo de IVA (ver tipos disponibles) 
                'BaseImp' 	=> 100, // Base imponible
                'Importe' 	=> 21 // Importe 
            )
        ), 
        'Opcionales' 	=> array(), 
        'Compradores' 	=> array()
    );
    

    $AFIP = new Afip();

    $AFIP->service('wsfe')->login();

    try{
        $result = $AFIP->service('wsfe')->factory()->FECAESolicitar($data);
    
        die(json_encode($result));
    }
    catch(Exception $e){

        $item = new stdClass;
        $item->id = 1;
        $item->message = $e->getMessage();
        
        die(json_encode($item));
    }
});