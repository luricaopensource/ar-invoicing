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

    // 1- Controlar que venga el campo CAE
    // 2- Si es cae insertar en comprobantes_emitidos 
    if (isset($result->FeDetResp->FECAEDetResponse->CAE) && 
        isset($result->FeDetResp->FECAEDetResponse->Resultado) && 
        $result->FeDetResp->FECAEDetResponse->Resultado == 'A') {
        
        $detResponse = $result->FeDetResp->FECAEDetResponse;
        $cabResponse = $result->FeCabResp;
        
        // Convertir fechas al formato correcto
        $fechaProceso = DateTime::createFromFormat('YmdHis', $cabResponse->FchProceso);
        $fechaCbte = DateTime::createFromFormat('Ymd', $detResponse->CbteFch);
        $fechaCae = DateTime::createFromFormat('Ymd', $detResponse->CAEFchVto);
        
        $insertData = [
            'nro_cbte' => $detResponse->CbteHasta,
            'pto_vta' => $cabResponse->PtoVta,
            'tipo_cbte' => $cabResponse->CbteTipo,
            'cuit_emisor' => $cabResponse->Cuit,
            'cuit_receptor' => $detResponse->DocNro,
            'resultado' => $detResponse->Resultado,
            'concepto' => $detResponse->Concepto,
            'fecha_proceso' => $fechaProceso ? $fechaProceso->format('Y-m-d H:i:s') : $cabResponse->FchProceso,
            'fecha_cbte' => $fechaCbte ? $fechaCbte->format('Y-m-d') : $detResponse->CbteFch,
            'fecha_cae' => $fechaCae ? $fechaCae->format('Y-m-d') : $detResponse->CAEFchVto,
            'doc_tipo' => $detResponse->DocTipo,
            'cae' => $detResponse->CAE,
            'imp_total' => number_format($post->total, 2, ".", ""),
            'imp_neto' => number_format($post->importe_neto, 2, ".", ""),
            'imp_iva' => number_format($post->iva, 2, ".", ""),
            'moneda' => $post->moneda,
            'id_user' => $sessionId,
            'id_emisor' => $emisorId
        ];
        
        $this->db->query("INSERT INTO comprobantes_emitidos 
            (nro_cbte, pto_vta, tipo_cbte, cuit_emisor, cuit_receptor, resultado, concepto, 
             fecha_proceso, fecha_cbte, fecha_cae, doc_tipo, cae, imp_total, imp_neto, imp_iva, 
             moneda, id_user, id_emisor) VALUES 
            ('{$insertData['nro_cbte']}', '{$insertData['pto_vta']}', '{$insertData['tipo_cbte']}', 
             '{$insertData['cuit_emisor']}', '{$insertData['cuit_receptor']}', '{$insertData['resultado']}', 
             '{$insertData['concepto']}', '{$insertData['fecha_proceso']}', '{$insertData['fecha_cbte']}', 
             '{$insertData['fecha_cae']}', '{$insertData['doc_tipo']}', '{$insertData['cae']}', 
             '{$insertData['imp_total']}', '{$insertData['imp_neto']}', '{$insertData['imp_iva']}', 
             '{$insertData['moneda']}', '{$insertData['id_user']}', '{$insertData['id_emisor']}')");
    }

    $this->output->json($result);
});

