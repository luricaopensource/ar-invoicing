<?php if ( !defined('BASEPATH')) exit('No direct script access allowed');
 
$App = core::getInstance();  

require BASEPATH."app/third_party/afip/src/AFIP.php";

use AFIP\Afip;
use AFIP\Exceptions\AfipLoginException;
 
$App->get('index', function ()
{
    var_dump($_SERVER);
    $this->data->set("rand",rand(111,999) );
    $this->parser->parse(BASEPATH."ui/index.html", $this->data->get());
});

$App->get('afip.login', function ()
{
    $AFIP = new Afip();

    try{
        $AFIP->service('wsfe')->login();
    }catch (AfipLoginException $e){
        $data = new stdClass;
        $data->status = FALSE;
        $data->message = $e->getMessage();
        die(json_encode($data));
    }
    
    $data = new stdClass;
    $data->status   = TRUE;
    $data->message  = "Success";
    die(json_encode($data));
});

$App->get('home.stats', function(){

    $factura =  new stdClass;
    $factura->tipo              = 201;
    $factura->punto_venta       = "00001";
    $factura->nro               = "";
    $factura->concepto          = 2;
    $factura->tipo_doc          = "80";
    $factura->receptor          = "305000100842";
    $factura->emisor            = "33716282819";
    $factura->tipo_agente       = "ADC";
    $factura->importe_neto      = "1000";
    $factura->fecha_vto         = "2025-06-15";
    $factura->iva               = "210";
    $factura->iva_porc          = 5;
    $factura->total             = "1210";
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

    /*
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
    */
    die('[{"id":201, "value":"201 - FACTURA DE CRÉDITO"}, {"id":202, "value":"202 -NOTA DE DÉBITO"}, {"id":203, "value":"203 - NOTA DE CRÉDITO"}]');
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

    /*
    $AFIP = new Afip(); 
    $AFIP->service('wsfe')->login();
    $tipoDoc = $AFIP->service('wsfe')->factory()->FEParamGetTiposIva();

    $data = [];

    foreach($tipoDoc as $doc){
        
        $item = new stdClass;
        $item->id = (int) $doc->Id;
        $item->value = "{$doc->Id} - {$doc->Desc}";
        $data[]=$item;
    }

    die(json_encode($data));
    */
    die('[{"id":3, "value":"0"}, {"id":5, "value":"21"}, {"id":4, "value":"10.5"}]');
});

$App->get('moneda.combo', function(){

    /*
    $AFIP = new Afip(); 
    $AFIP->service('wsfe')->login();
    $tipoDoc = $AFIP->service('wsfe')->factory()->FEParamGetTiposMonedas();

    $data = [];

    foreach($tipoDoc as $doc){
        
        $item = new stdClass;
        $item->id = $doc->Id;
        $item->value = "{$doc->Id} - {$doc->Desc}";
        $data[]=$item;
    }

    die(json_encode($data));
    */
     die('[{"id":"PES", "value":"PESOS ARGENTINOS"}, {"id":"DOL", "value":"DOLAR ESTADOUNIDENSE"}]');
});


$App->get('tipo_concepto.combo', function(){

    $AFIP = new Afip(); 
    $AFIP->service('wsfe')->login();
    $tipoDoc = $AFIP->service('wsfe')->factory()->FEParamGetTiposConcepto();

    $data = [];

    foreach($tipoDoc as $doc){
        
        $item = new stdClass;
        $item->id = $doc->Id;
        $item->value = "{$doc->Id} - {$doc->Desc}";
        $data[]=$item;
    }

    die(json_encode($data));
});

