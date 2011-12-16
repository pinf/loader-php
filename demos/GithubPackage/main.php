<?php

use monolog1/Monolog/Logger as Logger1;
use monolog1/Monolog/Handler/StreamHandler as StreamHandler1;
use monolog1/Monolog/Handler/AbstractProcessingHandler as AbstractProcessingHandler1;

use monolog2/Monolog/Logger as Logger2;
use monolog2/Monolog/Handler/StreamHandler as StreamHandler2;
use monolog2/Monolog/Handler/AbstractProcessingHandler as AbstractProcessingHandler2;


class EchoHandler1 extends AbstractProcessingHandler1
{
    protected function write(array $record)
    {
        print((string) $record['formatted']);
    }
}

class EchoHandler2 extends AbstractProcessingHandler2
{
    protected function write(array $record)
    {
        print((string) $record['formatted']);
    }
}


$log1 = new Logger1('name');
$log1->pushHandler(new EchoHandler1(Logger1::WARNING));

$log1->addWarning('Warning Message');
$log1->addError('Error Message');


$log2 = new Logger2('name');
$log2->pushHandler(new EchoHandler2(Logger2::WARNING));

$log2->addWarning('Warning Message');
$log2->addError('Error Message');
