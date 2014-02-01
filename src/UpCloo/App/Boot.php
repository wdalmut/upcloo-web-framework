<?php
namespace UpCloo\App;

use Zend\EventManager\EventManager;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config as ServiceManagerConfig;

class Boot
{
    private $conf;

    private $serviceManager;
    private $eventManager;

    public function __construct(Config\Mergeable $config)
    {
        $this->conf = $config;
    }

    public function setServiceManager($serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function services()
    {
        if (!$this->serviceManager) {
            $this->serviceManager = new ServiceManager();
        }

        return $this->serviceManager;
    }

    public function setEventManager($eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function events()
    {
        if (!$this->eventManager) {
            $this->eventManager = new EventManager();
        }

        return $this->eventManager;
    }

    public function bootstrap()
    {
        $conf = $this->conf;
        $conf->prependConfig($this->getBaseConfiguration());

        $this->registerServices($conf->merge());
        $this->registerListenersFromServices();

        return $this;
    }

    private function registerServices($conf)
    {
        $serviceManager = $this->services();

        $serviceConfig = new ServiceManagerConfig($conf["services"]);
        $serviceConfig->configureServiceManager($serviceManager);

        $serviceManager->setService("Config", $conf);
    }

    private function registerListenersFromServices()
    {
        foreach ($this->services()->get("Config")["listeners"] as $eventName => $callables) {
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

    private function getBaseConfiguration()
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
                    "route" => ["route.listener", "prepareControllerToBeExecuted"]
                ],
                "pre.fetch" => [],
                "execute" => [],
                "renderer" => [
                    "renderer" => ["renderer.listener", "render"]
                ],
                "send.response" => [
                    "response" => ["response.listener", "sendResponse"]
                ],
            ]
        ];
    }
}
