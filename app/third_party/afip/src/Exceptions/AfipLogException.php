<?php 

namespace AFIP\Exceptions;

use Exception;
use AFIP\Services\AfipLogService;

class AfipLogException extends Exception
{
    const TAG = 'AfipEnvException';
    const CODE = 401;

    public function __construct(string $message = 'IO Error to log', AfipLogService $logger= null)
    {
        if( !is_null( $logger ) )$logger->warn(self::TAG, $message);
        parent::__construct($message, self::CODE);
    }    
}