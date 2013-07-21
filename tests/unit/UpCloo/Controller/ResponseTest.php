<?php
namespace UpCloo\Controller;

use Zend\Http\PhpEnvironment\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    private $object;

    public function setUp()
    {
        $this->object = $this->getObjectForTrait(__NAMESPACE__ . "\\Response");
    }

    public function testSimpleGetSet()
    {
        $response = new Response();
        $this->object->setResponse($response);
        $this->assertSame($response, $this->object->getResponse());
    }
}

