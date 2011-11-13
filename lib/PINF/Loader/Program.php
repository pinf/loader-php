<?php

require_once(__DIR__ . '/Autoloader.php');


class PINF_Loader_Program
{
    private $path = "";
    private $descriptorPath = "";
    private $descriptor = array();

    private $packages = array();
    
    public $autoloader = null;

    
    public function __construct($path, $options)
    {
        $this->descriptorPath = $path;

        if (is_dir($this->descriptorPath)) {
            $this->descriptorPath .= '/program.json';
        }

        if (!file_exists($this->descriptorPath))
            throw new Exception('Program descriptor file not found at path "' . $this->descriptorPath . '"!');

        $this->options = $options;

        $this->path = dirname($this->descriptorPath);
        $this->descriptor = json_decode(file_get_contents($this->descriptorPath), true);

        if ($this->descriptor===null || !is_array($this->descriptor))
            throw new Exception('Program descriptor not a valid JSON file at path "' . $this->descriptorPath . '"!');

        $this->autoloader = PINF_Loader_Autoloader::init();
    }

    public function boot()
    {
        if (!isset($this->descriptor['boot']))
            throw new Exception('No "boot" property found in descriptor "' . $this->descriptorPath . '"!');

        $package = $this->packageForID($this->descriptor['boot']);

        require_once($package->compile($package->getMainModulePath()));
    }

    public function packageForID($packageId)
    {
        if (!isset($this->descriptor['packages'][$packageId]))
            throw new Exception('PackageID "' . $packageId . '" not found in "packages" property for program descriptor "' . $this->descriptorPath . '"!');
        
        $locator = $this->descriptor['packages'][$packageId]['locator'];

        if (isset($locator['location']))
        {
            if (substr($locator['location'], 0, 2) === './')
            {
                $locator['location'] = dirname($this->descriptorPath) . substr($locator['location'], 1);
            }
        }
        else
            throw new Exception('Invalid package locator at "' . $this->descriptorPath . '" ~ packages["' . $packageId . '"].locator!');

        $path = $locator['location'];

        if (is_dir($path)) {
            $path = realpath($path) . '/package.json';
        }

        if (!$this->packages[$path]) {
            $this->packages[$path] = new PINF_Loader_Package($this, $packageId, $path, $this->options);
        }

        return $this->packages[$path];
    }
}
