<?php
namespace UpCloo\Test;

use Zend\EventManager\Event;
use Zend\Mvc\Router\RouteMatch;

trait ControllerTestUtils
{
    /**
     * @return Event The prepared event
     */
    public function getEventFromParams(array $params = [])
    {
        $routeMatch = new RouteMatch($params);

        $event = new Event();
        $event->setTarget($routeMatch);

        return $event;
    }
}
