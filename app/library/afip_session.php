<?php if ( !defined('BASEPATH')) exit('No direct script access allowed');

use AFIP\Exceptions\AfipLoginException;

class afip_session {
    private $afip;
    private $db;

    public function load()
    {  
        $this->db = core::getInstance()->module('db');
        $this->afip = core::getInstance()->module('afip');

        return $this;
    }

    public function getEmisorBySessionAndService($sessionId, $afipService) {
        $query = "SELECT e.id 
                  FROM usuarios u 
                  INNER JOIN usuarios_emisores eu ON (eu.id_user = u.id)  
                  INNER JOIN emisores e ON (e.id = eu.id_emisor)  
                  WHERE u.id = '{$sessionId}' AND e.afip_service = '{$afipService}'";
        
        $result = $this->db->query($query)->first();
        return $result ? $result->id : null;
    }  

    public function login(int $emisor_id = 1): bool {
        $emisor = $this->db->query("SELECT afip_crt, afip_key, afip_passphrase, afip_tra, afip_service FROM emisores WHERE id = {$emisor_id}")->first();

        if (!$emisor) {
            error_log("No se encontraron certificados para el emisor");
            return false;
        }

        try{
            $traStringXML = $this->afip->service($emisor->afip_service)->loginWithCredentials($emisor->afip_crt, $emisor->afip_key, $emisor->afip_passphrase, $emisor->afip_tra);
    
            if ($traStringXML && $traStringXML !== $emisor->afip_tra) {
                // Validar TRA usando la librería AFIP
                if ($this->afip->checkTicketResponseAccess($traStringXML)) {
                    try {
                        // Escape del XML para prevenir SQL injection
                        $escapedXml = $this->db->escape($traStringXML);
                        
                        // Actualizar en base de datos
                        $result = $this->db->query("UPDATE emisores SET afip_tra = '{$escapedXml}' WHERE id = {$emisor_id}");
                        
                        if (!$result) {
                            error_log("Error al guardar TRA en base de datos");
                            return false;
                        }
                        
                        // Log exitoso (opcional)
                        error_log("TRA actualizado exitosamente en BD");
                        return true;
                    } catch (Exception $e) {
                        // Log error pero no fallar la operación principal
                        error_log("Error guardando TRA en BD: " . $e->getMessage());
                        return false;
                    }
                } else {
                    error_log("TRA XML inválido, no se guardó en BD");
                    return false;
                }
            }
        }catch (AfipLoginException $e){
            error_log("Error al iniciar sesión: " . $e->getMessage());
            return false;
        }
        error_log("Sesión iniciada exitosamente");
        
        return true;
    }
  
}