<?php
namespace UpCloo\Renderer;

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

        $event->setParam("dataPack", array("walter" => "ciao"));
        $event->setParam("response", $response);

        $this->object->render($event);
        $this->assertJsonStringEqualsJsonString(
            json_encode(array("walter" => "ciao")), $response->getContent()
        );
    }
}
