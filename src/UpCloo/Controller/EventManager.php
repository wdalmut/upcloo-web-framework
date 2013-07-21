<?php
namespace UpCloo\Controller;

trait EventManager
{
    private $events;

    public function events()
    {
        return $this->events;
    }

    public function setEventManager($eventManager)
    {
        $this->events = $eventManager;
    }
}
