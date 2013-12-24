<?php
namespace UpCloo\Controller;

use Zend\Http\PhpEnvironment\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    private $object;

    public function setUp()
    {
        $this->object = $this->getObjectForTrait(__NAMESPACE__ . "\\Request");
    }

    public function testSimpleGetSet()
    {
        $request = new Request();
        $this->object->setRequest($request);
        $this->assertSame($request, $this->object->getRequest());
    }
}

