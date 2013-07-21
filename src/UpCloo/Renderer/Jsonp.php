<?php
namespace UpCloo\Renderer;

use Zend\Http\Exception\InvalidArgumentException;

class Jsonp extends Json
{
    public function render($event)
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
