<?php
namespace UpCloo;

use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config as ServiceManagerConfig;
use Zend\Mvc\Router;
use Zend\Uri\UriInterface;
use Zend\EventManager\Event;

class App
{
    private $conf;
    private $router;
    private $events;
    private $request;
    private $response;
    private $eventManager;
    private $serviceManager;

    use Hydrator\ControllerHydrator;

    public function __construct(array $conf)
    {
        $this->conf = $conf;
    }

    public function bootstrap()
    {
        $closure = function($event){
            $request = $event->getParam('request');
            $match = $this->getRouter()->match($request);

            if ($match) {
                $controller = $match->getParam("controller");
                if (!$this->services()->has($controller)) {
                    $this->services()->setInvokableClass($controller, $controller);
                }

                $controller = $this->services()->get($controller);
                $action = $match->getParam("action");
                $this->hydrate($this, $controller);

                $this->events()->attach("execute", array($controller, $action));

                $renderer = $match->getParam("renderer");
                if (!$this->services()->has($renderer)) {
                    $this->services()->setInvokableClass($renderer, $renderer);
                }
                $renderer = $this->services()->get($renderer);
                $this->events()->attach("renderer", array($renderer, "render"));
            }

            return $match;
        };

        $this->router = TreeRouteStack::factory($this->conf["router"]);
        $closure->bindTo($this);
        $this->events()->attach("route", $closure);

        $this->registerListeners();
        $this->registerServices();

        return $this;
    }

    /**
     * Register services into the ServiceManager
     */
    private function registerServices()
    {
        if (array_key_exists("services", $this->conf)) {
            $services = $this->conf["services"];
            $config = new ServiceManagerConfig($services);
            $serviceManager = new ServiceManager($config);
            $this->setServiceManager($serviceManager);
        }
    }

    private function registerListeners()
    {
        if (array_key_exists("listeners", $this->conf)) {
            foreach ($this->conf["listeners"] as $eventName => $callables) {
                $this->registerCallbacks($eventName, $callables);
            }
        }
    }

    private function registerCallbacks($eventName, $callables)
    {
        foreach ($callables as $callable) {
            $this->events()->attach($eventName, $callable);
        }
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function setEventManager(EventManagerInterface $em)
    {
        $this->events = $em;
    }

    public function event()
    {
        $event = new Event();
        $event->setTarget($this);
        return $event;
    }

    public function events()
    {
        if (!$this->events instanceof EventManagerInterface) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }

    public function trigger($name, array $params = array())
    {
        $event = $this->event();
        $event->setParams($params);
        return $this->events()->trigger($name, $event);
    }

    public function services()
    {
        if (!$this->serviceManager) {
            $this->serviceManager = new ServiceManager();
        }

        return $this->serviceManager;
    }

    public function setServiceManager($serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function request()
    {
        if (!$this->request instanceof Request) {
            $this->request = new Request;
        }
        return $this->request;
    }

    public function response()
    {
        if (!($this->response instanceof Response)) {
            $this->response = new Response;
        }
        return $this->response;
    }

    public function run()
    {
        $this->bootstrap();
        $events = $this->events();

        $this->trigger("begin");
        try {
            $request = $this->request();

            $eventCollection = $this->trigger("route", array("request" => $request));
            $routeMatch = $eventCollection->last();

            if ($routeMatch == null) {
                $this->response()->setStatusCode(Response::STATUS_CODE_404);
                throw new Exception\PageNotFoundException("page not found");
            }
            $this->trigger(
                "pre.fetch",
                array(
                    "eventManager" => $this->events(),
                    "request" => $this->request(),
                    "response" => $this->response(),
                    "routeMatch" => $routeMatch
                )
            );

            $this->response()->setStatusCode(Response::STATUS_CODE_200);
            $controllerExecution = $this->events()->trigger("execute", $routeMatch);
            $this->trigger(
                "renderer",
                array(
                    "data" => $controllerExecution,
                    "request" => $this->request(),
                    "response" => $this->response()
                )
            );

        } catch (Exception\HaltException $e) {
            $this->trigger("halt");
        } catch (Exception\PageNotFoundException $e) {
            $this->trigger("404");
        } catch (\Exception $e) {
            $this->response()->setStatusCode(Response::STATUS_CODE_500);
            $this->trigger("500", array("exception" => $e));
        }

        $this->trigger('finish');
        $this->response()->send();
    }
}
