<?php if ( !defined('BASEPATH')) exit('No direct script access allowed');
 
$App = core::getInstance();  

use AFIP\Exceptions\AfipLoginException;

$App->module('afip_session')->load();


$App->get('index', function ()
{ 
    $this->data->set("rand",rand(111,999) );
    $this->parser->parse(BASEPATH."ui/index.html", $this->data->get());
});

$App->get('afip.login', function ()
{
    $sessionId  = (int)$this->session->recv(); if($sessionId <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);
    
    $emisorId = $this->afip_session->getEmisorBySessionAndService($sessionId, 'wsfe');
    if (!$emisorId) $this->output->json(['status' => false, 'message' => 'No se encontró emisor para wsfe']);
    
    $result = $this->afip_session->login($emisorId); 
   
    if(!$result) $this->output->json(['status' => false, 'message' => 'Error al iniciar sesión afip']);

    $data = new stdClass;
    $data->status   = $result;
    $data->message  = $result ? "Success" : "Error"; 

    $this->output->json($data,$result?200:401);
});


$App->get('home.stats', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);

    $emisor = $this->db->query("
                SELECT 
                    e.id
                FROM 
                    usuarios u 
                INNER JOIN usuarios_emisores eu ON (eu.id_user = u.id)  
                INNER JOIN emisores e ON (e.id = eu.id_emisor)  
                WHERE u.id = '{$sessionId}' and e.afip_service = 'wsfe' 
                LIMIT 1
            ")->first();

    $user = $this->db->query("SELECT * FROM usuarios WHERE id = '{$sessionId}'")->first();

    $user->tel =  $user->tel == "" ? "305000100842" : $user->tel ;

    $factura =  new stdClass;
    $factura->tipo              = 201;
    $factura->punto_venta       = "00001";
    $factura->nro               = "";
    $factura->concepto          = 2;
    $factura->tipo_doc          = "80";
    $factura->receptor          = $user->tel;
    $factura->emisor            = $emisor ? $emisor->id : "0";
    $factura->tipo_agente       = "ADC";
    $factura->importe_neto      = "1000";
    $factura->fecha_vto         = date("Y-m-d", strtotime("+3 months"));
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

    $this->output->json($factura);
});

$App->get('tipo_factura.combo', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);

    $data = json_decode('[{"id":201, "value":"201 - FACTURA DE CRÉDITO"}, {"id":202, "value":"202 -NOTA DE DÉBITO"}, {"id":203, "value":"203 - NOTA DE CRÉDITO"}]');
    $this->output->json($data);
});

$App->get('tipo_doc.combo', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);
    
    $emisorId = $this->afip_session->getEmisorBySessionAndService($sessionId, 'wsfe');
    if (!$emisorId) $this->output->json(['status' => false, 'message' => 'No se encontró emisor para wsfe']);
    
    $result = $this->afip_session->login($emisorId); if(!$result) $this->output->json(['status' => false, 'message' => 'Error al iniciar sesión afip']);

    $tipoDoc = $this->afip->service('wsfe')->factory()->FEParamGetTiposDoc();
    $data = [];

    foreach($tipoDoc as $doc){
        $item = new stdClass;
        $item->id = $doc->Id;
        $item->value = str_pad($doc->Id, 2, 0, STR_PAD_LEFT)." - {$doc->Desc}";
        $data[]=$item;
    }

    $this->output->json($data);
});


$App->get('pto_vta.combo', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);
    
    $emisorId = $this->afip_session->getEmisorBySessionAndService($sessionId, 'wsfe');
    if (!$emisorId) $this->output->json(['status' => false, 'message' => 'No se encontró emisor para wsfe']);
    
    $result = $this->afip_session->login($emisorId); if(!$result) $this->output->json(['status' => false, 'message' => 'Error al iniciar sesión afip']);

    $tipo = $this->afip->service('wsfe')->factory()->FEParamGetPtosVenta(); 
    $data = [];

    foreach($tipo as $row){
        
        $item = new stdClass;
        $item->id = $row->Id;
        $item->value = str_pad($row->Id, 3, 0, STR_PAD_LEFT)." - {$row->Desc}";
        $data[]=$item;
    }

    $this->output->json($data);
});

$App->get('iva.combo', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);
    $data = json_decode('[{"id":3, "value":"0"}, {"id":5, "value":"21"}, {"id":4, "value":"10.5"}]');
    $this->output->json($data);
});

