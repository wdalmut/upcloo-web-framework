<?php
namespace UpCloo;

use Zend\EventManager\EventManager;
use Zend\ServiceManager\ServiceManager;

class AppTest extends Test\WebTestCase
{
    private $app;

    public function setUp()
    {
        $this->appendConfig($this->getAppConf());
    }

    private function getAppConf()
    {
        return [
            "router" => [
                "routes" => [
                    "home" => [
                        "type" => "Literal",
                        "options" => [
                            "route" => "/walter",
                            'defaults' => [
                                'controller' => 'UpCloo\\Test\\BaseController',
                                'action' => 'indexAction',
                            ]
                        ],
                        'may_terminate' => true
                    ]
                ]
            ],
            "services" => [
                "invokables" => [
                    "UpCloo\\Test\\BaseController" => "UpCloo\\Test\\BaseController",
                ],
            ]
        ];
    }

    public function testAppFlowWorks()
    {
        $response = $this->dispatch("/walter");
        $this->assertJsonStringEqualsJsonString(json_encode(["ok" => true]), $response->getContent());
    }

    public function testAppFlowWorksWithoutHydrator()
    {
        $this->appendConfig([
            "services" => [
                "factories" => [
                    "fakeHydrator" => function() { return false; },
                ],
                "aliases" => [
                    "hydrator" => "fakeHydrator"
                ]
            ]
        ]);

        $response = $this->dispatch("/walter");
        $this->assertJsonStringEqualsJsonString(json_encode(["ok" => true]), $response->getContent());
    }

    public function testRequestedRouteAreCorrectlyParsed()
    {
        $event = null;
        $this->appendConfig([
            "listeners" => [
                "execute" => [
                    function($e) use (&$event) {
                        $event = $e;
                        $e->stopPropagation(true);
                    },
                ]
            ]
        ]);

        $this->dispatch("/walter");

        $this->assertInstanceOf("Zend\\EventManager\\Event", $event);
        $this->assertInstanceOf("Zend\\Mvc\\Router\\Http\\RouteMatch", $event->getTarget());

        $routeMatch = $event->getTarget();
        $this->assertEquals("UpCloo\\Test\\BaseController", $routeMatch->getParam("controller"));
        $this->assertEquals("indexAction", $routeMatch->getParam("action"));
    }

    public function testMissingPageForce404ErrorHeader()
    {
        $response = $this->dispatch("/missing-page");

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testAttachAStaticListener()
    {
        $this->appendConfig([
            "listeners" => [
                "404" => [
                    ["UpCloo\\Test\\BaseController", "aStaticListener"]
                ]
            ]
        ]);
        $this->dispatch("/missing-page");

        $this->assertTrue(Test\BaseController::$call);
    }
}