$App->get('home.facturacion', function(){

    $post = $this->input->post(); 

    $post->concepto = (int)$post->concepto;

    //$post->punto_venta = 4002; 
    //$post->tipo = 201;

    $AFIP = new Afip(); 
    $AFIP->service('wsfe')->login();
    $last_voucher = $AFIP->service('wsfe')->factory()->FECompUltimoAutorizado(['PtoVta'=> $post->punto_venta, 'CbteTipo'=> $post->tipo]);

    $voucher_number = $last_voucher->CbteNro + 1;

    $post->CbteDesde = $voucher_number;
    $post->CbteHasta = $voucher_number;

    $opcionales = [];
    $iva = [];
    $tributo = [];
    $fecha_venc_pago = intval(date('Ymd', strtotime($post->fecha_vto)));
    $cmp_asoc = [];

    if($post->tipo == 201){
        $opcionales = [
            [
                'Id' 		=> 2101, 
                'Valor' 	=> $post->cbu
            ], 
            [
                'Id' 		=> 2102, 
                'Valor' 	=> $post->alias
            ], 
            [
                'Id' 		=> 27, 
                'Valor' 	=> $post->tipo_agente
            ]                        
        ];


    }
    else{
        $opcionales = [ 
            [
                'Id' 		=> 22, 
                'Valor' 	=> 'N'
            ]                        
        ];        

        $fecha_venc_pago = "";
        $cmp_asoc = [
            [
                'Tipo' 		=> $post->tipo_asoc, 
                'PtoVta' 	=> $post->pto_vta_cbte_asoc, 
                'Nro' 	    => $post->cbte_asoc, 
                'Cuit' 	    => $post->emisor, 
                'CbteFch' 	=> intval(date('Ymd', strtotime($post->fecha_cbte_asoc)))
            ]
        ];

    }

    
    $iva = [ // (Opcional) Alícuotas asociadas al comprobante
        [
            'Id' 		=> $post->iva_porc, // Id del tipo de IVA (ver tipos disponibles) 
            'BaseImp' 	=> 100, // Base imponible
            'Importe' 	=> 21 // Importe 
        ]
    ];

    $tributo = [ 
        [
            'Id' => 99,
            'Desc' => "Impuesto Municipal Matanza",
            'BaseImp' => "100.00",
            'Alic' =>  "1.00",
            'Importe' => "1.00" 
        ]
    ];


    $data = 
    [
        'FeCAEReq' => 
        [
            'FeCabReq' => [
                'CantReg' 		=> $post->CbteHasta-$post->CbteDesde+1, // Cantidad de comprobantes a registrar
                'PtoVta' 		=> $post->punto_venta, // Punto de venta
                'CbteTipo' 		=> $post->tipo, // Tipo de comprobante (ver tipos disponibles) 
            ],
            'FeDetReq' => [ 
                'FECAEDetRequest' => [
                    'Concepto' 		=> $post->concepto, // Concepto del Comprobante: (1)Productos, (2)Servicios, (3)Productos y Servicios
                    'DocTipo' 		=> $post->tipo_doc, // Tipo de documento del comprador (ver tipos disponibles)
                    'DocNro' 		=> "33693450239", // Numero de documento del comprador
                    'CbteDesde' 	=> $post->CbteDesde, // Numero de comprobante o numero del primer comprobante en caso de ser mas de uno
                    'CbteHasta' 	=> $post->CbteHasta, // Numero de comprobante o numero del ultimo comprobante en caso de ser mas de uno
                    'CbteFch' 		=> intval(date('Ymd', time())), // (Opcional) Fecha del comprobante (yyyymmdd) o fecha actual si es nulo
                    'ImpTotal' 		=> "122.00", // Importe total del comprobante
                    'ImpTotConc' 	=> "0.00", // Importe neto no gravado
                    'ImpNeto' 		=> "100.00", // Importe neto gravado
                    'ImpOpEx' 		=> "0.00", // Importe exento de IVA
                    'ImpIVA' 		=> "21.00", //Importe total de IVA
                    'ImpTrib' 		=> "1.00", //Importe total de tributos
                    'FchServDesde' 	=> "20190101", // (Opcional) Fecha de inicio del servicio (yyyymmdd), obligatorio para Concepto 2 y 3
                    'FchServHasta' 	=> "20190131", // (Opcional) Fecha de fin del servicio (yyyymmdd), obligatorio para Concepto 2 y 3
                    'FchVtoPago' 	=> $fecha_venc_pago, // (Opcional) Fecha de vencimiento del servicio (yyyymmdd), obligatorio para Concepto 2 y 3
                    'MonId' 		=> $post->moneda, //Tipo de moneda usada en el comprobante (ver tipos disponibles)('PES' para pesos argentinos) 
                    'MonCotiz' 		=> 1, // Cotización de la moneda usada (1 para pesos argentinos)  
                    //'CanMisMonExt'  => 0,
                    'CondicionIVAReceptorId'=> 1, 
                    'CbtesAsoc' 	=> $cmp_asoc,
                    'Tributos' 		=> $tributo, 
                    'Iva' 			=> $iva, 
                    'Opcionales' 	=> $opcionales, 
                    //'Compradores' 	=> []
                ]
            ]
        ]
    ];

    if(empty($cmp_asoc)){
        unset($data['FeCAEReq']['FeDetReq']['FECAEDetRequest']['CbtesAsoc']);
    }

    if(empty($opcionales)){
        unset($data['FeCAEReq']['FeDetReq']['FECAEDetRequest']['Opcionales']);
    }

    if(empty($iva)){
        unset($data['FeCAEReq']['FeDetReq']['FECAEDetRequest']['Iva']);
    }

    if(empty($tributo)){
        unset($data['FeCAEReq']['FeDetReq']['FECAEDetRequest']['Tributos']);
    }

    if($post->concepto == 1){
        unset($data['FeCAEReq']['FeDetReq']['FECAEDetRequest']['FchServDesde']);
        unset($data['FeCAEReq']['FeDetReq']['FECAEDetRequest']['FchServHasta']);
    }

    $result = $AFIP->service('wsfe')->factory()->FECAESolicitar($data);

    die(json_encode($result));
});