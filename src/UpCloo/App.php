<?php
namespace UpCloo;

use Zend\ServiceManager\ServiceManager;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;

class App
{
    private $engine;
    private $bootstrap;

    public function __construct(App\Engine $engine, App\Boot $bootstrap)
    {
        $this->engine = $engine;
        $this->bootstrap = $bootstrap;
    }

    public function run()
    {
        $this->bootstrap->bootstrap();

        $this->engine->setServiceManager($this->bootstrap->services());
        $this->engine->setEventManager($this->bootstrap->events());

        return $this->engine->run();
    }
}
