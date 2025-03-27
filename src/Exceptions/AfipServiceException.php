<?php 

namespace AFIP\Exceptions;

use Exception;
use AFIP\Services\AfipLogService;

class AfipServiceException extends Exception
{
    const TAG = 'AfipServiceException';
    const CODE = 401;    
    public function __construct(string $message = 'Service error', AfipLogService $logger= null)
    {
        if( !is_null( $logger ) )$logger->warn(self::TAG, $message);
        parent::__construct($message, self::CODE);
    }    
}