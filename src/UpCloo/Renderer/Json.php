<?php
namespace UpCloo\Renderer;

class Json
{
    public function render($event)
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
