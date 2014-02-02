<?php
namespace UpCloo\Listener\Renderer;

use Zend\EventManager\Event;

class Json
{
    public function render(Event $event)
    {
        $dataPack = $this->getDataPack($event);
        $response = $event->getTarget()->response();

        $response->getHeaders()->addHeaders(
            array(
                'Content-Type' => 'application/json'
            )
        );
        $response->setContent(json_encode($dataPack));
    }

    protected function getDataPack($event)
    {
        $dataPack = $event->getParam("data")->last();
        return $dataPack;
    }
}
