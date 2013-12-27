<?php
namespace UpCloo\Listener\Renderer;

use Zend\Http\Exception\InvalidArgumentException;

use Zend\EventManager\Event;

class Jsonp extends Json
{
    public function render(Event $event)
    {
        parent::render($event);

        $callback = $event->getParam("request")->getQuery("callback", false);
        if (!$callback || trim($callback) == '') {
            return;
        }

        $dataPack = $event->getParam("response")->getContent();
        $event->getParam("response")->setContent(sprintf("%s(%s)", $callback, $dataPack));
    }
}
