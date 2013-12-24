<?php
namespace UpCloo\Renderer;

use Zend\EventManager\Event;

interface Renderizable
{
    public function render(Event $event);
}
