<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$App = core::getInstance();

use AFIP\Exceptions\AfipLoginException;

// Test 1: Probar certificados de la base de datos
$App->get('test.cert.bd', function ()
{
    try {
        // Obtener certificados desde BD para el emisor específico
        $emisor = $this->db->query("SELECT afip_crt, afip_key, afip_passphrase, afip_tra FROM emisores WHERE id = 1")->first();

        if (!$emisor) {
            $this->output->json(['status' => false, 'message' => 'No se encontraron certificados para el emisor']);
        }

        // Decodificar certificado para verificar fechas
        $certData = openssl_x509_parse($emisor->afip_crt);
        
        $result = new stdClass;
        $result->status = "success";
        $result->message = "Certificados de BD obtenidos correctamente";
        $result->certificate_info = [
            "subject" => $certData['subject']['CN'] ?? 'N/A',
            "serial_number" => $certData['serialNumber'] ?? 'N/A',
            "valid_from" => date('Y-m-d H:i:s', $certData['validFrom_time_t']),
            "valid_to" => date('Y-m-d H:i:s', $certData['validTo_time_t']),
            "is_valid_now" => (time() >= $certData['validFrom_time_t'] && time() <= $certData['validTo_time_t']),
            "days_until_expiry" => round(($certData['validTo_time_t'] - time()) / 86400)
        ];

        $this->output->json($result);
        
    } catch (Exception $e) {
        $result = new stdClass;
        $result->status = "error";
        $result->message = $e->getMessage();
        $this->output->json($result);
    }
});

// Test 2: Probar login AFIP con certificados de BD
$App->get('test.afip.login', function ()
{
    try {
        // Obtener certificados desde BD para el emisor específico
        $emisor = $this->db->query("SELECT afip_crt, afip_key, afip_passphrase, afip_tra FROM emisores WHERE id = 1")->first();

        if (!$emisor) {
            $this->output->json(['status' => false, 'message' => 'No se encontraron certificados para el emisor']);
        }

        $startTime = microtime(true);
        $this->afip->service('wsfe')->loginWithCredentials($emisor->afip_crt, $emisor->afip_key, $emisor->afip_passphrase, $emisor->afip_tra);
        $endTime = microtime(true);
        
        $result = new stdClass;
        $result->status = "success";
        $result->message = "Login AFIP exitoso";
        $result->login_time = round(($endTime - $startTime) * 1000, 2) . " ms";
        $result->timestamp = date('Y-m-d H:i:s');

        $this->output->json($result);
        
    } catch (AfipLoginException $e) {
        $result = new stdClass;
        $result->status = "error";
        $result->message = "Error de login AFIP: " . $e->getMessage();
        $result->timestamp = date('Y-m-d H:i:s');
        $this->output->json($result);
    } catch (Exception $e) {
        $result = new stdClass;
        $result->status = "error";
        $result->message = "Error general: " . $e->getMessage();
        $result->timestamp = date('Y-m-d H:i:s');
        $this->output->json($result);
    }
});

// Test 3: Probar funcionalidad completa de AFIP
$App->get('test.afip.complete', function(){
    try {
        // Obtener certificados desde BD para el emisor específico
        $emisor = $this->db->query("SELECT afip_crt, afip_key, afip_passphrase, afip_tra FROM emisores WHERE id = 1")->first();

        if (!$emisor) {
            $this->output->json(['status' => false, 'message' => 'No se encontraron certificados para el emisor']);
        }

        $startTime = microtime(true);
        
        // Login
        $this->afip->service('wsfe')->loginWithCredentials($emisor->afip_crt, $emisor->afip_key, $emisor->afip_passphrase, $emisor->afip_tra);
        $loginTime = microtime(true);
        
        // Obtener tipos de documento
        $tiposDoc = $this->afip->service('wsfe')->factory()->FEParamGetTiposDoc();
        $endTime = microtime(true);
        
        $result = new stdClass;
        $result->status = "success";
        $result->message = "Funcionalidad AFIP completa exitosa";
        $result->timing = [
            "login_time" => round(($loginTime - $startTime) * 1000, 2) . " ms",
            "total_time" => round(($endTime - $startTime) * 1000, 2) . " ms"
        ];
        $result->tipos_documento_count = count($tiposDoc);
        $result->timestamp = date('Y-m-d H:i:s');

        $this->output->json($result);
        
    } catch (AfipLoginException $e) {
        $result = new stdClass;
        $result->status = "error";
        $result->message = "Error de login AFIP: " . $e->getMessage();
        $result->timestamp = date('Y-m-d H:i:s');
        $this->output->json($result);
    } catch (Exception $e) {
        $result = new stdClass;
        $result->status = "error";
        $result->message = "Error general: " . $e->getMessage();
        $result->timestamp = date('Y-m-d H:i:s');
        $this->output->json($result);
    }
});

