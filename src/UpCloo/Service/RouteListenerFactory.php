<?php
namespace UpCloo\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\Router\Http\TreeRouteStack;
use UpCloo\Listener\RouteListener;

class RouteListenerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $router = TreeRouteStack::factory($serviceLocator->get("Config")["router"]);

        $routeListener = new RouteListener($router);

        $hydrator = $serviceLocator->get("hydrator");
        $routeListener->setHydrator($hydrator);

        return $routeListener;
    }
}
