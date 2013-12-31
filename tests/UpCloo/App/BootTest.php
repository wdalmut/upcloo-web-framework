<?php
namespace UpCloo\App;

class BootTest extends \PHPUnit_Framework_TestCase
{
    public function testGetEmptyServiceManagerOnMissingConfiguration()
    {
        $boot = new Boot(new Config\ArrayProcessor());
        $boot->bootstrap();
        $this->assertInstanceOf("Zend\\ServiceManager\\ServiceManager", $boot->services());
        $this->assertInstanceOf("Zend\\EventManager\\EventManager", $boot->events());
    }

    public function testConfigurationOverwrite()
    {
        $processor = new Config\ArrayProcessor();
        $myConf = [
            "services" => [
                "invokables" => [
                    "UpCloo\\Listener\\Renderer\\Json" => "UpCloo\\Listener\\Renderer\\Json"
                ],
                "aliases" => [
                    "renderer" => "UpCloo\\Listener\\Renderer\\Json",
                ]
            ]
        ];
        $processor->appendConfig($myConf);

        $boot = new Boot($processor);
        $boot->bootstrap();

        $renderer = $boot->services()->get("renderer");

        $this->assertInstanceOf("UpCloo\\Listener\\Renderer\\Json", $renderer);
    }

    public function testServiceManagerIsNotReplaced()
    {
        $boot = new Boot(new Config\ArrayProcessor());

        $serviceManager = new \Zend\ServiceManager\ServiceManager();
        $boot->setServiceManager($serviceManager);

        $boot->bootstrap();

        $this->assertSame($serviceManager, $boot->services());
    }

    public function testEventManagerIsNotReplaced()
    {
        $boot = new Boot(new Config\ArrayProcessor());

        $eventManager = new \Zend\EventManager\EventManager();
        $boot->setEventManager($eventManager);

        $boot->bootstrap();

        $this->assertSame($eventManager, $boot->events());
    }
}
