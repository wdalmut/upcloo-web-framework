<?php
namespace UpCloo\Listener;

use Zend\Mvc\Router\RouteInterface as Router;
use Zend\Stdlib\Hydrator\HydratorInterface as Hydrator;

class RouteListener
{
    private $router;
    private $hydrator;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function setHydrator($hydrator)
    {
        $this->hydrator = $hydrator;
    }

    public function getHydrator()
    {
        return $this->hydrator;
    }

    public function prepareControllerToBeExecuted($event)
    {
        $target = $event->getTarget();
        $request = $event->getParam('request');
        $match = $this->getRouter()->match($request);

        if ($match) {
            $controller = $match->getParam("controller");
            $action = $match->getParam("action");

            $controller = $target->services()->get($controller, $controller);
            $callable = [$controller, $action];

            if ($this->isHydratable($callable[0])) {
                if ($this->hasValidHydrator()) {
                    $hydrator = $this->getHydrator();

                    $data = [
                        "request" => $target->request(),
                        "response" => $target->response(),
                        "eventManager" => $target->events(),
                        "serviceManager" => $target->services(),
                    ];
                    $hydrator->hydrate($data, $callable[0]);
                }
            }

            $target->events()->attach("execute", $callable);
        }

        return $match;
    }

    private function isHydratable($type) {
        return (is_object($type));
    }

    private function hasValidHydrator()
    {
        if ($this->getHydrator() instanceof Hydrator) {
            return true;
        }

        return false;
    }
}
