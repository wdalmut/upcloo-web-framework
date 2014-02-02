<?php
namespace UpCloo\App;

use UpCloo\Exception\HaltException;

use Zend\EventManager\EventManager;
use Zend\ServiceManager\ServiceManager;

use Zend\Http\PhpEnvironment\Response;

use Zend\Mvc\Router\Http\RouteMatch;

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

    public function testErrorEventsAreCalledInOrder()
    {
        $eventManager = new EventManager();
        $serviceManager = new ServiceManager();

        $engine = new Engine();
        $engine->setServiceManager($serviceManager);
        $engine->setEventManager($eventManager);

        $result = "";
        $eventManager->attach("begin", function() use (&$result) {
            $result .= "b";
        });
        $eventManager->attach("route", function() use (&$result) {
            $result .= "r";
        });
        $eventManager->attach("404", function() use (&$result) {
            $result .= "4";
        });
        $eventManager->attach("renderer", function() use (&$result) {
            $result .= "re";
        });
        $eventManager->attach("finish", function() use (&$result) {
            $result .= "f";
        });

        $engine->run();
        $this->assertEquals("br4ref", $result);
    }

    public function testEventsAreCalledInOrder()
    {
        $eventManager = new EventManager();
        $serviceManager = new ServiceManager();

        $engine = new Engine();
        $engine->setServiceManager($serviceManager);
        $engine->setEventManager($eventManager);

        $result = "";
        $eventManager->attach("begin", function() use (&$result) {
            $result .= "b";
        });
        $eventManager->attach("route", function() use (&$result) {
            $result .= "r";
            return new RouteMatch([]);
        });
        $eventManager->attach("pre.fetch", function() use (&$result) {
            $result .= "p";
        });
        $eventManager->attach("execute", function() use (&$result) {
            $result .= "e";
        });
        $eventManager->attach("renderer", function() use (&$result) {
            $result .= "re";
        });
        $eventManager->attach("finish", function() use (&$result) {
            $result .= "f";
        });

        $engine->run();
        $this->assertSame("brperef", $result);
    }

    public function testHaltEventIsFired()
    {
        $eventManager = new EventManager();
        $serviceManager = new ServiceManager();

        $engine = new Engine();
        $engine->setServiceManager($serviceManager);
        $engine->setEventManager($eventManager);

        $isCalled = false;
        $eventManager->attach("halt", function() use (&$isCalled){
            $isCalled = true;
        });
        $eventManager->attach("route", function() use ($eventManager) {
            $eventManager->attach("pre.fetch", function() {
                throw new HaltException("");
            });
            return new RouteMatch([]);
        });

        $engine->run();

        $this->assertTrue($isCalled);
    }

    public function testApplicationErrorEventIsFired()
    {
        $eventManager = new EventManager();
        $serviceManager = new ServiceManager();

        $engine = new Engine();
        $engine->setServiceManager($serviceManager);
        $engine->setEventManager($eventManager);

        $isCalled = false;
        $eventManager->attach("500", function() use (&$isCalled){
            $isCalled = true;
        });
        $eventManager->attach("route", function() use ($eventManager) {
            $eventManager->attach("pre.fetch", function() {
                throw new \RuntimeException("");
            });
            return new RouteMatch([]);
        });

        $engine->run();

        $this->assertTrue($isCalled);
    }
}
