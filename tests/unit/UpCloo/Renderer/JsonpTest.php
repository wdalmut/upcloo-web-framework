<?php
namespace UpCloo\Renderer;

use Zend\EventManager\Event;
use Zend\Http\Request;
use Zend\Http\Response;

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
        $request = Request::fromString(<<<EOS
GET /a-path HTTP/1.1
    \r\n
HeaderField1: text/plain
\r\n\r\n
foo=bar&
EOS
        );
        $p = new \Zend\Stdlib\Parameters(array(
            'callback' => 'walter'
        ));
        $request->setQuery($p);

        $response = new Response();

        $data = new \Zend\EventManager\ResponseCollection();
        $data->push(array("walter" => "ciao"));
        $event->setParam("data", $data);
        $event->setParam("response", $response);
        $event->setParam("request", $request);

        $this->object->render($event);

        $this->assertEquals('walter({"walter":"ciao"})', $response->getContent());
    }

    public function testMissingJsonpFallbackToJson()
    {
         $event = new Event();
        $request = Request::fromString(<<<EOS
GET /a-path HTTP/1.1
    \r\n
HeaderField1: text/plain
\r\n\r\n
foo=bar&
EOS
        );

        $response = new Response();

        $data = new \Zend\EventManager\ResponseCollection();
        $data->push(array("walter" => "ciao"));
        $event->setParam("data", $data);
        $event->setParam("response", $response);
        $event->setParam("request", $request);

        $this->object->render($event);

        $this->assertJsonStringEqualsJsonString(
            json_encode(array("walter" => "ciao")), $response->getContent()
        );

    }
}
