<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ConsoleAttribute {
    public const TEXT_GREEN = "\e[32m"; 
    public const TEXT_YELLOW = "\e[33m"; 
    public const TEXT_RED = "\e[31m"; 
    public const TEXT_DEFAULT = "\e[39m"; 
}

class ConsoleAlert{
    public static function info(){
        return ConsoleAttribute::TEXT_GREEN."[INFO]".ConsoleAttribute::TEXT_DEFAULT;
    }

    public static function warn(){
        return ConsoleAttribute::TEXT_YELLOW."[WARNING]".ConsoleAttribute::TEXT_DEFAULT;
    }

    public static function err(){
        return ConsoleAttribute::TEXT_RED."[ERROR]".ConsoleAttribute::TEXT_DEFAULT;
    }
}

class Migrate{
    private $input;
    private $output;
    private $db; 

    private $dependencies = ['input', 'output', 'db' ];

    private $currentMigrationFile = "{BASEPATH}/var/migration/last.log"; 
    private $searchMigrationsPattern = "glob://{BASEPATH}/var/migration/files/*-up.sql"; 
    private $migrationFile= "{BASEPATH}/var/migration/files/{migrationId}-{type}.sql";

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

        foreach(['currentMigrationFile','searchMigrationsPattern','migrationFile'] as $item){
            $this->{$item} = $this->replace($this->{$item}, ['BASEPATH'=>BASEPATH]);
        }
    }

    public function replace($str, $arr) 
	{ 
		foreach ($arr as $k => $v) 
		{
			$str = str_replace('{'.$k.'}', $v, $str);
		} 
		return $str;
	}

    public function isCommandEqual($name, $value){
        $comandValue = $this->input->command($name);

        if(!$comandValue) return FALSE;

        return ($comandValue == $value) ;
    }

    public function commandValue($name){
        return $this->input->command($name);
    }

    private function _findCurrentMigration(){
 
        if(!is_file($this->currentMigrationFile)) return FALSE;

        $raw = @file_get_contents($this->currentMigrationFile);

        if(!$raw) return FALSE;

        $raw = trim($raw);

        $date = DateTimeImmutable::createFromFormat('Ymd', $raw);

        if(!$date) return FALSE;

        $current        = new stdClass;
        $current->date  = $date; 
        $current->text  = $raw; 

        return $current;
    } 

    private function _saveNewMigration($migrationId){
        if(!is_file($this->currentMigrationFile)) return FALSE;

        $raw = @file_put_contents($this->currentMigrationFile, $migrationId, LOCK_EX);

        return $raw;
    }

    private function _findStackMigration(){ 

        $files          = new DirectoryIterator($this->searchMigrationsPattern); 
        $totalFiles     = iterator_count($files);
        $currentFile    = 0; 
        $stack          = [];

        foreach($files as $file) {
            $currentFile++; 

            $fileItem = str_replace("-up.sql", "", $file->getFilename()) ;

            if(strlen($fileItem)!=8) 
            {
                $this->output->write(ConsoleAlert::err()." Nombre invalido de migracion: {$file->getFilename()} \n");
                return FALSE;
            }
 
            $conversion = DateTimeImmutable::createFromFormat('Ymd', $fileItem);

            if(!$conversion) 
            {
                $this->output->write(ConsoleAlert::err()." No se pudo obtener la fecha para: {$file->getFilename()} \n");
                return FALSE;
            }
          
            $current            = new stdClass;
            $current->date      = $conversion; 
            $current->text      = $fileItem;  
            $current->current   = $currentFile;  
            $current->total     = $totalFiles;  

            $stack[]=$current;
           
        }

        return $stack;
    }

    private function _checkUpdates($current, $stack){
        $newUpdates = new stdClass;
        $newUpdates->count = 0;
        $newUpdates->stack= [];
        $newUpdates->migrable= FALSE;

        foreach($stack as $migration){ 

            $hasNewMigrate = (  $migration->date > $current->date );

            if($hasNewMigrate){
                $newUpdates->count++;
                $newUpdates->stack[]= $migration;
                $newUpdates->migrable= TRUE;
            }
        }

        return $newUpdates;
    }

    private function _getGeneralStatus(){
        $current = $this->_findCurrentMigration();

        if(!$current){
            $this->output->write(ConsoleAlert::err()." Current migration not found.\n");
            return;
        }

        $stack = $this->_findStackMigration();

        if(!$stack){
            $this->output->write(ConsoleAlert::err()." No hay stack de migracion para procesar.\n");
            return;
        }

        $newUpdates = $this->_checkUpdates($current, $stack);

        if(!$newUpdates){
            $this->output->write(ConsoleAlert::info()." No hay migraciones para procesar.\n");
            return;
        }

        $this->output->write("\n");
        $this->output->write(ConsoleAlert::info()." Hay {$newUpdates->count} migracion(es) para procesar.\n");

        foreach($newUpdates->stack as $stackInfo){
            $this->output->write( ConsoleAlert::info()." Migracion encontrada: {$stackInfo->text} [ {$stackInfo->current} de {$stackInfo->total}].\n");
        }

        $this->output->write("\n");
    }

    private function _isValidMigration($checkMigration){
        $stack = $this->_findStackMigration();

        if(!$stack){ 
            return FALSE;
        }

        $found = FALSE;
        foreach($stack as $migration){
            if($migration->date == $checkMigration ){
                $found = TRUE;
            }
        }

        return $found;
    }

    private function _getStatusOf($migrationId)
    {
        $migrationDate = DateTimeImmutable::createFromFormat('Ymd', $migrationId);

        if(!$migrationDate){
            $this->output->write(ConsoleAlert::err()." Migration Id no es (date): {$migrationId}\n");
            return;
        }

        $current = $this->_findCurrentMigration();

        if(!$current){
            $this->output->write(ConsoleAlert::err()." Current migration not found.\n");
            return;
        }

        $found = $this->_isValidMigration($migrationDate);

        if(!$found) {
            $this->output->write(ConsoleAlert::err()." Migration {$migrationId} not found.\n");
            return;
        }

        if( $migrationDate <= $current->date ){
            $this->output->write(ConsoleAlert::warn()." {$migrationId} ya fue migrada\n");
        } else {
            $this->output->write(ConsoleAlert::info()." {$migrationId} aun no corrio y esta lista para migrar\n");
        } 
    }

    public function status()
    {
        $statusMigrate = $this->input->command('status'); 

        if($statusMigrate == "all")
        {
            $this->_getGeneralStatus();
        } 
        else 
        {
            $this->_getStatusOf($statusMigrate);
        } 
    }

    public function make(){
        $statusMigrate = $this->input->command('make'); 

        if($statusMigrate != "migration") {
            $this->output->write(ConsoleAlert::err()." Invalid command\n");
            return;
        }

        $migrationId = date('Ymd', time()); 

        $upFile = $this->replace($this->migrationFile, [ 'migrationId'=>$migrationId, 'type'=> 'up' ]);
        $upDown = $this->replace($this->migrationFile, [ 'migrationId'=>$migrationId, 'type'=> 'down' ]);

        if(is_file($upFile) ) {
            $this->output->write(ConsoleAlert::warn()." La migracion ya esta creada\n");
            return;
        }

        @file_put_contents($upFile, "");
        @file_put_contents($upDown, "");
        
        $this->output->write(ConsoleAlert::info()." Migracion {$migrationId} creada\n");
    }

    private function _runSQL($sql){

        foreach(explode(";", $sql) as $stmt) {
            try{

                $stmt = trim($stmt);

                if($stmt) $this->db->query($stmt);
            } catch(Exception $e) {
                $this->output->write(ConsoleAlert::err()." ".$e->getMessage()."\n");
                $this->output->write(ConsoleAlert::err()."[SQL FAILED] {$stmt}\n");
                return FALSE;
            }
        }

        return TRUE; 
    }

    private function _runDirectionMigration($migrationId, $type='up'){
        $migrationDate = DateTimeImmutable::createFromFormat('Ymd', $migrationId);

        if(!$migrationDate){
            $this->output->write(ConsoleAlert::err()." Migration Id no es (date): {$migrationId}\n");
            return;
        }

        $found = $this->_isValidMigration($migrationDate);

        if(!$found) {
            $this->output->write(ConsoleAlert::err()." Migration {$migrationId} not found.\n");
            return;
        }

        $upFile = $this->replace($this->migrationFile, [ 'migrationId'=>$migrationId, 'type'=> $type ]) ;

        $sql = @file_get_contents($upFile);

        if(!$sql) {
            $this->output->write(ConsoleAlert::err()." Migration {$migrationId} has no content\n");
            return;
        }

        $result = $this->_runSQL($sql);

        if(!$result){
            $this->output->write(ConsoleAlert::err()." Ocurrieron errores al procesar {$migrationId}.\n");
        } else {
            $this->output->write(ConsoleAlert::info()." Migracion [{$type}] {$migrationId} exitosa.\n");
        }

        return $result;
    }

    private function _runDirection($command, $type='up'){
        $migrationId = $this->input->command($command); 

        $this->_runDirectionMigration($migrationId, $type);
    }

    public function migrate(){

        $this->_runDirection("migrate", "up");
    }

    public function rollback(){

        $this->_runDirection("rollback", "down");
    }

    public function run(){

        $statusMigrate = $this->input->command('run'); 

        if($statusMigrate != "migration") {
            $this->output->write(ConsoleAlert::err()." Invalid command\n");
            return;
        }
    
        $current = $this->_findCurrentMigration();

        if(!$current){
            $this->output->write(ConsoleAlert::err()." Current migration not found.\n");
            return;
        }

        $stack = $this->_findStackMigration();

        if(!$stack){
            $this->output->write(ConsoleAlert::err()." No hay stack de migracion para procesar.\n");
            return;
        }

        $newUpdates = $this->_checkUpdates($current, $stack);

        $lastMigration = FALSE;

        foreach($newUpdates->stack as $migrationItem){
            $statusRun = $this->_runDirectionMigration($migrationItem->text, 'up');

            if(!$statusRun){
                return;
            }

            $lastMigration = $migrationItem;
        }

        if(!$lastMigration){
            $this->output->write(ConsoleAlert::info()." Nada para migrar.\n");
            return;
        }

        if(!isset($lastMigration->text)){
            $this->output->write(ConsoleAlert::err()." [ASSERTION] La ultima migracion no posee id valido.\n");
            return;
        }

        $this->_saveNewMigration($lastMigration->text);

        $this->output->write(ConsoleAlert::info()." Migracion exitosa!. Posicion activa: {$lastMigration->text}\n");
    }

    public function help(){

        $this->output->write("USAGE: migrate.php [--status] [--make] [--migrate] [--rollback] [--run] [--help] [expresion]\n\n");
        $this->output->write("Realiza operaciones versionadas sobre las migraciones\n\n");
        $this->output->write("
--status: Muestra el estado de una migracion {MigrationId} o el estado general {all}

--make=migration: Genera una nueva migracion 

--migrate: Ejecuta una migracion especifica (Up) {MigrationId}
    {MigrationId}: Id de la migracion (Numerico en formato YYYYMMDD) 
    CUIDADO: Esta operacion NO posiciona el cursor en el MigrationId 
    especificado

--rollback: Revierte una migracion especifica (Down) {MigrationId}
    {MigrationId}: Id de la migracion (Numerico en formato YYYYMMDD)
    CUIDADO: Esta operacion NO posiciona el cursor en el MigrationId 
    especificado 

--run=migration: Ejecuta todas las migraciones pendientes y posiciona
                 el cursor en la fecha de ultima migracion

--help: ayuda en pantalla

EXAMPLES:
        Mostrar el estado general
        php ./migration/migrate.php --status=all

        Mostrar el estado de una migracion especifica 
        php ./migration/migrate.php --status=20230426

        Crear una nueva migracion
        php ./migration/migrate.php --make=migration

        Levantar una migracion especifica
        php ./migration/migrate.php --migrate=20230426

        Bajar una migracion especifica
        php ./migration/migrate.php --rollback=20230426

        Correr todas las migraciones pendientes
        php ./migration/migrate.php --run=migration
");

    }
}

$migrate = new Migrate();
 
if( $migrate->commandValue("status") )
{
    $migrate->status();
} 

if( $migrate->commandValue("make") )
{
    $migrate->make();
} 

if( $migrate->commandValue("migrate") )
{
    $migrate->migrate();
} 

if( $migrate->commandValue("rollback") )
{
    $migrate->rollback();
} 

if( $migrate->commandValue("run") )
{
    $migrate->run();
} 

if( $migrate->commandValue("help") )
{
    $migrate->help();
}