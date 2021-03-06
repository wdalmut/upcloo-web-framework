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
        $configs = $this->getConfigs();
        $configs->appendConfig($config);

        return $this;
    }

    private function disableRenderer()
    {
        $this->appendConfig([
            "listeners" => [
                "finish" => [
                    "response" => function() {}
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

    public function getConfigs()
    {
        if (!$this->configs) {
            $this->configs = new ArrayProcessor();
        }

        return $this->configs;
    }
}
