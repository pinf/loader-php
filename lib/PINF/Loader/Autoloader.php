<?php

class PINF_Loader_Autoloader
{
    private static $instance = null;
    
    private $mappings = array();

    public static function init()
    {
        if (self::$instance === null)
        {
            self::$instance = new PINF_Loader_Autoloader();
            spl_autoload_register('PINF_Loader_Autoloader::_loadClass');
        }
        return self::$instance;
    }

    public static function _loadClass($name)
    {
        self::$instance->loadClass($name);
    }

    public function loadClass($name)
    {
        $name = str_replace('\\', '/', $name);
        
        $key = substr($name, 0, strpos($name, '/'));

        if (!isset($this->mappings[$key]))
            return;
    
        $path = $this->mappings[$key] . substr($name, strlen($key)) . '.php';
    
        require_once($path);
    }
    
    public function addMapping($key, $path)
    {
        $this->mappings[$key] = $path;
    }
}