$App->get('moneda.combo', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);
    $data = json_decode('[{"id":"PES", "value":"PESOS ARGENTINOS"}, {"id":"DOL", "value":"DOLAR ESTADOUNIDENSE"}]');
    $this->output->json($data);
});

$App->get('tipos_opcionales.combo', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);
    
    $emisorId = $this->afip_session->getEmisorBySessionAndService($sessionId, 'wsfe');
    if (!$emisorId) $this->output->json(['status' => false, 'message' => 'No se encontró emisor para wsfe']);
    
    $result = $this->afip_session->login($emisorId); if(!$result) $this->output->json(['status' => false, 'message' => 'Error al iniciar sesión afip']);

    $tiposOpcionales = $this->afip->service('wsfe')->factory()->FEParamGetTiposOpcional();
    $data = [];

    foreach($tiposOpcionales as $tipo){
        $item = new stdClass;
        $item->id = $tipo->Id;
        $item->value = "{$tipo->Id} - {$tipo->Desc}";
        $data[]=$item;
    }

    $this->output->json($data);
});


$App->get('tipo_concepto.combo', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);
    
    $emisorId = $this->afip_session->getEmisorBySessionAndService($sessionId, 'wsfe');
    if (!$emisorId) $this->output->json(['status' => false, 'message' => 'No se encontró emisor para wsfe']);
    
    $result = $this->afip_session->login($emisorId); if(!$result) $this->output->json(['status' => false, 'message' => 'Error al iniciar sesión afip']);

    $tipoDoc = $this->afip->service('wsfe')->factory()->FEParamGetTiposConcepto();
    $data = [];

    foreach($tipoDoc as $doc){
        $item = new stdClass;
        $item->id = $doc->Id;
        $item->value = "{$doc->Id} - {$doc->Desc}";
        $data[]=$item;
    }

    $this->output->json($data);
});

$App->get('emisores.combo', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);
    
    $rs = $this->db->query("
                SELECT 
                    e.id, CONCAT(e.nombre, ' (', e.afip_cuit, ')') as value 
                FROM 
                    usuarios u 
                INNER JOIN usuarios_emisores eu ON (eu.id_user = u.id)  
                INNER JOIN emisores e ON (e.id = eu.id_emisor)  
                WHERE u.id = '{$sessionId}' and e.afip_service = 'wsfe'
            ");
 
    $this->output->json($rs->result());
});


