<?php

class PINF_Loader_Cache
{
    private static $instance = null;
    private $basePath = "";


    public function __construct()
    {
        if (!isset($_SERVER["PWD"]))
            throw new Exception('$_SERVER["PWD"] not set!');

        $this->basePath = $_SERVER["PWD"] . '/.pinf_packages';
    }
    
    private static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new PINF_Loader_Cache();
        }
        return self::$instance;
    }

    public static function getPath($namespace, $path)
    {
        if (substr($namespace, -1, 1) === "/")
            $namespace = substr($namespace, 0, -1);

        return self::getInstance()->basePath . '/' . $namespace . '/' . $path; 
    }    
}
