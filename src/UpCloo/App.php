<?php
namespace UpCloo;

use Zend\Mvc\Router;
use Zend\Uri\UriInterface;
use Zend\EventManager\Event;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config as ServiceManagerConfig;
use Zend\Stdlib\Hydrator\HydratorInterface;

class App
{
    private $conf;
    private $router;
    private $events;
    private $request;
    private $response;
    private $serviceManager;

    public function __construct(array $userConfigs)
    {
        $baseConfig = $this->getAnEmptyConf();
        $this->conf = $this->mergeConfigs($baseConfig, $userConfigs);
    }

    private function mergeConfigs(array $conf, array $configs)
    {
        foreach ($configs as $confFile) {
            $conf = array_replace_recursive($conf, $confFile);
        }

        return $conf;
    }

    private function getAnEmptyConf()
    {
        return [
            "router" => [],
            "services" => [
                "invokables" => [
                    "UpCloo\\Renderer\\Json" => "UpCloo\\Renderer\\Json",
                    "UpCloo\\Renderer\\Jsonp" => "UpCloo\\Renderer\\Jsonp",
                    "Zend\\Stdlib\\Hydrator\\ClassMethods" => "Zend\\Stdlib\\Hydrator\\ClassMethods",
                ],
                "aliases" => [
                    "renderer" => "UpCloo\\Renderer\\Jsonp",
                    "hydrator" => "Zend\\Stdlib\\Hydrator\\ClassMethods",
                ]
            ],
            "listeners" => []
        ];
    }

    public function bootstrap()
    {
        $this->registerRouter();
        $this->registerServices();
        $this->registerListeners();

        $this->events()->attach("route", [$this, "prepareControllerToBeExecuted"]);

        $renderer = $this->services()->get("renderer");

        if ($this->isNotARenderer($renderer)) {
            throw new \InvalidArgumentException("The 'renderer' alias must implement Renderizable interface");
        }

        $this->events()->attach("renderer", [$renderer, "render"]);

        $this->events()->attach("send.response", [$this, "sendResponse"]);

        return $this;
    }

    public function sendResponse()
    {
        $this->response()->send();
    }

    public function prepareControllerToBeExecuted($event)
    {
        $request = $event->getParam('request');
        $match = $this->getRouter()->match($request);

        if ($match) {
            $controller = $match->getParam("controller");
            $action = $match->getParam("action");

            $callable = $this->resolveCallableWithServiceManager([$controller, $action]);

            if ($this->isHydratable($callable[0])) {
                $this->hydrateCallableWithBaseServices($callable[0]);
            }

            $this->events()->attach("execute", $callable);
        }

        return $match;
    }

    private function isHydratable($type) {
        return (is_object($type));
    }

    private function hydrateCallableWithBaseServices($object)
    {
        $data = [
            "request"        => $this->request(),
            "response"       => $this->response(),
            "eventManager"   => $this->events(),
            "serviceManager" => $this->services(),
        ];

        if ($this->hasValidHydrator()) {
            $hydrator = $this->services()->get("hydrator");
            $hydrator->hydrate($data, $object);
        }
    }

    private function hasValidHydrator()
    {
        if ($this->services()->get("hydrator") instanceof HydratorInterface) {
            return true;
        }

        return false;
    }

    private function isNotARenderer($renderer)
    {
        return (!($renderer instanceof Renderer\Renderizable));
    }

    private function registerRouter()
    {
        $this->router = TreeRouteStack::factory($this->conf["router"]);
    }

    private function registerServices()
    {
        $serviceManager = $this->services();

        $serviceConfig = new ServiceManagerConfig($this->conf["services"]);
        $serviceConfig->configureServiceManager($serviceManager);

        $this->services()->setService("Config", $this->conf);
    }

    private function registerListeners()
    {
        foreach ($this->conf["listeners"] as $eventName => $callables) {
            $this->registerCallbacks($eventName, $callables);
        }
    }

    private function registerCallbacks($eventName, $callables)
    {
        foreach ($callables as $callable) {
            $callable = $this->resolveCallableWithServiceManager($callable);
            $this->events()->attach($eventName, $callable);
        }
    }

    private function resolveCallableWithServiceManager($callable)
    {
        if (is_array($callable)) {
            if ($this->services()->has($callable[0])) {
                $callable[0] = $this->services()->get($callable[0]);
            }
        }

        return $callable;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->events = $eventManager;
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

    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function request()
    {
        if (!$this->request instanceof Request) {
            $this->request = new Request();
        }

        return $this->request;
    }

    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function response()
    {
        if (!($this->response instanceof Response)) {
            $this->response = new Response();
        }

        return $this->response;
    }

    public function run()
    {
        $this->bootstrap();

        $this->trigger("begin");

        try {
            $controllerExecution = $this->dispatchUserRequest();
        } catch (Exception\HaltException $e) {
            $controllerExecution = $this->trigger("halt");
        } catch (Exception\PageNotFoundException $e) {
            $this->response()->setStatusCode(Response::STATUS_CODE_404);
            $controllerExecution = $this->trigger("404");
        } catch (\Exception $e) {
            $this->response()->setStatusCode(Response::STATUS_CODE_500);
            $controllerExecution = $this->trigger("500", array("exception" => $e));
        }

        $this->trigger(
            "renderer",
            array(
                "data" => $controllerExecution,
                "request" => $this->request(),
                "response" => $this->response()
            )
        );

        $this->trigger("finish");
        $this->trigger("send.response");
    }

    private function dispatchUserRequest()
    {
        $request = $this->request();

        $eventCollection = $this->trigger("route", array("request" => $request));
        $routeMatch = $eventCollection->last();

        if ($this->isPageMissing($routeMatch)) {
            throw new Exception\PageNotFoundException("page not found");
        }

        $this->trigger(
            "pre.fetch",
            array(
                "request" => $this->request(),
                "response" => $this->response(),
                "routeMatch" => $routeMatch
            )
        );

        $this->response()->setStatusCode(Response::STATUS_CODE_200);
        $controllerExecution = $this->events()->trigger("execute", $routeMatch);

        return $controllerExecution;
    }

    private function isPageMissing($routeMatch)
    {
        return ($routeMatch == null);
    }
}
