<?php
namespace UpCloo\Listener;

class SendResponseListener
{
    public function sendResponse($event)
    {
        $event->getParams()["response"]->send();
    }
}
