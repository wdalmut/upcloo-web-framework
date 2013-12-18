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
    private $serviceManager;

    use Hydrator\ControllerHydrator;

    public function __construct(array $configs)
    {
        $conf = [];
        foreach ($configs as $confFile) {
            $conf = array_replace_recursive($conf, $confFile);
        }
        $this->conf = $conf;
    }

    /**
     *Prepare the application
     *
     * @return UpCloo\App The application
     */
    public function bootstrap()
    {
        $this->registerServices();
        $this->registerListeners();

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
            }

            return $match;
        };

        $this->router = TreeRouteStack::factory($this->conf["router"]);
        $closure->bindTo($this);
        $this->events()->attach("route", $closure);

        if ($this->services()->has("renderer")) {
            $renderer = $this->services()->get("renderer", "UpCloo\\Renderer\\Jsonp");
            $this->events()->attach("renderer", array($renderer, "render"));
        }

        $this->events()->attach("send.response", function($event) {
            $this->response()->send();
        });

        return $this;
    }

    /**
     * Register services into the ServiceManager
     */
    private function registerServices()
    {
        if (!array_key_exists("services", $this->conf)) {
            $this->conf["services"] = array();
        }

        $services = $this->conf["services"];
        $config = new ServiceManagerConfig($services);
        $serviceManager = new ServiceManager($config);
        $this->setServiceManager($serviceManager);
        $this->services()->setService("Config", $this->conf);
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
            if (is_array($callable)) {
                if ($this->services()->has($callable[0])) {
                    $callable[0] = $this->services()->get($callable[0]);
                }
            }
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
            $this->registerServices();
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
            $this->request = new Request;
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
            $this->response = new Response;
        }
        return $this->response;
    }

    /**
     * Run the application
     */
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
        } catch (Exception\HaltException $e) {
            $controllerExecution = $this->events()->trigger("halt");
        } catch (Exception\PageNotFoundException $e) {
            $controllerExecution = $this->events()->trigger("404");
        } catch (\Exception $e) {
            $this->response()->setStatusCode(Response::STATUS_CODE_500);
            $controllerExecution = $this->events()->trigger("500", array("exception" => $e));
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
}
