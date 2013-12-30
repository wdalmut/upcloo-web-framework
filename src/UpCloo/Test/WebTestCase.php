<?php
namespace UpCloo\Test;

use UpCloo\App;

class WebTestCase extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setApp($app, $disableRenderer = true)
    {
        if ($disableRenderer) {
            $app->appendConfig([
                "services" => [
                    "factories" => [
                        "response.stub" => function() {
                            $stub = $this->getMock("UpCloo\\Listener\\SendResponseListener");
                            $stub->expects($this->any())
                                ->method("sendResponse")
                                ->will($this->returnValue(true));

                            return $stub;
                        },
                    ],
                    "aliases" => [
                        "response.listener" => "response.stub",
                    ]
                ]
            ]);
        }

        $this->app = $app;
    }

    public function getApp()
    {
        return $this->app;
    }

    public function dispatch($url, $method = "GET", array $params = array())
    {
        $request = Factory\RequestFactory::createRequest($url, $method, $params);
        $this->getApp()->setRequest($request);

        $this->getApp()->run();

        return $this->getApp()->response();
    }
}

