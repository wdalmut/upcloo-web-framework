<?php
namespace UpCloo\App\Config;

class ArrayProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testMergeConfigs()
    {
        $configurable = new ArrayProcessor();
        $configurable->appendConfig([
            "one" => []
        ]);
        $configurable->appendConfig([
            "one" => [
                "two" => "three"
            ]
        ]);

        $configured = $configurable->merge();

        $this->assertInternalType("array", $configured);
        $this->assertArrayHasKey("one", $configured);
        $this->assertInternalType("array", $configured["one"]);
        $this->assertEquals("three", $configured["one"]["two"]);
    }
}
