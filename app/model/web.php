<?php if ( !defined('BASEPATH')) exit('No direct script access allowed');
 
$App = core::getInstance();  
 
$App->get('index', function ()
{
    $this->data->set("rand",rand(111,999) );
    $this->parser->parse(BASEPATH."ui/index.html", $this->data->get());
});


$App->get('home.stats', function(){

    $factura =  new stdClass;
    $factura->tipo              = 1;
    $factura->punto_venta       = 1;
    $factura->nro               = "";
    $factura->concepto          = 2;
    $factura->tipo_doc          = 1;
    $factura->receptor          = "305000100842";
    $factura->emisor            = "33716282819";
    $factura->tipo_agente       = 1;
    $factura->importe_neto      = "1000000";
    $factura->fecha_vto         = "2025-06-15";
    $factura->iva               = "210000";
    $factura->iva_porc          = 1;
    $factura->total             = "1210000";
    $factura->moneda            = 1;
    $factura->tyc               = 1;
    $factura->cbu               = "2850590940090418135201";
    $factura->alias             = "ALIAS.CBU.TEST";
    $factura->cae               = "";
    $factura->orden_compra      = "";
    $factura->cbte_asoc         = "";
    $factura->pto_vta_cbte_asoc = "";
    $factura->fecha_cbte_asoc   = "";
    $factura->cond_iva_receptor = "";
    $factura->es_anulacion      = "";

    die(json_encode($factura));
});

$App->get('tipo_factura.combo', function(){

    die('[{"id":1,"value":"201 Factura A"}]');
});

$App->get('tipo_doc.combo', function(){

    die('[{"id":1,"value":"80 CUIT"}]');
});

$App->get('tipo_agente.combo', function(){

    die('[{"id":1,"value":"ADC"}]');
});

$App->get('pto_vta.combo', function(){

    die('[{"id":1,"value":"00002"}]');
});

$App->get('iva.combo', function(){

    die('[{"id":1,"value":"21"}]');
});

$App->get('moneda.combo', function(){

    die('[{"id":1,"value":"PES"}]');
});