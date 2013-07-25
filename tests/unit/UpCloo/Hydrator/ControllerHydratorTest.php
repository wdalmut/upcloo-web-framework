<?php
namespace UpCloo\Hydrator;

use UpCloo\App;
use UpCloo\Test\AllTraitsController;
use Zend\ServiceManager\ServiceManager;
use Zend\EventManager\EventManager;

class ControllerHydratorTest extends \PHPUnit_Framework_TestCase
{
    private $object;

    public function setUp()
    {
        $this->object = $this->getObjectForTrait(__NAMESPACE__ . '\\ControllerHydrator');
    }

    public function testHydrateAll()
    {
        $controller = new AllTraitsController;

        $app = new App(array());

        $sm = new ServiceManager();
        $app->setServiceManager($sm);

        $em = new EventManager();
        $app->setEventManager($em);

        $request = $app->request();
        $response = $app->response();

        $this->object->hydrate($app, $controller);

        $this->assertSame($sm, $controller->services());
        $this->assertSame($response, $controller->getResponse());
        $this->assertSame($request, $controller->getRequest());
        $this->assertSame($em, $controller->events());
    }
}
