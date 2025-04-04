<?php if ( !defined('BASEPATH')) exit('No direct script access allowed');

$App = core::getInstance();
 

/**
 * Login
 * 
 */
#request-login#
$App->get('auth-login', function () {

    if( !$this->input->has_post() ) die('{"status":false, "message":"URL invalida"}');

    $post = $this->input->post();

    if(!$post->user || !$post->pass) die('{"status":false, "message":"Debe completar los campos"}');

    $user = $this->db->query(" SELECT * FROM  usuarios WHERE  user = '{$post->user}' AND  pass = MD5('{$post->pass}')   LIMIT 1 ")->first();

    if($user){
        $tipo = $this->db->query(" SELECT * FROM usuarios_tipo WHERE id = '{$user->tipo}' ")->first();

        $this->session->send( $user->id );
        die('{"status":true, "message":"Welcome","dashboard":"'.$tipo->dashboard.'","dashcenter":"'.$tipo->dashcenter.'"}');
    }
    else {
        die('{"status":false, "message":"Usuario y/o password invalido"}');
    }

  
    
});
#/request-login#

/**
 * Logout
 * 
 */
#request-logout#
$App->get('auth-logout', function ($rid="")
{
    $this->session->close();

    die('{"status":false}');
});
#/request-logout#

/**
 * Online
 * 
 */
#request-online#
$App->get("auth-online", function ($rid="")
{
    $id  = (int)$this->session->recv(); if($id <1) die('{"status": false,"message":"Termino el tiempo de session"}');

    $user = $this->db->query(" SELECT * FROM usuarios WHERE id = '{$id}' ")->first();

    $tipo = $this->db->query(" SELECT * FROM usuarios_tipo WHERE id = '{$user->tipo}' ")->first();

    if($user) die('{"status": true,"message":"Welcome","dashboard":"'.$tipo->dashboard.'","dashcenter":"'.$tipo->dashcenter.'"}');
    
    die('{"status": false,"message":"Logoff"}');
      
});
 
$App->get("request-account", function ()
{
    $id  = (int)$this->session->recv(); if($id <1) die('{"status": false,"message":"Termino el tiempo de session"}');

    $user = $this->db->query("SELECT addr, apellido, mail, nombre, tel, user FROM usuarios WHERE id = '{$id}'")->first();

    if($user==FALSE)
    {
        die('{"result":false, "message":"No hay datos devueltos"}'); 
    }
    else
    {
        $user->result = true;
        $user->message= "Datos devueltos";

        die(json_encode($user));
    }
});

$App->get("update-account", function ()
{ 
    $id  = (int)$this->session->recv(); if($id <1) die('{"status": false,"message":"Termino el tiempo de session"}');

    if( !$this->input->has_post() ) die('{"status":false, "message":"URL invalida"}');

    $post = $this->input->post();

    if(!isset($post->id)) unset($post->id);

    if(!$post->pass) unset($post->pass); 
    
    $this->db->query(UPDATE("usuarios", $post , $id));

    die('{"result":true, "message":"Actualizado exitosamente"}'); 
});