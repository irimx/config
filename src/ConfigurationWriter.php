<?php declare(strict_types=1);
namespace irimx\config;

use Exception;

class ConfigurationWriter{
    
    private $filePath = '';

    public function __construct($filePath){
        try {
            if( !is_file($filePath) ){
                throw new Exception("please create the following database config directory {$filePath} ");
            }
            $this->filePath = $filePath;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function getLines() : array{
        $lines = array();
        if( is_file($this->filePath) ){
            $lines = explode("\n", file_get_contents($this->filePath));
        }
        return self::linesAsArray($lines);
    }

    public function setLines(array $env) : void{
        if( is_file($this->filePath) ){
            file_put_contents($this->filePath, self::arrayAsLines($env), LOCK_EX);
        }
    }

    public function setEnv(array $lines) : void{
        foreach($lines as $key => $value){
            if (function_exists('apache_getenv') && function_exists('apache_setenv') && apache_getenv($key) !== false) {
                apache_setenv($key, $value);
            }
            if (function_exists('putenv')) {
                putenv("$key=$value");
            }
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    private function linesAsArray(array $lines) : array{
        $ENV = array();
        foreach($lines as $line){
            $line = rtrim($line);
            $beginn = strpos($line, "=");
            if($beginn != false){
                $end = strlen($line);
                $key = substr($line, 0, $beginn);
                $ENV[$key] = substr($line, $beginn+1, $end);
            }
        }
        return $ENV;
    }

    private function arrayAsLines(array $env) : string{
        $lines = "";
        foreach($env as $key => $value){
            $lines .= "{$key}={$value}\n";
        }
        return $lines;
    }
}


