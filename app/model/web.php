<?php if ( !defined('BASEPATH')) exit('No direct script access allowed');
 
$App = core::getInstance();  

require BASEPATH."app/third_party/afip/src/AFIP.php";

use AFIP\Afip;
use AFIP\Exceptions\AfipLoginException;
 
$App->get('index', function ()
{ 
    $this->data->set("rand",rand(111,999) );
    $this->parser->parse(BASEPATH."ui/index.html", $this->data->get());
});

$App->get('afip.login', function ()
{
    $sessionId  = (int)$this->session->recv(); if($sessionId <1) die('{"status": false,"message":"Termino el tiempo de session"}');

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

$App->get('account.list', function(){
    $sessionId  = (int)$this->session->recv(); if($sessionId <1) die('{"status": false,"message":"Termino el tiempo de session"}');

    $data = $this->db->query("SELECT * FROM usuarios")->result();

    die(json_encode($data));
});

$App->get('account.row', function($id=0){
    $sessionId  = (int)$this->session->recv(); if($sessionId <1) die('{"status": false,"message":"Termino el tiempo de session"}');

    $id = (int)$id; if($id <1) die('{"status": false,"message":"Invalid user id"}'); 

    $user = $this->db->query("SELECT id, nombre, apellido, mail, user, '' as 'pass', tel, tipo, activo FROM usuarios WHERE id = '{$id}'")->first();

    die(json_encode($user));
});

$App->get('account.update', function(){
    $sessionId  = (int)$this->session->recv(); if($sessionId <1) die('{"status": false,"message":"Termino el tiempo de session"}');

    $post = $this->input->post();

    if(!property_exists($post,'id')) {
        $this->db->query("
            INSERT 
                usuarios 
            SET 
                nombre  = '{$post->nombre}',
                apellido= '{$post->apellido}',
                mail    = '{$post->mail}',
                user    = '{$post->user}',
                tel     = '{$post->tel}',
                tipo    = '{$post->tipo}',
                activo  = '{$post->activo}',
                pass    = md5('{$post->pass}')
        ");
        die('{"status": true,"message":"Insercion exitosa"}');
    }

    $post->id= (int)$post->id; if($post->id <1) die('{"status": false,"message":"Invalid user id"}');
    $passw = ""; if($post->pass) $passw = ", pass = md5('{$post->pass}')";

    $this->db->query("
        UPDATE 
            usuarios 
        SET 
            nombre  = '{$post->nombre}',
            apellido= '{$post->apellido}',
            mail    = '{$post->mail}',
            user    = '{$post->user}',
            tel     = '{$post->tel}',
            tipo    = '{$post->tipo}',
            activo  = '{$post->activo}'
            {$passw}
        WHERE 
            id = '{$post->id}'
    ")->first();

    die('{"status": true,"message":"Actualizacion exitosa"}');
});


$App->get('home.stats', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) die('{"status": false,"message":"Termino el tiempo de session"}');

    $user = $this->db->query("SELECT * FROM usuarios WHERE id = '{$sessionId}'")->first();

    $user->tel =  $user->tel == "" ? "305000100842" : $user->tel ;

    $factura =  new stdClass;
    $factura->tipo              = 201;
    $factura->punto_venta       = "00001";
    $factura->nro               = "";
    $factura->concepto          = 2;
    $factura->tipo_doc          = "80";
    $factura->receptor          = $user->tel;
    $factura->emisor            = "33716282819";
    $factura->tipo_agente       = "ADC";
    $factura->importe_neto      = "1000";
    $factura->fecha_vto         = date("Y-m-d", time());
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

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) die('{"status": false,"message":"Termino el tiempo de session"}');

    die('[{"id":201, "value":"201 - FACTURA DE CRÉDITO"}, {"id":202, "value":"202 -NOTA DE DÉBITO"}, {"id":203, "value":"203 - NOTA DE CRÉDITO"}]');
});

$App->get('tipo_doc.combo', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) die('{"status": false,"message":"Termino el tiempo de session"}');

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

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) die('{"status": false,"message":"Termino el tiempo de session"}');

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

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) die('{"status": false,"message":"Termino el tiempo de session"}');
    die('[{"id":3, "value":"0"}, {"id":5, "value":"21"}, {"id":4, "value":"10.5"}]');
});

$App->get('moneda.combo', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) die('{"status": false,"message":"Termino el tiempo de session"}');
     die('[{"id":"PES", "value":"PESOS ARGENTINOS"}, {"id":"DOL", "value":"DOLAR ESTADOUNIDENSE"}]');
});


