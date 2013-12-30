<?php
namespace UpCloo\App;

use Zend\EventManager\EventManager;
use Zend\ServiceManager\ServiceManager;

class EngineTest extends \PHPUnit_Framework_TestCase
{
    private $engine;

    public function setUp()
    {
        $this->engine = new Engine();
        $this->engine->setServiceManager(new ServiceManager());
        $this->engine->setEventManager(new EventManager());
    }

    public function testGetTheDefaultRequest()
    {
        $this->assertInstanceOf("Zend\\Http\\PhpEnvironment\\Request", $this->engine->request());
    }
}
