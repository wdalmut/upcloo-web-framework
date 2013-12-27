<?php
namespace UpCloo;

use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config as ServiceManagerConfig;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;

class App
{
    private $conf;
    private $request;
    private $response;
    private $events;
    private $serviceManager;

    public function __construct(array $userConfigs)
    {
        $baseConfig = $this->getBaseConf();
        $this->conf = $this->mergeConfigs($baseConfig, $userConfigs);
    }

    private function mergeConfigs(array $conf, array $configs)
    {
        foreach ($configs as $confFile) {
            $conf = $this->mergeConfig($conf, $confFile);
        }

        return $conf;
    }

    public function appendConfig(array $userConf)
    {
        $this->conf = $this->mergeConfig($this->conf, $userConf);
    }

    private function mergeConfig($conf, $userConf)
    {
        return array_replace_recursive($conf, $userConf);
    }

    private function getBaseConf()
    {
        return [
            "router" => [],
            "services" => [
                "invokables" => [
                    "UpCloo\\Listener\\Renderer\\Json" => "UpCloo\\Listener\\Renderer\\Json",
                    "UpCloo\\Listener\\Renderer\\Jsonp" => "UpCloo\\Listener\\Renderer\\Jsonp",
                    "Zend\\Stdlib\\Hydrator\\ClassMethods" => "Zend\\Stdlib\\Hydrator\\ClassMethods",
                    "UpCloo\\Listener\\SendResponseListener" => "UpCloo\\Listener\\SendResponseListener",
                ],
                "factories" => [
                    "UpCloo\\Listener\\RouteListener" => "UpCloo\\Service\\RouteListenerFactory",
                ],
                "aliases" => [
                    "renderer.listener" => "UpCloo\\Listener\\Renderer\\Jsonp",
                    "route.listener" => "UpCloo\\Listener\\RouteListener",
                    "response.listener" => "UpCloo\\Listener\\SendResponseListener",
                    "hydrator" => "Zend\\Stdlib\\Hydrator\\ClassMethods",
                ]
            ],
            "listeners" => [
                "route" => [
                    ["route.listener", "prepareControllerToBeExecuted"]
                ],
                "pre.fetch" => [],
                "execute" => [],
                "renderer" => [
                    ["renderer.listener", "render"]
                ],
                "send.response" => [
                    ["response.listener", "sendResponse"]
                ],
            ]
        ];
    }

    public function bootstrap()
    {
        $this->registerServices();
        $this->registerListenersFromServices();

        return $this;
    }

    private function registerServices()
    {
        $serviceManager = $this->services();

        $serviceConfig = new ServiceManagerConfig($this->conf["services"]);
        $serviceConfig->configureServiceManager($serviceManager);

        $this->services()->setService("Config", $this->conf);
    }

    private function registerListenersFromServices()
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

    public function resolveCallableWithServiceManager($callable)
    {
        if (is_array($callable) && is_string($callable[0])) {
            if ($this->services()->has($callable[0])) {
                $callable[0] = $this->services()->get($callable[0]);
            }
        }

        return $callable;
    }

    public function event()
    {
        $event = new Event();
        $event->setTarget($this);
        return $event;
    }

    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->events = $eventManager;
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
        $this->trigger("send.response", ['response' => $this->response()]);
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