$App->get('tipo_concepto.combo', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) die('{"status": false,"message":"Termino el tiempo de session"}');

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

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) die('{"status": false,"message":"Termino el tiempo de session"}');

    $post = $this->input->post();  
    $post->concepto = (int)$post->concepto; 

    $AFIP = new Afip(); 
    $AFIP->service('wsfe')->login();
    $last_voucher = $AFIP->service('wsfe')->factory()->FECompUltimoAutorizado(['PtoVta'=> $post->punto_venta, 'CbteTipo'=> $post->tipo]);

    $voucher_number = $last_voucher->CbteNro + 1;
    $post->CbteDesde = $voucher_number;
    $post->CbteHasta = $voucher_number;

    $opcionales = [];
    $iva = [];
    $tributo = [];
    $cmp_asoc = [];
    $fecha_venc_pago = intval(date('Ymd', strtotime($post->fecha_vto)));

    if($post->tipo == 201){
        $opcionales = [
            [ 'Id' => 2101, 'Valor' => $post->cbu ], 
            [ 'Id' => 2102, 'Valor' => $post->alias ], 
            [ 'Id' => 27  , 'Valor' => $post->tipo_agente ]                        
        ];
    }
    else{
        $opcionales = [ 
            [ 'Id' => 22, 'Valor' => 'N' ]                        
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

    $iva        = [ [ 'Id' => $post->iva_porc, 'BaseImp' => 100, 'Importe' => 21  ] ];
    $tributo    = [ [ 'Id' => 99, 'Desc' => "Impuesto Municipal Matanza", 'BaseImp' => "100.00", 'Alic' =>  "1.00", 'Importe' => "1.00" ]];

    $data = 
    [
        'FeCAEReq' => 
        [
            'FeCabReq' => [
                'CantReg' => $post->CbteHasta-$post->CbteDesde+1, // Cantidad de comprobantes a registrar
                'PtoVta' => $post->punto_venta, // Punto de venta
                'CbteTipo' => $post->tipo, // Tipo de comprobante (ver tipos disponibles) 
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
                    'CondicionIVAReceptorId'=> 1, 
                    'CbtesAsoc' 	=> $cmp_asoc,
                    'Tributos' 		=> $tributo, 
                    'Iva' 			=> $iva, 
                    'Opcionales' 	=> $opcionales, 
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

$App->get('service.consultarComprobantes', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) die('{"status": false,"message":"Termino el tiempo de session"}');

    $post = $this->input->post();  

    $data = json_decode( json_encode($post->payload), true );

    $AFIP = new Afip(); 
    $AFIP->service('wsfecred')->login();
    $AFIP->service('wsfecred')->factory()->setCuitRepresented( $post->cuit );
    $result = $AFIP->service('wsfecred')->factory()->consultarComprobantes( $data );

    die(json_encode($result));
});

$App->get('service.consultarCtasCtes', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) die('{"status": false,"message":"Termino el tiempo de session"}');

    $post = $this->input->post();  

    $data = json_decode( json_encode($post->payload), true );

    $AFIP = new Afip(); 
    $AFIP->service('wsfecred')->login();
    $AFIP->service('wsfecred')->factory()->setCuitRepresented( $post->cuit );
    $result = $AFIP->service('wsfecred')->factory()->ConsultarCtasCtes( $data );

    die(json_encode($result));
});

$App->get('service.consultarCtaCte', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) die('{"status": false,"message":"Termino el tiempo de session"}');

    $post = $this->input->post();  

    $data = json_decode( json_encode($post->payload), true );

    $AFIP = new Afip(); 
    $AFIP->service('wsfecred')->login();
    $AFIP->service('wsfecred')->factory()->setCuitRepresented( $post->cuit );
    $result = $AFIP->service('wsfecred')->factory()->consultarCtaCte( $data );

    die(json_encode($result));
});

$App->get('service.aceptarFECred', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) die('{"status": false,"message":"Termino el tiempo de session"}');

    $post = $this->input->post();  

    $data = json_decode( json_encode($post->payload), true );

    $AFIP = new Afip(); 
    $AFIP->service('wsfecred')->login();
    $AFIP->service('wsfecred')->factory()->setCuitRepresented( $post->cuit );
    $result = $AFIP->service('wsfecred')->factory()->aceptarFECred( $data );

    die(json_encode($result));
});

$App->get('service.informarFacturaAgtDptoCltv', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) die('{"status": false,"message":"Termino el tiempo de session"}');

    $post = $this->input->post();  

    $data = json_decode( json_encode($post->payload), true );

    $AFIP = new Afip(); 
    $AFIP->service('wsfecred')->login();
    $AFIP->service('wsfecred')->factory()->setCuitRepresented( $post->cuit );
    $result = $AFIP->service('wsfecred')->factory()->informarFacturaAgtDptoCltv( $data );

    die(json_encode($result));
});

$App->get('service.modificarOpcionTransferencia', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) die('{"status": false,"message":"Termino el tiempo de session"}');

    $post = $this->input->post();  

    $data = json_decode( json_encode($post->payload), true );

    $AFIP = new Afip(); 
    $AFIP->service('wsfecred')->login();
    $AFIP->service('wsfecred')->factory()->setCuitRepresented( $post->cuit );
    $result = $AFIP->service('wsfecred')->factory()->modificarOpcionTransferencia( $data );

    die(json_encode($result));
});