<?php
namespace UpCloo\Listener\Renderer;

use Zend\EventManager\Event;
use Zend\Http\PhpEnvironment\Response;

class JsonTest extends \PHPUnit_Framework_TestCase
{
    private $object;

    public function setUp()
    {
        $this->object = new Json();
    }

    public function testJsonResponse()
    {
        $event = new Event();
        $response = new Response();

        $data = new \Zend\EventManager\ResponseCollection();
        $data->push(array("walter" => "ciao"));

        $event->setParam("data", $data);
        $event->setParam("response", $response);

        $this->object->render($event);
        $this->assertJsonStringEqualsJsonString(
            json_encode(array("walter" => "ciao")), $response->getContent()
        );
    }
}
