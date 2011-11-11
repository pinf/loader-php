<?php

function __autoload__($class)
{
    $basePath = dirname(dirname(__FILE__)) . '/lib';
    if (!file_exists($basePath)) {
        $basePath = dirname(dirname(dirname(dirname(__FILE__)))) . '/lib';
    }

    // find relative
    if (file_exists($file = $basePath . '/' . str_replace('_', '/', $class) . '.php')) {
        require_once($file);
    }
}

spl_autoload_register('__autoload__');
