<?php

class PINF_Loader_Package
{
    private $program = null;
    private $id = "";
    private $idHash = "";
    private $path = "";
    private $descriptorPath = "";
    private $descriptor = array();

    public $libPath = "";


    public function __construct($program, $id, $path, $options)
    {
        $this->program = $program;
        $this->id = $id;
        
        $this->idHash = 'NS' . strtoupper(md5($this->id));
        
        $this->path = $path;

        if (!file_exists($this->path))
            throw new Exception('Package not found at path "' . $this->path . '"!');

        if (is_dir($this->path)) {
            $this->descriptorPath = $this->path . '/package.json';
        } else {
            $this->descriptorPath = $this->path;
            $this->path = dirname($this->descriptorPath);
        }

        $this->options = $options;
        
        if (!file_exists($this->descriptorPath))
            throw new Exception('Package descriptor not found at path "' . $this->descriptorPath . '"!');
        
        $this->descriptor = json_decode(file_get_contents($this->descriptorPath), true);

        if ($this->descriptor===null || !is_array($this->descriptor))
            throw new Exception('Package descriptor not a valid JSON file at path "' . $this->descriptorPath . '"!');

        $this->libPath = $this->path;
        if (isset($this->descriptor['directories']['lib']))
        {
            if (is_array($this->descriptor['directories']['lib']))
            {
                if (isset($packageMeta['directories']['lib']['path']))
                    $this->libPath .= '/' . $this->descriptor['directories']['lib']['path'];
                else
                    $this->libPath .= '/lib';
            } else
                $this->libPath .= '/' . $this->descriptor['directories'];
        }
        else
            $this->libPath .= '/lib';

        $this->program->autoloader->addMapping($this->idHash, PINF_Loader_Cache::getPath('compiled/package/' . $this->id, substr($this->libPath, strlen($this->path) + 1)));
    }

    private function packageForAlias($alias)
    {
        if (!isset($this->descriptor['mappings'][$alias]))
            throw new Exception('Alias "' . $alias . '" not found in "mappings" property for package descriptor "' . $this->descriptorPath . '"!');
        
        $packageID = $this->descriptor['mappings'][$alias];

        try
        {
            return $this->program->packageForID($packageID);
        }
        catch(Exception $e)
        {
            throw new Exception('Unable to resolve alias "' . $alias . '" for package "' . $this->descriptorPath . '" via packageID "' . $packageID . '"!', 0, $e);
        }
    }

    public function compile($path)
    {
        if (substr($path, 0, strlen($this->path)) !== $this->path)
            throw new Exception('Cannot compile module "' . $path . '" using package "' . $this->path . '"! Module must reside WITHIN package!');
            
        $compiledPath = PINF_Loader_Cache::getPath('compiled/package/' . $this->id, substr($path, strlen($this->path) + 1)); 
        
        // Module at $path already compiled
        if (file_exists($compiledPath) && $this->options['forceCompile'] !== true)
            return $compiledPath;
        
        // Compile module at $path once and cache
        
        $source = file_get_contents($path);
        
        preg_match_all("/\nuse\s*(\S*)\s*as\s*(\S*);/", $source, $m);
        
        if ($m)
        {
            $source = str_replace('<' . '?php', '<' . '?php' . "\n" . 'namespace ' . $this->idHash . ';' . "\n", $source);

            for ( $i=0, $l=sizeof($m[0]) ; $i<$l ; $i++ )
            {
                $moduleIdentifier = $m[1][$i];
                $alias = substr($moduleIdentifier, 0, strpos($moduleIdentifier, '/'));
                $id = substr($moduleIdentifier, strlen($alias) + 1);
                $moduleName = $m[2][$i];
                
                $package = $this->packageForAlias($alias);
            
                $useStatement = "\n" . 'use \\' . $package->idHash . '\\' . $id . ' as ' . $moduleName . ';';

                $source = str_replace($m[0][$i], $useStatement, $source);

                $modulePath = $package->resolveModuleIdentifier($id);

                $package->compile($modulePath);
            }
        }

        if (!file_exists(dirname($compiledPath)))
            mkdir(dirname($compiledPath), 0775, true);
        file_put_contents($compiledPath, $source);

        return $compiledPath;
    }
    
    public function resolveModuleIdentifier($moduleIdentifier)
    {
        if (!is_dir($this->libPath))
            throw new Exception('Lib directory "' . $this->libPath . '" not found for package "' . $this->descriptorPath . '"!');
        
        return $this->libPath . '/' . $moduleIdentifier . '.php';
    }
    
    public function getMainModulePath()
    {
        if (!isset($this->descriptor['main']))
            throw new Exception('No "main" property found in descriptor "' . $this->descriptorPath . '"!');
        
        $path = realpath($this->path . '/' . $this->descriptor['main']);
        
        if (!file_exists($path))
            throw new Exception('Main module "' . $this->descriptor['main'] . '" not found at "' . ($this->path . '/' . $this->descriptor['main']) . '" for package "' . $this->descriptorPath . '"!');

        return $path;
    }
 }
