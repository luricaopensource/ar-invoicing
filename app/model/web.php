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