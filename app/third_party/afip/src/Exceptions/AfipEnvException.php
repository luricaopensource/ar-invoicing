<?php 

namespace AFIP\Exceptions;

use AFIP\Services\AfipLogService;
use Exception;

class AfipEnvException extends Exception
{
    const TAG = 'AfipEnvException';
    const CODE = 401;

    public function __construct(string $message = 'Error al configura el entorno', AfipLogService $logger =  null)
    {   
        if( !is_null( $logger ) )$logger->warn(self::TAG, $message);
        parent::__construct($message,  self::CODE);
    }    
}