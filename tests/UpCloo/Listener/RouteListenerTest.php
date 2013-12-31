<?php
namespace UpCloo\Listener;

use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Request;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Stdlib\Hydrator\ObjectProperty;
use Zend\Mvc\Router\RouteMatch;
use UpCloo\App\Engine;

class RouteListenerTest extends \PHPUnit_Framework_TestCase
{
    private $routeListener;

    private function createEventFromRouteMatch($routeMatch)
    {
        $router = $this->getMockBuilder("Zend\\Mvc\\Router\\SimpleRouteStack")
            ->disableOriginalConstructor()
            ->getMock();
        $router->expects($this->any())
            ->method("match")
            ->will($this->returnValue($routeMatch));

        $this->routeListener = new RouteListener($router);
        $this->routeListener->setHydrator(new ClassMethods());

        $target = new Engine();
        $target->setServiceManager(new ServiceManager());
        $target->setEventManager(new EventManager());

        if ($routeMatch) {
            $target->services()->setInvokableClass(
                $routeMatch->getParams()["controller"], $routeMatch->getParams()["controller"]
            );
        }

        $event = new Event();
        $event->setTarget($target);
        $event->setParams(["request" => new Request()]);

        return $event;
    }

    public function testHydrateControllers()
    {
        $routeMatch = new RouteMatch([
            "controller" => "UpCloo\\Test\\HyController",
            "action" => "anAction"
        ]);
        $event = $this->createEventFromRouteMatch($routeMatch);
        $this->routeListener->prepareControllerToBeExecuted($event);

        $controller = $event->getTarget()->services()->get("UpCloo\\Test\\HyController");

        $this->assertInstanceOf("Zend\\EventManager\\EventManager", $controller->events());
        $this->assertInstanceOf("Zend\\ServiceManager\\ServiceManager", $controller->services());
        $this->assertInstanceOf("Zend\\Http\\PhpEnvironment\\Request", $controller->getRequest());
        $this->assertInstanceOf("Zend\\Http\\PhpEnvironment\\Response", $controller->getResponse());
    }

    public function testHydratePropertiesControllers()
    {
        $routeMatch = new RouteMatch([
            "controller" => "UpCloo\\Test\\HyPropController",
            "action" => "anAction"
        ]);
        $event = $this->createEventFromRouteMatch($routeMatch);

        $this->routeListener->setHydrator(new ObjectProperty());

        $this->routeListener->prepareControllerToBeExecuted($event);

        $controller = $event->getTarget()->services()->get("UpCloo\\Test\\HyPropController");

        $this->assertInstanceOf("Zend\\EventManager\\EventManager", $controller->eventManager);
        $this->assertInstanceOf("Zend\\ServiceManager\\ServiceManager", $controller->serviceManager);
        $this->assertInstanceOf("Zend\\Http\\PhpEnvironment\\Request", $controller->request);
        $this->assertInstanceOf("Zend\\Http\\PhpEnvironment\\Response", $controller->response);
    }

    public function testReturnNullRouteMatch()
    {
        $event = $this->createEventFromRouteMatch(null);

        $status = $this->routeListener->prepareControllerToBeExecuted($event);

        $this->assertNull($status);
    }

    public function testWorksWithNotValidHydrator()
    {
        $routeMatch = new RouteMatch([
            "controller" => "UpCloo\\Test\\HyController",
            "action" => "anAction"
        ]);
        $event = $this->createEventFromRouteMatch($routeMatch);
        $this->routeListener->setHydrator(function() { return "hello"; });
        $this->routeListener->prepareControllerToBeExecuted($event);

        $controller = $event->getTarget()->services()->get("UpCloo\\Test\\HyController");

        $this->assertNull($controller->events());
        $this->assertNull($controller->services());
        $this->assertNull($controller->getRequest());
        $this->assertNull($controller->getResponse());
    }
}
