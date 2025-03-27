<?php 

namespace AFIP\Services;

use AFIP\Exceptions\AfipLogException;

class AfipLogService{

    private string $filename = '{context}.log';
    private string $path = '';
    private bool $production = false;

    const LOG_FORMAT = "[{date}][{level}][{tag}] {message}\n";
    const DATE_FORMAT = 'Y-m-d H:i:s';

    function __construct(bool $production, string $resourcePath)
    {
        $this->path = $resourcePath;
        $this->production = $production;
    }

    private function write(string $line=''){

        if(!is_dir($this->path))
            throw new AfipLogException("Path {$this->path} no es directorio");

        $path = $this->path .'/'. str_replace('{context}', $this->production ? 'prod':'dev', $this->filename);

        file_put_contents($path, $line, FILE_APPEND);  
    }

    private function handleMessage($level, $tag, $message){
        $date = date(self::DATE_FORMAT, time()); 

        $line = str_replace('{date}'    , $date     , self::LOG_FORMAT);
        $line = str_replace('{level}'   , $level    , $line);
        $line = str_replace('{tag}'     , $tag      , $line);
        $line = str_replace('{message}' , $message  , $line);

        $this->write($line);
    }

    public function setResourcePath(string $value){
        $this->path = $value;
    }

    public function isProduction(bool $value){
        $this->production = $value;
    }

    public function log (string $tag, string $message){ 
        $this->handleMessage('LOG',$tag, $message);
    }
    public function err (string $tag, string $message){ 
        $this->handleMessage('ERROR',$tag, $message);
    }
    public function warn(string $tag, string $message){ 
        $this->handleMessage('WARNING',$tag, $message);
    }
    public function info(string $tag, string $message){ 
        $this->handleMessage('INFO',$tag, $message);
    }
}