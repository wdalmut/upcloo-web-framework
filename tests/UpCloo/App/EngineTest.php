<?php
namespace UpCloo\App;

use Zend\EventManager\EventManager;
use Zend\ServiceManager\ServiceManager;

use Zend\Http\PhpEnvironment\Response;

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

    public function testGetTheDefaultResponse()
    {
        $this->assertInstanceOf("Zend\\Http\\PhpEnvironment\\Response", $this->engine->response());
    }

    public function testMyResponse()
    {
        $response = new Response();
        $this->engine->setResponse($response);
        $this->assertSame($response, $this->engine->response());
    }
}
