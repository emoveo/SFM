<?php
namespace Generator;

use SFM\Cache\Generator\Md5Generator;

class Md5GeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGeneratorOnString()
    {
        $generator = new Md5Generator("ns");
        $value1 = $generator->generate("12");

        $generator = new Md5Generator("ns1");
        $value2 = $generator->generate("2");

        $this->assertInternalType("string", $value1);
        $this->assertInternalType("string", $value2);
        $this->assertNotEquals($value1, $value2);
    }

    public function testGeneratorOnString2()
    {
        $generator = new Md5Generator("ns");
        $value1 = $generator->generate("1");
        $value2 = $generator->generate("1");

        $this->assertInternalType("string", $value1);
        $this->assertInternalType("string", $value2);
        $this->assertEquals($value1, $value2);
    }
} 