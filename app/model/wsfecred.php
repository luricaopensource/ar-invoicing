<?php if ( !defined('BASEPATH')) exit('No direct script access allowed');

$App = core::getInstance();  

use AFIP\Exceptions\AfipLoginException;


$App->get('wsfecred.consultarComprobantes', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);
    $emisorId = $this->afip_session->getEmisorBySessionAndService($sessionId, 'wsfecred');
    if (!$emisorId) $this->output->json(['status' => false, 'message' => 'No se encontró emisor para wsfecred']);
    
    $result = $this->afip_session->login($emisorId); if(!$result) $this->output->json(['status' => false, 'message' => 'Error al iniciar sesión afip']);

    $post = $this->input->payload();  
    $data = json_decode( json_encode($post->payload), true );

    $this->afip->service('wsfecred')->factory()->setCuitRepresented( $post->cuit );
    $result = $this->afip->service('wsfecred')->factory()->consultarComprobantes( $data, FALSE, TRUE );

    $this->output->json($result);
});

$App->get('wsfecred.consultarCtasCtes', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);
    $emisorId = $this->afip_session->getEmisorBySessionAndService($sessionId, 'wsfecred');
    if (!$emisorId) $this->output->json(['status' => false, 'message' => 'No se encontró emisor para wsfecred']);
    
    $result = $this->afip_session->login($emisorId); if(!$result) $this->output->json(['status' => false, 'message' => 'Error al iniciar sesión afip']);

    $post = $this->input->payload();  
    $data = json_decode( json_encode($post->payload), true );

   
    $this->afip->service('wsfecred')->factory()->setCuitRepresented( $post->cuit );
    $result = $this->afip->service('wsfecred')->factory()->ConsultarCtasCtes( $data );

    $this->output->json($result);
});

$App->get('wsfecred.consultarCtaCte', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);
    $emisorId = $this->afip_session->getEmisorBySessionAndService($sessionId, 'wsfecred');
    if (!$emisorId) $this->output->json(['status' => false, 'message' => 'No se encontró emisor para wsfecred']);
    
    $result = $this->afip_session->login($emisorId); if(!$result) $this->output->json(['status' => false, 'message' => 'Error al iniciar sesión afip']);

    $post = $this->input->payload();  
    $data = json_decode( json_encode($post->payload), true );

    $this->afip->service('wsfecred')->factory()->setCuitRepresented( $post->cuit );
    $result = $this->afip->service('wsfecred')->factory()->consultarCtaCte( $data );

    $this->output->json($result);
});

$App->get('wsfecred.aceptarFECred', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);
    $emisorId = $this->afip_session->getEmisorBySessionAndService($sessionId, 'wsfecred');
    if (!$emisorId) $this->output->json(['status' => false, 'message' => 'No se encontró emisor para wsfecred']);
    
    $result = $this->afip_session->login($emisorId); if(!$result) $this->output->json(['status' => false, 'message' => 'Error al iniciar sesión afip']);

    $post = $this->input->payload();  
    $data = json_decode( json_encode($post->payload), true );

    $this->afip->service('wsfecred')->factory()->setCuitRepresented( $post->cuit );
    $result = $this->afip->service('wsfecred')->factory()->aceptarFECred( $data );

    $this->output->json($result);
});

$App->get('wsfecred.informarFacturaAgtDptoCltv', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);
    $emisorId = $this->afip_session->getEmisorBySessionAndService($sessionId, 'wsfecred');
    if (!$emisorId) $this->output->json(['status' => false, 'message' => 'No se encontró emisor para wsfecred']);
    
    $result = $this->afip_session->login($emisorId); if(!$result) $this->output->json(['status' => false, 'message' => 'Error al iniciar sesión afip']);

    $post = $this->input->payload();  
    $data = json_decode( json_encode($post->payload), true );

    $this->afip->service('wsfecred')->factory()->setCuitRepresented( $post->cuit );
    $result = $this->afip->service('wsfecred')->factory()->informarFacturaAgtDptoCltv( $data );

    $this->output->json($result);
});

$App->get('wsfecred.modificarOpcionTransferencia', function(){

    $sessionId  = (int)$this->session->recv(); if($sessionId <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);
    $emisorId = $this->afip_session->getEmisorBySessionAndService($sessionId, 'wsfecred');
    if (!$emisorId) $this->output->json(['status' => false, 'message' => 'No se encontró emisor para wsfecred']);
    
    $result = $this->afip_session->login($emisorId); if(!$result) $this->output->json(['status' => false, 'message' => 'Error al iniciar sesión afip']);

    $post = $this->input->payload();  
    $data = json_decode( json_encode($post->payload), true );

    $this->afip->service('wsfecred')->factory()->setCuitRepresented( $post->cuit );
    $result = $this->afip->service('wsfecred')->factory()->modificarOpcionTransferencia( $data );

    $this->output->json($result);
});