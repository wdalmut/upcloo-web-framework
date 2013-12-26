<?php
namespace UpCloo\Test\Factory\Request;

class GetRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testGetWithQuery()
    {
        $request = new GetRequest("/?w=t", array());

        $this->assertArrayHasKey("w", $request->getQuery()->toArray());
        $this->assertContains("t", $request->getQuery()->toArray());
    }

    public function testGetWithParams()
    {
        $request = new GetRequest("/", array('t' => 'w'));

        $this->assertArrayHasKey("t", $request->getQuery()->toArray());
        $this->assertContains("w", $request->getQuery()->toArray());
    }
}
