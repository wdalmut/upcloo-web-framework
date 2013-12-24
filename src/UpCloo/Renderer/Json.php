<?php
namespace UpCloo\Renderer;

use Zend\EventManager\Event;

class Json implements Renderizable
{
    public function render(Event $event)
    {
        $dataPack = $this->getDataPack($event);
        $response = $event->getParam("response");

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
