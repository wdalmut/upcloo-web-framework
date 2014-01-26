<?php
namespace UpCloo\Test;

use UpCloo\App;
use UpCloo\App\Engine;
use UpCloo\App\Boot;
use UpCloo\App\Config\ArrayProcessor;

trait WebTestUtils
{
    private $configs;

    public function appendConfig(array $config)
    {
        if (!$this->configs) {
            $this->configs = new ArrayProcessor();
        }

        $this->configs->appendConfig($config);
    }

    private function disableRenderer()
    {
        $this->appendConfig([
            "services" => [
                "invokables" => [
                    "response.stub" => "UpCloo\\Test\\Listener\\SendResponseListener"
                ],
                "aliases" => [
                    "response.listener" => "response.stub",
                ]
            ]
        ]);
    }

    public function dispatch($url, $method = "GET", array $params = array())
    {
        $this->disableRenderer();

        $engine = new Engine();
        $request = Factory\RequestFactory::createRequest($url, $method, $params);
        $engine->setRequest($request);

        $app = new App($engine, new Boot($this->configs));

        $app->run();

        return $engine->response();
    }
}
