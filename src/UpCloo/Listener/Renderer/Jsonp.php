<?php
namespace UpCloo\Listener\Renderer;

use Zend\Http\Exception\InvalidArgumentException;

use Zend\EventManager\Event;

class Jsonp extends Json
{
    public function render(Event $event)
    {
        parent::render($event);

        $request = $event->getTarget()->request();
        $callback = $request->getQuery("callback", false);
        if (!$callback || trim($callback) == '') {
            return;
        }

        $response = $event->getTarget()->response();
        $dataPack = $response->getContent();
        $response->setContent(sprintf("%s(%s)", $callback, $dataPack));
    }
}
