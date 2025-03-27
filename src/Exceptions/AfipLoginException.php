<?php 

namespace AFIP\Exceptions;

use Exception;
use AFIP\Services\AfipLogService;

class AfipLoginException extends Exception
{
    const TAG = 'AfipLoginException';
    const CODE = 401;

    public function __construct(string $message = 'Error al autenticar con afip', AfipLogService $logger= null)
    {
        if( !is_null( $logger ) )$logger->warn(self::TAG, $message);
        parent::__construct($message, self::CODE);
    }    
}