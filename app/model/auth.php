<?php if ( !defined('BASEPATH')) exit('No direct script access allowed');

$App = core::getInstance();
 

/**
 * Login
 * 
 */
#request-login#
$App->get('auth-login', function () {

    if( !$this->input->has_post() ) $this->output->json(['status' => false, 'message' => 'URL invalida']);

    $post = $this->input->post();

    if(!$post->user || !$post->pass) $this->output->json(['status' => false, 'message' => 'Debe completar los campos']);

    $user = $this->db->query(" SELECT * FROM  usuarios WHERE  user = '{$post->user}' AND  pass = MD5('{$post->pass}')   LIMIT 1 ")->first();

    if($user){
        $usuario_tipo = $this->db->query("
            SELECT 
                UT.dashboard, 
                UT.dashcenter,
                UT.id 
            FROM 
                usuarios U 
            INNER JOIN usuarios_tipo UT ON (U.tipo = UT.id)
            WHERE 
                U.id = {$user->id}
        ")->first();

        $this->session->send( $user->id );
        
        $output = new stdclass;
        $output->status = true;
        $output->id = $user->id; 

        if($usuario_tipo != FALSE) {
            $output->dashboard = $usuario_tipo->dashboard;
            $output->dashcenter = $usuario_tipo->dashcenter;
            $output->tipo = $usuario_tipo->id;
        }

        $this->output->json($output);
    }
    else {
        $this->output->json(['status' => false, 'message' => 'Usuario y/o password invalido']);
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

    $this->output->json(['status' => false]);
});
#/request-logout#

/**
 * Online
 * 
 */
#request-online#
$App->get("auth-online", function ($rid="")
{
    $id  = (int)$this->session->recv(); if($id <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);

    $user = $this->db->query(" SELECT * FROM usuarios WHERE id = '{$id}' ")->first();

    if($user) {
        $usuario_tipo = $this->db->query("
            SELECT 
                UT.dashboard, 
                UT.dashcenter,
                UT.id 
            FROM 
                usuarios U 
            INNER JOIN usuarios_tipo UT ON (U.tipo = UT.id)
            WHERE 
                U.id = {$user->id}
        ")->first();

        $output = new stdclass;
        $output->status = true;
        $output->id = $user->id; 

        if($usuario_tipo != FALSE) {
            $output->dashboard = $usuario_tipo->dashboard;
            $output->dashcenter = $usuario_tipo->dashcenter;
            $output->tipo = $usuario_tipo->id;
        }

        $this->output->json($output);
    }
    
    $this->output->json(['status' => false, 'message' => 'Logoff']);
      
});
 
$App->get("request-account", function ()
{
    $id  = (int)$this->session->recv(); if($id <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);

    $user = $this->db->query("SELECT addr, apellido, mail, nombre, tel, user FROM usuarios WHERE id = '{$id}'")->first();

    if($user==FALSE)
    {
        $this->output->json(['result' => false, 'message' => 'No hay datos devueltos']); 
    }
    else
    {
        $user->result = true;
        $user->message= "Datos devueltos";

        $this->output->json($user);
    }
});

$App->get("update-account", function ()
{ 
    $id  = (int)$this->session->recv(); if($id <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);

    if( !$this->input->has_post() ) $this->output->json(['status' => false, 'message' => 'URL invalida']);

    $post = $this->input->post();

    if(!isset($post->id)) unset($post->id);

    if(!$post->pass) unset($post->pass); 
    
    $this->db->query(UPDATE("usuarios", $post , $id));

    $this->output->json(['result' => true, 'message' => 'Actualizado exitosamente']); 
});


// ------------------------------------------------------------------------------------------------


$App->get('account.list', function(){
    $sessionId  = (int)$this->session->recv(); if($sessionId <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);

    $data = $this->db->query("SELECT * FROM usuarios")->result();

    $this->output->json($data);
});

$App->get('account.row', function($id=0){
    $sessionId  = (int)$this->session->recv(); if($sessionId <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);

    $id = (int)$id; if($id <1) $this->output->json(['status' => false, 'message' => 'Invalid user id']); 

    $user = $this->db->query("SELECT id, nombre, apellido, mail, user, '' as 'pass', tel, tipo, activo FROM usuarios WHERE id = '{$id}'")->first();

    $this->output->json($user);
});

$App->get('account.update', function(){
    $sessionId  = (int)$this->session->recv(); if($sessionId <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);

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
        $this->output->json(['status' => true, 'message' => 'Insercion exitosa']);
    }

    $post->id= (int)$post->id; if($post->id <1) $this->output->json(['status' => false, 'message' => 'Invalid user id']);
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

    $this->output->json(['status' => true, 'message' => 'Actualizacion exitosa']);
});
