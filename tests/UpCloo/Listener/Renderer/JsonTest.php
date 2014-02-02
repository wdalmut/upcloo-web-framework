<?php
namespace UpCloo\Listener\Renderer;

use UpCloo\App\Engine;

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

        $app = new Engine();
        $app->setResponse($response);

        $data = new \Zend\EventManager\ResponseCollection();
        $data->push(["walter" => "ciao"]);

        $event->setTarget($app);
        $event->setParam("data", $data);

        $this->object->render($event);
        $this->assertJsonStringEqualsJsonString(
            json_encode(["walter" => "ciao"]), $response->getContent()
        );
    }
}
