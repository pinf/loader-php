<?php

class PINF_Loader_ProgramTest extends PHPUnit_Framework_TestCase
{
    public function testHelloWorldProgram()
    {
//ob_end_flush();        
    
        $programPath = dirname(dirname(dirname(__DIR__))) . '/demos/StaticMappings';

        $program = new PINF_Loader_Program($programPath, array(
            'forceCompile' => true
        ));

        ob_start();

        $program->boot();
        
        $this->assertEquals(implode("\n", array(
            'Hello World',
            'Hallo Welt',
            ''
        )), ob_get_clean());
    }     
}
