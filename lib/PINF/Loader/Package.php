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
        {
            // We don't have a package.json file so let's check for a composer.json file.
            // @see http://packagist.org/            
            $descriptorPath = dirname($this->descriptorPath) . '/composer.json';
            if (file_exists($descriptorPath))
            {
                $descriptor = json_decode(file_get_contents($descriptorPath), true);
                if (isset($descriptor['require']))
                {
                    if (count($descriptor['require']) > 1 || !isset($descriptor['require']['php']))
                        throw new Error('When using a Composer-based package as an archive it may not declare any "require"s other than "php"! Use a "pm=composer" dependency declaration to install via Composer instead.');
                    if (isset($descriptor['require']['php']))
                    {
                        if (!preg_match_all("/^([^\d]*)(\d*.*)$/", $descriptor['require']['php'], $m))
                            throw new Error('Error parsing PHP version requirement "' . $descriptor['require']['php'] . '" from: ' . $descriptorPath);

                        if (version_compare(PHP_VERSION, $m[2][0], $m[1][0]) !== true)
                            throw new Exception('Package "' . $this->path . '" requires PHP ' . $descriptor['require']['php']);
                    }
                }
                
                if (!isset($descriptor['autoload']))
                {
                    $descriptor['autoload'] = array();
                    if (!isset($descriptor['autoload']['psr-0']))
                    {
                        if (is_dir(dirname($this->descriptorPath) . '/src'))
                            $descriptor['autoload']['psr-0'] = array('__NS__' => 'src/');
                        if (is_dir(dirname($this->descriptorPath) . '/lib'))
                            $descriptor['autoload']['psr-0'] = array('__NS__' => 'lib/');
                    }
                }

                $this->descriptor = array(
                    'directories' => array(
                        'lib' => $descriptor['autoload']['psr-0'][key($descriptor['autoload']['psr-0'])]
                    )
                );
            }
            else
                throw new Exception('Package descriptor not found at path "' . $this->descriptorPath . '"!');
        }
        else
        {
            $this->descriptor = json_decode(file_get_contents($this->descriptorPath), true);
        }

        if ($this->descriptor===null || !is_array($this->descriptor))
            throw new Exception('Package descriptor not a valid JSON file at path "' . $this->descriptorPath . '"!');

        $this->libPath = $this->path;
        if (isset($this->descriptor['directories']))
        {
            if (isset($this->descriptor['directories']['lib']))
            {
                $this->libPath = realpath($this->libPath . '/' . $this->descriptor['directories']['lib']);
            } else
                $this->libPath .= '/lib';
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
        static $compiling = array();
        if (isset($compiling[$path]))
            return;
        $compiling[$path] = true;
    
        if (substr($path, 0, strlen($this->path)) !== $this->path)
            throw new Exception('Cannot compile module "' . $path . '" using package "' . $this->path . '"! Module must reside WITHIN package!');
            
        $compiledPath = PINF_Loader_Cache::getPath('compiled/package/' . $this->id, substr($path, strlen($this->path) + 1)); 
        
        // Module at $path already compiled
        if (file_exists($compiledPath) && $this->options['forceCompile'] !== true)
            return $compiledPath;

        // Compile module at $path once and cache
                                
        $source = file_get_contents($path);
        
        // Strip 'namespace' statements
        preg_match_all("/\nnamespace\s*(\S*)\s*;/", $source, $m);
        $namespace = $this->idHash;
        if ($m)
        {
            if (sizeof($m[0]) > 1)
                throw new Exception('Only one namespace statement is allowed in: ' . $path);

            for ( $i=0, $l=sizeof($m[0]) ; $i<$l ; $i++ )
            {
                if (substr(dirname($path), strlen($m[1][$i]) * -1) !== str_replace('\\', '/', $m[1][$i]))
                    throw new Exception('Namespace statement in "' . $path . '" does not follow PSR-0 conventions (based on file path)!');
                $source = str_replace($m[0][$i], "", $source);
                $namespace .= '\\' . $m[1][$i];
                
                // Compile all files for our namespace
                foreach (new DirectoryIterator(dirname($path)) as $fileinfo)
                {
                    if ($fileinfo->isFile())
                    {
                        $this->compile($fileinfo->getPathname());
                    }
                }
            }
        }

        // Rewrite 'use' statements and compile linked modules
        preg_match_all("/\nuse\s*(\S*)\s*(as\s*(\S*))?;/", $source, $m);
        if ($m)
        {
            $source = str_replace('<' . '?php', '<' . '?php' . "\n" . 'namespace ' . $namespace . ';' . "\n", $source);

            for ( $i=0, $l=sizeof($m[0]) ; $i<$l ; $i++ )
            {
                $moduleIdentifier = $m[1][$i];

                $package = $this;

                if (strpos($moduleIdentifier, '/') === false)
                {
                    $id = str_replace('\\', '/', $moduleIdentifier);
                }
                else
                {
                    $alias = substr($moduleIdentifier, 0, strpos($moduleIdentifier, '/'));
                    $id = substr($moduleIdentifier, strlen($alias) + 1);
                    if ($alias !== ".")
                        $package = $this->packageForAlias($alias);
                }
                
                $useStatement = "\n" . 'use \\' . $package->idHash . '\\' . str_replace('/', '\\', $id);
                
                if ($m[2][$i])
                    $useStatement .= ' as ' . $m[3][$i];

                $useStatement .= ';';

                $modulePath = $package->resolveModuleIdentifier($id);

                if (!file_exists($modulePath))
                {
                    // This happens if the 'use' statement refers to a namespace/file that does not exist.
                    // We ignore this as PHP ignores it
                    if (isset($this->options['verbose']) && $this->options['verbose'] === true)
                        echo 'WARNING: Discarding statement "' . str_replace("\n", '\n', $m[0][$i]) . '" from file "' . $path . '" as target "' . $modulePath . '" not found!' . "\n";
                    $useStatement = "";
                    $modulePath = false;
                }

                $source = str_replace($m[0][$i], $useStatement, $source);

                if ($modulePath !== false)
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