$App->get('home.facturacion', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);
    
    $emisorId = $this->afip_session->getEmisorBySessionAndService($sessionId, 'wsfe');
    if (!$emisorId) $this->output->json(['status' => false, 'message' => 'No se encontró emisor para wsfe']);
    
    $result = $this->afip_session->login($emisorId); if(!$result) $this->output->json(['status' => false, 'message' => 'Error al iniciar sesión afip']);

    $post = $this->input->post();  

    $post->concepto = (int)$post->concepto; 

    $last_voucher = $this->afip->service('wsfe')->factory()->FECompUltimoAutorizado(['PtoVta'=> $post->punto_venta, 'CbteTipo'=> $post->tipo]);

    $voucher_number = $last_voucher->CbteNro + 1;
    $post->CbteDesde = $voucher_number;
    $post->CbteHasta = $voucher_number;

    $post->emisor_cuit = $this->db->query("SELECT afip_cuit FROM emisores WHERE id = '{$post->emisor}'")->first();


    $opcionales = [];
    $iva = [];
    $tributo = [];
    $cmp_asoc = [];

    // Primer día del mes actual
    $FchServDesde = date('Ym01');

    // Último día del mes actual
    $FchServHasta = date('Ymt');

    $post->total        = (float) $post->total;
    $post->importe_neto = (float) $post->importe_neto;
    $post->iva          = (float) $post->iva;
    
    $fecha_venc_pago = intval(date('Ymd', strtotime($post->fecha_vto)));

    if($post->tipo == 201){
        $opcionales = [
            [ 'Id' => '2101', 'Valor' => $post->cbu ], 
            [ 'Id' => '2102', 'Valor' => $post->alias ], 
            [ 'Id' => '27'  , 'Valor' => $post->tipo_agente ]                        
        ];
        
        // Agregar orden de compra si está presente (usando Referencia Comercial)
        if(!empty($post->orden_compra)){
            $opcionales[] = [ 'Id' => '23', 'Valor' => $post->orden_compra ];
        }
    }
    else{
        // Para Notas de Crédito y Débito, verificar si es anulación
        $es_anulacion = isset($post->es_anulacion) && $post->es_anulacion == '1';
        $valor_anulacion = $es_anulacion ? 'S' : 'N';
        
        $opcionales = [ 
            [ 'Id' => '22', 'Valor' => $valor_anulacion ]                        
        ];        

        $fecha_venc_pago = "";
        
        // Configurar comprobante asociado para Notas de Crédito y Débito
        if(!empty($post->tipo_asoc) && !empty($post->cbte_asoc) && !empty($post->fecha_cbte_asoc)){
            // Convertir la fecha a formato AFIP (YYYYMMDD)
            $fecha_afip = '';
            if(is_string($post->fecha_cbte_asoc)){
                // Si viene como string, convertir a timestamp y luego a formato AFIP
                $timestamp = strtotime($post->fecha_cbte_asoc);
                if($timestamp !== false){
                    $fecha_afip = date('Ymd', $timestamp);
                }
            } else {
                // Si viene como timestamp, convertir directamente
                $fecha_afip = date('Ymd', $post->fecha_cbte_asoc);
            }
            
            $cmp_asoc = [
                [
                    'Tipo' 		=> (int)$post->tipo_asoc, 
                    'PtoVta' 	=> (int)$post->pto_vta_cbte_asoc, 
                    'Nro' 	    => (int)$post->cbte_asoc, 
                    'Cuit' 	    => $post->emisor_cuit, 
                    'CbteFch' 	=> (int)$fecha_afip
                ]
            ];
        }
    }

    $iva = [ [ 'Id' => $post->iva_porc, 'BaseImp' => $post->importe_neto, 'Importe' => $post->iva  ] ];
    //$tributo = [ [ 'Id' => 99, 'Desc' => "Impuesto Municipal Matanza", 'BaseImp' => "100.00", 'Alic' =>  "1.00", 'Importe' => "1.00" ]];
    $tributo =[];

    $data = 
    [
        'FeCAEReq' => 
        [
            'FeCabReq' => [
                'CantReg' => $post->CbteHasta-$post->CbteDesde+1, // Cantidad de comprobantes a registrar
                'PtoVta' => (int)$post->punto_venta, // Punto de venta
                'CbteTipo' => (int)$post->tipo, // Tipo de comprobante (ver tipos disponibles) 
            ],
            'FeDetReq' => [ 
                'FECAEDetRequest' => [
                    'Concepto' 		=> $post->concepto, // Concepto del Comprobante: (1)Productos, (2)Servicios, (3)Productos y Servicios
                    'DocTipo' 		=> $post->tipo_doc, // Tipo de documento del comprador (ver tipos disponibles)
                    'DocNro' 		=> $post->receptor, // Numero de documento del comprador
                    'CbteDesde' 	=> $post->CbteDesde, // Numero de comprobante o numero del primer comprobante en caso de ser mas de uno
                    'CbteHasta' 	=> $post->CbteHasta, // Numero de comprobante o numero del ultimo comprobante en caso de ser mas de uno
                    'CbteFch' 		=> intval(date('Ymd', time())), // (Opcional) Fecha del comprobante (yyyymmdd) o fecha actual si es nulo
                    'ImpTotal' 		=> number_format($post->total,2,".",""), // Importe total del comprobante
                    'ImpTotConc' 	=> "0.00", // Importe neto no gravado
                    'ImpNeto' 		=> number_format($post->importe_neto,2,".",""), // Importe neto gravado
                    'ImpOpEx' 		=> "0.00", // Importe exento de IVA
                    'ImpIVA' 		=> number_format($post->iva,2,".",""), //Importe total de IVA
                    'ImpTrib' 		=> "0.00", //Importe total de tributos
                    'FchServDesde' 	=> $FchServDesde, // (Opcional) Fecha de inicio del servicio (yyyymmdd), obligatorio para Concepto 2 y 3
                    'FchServHasta' 	=> $FchServHasta, // (Opcional) Fecha de fin del servicio (yyyymmdd), obligatorio para Concepto 2 y 3
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

    $result = $this->afip->service('wsfe')->factory()->FECAESolicitar($data);

    $this->output->json($result);
});