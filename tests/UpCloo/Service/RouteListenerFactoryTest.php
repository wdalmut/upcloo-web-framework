<?php
namespace UpCloo\Service;

use Zend\ServiceManager\ServiceManager;

class RouteListenerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService("Config", ["router" => []]);
        $serviceManager->setService("hydrator", false);

        $instance = new RouteListenerFactory();
        $instance = $instance->createService($serviceManager);

        $this->assertInstanceOf("UpCloo\\Listener\\RouteListener", $instance);
    }
}
