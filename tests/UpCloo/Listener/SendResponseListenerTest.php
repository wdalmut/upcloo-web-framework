<?php
namespace UpCloo\Listener;

use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Request;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Stdlib\Hydrator\ObjectProperty;
use Zend\Mvc\Router\RouteMatch;
use UpCloo\App\Engine;

class SendResponseListenerTest extends \PHPUnit_Framework_TestCase
{
    private $responseListener;

    public function setUp()
    {
        $this->responseListener = new SendResponseListener();
    }

    public function testResponseIsSentToBrowser()
    {
        $responseMock = $this->getMock("Zend\\Http\\PhpEnvironment\\Response", ["send"]);
        $responseMock->expects($this->once())
            ->method("send")
            ->will($this->returnValue(true));

        $event = new Event();
        $event->setTarget(null);
        $event->setParams(["response" => $responseMock]);

        $this->responseListener->sendResponse($event);
    }
}
