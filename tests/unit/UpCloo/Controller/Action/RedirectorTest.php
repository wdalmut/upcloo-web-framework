<?php
namespace UpCloo\Controller\Action;

use UpCloo\Exception\HaltException;
use Zend\Http\PhpEnvironment\Response;

class RedirectorTest extends \PHPUnit_Framework_TestCase
{
    private $object;

    public function setUp()
    {
        $this->object = $this->getObjectForTrait(__NAMESPACE__ . "\\Redirector");
        $this->object->setResponse(new Response());
    }

    /**
     * @expectedException UpCloo\Exception\HaltException
     */
    public function testHaltException()
    {
        $this->object->redirect("/");
    }

    public function testRedirectPermanent()
    {
        try {
            $this->object->redirect("/ciao", 301);
        } catch (HaltException $e) {
            $response = $this->object->getResponse();
            $this->assertEquals("301", $response->getStatusCode());
        }
    }
}

