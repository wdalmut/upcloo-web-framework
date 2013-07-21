<?php
namespace My\Controller;

use UpCloo\Controller\ServiceManager;
use UpCloo\Controller\Request;
use UpCloo\Controller\Action\Redirector;

class Example
{
    use ServiceManager, Redirector;

    public function method($event)
    {
        /*$this->redirect("http://walterdalmut.com", 302);*/

        $routeMatch = $event->getTarget();
        return array(
            "hello" => $routeMatch->getParam("param", false),
            "param2" => $routeMatch->getParam("param2", false),
            "service" => $this->get("example")
        );
    }
}
