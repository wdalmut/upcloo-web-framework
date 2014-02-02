<?php
namespace UpCloo\Listener\Renderer;

use UpCloo\App\Engine;

use Zend\EventManager\Event;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;

class JsonpTest extends \PHPUnit_Framework_TestCase
{
    private $object;

    public function setUp()
    {
        $this->object = new Jsonp();
    }

    public function testJsonpResponse()
    {
        $event = new Event();

        $request = new Request("/path");
        $params = new \Zend\Stdlib\Parameters(array(
            'callback' => 'walter'
        ));
        $request->setQuery($params);

        $response = new Response();

        $data = new \Zend\EventManager\ResponseCollection();
        $data->push(["walter" => "ciao"]);

        $engine = new Engine();
        $engine->setRequest($request);
        $engine->setResponse($response);

        $event->setTarget($engine);
        $event->setParam("data", $data);

        $this->object->render($event);

        $this->assertEquals('walter({"walter":"ciao"})', $response->getContent());
    }

    public function testMissingJsonpFallbackToJson()
    {
        $event = new Event();

        $request = new Request("/path");
        $response = new Response();

        $engine = new Engine();
        $engine->setRequest($request);
        $engine->setResponse($response);

        $data = new \Zend\EventManager\ResponseCollection();
        $data->push(["walter" => "ciao"]);

        $event->setTarget($engine);
        $event->setParam("data", $data);

        $this->object->render($event);

        $this->assertJsonStringEqualsJsonString(
            json_encode(["walter" => "ciao"]), $response->getContent()
        );

    }
}
