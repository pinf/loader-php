<?php

class PINF_Loader_ProgramTest extends PHPUnit_Framework_TestCase
{
    public function testHelloWorldProgram()
    {
ob_end_flush();        
    
        $programPath = dirname(dirname(dirname(__DIR__))) . '/demos/StaticMappings';

        $program = new PINF_Loader_Program($programPath, array(
            'forceCompile' => true
        ));

        $program->boot();
    }     
}
