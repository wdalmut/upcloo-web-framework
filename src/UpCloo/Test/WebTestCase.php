<?php
namespace UpCloo\Test;

use UpCloo\App;

class WebTestCase extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setApp($app, $disableRenderer = true)
    {
        if ($disableRenderer) {
            $app->events()->attach("send.response", function($event) {
                $event->stopPropagation(true);
            }, 100);
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
    }
}

