<?php
use UpCloo\Test;

class ControllerTestUtilsTest extends \PHPUnit_Framework_TestCase
{
    private $object;

    public function setUp()
    {
        $this->object = $this->getObjectForTrait("UpCloo\\Test\\ControllerTestUtils");
    }

    public function testGetEvent()
    {
        $event = $this->object->getEventFromParams(["p" => "t"]);

        $this->assertInstanceOf("Zend\\EventManager\\Event", $event);
        $this->assertInstanceOf("Zend\\Mvc\\Router\\RouteMatch", $event->getTarget());
    }
}
