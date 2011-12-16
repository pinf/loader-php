<?php

class PINF_Loader_Downloader
{
    private $options = null;
    private $verbose = false;

    // TODO: Get from ENV variable
    private $basePath = '/pinf/pinf_packages';

    
    public function __construct($options)
    {
        $this->options = $options;

        if (!file_exists($this->basePath))
        {
            $this->basePath = getcwd() . '/.pinf_packages';
            if (!file_exists($this->basePath))
                mkdir($this->basePath, 0775, true);
        }
        
        if (isset($this->options['verbose']) && $this->options['verbose'] === true)
            $this->verbose = true;
    }
    
    public function getLocationForArchive($archive)
    {
        $archiveParts = parse_url($archive);
        
        $pathID = $archiveParts['host'] . $archiveParts['path'];
        
        $packagePath = $this->basePath . '/downloads/packages/' . $pathID . '~pkg';
        
        if (file_exists($packagePath))
            return $packagePath;

        if ($this->verbose)
            echo 'Downloading archive: ' . $archive  . "\n";

        $archivePath = $this->_downloadArchive($archive);
        $extractedPath = realpath($archivePath) . '~content';

        if (!file_exists($extractedPath))
        {
            // TODO: Check for type of archive and extract accordingly
            // NOTE: Currently assuming ZIP archive!
    
            $zip = new ZipArchive();
            if ($zip->open($archivePath) === TRUE)
            {
                $zip->extractTo($extractedPath);
                $zip->close();
            } else {
                throw new Error("Zip extraction of '$archivePath' to '$extractedPath' failed!");
            }
        }

        $extractedPackagePath = $extractedPath;
        // Archives usually contain a root folder that holds all content
        $dirs = scandir($extractedPackagePath);
        if (count($dirs) === 3)
            $extractedPackagePath .= '/' . $dirs[2];

        if (!file_exists(dirname($packagePath)))
            mkdir(dirname($packagePath), 0775, true);

        symlink($extractedPackagePath, $packagePath);
        
        return $packagePath;
    }


    private function _downloadArchive($url)
    {
        $urlParts = parse_url($url);

        $path = $this->basePath . '/downloads/archives/' . $urlParts['host'] . $urlParts['path'];

        if (file_exists($path))
            return $path;
        
        if (!extension_loaded("curl"))
            throw new Exception('The "curl" extension must be installed and loaded!');

        if (!file_exists(dirname($path)))
            mkdir(dirname($path), 0775, true);

        $curl = curl_init($url);
        $fp = fopen($path, 'w');
        
        curl_setopt($curl, CURLOPT_FILE, $fp);
        curl_setopt($curl, CURLOPT_HEADER, 0);

        curl_exec($curl);
        
        $info = curl_getinfo($curl);

        curl_close($curl);
        fclose($fp);

        if ($info['http_code'] === 302)
        {
            unlink($path);

            $archivePath = $this->_downloadArchive($info['redirect_url']);

            symlink($archivePath, $path);
        }
        else
        if ($info['http_code'] !== 200)
        {
            throw new Exception('Got status "' . $info['http_code'] . '" while downloading: ' . $url);
        }

        return $path;
    }
}
