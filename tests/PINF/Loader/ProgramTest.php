<?php

class PINF_Loader_ProgramTest extends PHPUnit_Framework_TestCase
{
    public function testStaticMappingsProgram()
    {
        $programPath = dirname(dirname(dirname(__DIR__))) . '/demos/StaticMappings';

        $program = new PINF_Loader_Program($programPath, array(
            'forceCompile' => true,
            'verbose' => true,
            'debug' => true
        ));

        ob_start();

        $program->boot();
        
        $this->assertEquals(implode("\n", array(
            'Hello World',
            'Hallo Welt',
            ''
        )), ob_get_clean());
    }


    public function testGithubPackageProgram()
    {
        $programPath = dirname(dirname(dirname(__DIR__))) . '/demos/GithubPackage';

        $program = new PINF_Loader_Program($programPath, array(
            'forceCompile' => true,
            'verbose' => false,
            'debug' => false
        ));

        ob_start();

        $program->boot();

        foreach (explode("\n", ob_get_clean()) as $line)
        {
            if ($line)
            {
                if (!preg_match_all('/^\[[^\]]*\] name.(WARNING|ERROR): (Warning|Error) Message \[\] \[\]$/', $line, $m))
                    $this->fail("Line '$line' does not match pattern!");
            }
  
        }
    }
}
