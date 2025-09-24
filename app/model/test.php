<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$App = core::getInstance();

use AFIP\Exceptions\AfipLoginException;

// Test 1: Probar certificados de la base de datos
$App->get('test.cert.bd', function ()
{
    try {
        // Obtener certificados desde BD para el emisor específico
        $emisor = $this->db->query("SELECT afip_crt, afip_key, afip_passphrase FROM emisores WHERE id = 1")->first();

        if (!$emisor) {
            throw new Exception("No se encontraron certificados para el emisor");
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

        die(json_encode($result, JSON_PRETTY_PRINT));
        
    } catch (Exception $e) {
        $result = new stdClass;
        $result->status = "error";
        $result->message = $e->getMessage();
        die(json_encode($result, JSON_PRETTY_PRINT));
    }
});

// Test 2: Probar login AFIP con certificados de BD
$App->get('test.afip.login', function ()
{
    try {
        // Obtener certificados desde BD para el emisor específico
        $emisor = $this->db->query("SELECT afip_crt, afip_key, afip_passphrase FROM emisores WHERE id = 1")->first();

        if (!$emisor) {
            throw new Exception("No se encontraron certificados para el emisor");
        }

        $startTime = microtime(true);
        $this->afip->service('wsfe')->loginWithCredentials($emisor->afip_crt, $emisor->afip_key, $emisor->afip_passphrase);
        $endTime = microtime(true);
        
        $result = new stdClass;
        $result->status = "success";
        $result->message = "Login AFIP exitoso";
        $result->login_time = round(($endTime - $startTime) * 1000, 2) . " ms";
        $result->timestamp = date('Y-m-d H:i:s');

        die(json_encode($result, JSON_PRETTY_PRINT));
        
    } catch (AfipLoginException $e) {
        $result = new stdClass;
        $result->status = "error";
        $result->message = "Error de login AFIP: " . $e->getMessage();
        $result->timestamp = date('Y-m-d H:i:s');
        die(json_encode($result, JSON_PRETTY_PRINT));
    } catch (Exception $e) {
        $result = new stdClass;
        $result->status = "error";
        $result->message = "Error general: " . $e->getMessage();
        $result->timestamp = date('Y-m-d H:i:s');
        die(json_encode($result, JSON_PRETTY_PRINT));
    }
});

// Test 3: Probar funcionalidad completa de AFIP
$App->get('test.afip.complete', function(){
    try {
        // Obtener certificados desde BD para el emisor específico
        $emisor = $this->db->query("SELECT afip_crt, afip_key, afip_passphrase FROM emisores WHERE id = 1")->first();

        if (!$emisor) {
            throw new Exception("No se encontraron certificados para el emisor");
        }

        $startTime = microtime(true);
        
        // Login
        $this->afip->service('wsfe')->loginWithCredentials($emisor->afip_crt, $emisor->afip_key, $emisor->afip_passphrase);
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

        die(json_encode($result, JSON_PRETTY_PRINT));
        
    } catch (AfipLoginException $e) {
        $result = new stdClass;
        $result->status = "error";
        $result->message = "Error de login AFIP: " . $e->getMessage();
        $result->timestamp = date('Y-m-d H:i:s');
        die(json_encode($result, JSON_PRETTY_PRINT));
    } catch (Exception $e) {
        $result = new stdClass;
        $result->status = "error";
        $result->message = "Error general: " . $e->getMessage();
        $result->timestamp = date('Y-m-d H:i:s');
        die(json_encode($result, JSON_PRETTY_PRINT));
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

    die(json_encode($result, JSON_PRETTY_PRINT));
});

// Test 5: Verificar fuente de certificados (BD vs archivos)
$App->get('test.cert.source', function(){
    $result = new stdClass;
    $result->certificate_sources = [];
    
    // Verificar certificados en BD
    try {
        $emisor = $this->db->query("SELECT afip_crt, afip_key, afip_passphrase FROM emisores WHERE id = 1")->first();
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
    
    die(json_encode($result, JSON_PRETTY_PRINT));
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
    
    die(json_encode($result, JSON_PRETTY_PRINT));
});
