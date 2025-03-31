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

    $user = $this->db->query(" SELECT  id, id_empresa FROM  usuarios WHERE  user = '{$post->user}' AND  pass = MD5('{$post->pass}')   LIMIT 1 ")->first();

    if($user != FALSE)
    {
        $this->session->send( $user->id );

        $usuario_tipo = $this->db->query 
        (" 
            SELECT 
                UT.dashboard, 
                UT.dashcenter,
                UT.id 
            FROM 
                usuarios U 
            INNER JOIN  usuarios_tipo UT  ON  (U.tipo = UT.id)
            WHERE 
                U.id = {$user->id}
        ")->first();

        $output             = new stdclass;
        $output->status     = true;
        $output->id         = $user->id;
        $output->id_empresa = (int)$user->id_empresa;
        $output->invitado   = false;

        if($usuario_tipo!=FALSE)
        {
            $output->dashboard  = $usuario_tipo->dashboard;
            $output->dashcenter = $usuario_tipo->dashcenter;
            $output->tipo       = $usuario_tipo->id;
        }

        die(json_encode($output));
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

    $usuario_tipo = $this->db->query 
    (" 
        SELECT 
            U.id_empresa,
            UT.dashboard, 
            UT.dashcenter,
            UT.id 
        FROM 
            usuarios U 
        INNER JOIN 
            usuarios_tipo UT 
        ON
            (U.tipo = UT.id)
        WHERE 
            U.id = {$id}
    ")->first();

    if($usuario_tipo!= false)
    {
        $output             = new stdclass;
        $output->status     = true;
        $output->id         = $id; 
        $output->id_empresa = (int)$usuario_tipo->id_empresa; 
        $output->dashboard  = $usuario_tipo->dashboard;
        $output->dashcenter = $usuario_tipo->dashcenter;
        $output->tipo       = $usuario_tipo->id;

        die(json_encode($output));
    }
    else
    {
        $invitado = $this->db->query 
        (" 
            SELECT 
                * 
            FROM 
                invitados
            WHERE 
                (id + 10000000) = '{$id}'
            LIMIT 1 

        ")->first();

        if($invitado!= false)
        {
            $invitado->real_id  = $invitado->id; 
            $invitado->id       = 10000000 + (int)$invitado->id;
            $invitado->invitado = true;
            $invitado->status   = true;

            $usuario_tipo = $this->db->query("SELECT id, dashboard, dashcenter FROM usuarios_tipo  WHERE id = 2")->first();

            if($usuario_tipo!=FALSE)
            {
                $invitado->dashboard  = $usuario_tipo->dashboard;
                $invitado->dashcenter = $usuario_tipo->dashcenter;
                $invitado->tipo       = $usuario_tipo->id;
            }

            die(json_encode($invitado));
        }
        else
        {
            $user1a1 = $this->db->query 
            (" 
                SELECT 
                    * 
                FROM 
                    invitados
                WHERE  
                    cod_ingreso= '{$id}'  
                LIMIT 1 

            ")->first();

            if($user1a1 != FALSE)
            {
                $user1a1->real_id    = $user1a1->id;
                $user1a1->id         = (int)$id;
                $user1a1->invitado   = true;
                $user1a1->call       = true;
                $user1a1->status     = true;
                $user1a1->dashboard  = "app.dash.board_cliente_1x1";
                $user1a1->dashcenter = "app.dash.home_cliente_1x1";
                $user1a1->tipo       = 2;

                $this->session->send( $user1a1->id );

                die(json_encode($user1a1));
            }
            else
            {
                die('{"status":false, "message":"Email o codigo de evento invalido"}');
            }


            //die('{"status":false, "message":"Codigo de invitado invalido '.$id.' "}');
        }

    }
 
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