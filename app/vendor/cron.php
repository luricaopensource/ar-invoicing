<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cron{

    private $input;
    private $output;
    private $db;
    private $logger; 

    private $dependencies = ['input', 'output', 'db', 'logger'];
    private $cronjobfiles = "/var/cronjobs/*.json";

    private function cloneIn($object){
        foreach ($this->dependencies as $item) {
            if(!property_exists($object, $item)){
                die("Dependency {$item} not found");
            }
            
            $this->{$item} = $object->{$item};
        }
    }

    function __construct(){
        $this->cloneIn( core::getInstance() );

        if (!$this->input->has_command()) {
            die("No hay parametros para procesar");
        } 
         
    }

    public function isCommandEqual($name, $value){
        $comandValue = $this->input->command($name);

        if(!$comandValue) return FALSE;

        return ($comandValue == $value) ;
    }


    public function commandValue($name){
        return $this->input->command($name);
    }

    private function _findLastFile(){
        $filenames = glob( BASEPATH.$this->cronjobfiles );

        if(!$filenames) return FALSE;

        $file = array_pop($filenames); 

        if(is_null ($file)) return FALSE;
        if(!is_file($file)) return FALSE;

        return $file;
    } 

    private function _findLastJobs(){ 

        $filenames = glob( BASEPATH.$this->cronjobfiles );

        if(!$filenames) return FALSE;

        $file = array_pop($filenames); 

        if(is_null ($file)) return FALSE;
        if(!is_file($file)) return FALSE;

        $raw = @file_get_contents($file);

        if(!$raw) return FALSE;

        $json = json_decode($raw);

        if(is_null ($json)) return FALSE;

        return $json;
    }

    private function printJob($job){

        foreach([ 'disable', 'name', 'when', 'expresion', 'command' ] as $valid){
            if(!property_exists($job, $valid)){
                $this->output->write("Job property {$valid} not found\n");
                return;
            }
        }

        $line = "# ".( $job->disable ? '[DISABLED]' : '' )." {$job->name} - {$job->when} \n".( $job->disable ? '#' : '' )."{$job->expresion} {$job->command}\n\n";  
        $this->output->write($line);
    }

    public function showJobs(){
        $jobs = $this->_findLastJobs();

        if(count((array)$jobs)>0){
            $this->output->write("\n");
            $this->output->write("##############\n");
            $this->output->write("# -CRONJOBS- #\n");
            $this->output->write("##############\n");
            $this->output->write("\n");
        }

        foreach($jobs as $key => $job){
            $this->printJob($job);
        }
        
    }

    public function showJob($jobId){
        $jobs = $this->_findLastJobs();

        $job = isset( $jobs->{$jobId} ) ? $jobs->{$jobId} : FALSE;

        if(!$job) {
            $this->output->write("JobId [{$jobId}] not found!\n");
            return;
        }

        $this->output->write("\n");
        $this->output->write("# JobId: {$jobId}\n"); 
        $this->output->write("\n");

        $this->printJob($job);
    }

    public function createJob(){

        $createJob = $this->input->command(); 

        foreach([ 'id', 'name', 'when', 'expresion', 'command' ] as $valid){
            if(!property_exists($createJob, $valid)){
                $this->output->write("Create job property {$valid} not found\n");
                return;
            }
        }
 
        $createJob->disable = $this->input->command("disable");

        $jobId = $createJob->id;

        unset($createJob->id);
        unset($createJob->create);

        $jobs = $this->_findLastJobs();

        if(!$jobs) {
            $jobs = new stdclass;
        }

        $jobs->{$jobId} = $createJob;

        $raw = json_encode($jobs, JSON_PRETTY_PRINT);

        $date = date('Ymd',time());

        file_put_contents(BASEPATH."/cronjobs/{$date}.json", $raw);

        $this->output->write("Create job successfully!\n");
    }

    public function disable(){
        $disableJob = $this->input->command(); 

        foreach([ 'id' ] as $valid){
            if(!property_exists($disableJob, $valid)){
                $this->output->write("Disable job property {$valid} not found\n");
                return;
            }
        }

        $jobs = $this->_findLastJobs();

        if(!$jobs) {
            $this->output->write("No jobs to disable\n");
            return;
        }

        if(!isset($jobs->{$disableJob->id})){
            $this->output->write("JobId {$disableJob->id} not found\n");
            return;
        }

        $jobs->{$disableJob->id}->disable = true;

        $file = $this->_findLastFile();

        if(!$file) {
            $this->output->write("No file to write\n");
            return;
        }

        $raw = json_encode($jobs, JSON_PRETTY_PRINT);

        file_put_contents($file, $raw);

        $this->output->write("Disable job successfully!\n");
    }

    public function edit(){
        $editJob = $this->input->command(); 

        foreach([ 'id' ] as $valid){
            if(!property_exists($editJob, $valid)){
                $this->output->write("Edit job property {$valid} not found\n");
                return;
            }
        }
 
        $editJob->name = $this->input->command("name");
        $editJob->when = $this->input->command("when");
        $editJob->expresion = $this->input->command("expresion");
        $editJob->command = $this->input->command("command");

        $jobId = $editJob->id;

        unset($editJob->id);
        unset($editJob->edit);

        $jobs = $this->_findLastJobs();

        if(!$jobs) {
            $this->output->write("No jobs to disable\n");
            return;
        }

        if(!isset($jobs->{$jobId})){
            $this->output->write("JobId {$jobId} not found\n");
            return;
        }

        if($editJob->name)  $jobs->{$jobId}->name = $editJob->name;
        if($editJob->when)  $jobs->{$jobId}->when = $editJob->when;
        if($editJob->expresion)  $jobs->{$jobId}->expresion = $editJob->expresion;
        if($editJob->command)  $jobs->{$jobId}->command = $editJob->command;

        $file = $this->_findLastFile();

        if(!$file) {
            $this->output->write("No file to write\n");
            return;
        }

        $raw = json_encode($jobs, JSON_PRETTY_PRINT);

        file_put_contents($file, $raw);

        $this->output->write("Edit job successfully!\n");
    }


    public function help(){

        $this->output->write("USAGE: cron.php [--show] [--create] [--edit] [--disable] [--help] [expresion]\n\n");
        $this->output->write("Realiza operaciones versionadas sobre tareas programadas\n\n");
        $this->output->write("
--show: Muestra un cron especifico {JobId} o todos {all}

--create: Agrega una nueva tarea programada
    --id: Id del job [JobId] (Alfanumerico)
    --name: Nombre de la tarea
    --when: Cuando se ejecutara el comando
    --expresion: Unix time task spec
    --command: Comando a ejecutar
    --disable (opcional): Indica si el comando se crea deshabilitado

--edit: Edita un cron especifico {JobId}
    --id: Id del job [JobId] (Alfanumerico)
    --name (opcional): Nombre de la tarea
    --when (opcional): Cuando se ejecutara el comando
    --expresion (opcional): Unix time task spec
    --command (opcional): Comando a ejecutar 

--disable: Deshabilita un cron especifico {JobId}
    --id: Id del job [JobId] a deshabilitar

--help: ayuda en pantalla

EXAMPLES:
        Mostrar todos los cronjobs
        php ./cronjobs/cron.php --show=all

        Mostrar un job especifico 
        php ./cronjobs/cron.php --show=test

        Crear un job 
        php ./cronjobs/cron.php --create=job --id=test --name=\"demo\" --when=\"allways\" --expresion=\"0 6 * * *\" --command=\"/path/to/cmd frula.sh\"

        Deshabilitar un job
        php ./cronjobs/cron.php --disable=job --id=test

        Editar un job 
        php ./cronjobs/cron.php --edit=job --id=test --name=\"Un nombre super frula para rellenar\"


");

    }
}


$cron = new Cron();

if( $cron->commandValue("show") )
{
    if($cron->isCommandEqual("show","all")){
        $cron->showJobs();
    } else {
        $cron->showJob( $cron->commandValue("show") );
    }
} 
 
if( $cron->commandValue("create") )
{
    $cron->createJob();
} 

if( $cron->commandValue("disable") )
{
    $cron->disable();
} 

if( $cron->commandValue("edit") )
{
    $cron->edit();
} 

if( $cron->commandValue("help") )
{
    $cron->help();
} 