// Test 4: Verificar estado del cache SOAP
$App->get('test.cache.soap', function(){
    $result = new stdClass;
    $result->cache_info = [];

    // Verificar configuración de cache SOAP
    $result->cache_info[] = [
        "setting" => "soap.wsdl_cache_enabled",
        "value" => ini_get('soap.wsdl_cache_enabled'),
        "description" => "Cache WSDL habilitado"
    ];

    $result->cache_info[] = [
        "setting" => "soap.wsdl_cache_ttl",
        "value" => ini_get('soap.wsdl_cache_ttl'),
        "description" => "TTL del cache WSDL (segundos)"
    ];

    $result->cache_info[] = [
        "setting" => "soap.wsdl_cache_dir",
        "value" => ini_get('soap.wsdl_cache_dir'),
        "description" => "Directorio de cache WSDL"
    ];

    // Verificar archivos de cache
    $cacheDir = BASEPATH . "var/afip/dev/";
    $cacheFiles = glob($cacheDir . "wsdl--*");

    $result->cache_files = [];
    foreach($cacheFiles as $file) {
        $result->cache_files[] = [
            "file" => basename($file),
            "size" => filesize($file),
            "modified" => date('Y-m-d H:i:s', filemtime($file)),
            "age_hours" => round((time() - filemtime($file)) / 3600, 2)
        ];
    }

    // Verificar archivos WSDL principales
    $wsdlFiles = [
        "wsaa.wsdl" => $cacheDir . "wsaa.wsdl",
        "wsfe.wsdl" => $cacheDir . "wsfe.wsdl"
    ];

    $result->wsdl_files = [];
    foreach($wsdlFiles as $name => $path) {
        if (file_exists($path)) {
            $result->wsdl_files[] = [
                "name" => $name,
                "size" => filesize($path),
                "modified" => date('Y-m-d H:i:s', filemtime($path)),
                "age_hours" => round((time() - filemtime($path)) / 3600, 2)
            ];
        }
    }

    $this->output->json($result);
});

// Test 5: Verificar fuente de certificados (BD vs archivos)
$App->get('test.cert.source', function(){
    $result = new stdClass;
    $result->certificate_sources = [];
    
    // Verificar certificados en BD
    try {
        $emisor = $this->db->query("SELECT afip_crt, afip_key, afip_passphrase, afip_tra FROM emisores WHERE id = 1")->first();
        if ($emisor) {
            $certData = openssl_x509_parse($emisor->afip_crt);
            $result->certificate_sources[] = [
                "source" => "database",
                "subject" => $certData['subject']['CN'] ?? 'N/A',
                "serial_number" => $certData['serialNumber'] ?? 'N/A',
                "valid_from" => date('Y-m-d H:i:s', $certData['validFrom_time_t']),
                "valid_to" => date('Y-m-d H:i:s', $certData['validTo_time_t']),
                "is_valid_now" => (time() >= $certData['validFrom_time_t'] && time() <= $certData['validTo_time_t'])
            ];
        }
    } catch (Exception $e) {
        $result->certificate_sources[] = [
            "source" => "database",
            "error" => $e->getMessage()
        ];
    }
    
    // Verificar certificados en archivos
    $certFiles = [
        "33716282819-dev.crt" => BASEPATH . "var/afip/dev/33716282819-dev.crt",
        "tmp/test.crt" => BASEPATH . "tmp/test.crt"
    ];
    
    foreach($certFiles as $name => $path) {
        if (file_exists($path)) {
            try {
                $certContent = file_get_contents($path);
                $certData = openssl_x509_parse($certContent);
                $result->certificate_sources[] = [
                    "source" => "file",
                    "file" => $name,
                    "path" => $path,
                    "subject" => $certData['subject']['CN'] ?? 'N/A',
                    "serial_number" => $certData['serialNumber'] ?? 'N/A',
                    "valid_from" => date('Y-m-d H:i:s', $certData['validFrom_time_t']),
                    "valid_to" => date('Y-m-d H:i:s', $certData['validTo_time_t']),
                    "is_valid_now" => (time() >= $certData['validFrom_time_t'] && time() <= $certData['validTo_time_t'])
                ];
            } catch (Exception $e) {
                $result->certificate_sources[] = [
                    "source" => "file",
                    "file" => $name,
                    "error" => $e->getMessage()
                ];
            }
        }
    }
    
    // Verificar configuración AFIP
    $result->afip_config = [
        "mode" => $this->afip->isProduction() ? "PRODUCTION" : "HOMOLOGACION",
        "cuit" => $this->afip->getCUIT(),
        "resource_folder" => $this->afip->getResourceFolder()
    ];
    
    $result->timestamp = date('Y-m-d H:i:s');
    
    $this->output->json($result);
});