$App->get('comprobantes.list', function(){
    $sessionId = (int)$this->session->recv(); 
    if($sessionId < 1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);

    $comprobantes = $this->db->query("
        SELECT 
            ce.id,
            ce.nro_cbte,
            ce.pto_vta,
            ce.tipo_cbte,
            ce.cuit_emisor,
            ce.cuit_receptor,
            ce.resultado,
            ce.concepto,
            ce.fecha_proceso,
            ce.fecha_cbte,
            ce.fecha_cae,
            ce.doc_tipo,
            ce.cae,
            ce.imp_total,
            ce.imp_neto,
            ce.imp_iva,
            ce.moneda,
            ce.created_at,
            CONCAT(e.nombre, ' (', e.afip_cuit, ')') as emisor_nombre
        FROM comprobantes_emitidos ce
        INNER JOIN emisores e ON e.id = ce.id_emisor
        WHERE ce.id_user = '{$sessionId}'
        ORDER BY ce.created_at DESC
    ")->result();

    $this->output->json($comprobantes);
});

$App->get('comprobantes.afip.list', function($param = null){
    $sessionId = (int)$this->session->recv(); 
    if($sessionId < 1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);

    $emisorId = $this->afip_session->getEmisorBySessionAndService($sessionId, 'wsfecred');
    if (!$emisorId) $this->output->json(['status' => false, 'message' => 'No se encontró emisor para wsfecred']);
    
    $result = $this->afip_session->login($emisorId); 
    if(!$result) $this->output->json(['status' => false, 'message' => 'Error al iniciar sesión afip']);

    // Obtener datos del emisor
    $emisor = $this->db->query("SELECT afip_cuit FROM emisores WHERE id = '{$emisorId}'")->first();
    if (!$emisor) $this->output->json(['status' => false, 'message' => 'No se encontró CUIT del emisor']);

    // Configurar datos para consulta (últimos 30 días)
    $fechaDesde = date('Y-m-d', strtotime('-30 days'));
    $fechaHasta = date('Y-m-d');
    
   /*
        'FechaDesde' => $fechaDesde,
        'FechaHasta' => $fechaHasta,
        'CbteTipo' => 201, // Factura de crédito
        'PtoVta' => 1,
   */ 
    $data = [
        'rolCUITRepresentada' => 'Emisor',
        'fecha' => [
            'tipo'  => 'Emision',
            'desde' => $fechaDesde,
            'hasta' => $fechaHasta
        ]
    ];

    // Configurar el CUIT representado
    $this->afip->service('wsfecred')->factory()->setCuitRepresented($emisor->afip_cuit);

    
    try {
        // Obtener el cliente SOAP para acceder a la respuesta raw
        $soapClient = $this->afip->service('wsfecred')->factory()->getSoapClient();
        
        // Hacer la consulta
        $result = $this->afip->service('wsfecred')->factory()->consultarComprobantes($data, FALSE, TRUE);


        // Formatear la respuesta para la vista
        $comprobantes = [];
        
        if (isset($result->consultarCmpReturn) && isset($result->consultarCmpReturn->arrayComprobantes)) {
            $arrayComprobantes = $result->consultarCmpReturn->arrayComprobantes;
            
            // Verificar si hay comprobantes
            if (isset($arrayComprobantes->comprobante)) {
                $comprobantesArray = $arrayComprobantes->comprobante;
                
                // Si es un solo comprobante, convertir a array
                if (!is_array($comprobantesArray)) {
                    $comprobantesArray = [$comprobantesArray];
                }
                
                foreach ($comprobantesArray as $comprobante) {
                    // Extraer importe neto e IVA de los subtotales
                    $impNeto = 0;
                    $impIva = 0;
                    
                    if (isset($comprobante->arraySubtotalesIVA) && isset($comprobante->arraySubtotalesIVA->subtotalIVA)) {
                        $subtotalIva = $comprobante->arraySubtotalesIVA->subtotalIVA;
                        if (!is_array($subtotalIva)) {
                            $subtotalIva = [$subtotalIva];
                        }
                        
                        foreach ($subtotalIva as $iva) {
                            $impNeto += $iva->baseImponible;
                            $impIva += $iva->importe;
                        }
                    }
                    
                    $comprobantes[] = [
                        'tipo_cbte' => $comprobante->codTipoCmp,
                        'pto_vta' => $comprobante->ptovta,
                        'nro_cbte' => $comprobante->nroCmp,
                        'cuit_emisor' => $comprobante->cuitEmisor,
                        'cuit_receptor' => $comprobante->cuitReceptor,
                        'razon_social_emisor' => $comprobante->razonSocialEmi,
                        'razon_social_receptor' => $comprobante->razonSocialRecep,
                        'fecha_cbte' => $comprobante->fechaEmision,
                        'fecha_puesta_dispo' => $comprobante->fechaPuestaDispo,
                        'fecha_ven_pago' => $comprobante->fechaVenPago,
                        'fecha_ven_acep' => $comprobante->fechaVenAcep,
                        'imp_total' => $comprobante->importeTotal,
                        'imp_neto' => $impNeto,
                        'imp_iva' => $impIva,
                        'moneda' => $comprobante->codMoneda,
                        'cotizacion_moneda' => $comprobante->cotizacionMoneda,
                        'cod_autorizacion' => $comprobante->codAutorizacion,
                        'tipo_cod_auto' => $comprobante->tipoCodAuto,
                        'estado' => $comprobante->estado->estado,
                        'fecha_hora_estado' => $comprobante->estado->fechaHoraEstado,
                        'tipo_acep' => $comprobante->tipoAcep ?? null,
                        'fecha_hora_acep' => $comprobante->fechaHoraAcep ?? null,
                        'opcion_transferencia' => $comprobante->opcionTransferencia,
                        'cod_cta_cte' => $comprobante->codCtaCte,
                        'cbu_emisor' => $comprobante->CBUEmisor ?? null,
                        'alias_emisor' => $comprobante->AliasEmisor ?? null
                    ];
                }
            }
        }

        $this->output->json($comprobantes);
        
    } catch (Exception $e) {
        // En caso de error, capturar la respuesta raw del cliente SOAP

        
        // En caso de error de conectividad con AFIP, devolver error simplificado
        error_log("Error conectando a AFIP: " . $e->getMessage());
        
        // Simplificar el mensaje de error
        $errorMessage = 'Error de conectividad con AFIP';
        if (strpos($e->getMessage(), 'Could not connect to host') !== false) {
            $errorMessage = 'No se pudo conectar con los servidores de AFIP';
        } elseif (strpos($e->getMessage(), 'SOAP Fault') !== false) {
            $errorMessage = 'Error en el servicio de AFIP';
        } elseif (strpos($e->getMessage(), 'looks like we got no XML document') !== false) {
            $errorMessage = 'AFIP devolvió una respuesta vacía o inválida';
        }
        
        $this->output->json(['status' => false, 'message' => $errorMessage]);
    }
});

// Endpoints para gestión de menús
$App->get('menu-list', function(){
    $sessionId = (int)$this->session->recv(); 
    if($sessionId < 1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);
    
    $menus = $this->db->query("
        SELECT 
            m.id,
            m.id_tipo,
            m.vista,
            m.value,
            m.icon,
            m.orden,
            ut.nombre as tipo
        FROM menu m
        INNER JOIN usuarios_tipo ut ON m.id_tipo = ut.id
        ORDER BY m.id_tipo, m.orden ASC
    ")->result();
    
    $this->output->json($menus);
});

$App->get('menu-row', function($id = 0){
    $sessionId = (int)$this->session->recv(); 
    if($sessionId < 1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);
    
    $id = (int)$id; 
    if($id < 1) $this->output->json(['status' => false, 'message' => 'Invalid menu id']);
    
    $menu = $this->db->query("SELECT * FROM menu WHERE id = '{$id}'")->first();
    $this->output->json($menu);
});

$App->get('menu-add', function(){
    $sessionId = (int)$this->session->recv(); 
    if($sessionId < 1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);

    if( !$this->input->has_post() ) $this->output->json(['status' => false, 'message' => 'URL invalida']);

    $post = $this->input->post();

    if(!$post->id_tipo || !$post->vista || !$post->value || !$post->icon || !$post->orden) {
        $this->output->json(['status' => false, 'message' => 'Debe completar todos los campos']);
    }

    $tipoExists = $this->db->query("SELECT id FROM usuarios_tipo WHERE id = '{$post->id_tipo}'")->first();
    if(!$tipoExists) {
        $this->output->json(['status' => false, 'message' => 'Tipo de usuario no existe']);
    }

    $this->db->query("
        INSERT INTO menu 
        SET 
            id_tipo = '{$post->id_tipo}',
            vista   = '{$post->vista}',
            value   = '{$post->value}',
            icon    = '{$post->icon}',
            orden   = '{$post->orden}'
    ");

    $this->output->json(['status' => true, 'message' => 'Menu creado exitosamente']);
});

$App->get('menu-update', function($id = 0){
    $sessionId = (int)$this->session->recv(); 
    if($sessionId < 1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);

    if( !$this->input->has_post() ) $this->output->json(['status' => false, 'message' => 'URL invalida']);

    $id = (int)$id; 
    if($id < 1) $this->output->json(['status' => false, 'message' => 'Invalid menu id']);

    $post = $this->input->post();

    $menuExists = $this->db->query("SELECT id FROM menu WHERE id = '{$id}'")->first();
    if(!$menuExists) {
        $this->output->json(['status' => false, 'message' => 'Menu no existe']);
    }

    if($post->id_tipo) {
        $tipoExists = $this->db->query("SELECT id FROM usuarios_tipo WHERE id = '{$post->id_tipo}'")->first();
        if(!$tipoExists) {
            $this->output->json(['status' => false, 'message' => 'Tipo de usuario no existe']);
        }
    }

    $this->db->query("
        UPDATE menu 
        SET 
            id_tipo = '{$post->id_tipo}',
            vista   = '{$post->vista}',
            value   = '{$post->value}',
            icon    = '{$post->icon}',
            orden   = '{$post->orden}'
        WHERE 
            id = '{$id}'
    ");

    $this->output->json(['status' => true, 'message' => 'Menu actualizado exitosamente']);
});

$App->get('menu-rem', function($id = 0){
    $sessionId = (int)$this->session->recv(); 
    if($sessionId < 1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);
    
    $id = (int)$id; 
    if($id < 1) $this->output->json(['status' => false, 'message' => 'Invalid menu id']);
    
    $menuExists = $this->db->query("SELECT id FROM menu WHERE id = '{$id}'")->first();
    if(!$menuExists) {
        $this->output->json(['status' => false, 'message' => 'Menu no existe']);
    }

    $this->db->query("DELETE FROM menu WHERE id = '{$id}'");
    
    $this->output->json(['status' => true, 'message' => 'Menu eliminado exitosamente']);
});

$App->get('usuarios_tipo.combo', function(){
    $sessionId = (int)$this->session->recv(); 
    if($sessionId < 1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);

    $tipos = $this->db->query("
        SELECT 
            id, 
            nombre as value 
        FROM usuarios_tipo 
        ORDER BY nombre ASC
    ")->result();

    $this->output->json($tipos);
});

$App->get('sidebar-list', function(){
    $sessionId = (int)$this->session->recv(); 
    if($sessionId < 1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);

    $user = $this->db->query("SELECT tipo FROM usuarios WHERE id = '{$sessionId}'")->first();
    
    $menus = $this->db->query("
        SELECT 
            id, 
            vista, 
            value,
            CONCAT('fa fa-', icon) as icon
        FROM menu 
        WHERE id_tipo = '{$user->tipo}' 
        ORDER BY orden ASC
    ")->result();

    $this->output->json($menus);
});