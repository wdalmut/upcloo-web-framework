<?php
namespace UpCloo\Controller;

trait ServiceManager
{
    private $services;

    public function services()
    {
        return $this->services;
    }

    public function setServiceManager($serviceManager)
    {
        $this->services = $serviceManager;
    }

    public function get($key)
    {
        return $this->services()->get($key);
    }
}