// Test 6: Limpiar cache SOAP manualmente
$App->get('test.cache.clean', function(){
    $result = new stdClass;
    $cacheDir = BASEPATH . "var/afip/dev/";
    
    $filesRemoved = [];
    $errors = [];
    
    // Limpiar archivos de cache
    $cacheFiles = glob($cacheDir . "wsdl--*");
    foreach($cacheFiles as $file) {
        if (unlink($file)) {
            $filesRemoved[] = basename($file);
        } else {
            $errors[] = "No se pudo eliminar: " . basename($file);
        }
    }
    
    // Limpiar archivos temporales
    $tmpFiles = glob($cacheDir . "tmp.wsdl.*");
    foreach($tmpFiles as $file) {
        if (unlink($file)) {
            $filesRemoved[] = basename($file);
        } else {
            $errors[] = "No se pudo eliminar: " . basename($file);
        }
    }
    
    // Limpiar archivos REQUEST (conservar RESPONSE con tokens válidos)
    $requestFiles = glob($cacheDir . "REQUEST-*");
    foreach($requestFiles as $file) {
        if (unlink($file)) {
            $filesRemoved[] = basename($file);
        } else {
            $errors[] = "No se pudo eliminar: " . basename($file);
        }
    }
    
    // Verificar archivos RESPONSE (no eliminar para conservar tokens válidos)
    $responseFiles = glob($cacheDir . "RESPONSE-*");
    $result->response_files_preserved = [];
    foreach($responseFiles as $file) {
        $result->response_files_preserved[] = [
            "file" => basename($file),
            "size" => filesize($file),
            "modified" => date('Y-m-d H:i:s', filemtime($file)),
            "age_hours" => round((time() - filemtime($file)) / 3600, 2)
        ];
    }
    
    $result->status = "success";
    $result->message = "Limpieza de cache completada (RESPONSE conservados para tokens válidos)";
    $result->files_removed = $filesRemoved;
    $result->files_removed_count = count($filesRemoved);
    $result->errors = $errors;
    $result->timestamp = date('Y-m-d H:i:s');
    
    $this->output->json($result);
});

// Test 7: Verificar TRA en base de datos
$App->get('test.tra.db', function(){
    $result = new stdClass;
    
    try {
        $emisor = $this->db->query("SELECT afip_tra FROM emisores WHERE id = 1")->first();
        
        if ($emisor && $emisor->afip_tra) {
            $tra = new SimpleXMLElement($emisor->afip_tra);
            $result->status = "success";
            $result->tra_info = [
                "unique_id" => (string)$tra->header->uniqueId,
                "generation_time" => (string)$tra->header->generationTime,
                "expiration_time" => (string)$tra->header->expirationTime,
                "is_valid" => time() < strtotime((string)$tra->header->expirationTime)
            ];
        } else {
            $result->status = "info";
            $result->message = "No hay TRA almacenado en base de datos";
        }
    } catch (Exception $e) {
        $result->status = "error";
        $result->message = $e->getMessage();
    }
    
    $this->output->json($result);
});

// Test 8: Probar login con TRA de BD
$App->get('test.afip.login.tra', function(){
    try {
        $emisor = $this->db->query("SELECT afip_crt, afip_key, afip_passphrase, afip_tra FROM emisores WHERE id = 1")->first();
        
        if (!$emisor) {
            $this->output->json(['status' => false, 'message' => 'No se encontraron certificados para el emisor']);
        }
        
        $startTime = microtime(true);
        $traStringXML = $this->afip->service('wsfe')->loginWithCredentials($emisor->afip_crt, $emisor->afip_key, $emisor->afip_passphrase, $emisor->afip_tra);
        $endTime = microtime(true);
        
        $result = new stdClass;
        $result->status = "success";
        $result->message = "Login AFIP con TRA de BD exitoso";
        $result->login_time = round(($endTime - $startTime) * 1000, 2) . " ms";
        $result->tra_updated = ($traStringXML && $traStringXML !== $emisor->afip_tra);
        $result->timestamp = date('Y-m-d H:i:s');
        
        $this->output->json($result);
        
    } catch (Exception $e) {
        $result = new stdClass;
        $result->status = "error";
        $result->message = $e->getMessage();
        $result->timestamp = date('Y-m-d H:i:s');
        $this->output->json($result);
    }
});

$App->get('test.afip.tipos_opcionales', function(){
    $sessionId  = (int)$this->session->recv(); if($sessionId <1) $this->output->json(['status' => false, 'message' => 'Termino el tiempo de session']);
    $result = $this->afip_session->login(1); if(!$result) $this->output->json(['status' => false, 'message' => 'Error al iniciar sesión afip']);

    $tiposOpcionales = $this->afip->service('wsfe')->factory()->FEParamGetTiposOpcional();
    $this->output->json($tiposOpcionales);
});
