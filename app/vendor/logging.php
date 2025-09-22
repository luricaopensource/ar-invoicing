<?php 

function errorLogging($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) { 
        return;
    }

    $handle             = new stdClass;
    $handle->template   = "%s. File: %s (Line: %s) PHP %s (OS: %s) [Input: %s] Request: %s";
    $handle->type       = $errno;
    $handle->details    = $errstr;
    $handle->file       = $errfile;
    $handle->line       = $errline;
    $handle->level      = "ERROR";
    $handle->php        = PHP_VERSION;
    $handle->os         = PHP_OS;
    $handle->sapi       = php_sapi_name();

    if($handle->sapi == "cli")
        $handle->request = (isset($_SERVER['argv']) ? implode(" ",$_SERVER['argv']) : '');
    else
        $handle->request    = (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '' ) . (isset($_SERVER['argv'][0]) ? '?'.implode(" ",$_SERVER['argv']) : '');
    
    switch ($errno) {
        case E_USER_ERROR   : $handle->level = 'ERROR'  ; break; 
        case E_USER_WARNING : $handle->level = 'WARNING'; break; 
        case E_USER_NOTICE  : $handle->level = 'INFO' ; break;  
        default             : $handle->level = 'LOG'; break;  
    }

    $message = sprintf( $handle->template,
                        $handle->details, 
                        $handle->file,
                        $handle->line, 
                        $handle->php,
                        $handle->os,
                        $handle->sapi,
                        $handle->request);

    $logger = Logging::getInstance();
    $logger->setEnvironment($_ENV['ENVIRONMENT']!='dev', BASEPATH.'/var/log');
    $logger->setChannel("error");
    $logger->handleMessage($handle->level, 'CORE', $message);

    return true;
}

set_error_handler("errorLogging");

class Logging{

    private string $filename = '{context}.log';
    private string $path = '';
    private bool $production = false;
    private $event = ['before'=> null, 'after'=> null]; 

    public static $instance;

    const LOG_FORMAT = "[{date}][{level}][{tag}] {message}\n";
    const DATE_FORMAT = 'Y-m-d H:i:s';

    function __construct(bool $production = false, string $resourcePath = '', string $filename = '{context}.log'){
        $this->setEnvironment($production,$resourcePath, $filename);
    }

    public function setEnvironment(bool $production = false, string $resourcePath = '', string $filename = '{context}.log'){
        $this->path = $resourcePath;
        $this->production = $production;
        $this->filename = $filename;
    }

    public function setChannel(string $channel, $date = FALSE) {
        if($date) {
            $date = date('Y-m-d', time());
            $this->filename = "{$date}-{$channel}-{context}.log";
        } else {
            $this->filename = "{$channel}-{context}.log";
        } 
    } 

    public function setFilename(string $filename){
        $this->filename = $filename;
    }

    public static function getInstance(){

        if(is_null(self::$instance)){
            self::$instance = new Logging();
        }

        return self::$instance;
    }

    public function setAfterloggingEvent($value){
        $this->event['after'] = $value;
    }

    public function setBeforeloggingEvent($value){
        $this->event['before'] = $value;
    }

    private function write(string $line=''){

        if(!is_dir($this->path))
            throw new Exception("Path {$this->path} no es directorio");

        $path = $this->path .'/'. str_replace('{context}', $this->production ? 'prod':'dev', $this->filename);

        file_put_contents($path, $line, FILE_APPEND | LOCK_EX);  
    }

    public function handleMessage($level, $tag, $message){
        $date = date(self::DATE_FORMAT, time()); 

        $line = str_replace('{date}'    , $date     , self::LOG_FORMAT);
        $line = str_replace('{level}'   , $level    , $line);
        $line = str_replace('{tag}'     , $tag      , $line);
        $line = str_replace('{message}' , $message  , $line);

        if(!is_null($this->event['before'])) $this->event['before']($level, $tag, $message); 

        $this->write($line);

        if(!is_null($this->event['after'])) $this->event['after']($level, $tag, $message); 
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