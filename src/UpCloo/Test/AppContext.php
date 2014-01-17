<?php
namespace UpCloo\Test;

use Behat\Behat\Context\BehatContext;

use UpCloo\App,
    UpCloo\App\Engine,
    UpCloo\App\Boot,
    UpCloo\App\Config\ArrayProcessor,
    UpCloo\Test\Factory;

class AppContext extends BehatContext
{
    private $configs;

    public function __construct()
    {
        $this->configs = new ArrayProcessor();
        $this->disableRenderer();
    }

    public function disableRenderer()
    {
        $this->appendConfig([
            "listeners" => [
                "begin" => [
                    function($e) {
                        $e->getTarget()->events()->clearListeners("send.response");
                    }
                ],
            ]
        ]);
    }

    public function appendConfig(array $config)
    {
        $this->configs->appendConfig($config);
    }

    public function dispatch($path, $method, array $data = [])
    {
        $engine = new Engine();
        $request = Factory\RequestFactory::createRequest($path, $method, $data);
        $engine->setRequest($request);

        $app = new App($engine, new Boot($this->configs));

        $app->run();

        return $engine->response();
    }
}
