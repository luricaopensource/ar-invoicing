<?php 

namespace AFIP\Exceptions;

use Exception;
use AFIP\Services\AfipLogService;

class AfipResultException extends Exception
{
    const TAG = 'AfipResultException';
    const CODE = 406;    
    public function __construct(string $message = 'Service Result error', AfipLogService $logger= null)
    {
        if( !is_null( $logger ) )$logger->err(self::TAG, $message);
        parent::__construct($message, self::CODE);
    }    
}