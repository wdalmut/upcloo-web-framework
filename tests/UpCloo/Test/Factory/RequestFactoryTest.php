<?php
namespace UpCloo\Test\Factory;

class RequestFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetRequest()
    {
        $request = RequestFactory::createRequest("/", "GET", array());

        $this->assertInstanceOf("UpCloo\\Test\\Factory\\Request\\GetRequest", $request);
    }

    public function testPostRequest()
    {
        $request = RequestFactory::createRequest("/", "POST", array());

        $this->assertInstanceOf("UpCloo\\Test\\Factory\\Request\\PostRequest", $request);
    }

    public function testPutRequest()
    {
        $request = RequestFactory::createRequest("/", "PUT", array());

        $this->assertInstanceOf("UpCloo\\Test\\Factory\\Request\\PutRequest", $request);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMissingType()
    {
        $request = RequestFactory::createRequest("/", "PSEUDO_PUT", array());
    }
}
