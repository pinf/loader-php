<?php

require_once(__DIR__ . '/Autoloader.php');


class PINF_Loader_Program
{
    private $cacheBasePath = "";
    
    private $programDescriptorPath = "";
    
    private $autoloader = null;

    
    public function __construct($path, $options)
    {
        if (!isset($_SERVER["PWD"]))
            throw new Exception('$_SERVER["PWD"] not set!');

        $this->cacheBasePath = $_SERVER["PWD"] . '/.pinf_packages';
        
        if (is_dir($path)) {
            $path .= '/program.json';
        }
        
        if (!file_exists($path))
            throw new Exception('Program descriptor file not found at path "' + $path + '"!');

        $this->programDescriptorPath = $path;
        $this->programDescriptor = json_decode(file_get_contents($this->programDescriptorPath), true);
        
        $this->options = $options;

        $this->autoloader = PINF_Loader_Autoloader::init();
    }

    public function boot()
    {
        $mainModulePath = dirname($this->programDescriptorPath) . '/main.php';
        
        if (!file_exists($mainModulePath))
            throw new Exception('Main module not found at path "' . $mainModulePath . '"!');
    
        $compiledMainModulePath = $this->compile($mainModulePath, dirname($this->programDescriptorPath) . '/package.json');
        
        
    
    
var_dump($compiledMainModulePath);    

        require_once($compiledMainModulePath);
    }
    
    private function compile($path, $ourPackageDescriptorPath, $ns = null)
    {
        $compiledPath = $this->getCachePath($path);
        
        // Module at $path already compiled
        if (file_exists($compiledPath) && $this->options['forceCompile'] !== true)
            return $compiledPath;
        
        // Compile module at $path once and cache
        
        $source = file_get_contents($path);
        
        preg_match_all("/\nuse\s*(\S*)\s*as\s*(\S*);/", $source, $m);
        
        if ($m)
        {
            if ($ns !== null)
            {
                $source = str_replace('<' . '?php', '<' . '?php' . "\n" . 'namespace ' . $ns . ';' . "\n", $source);
            }

            for ( $i=0, $l=sizeof($m[0]) ; $i<$l ; $i++ )
            {
                $moduleIdentifier = $m[1][$i];
                $alias = substr($moduleIdentifier, 0, strpos($moduleIdentifier, '/'));
                $id = substr($moduleIdentifier, strlen($alias) + 1);
                $moduleName = $m[2][$i];
                
                $packageMeta = $this->packageMetaForAlias($alias, $ourPackageDescriptorPath);
            
var_dump($packageMeta);

                $modulePath = $moduleLibPath = $packageMeta['locator']['location'];
                if (isset($packageMeta['directories']['lib']))
                {
                    if (is_array($packageMeta['directories']['lib']))
                    {
                        if (isset($packageMeta['directories']['lib']['path']))
                            $modulePath = $moduleLibPath .= '/' . $packageMeta['directories']['lib']['path'];
                        else
                            $modulePath = $moduleLibPath .= '/lib';
                    }
                    else
                        $modulePath = $moduleLibPath .= '/' . $packageMeta['directories'];
                }
                else
                    $modulePath = $moduleLibPath .= '/lib';
                $modulePath .= '/' . $id . '.php';
                
                $moduleClassName = $id;
                $useStatement = "\n" . 'use ' . $packageMeta['idHash'] . '\\' . $id . ' as ' . $moduleName . ';';

                // var_dump($modulePath);
                // var_dump($moduleClassName);

                $source = str_replace($m[0][$i], $useStatement, $source);

                $this->autoloader->addMapping($packageMeta['idHash'], $this->getCachePath($moduleLibPath));
                
                $this->compile($modulePath, $packageMeta['locator']['location'] . '/package.json', $packageMeta['idHash']);
            }
        }

        if (!file_exists(dirname($compiledPath)))
            mkdir(dirname($compiledPath), 0775, true);
        file_put_contents($compiledPath, $source);

        return $compiledPath;
    }
    
    private function getCachePath($path)
    {
        // TODO: See if $path points to inside a package to store in package-specific cache

        return $this->cacheBasePath . '/compiled/program/' . substr($path, strlen(dirname($this->programDescriptorPath)) + 1); 
    }
    
    // TODO: Refactor this into various methods
    private function packageMetaForAlias($alias, $ourPackageDescriptorPath)
    {
        if (!file_exists($ourPackageDescriptorPath))
            throw new Exception('Package descriptor file not found at path "' + $ourPackageDescriptorPath + '"!');
        
        $ourPackageDescriptor = json_decode(file_get_contents($ourPackageDescriptorPath), true);

        if (!isset($ourPackageDescriptor['mappings'][$alias]))
            throw new Exception('Alias "' . $alias . '" not found in "mappings" property in package descriptor at path "' . $ourPackageDescriptorPath . '"!');
        
        $packageId = $ourPackageDescriptor['mappings'][$alias];
        
        if (!isset($this->programDescriptor['packages'][$packageId]))
            throw new Exception('Package ID "' . $packageId . '" not found in "packages" property in program descriptor at path "' . $programDescriptorPath . '" needed while resolving alias "' . $alias . '" in package at path "' . $ourPackageDescriptorPath . '"!');
        
        $packageLocator = $this->programDescriptor['packages'][$packageId]['locator'];

        if (isset($packageLocator['location']))
        {
            if (substr($packageLocator['location'], 0, 2) === './')
            {
                $packageLocator['location'] = dirname($ourPackageDescriptorPath) . substr($packageLocator['location'], 1);
            }
        }
        else
            throw new Exception('Invalid package locator at "' . $programDescriptorPath . '" ~ packages["' . $packageId . '"].locator!');

        $packageDescriptorPath = $packageLocator['location'] . '/package.json';

        $packageDescriptor = json_decode(file_get_contents($packageDescriptorPath), true);

        return array(
            'id' => $packageId,
            'idHash' => 'NS' . strtoupper(md5($packageId)),
            'locator' => $packageLocator,
            'directories' => (isset($packageDescriptor['directories']))?$packageDescriptor['directories']:array()
        );
    }
    
}
