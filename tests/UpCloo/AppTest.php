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
        return array(
            "router" => array(
                "routes" => array(
                    "home" => array(
                        "type" => "Literal",
                        "options" => array(
                            "route" => "/walter",
                            'defaults' => array(
                                'controller' => 'UpCloo\\Test\\BaseController',
                                'action' => 'indexAction',
                            )
                        ),
                        'may_terminate' => true
                    )
                )
            ),
            "services" => array(
                "invokables" => array(
                    "UpCloo\\Test\\BaseController" => "UpCloo\\Test\\BaseController",
                ),
            )
        );
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

    public function testRouteEventIsFired()
    {
        $routeIsFired = false;
        $this->appendConfig([
            "listeners" => [
                "route" => [
                    function() use (&$routeIsFired) {
                        $routeIsFired = true;
                    },
                ]
            ]
        ]);
        $this->dispatch("/walter");

        $this->assertTrue($routeIsFired);
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
        $this->assertInstanceOf("Zend\Mvc\Router\Http\RouteMatch", $event->getTarget());

        $routeMatch = $event->getTarget();
        $this->assertEquals("UpCloo\\Test\\BaseController", $routeMatch->getParam("controller"));
        $this->assertEquals("indexAction", $routeMatch->getParam("action"));
    }

    public function testMissingPage()
    {
        $response = $this->dispatch("/missing-page");

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testAppWorksAlsoWithEmptyConf()
    {
        $response = $this->dispatch("/a-page");
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testRendererEventIsFired()
    {
        $rendererIsFired = false;
        $this->appendConfig([
            "listeners" => [
                "renderer" => [
                    function() use (&$rendererIsFired) {
                        $rendererIsFired = true;
                    }
                ]
            ]
        ]);

        $this->dispatch("/walter");

        $this->assertTrue($rendererIsFired);
    }

    public function testListenersAreAttachedToInternalEvents()
    {
        $attached = false;
        $this->appendConfig([
            "listeners" => [
                "begin" => [
                    function () use (&$attached) {
                        $attached = true;
                    }
                ]
            ]
        ]);

        $this->dispatch("/a-page");

        $this->assertTrue($attached);
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

    public function testAttachServicesToListeners()
    {
        $this->markTestSkipped("Not in the right place");
        $this->appendConfig([
            "services" => [
                "invokables" => [
                    "UpCloo\\Test\\BaseController" => "UpCloo\\Test\\BaseController"
                ]
            ],
            "listeners" => [
                "404" => [
                    ["UpCloo\\Test\\BaseController", "nonStaticMethod"]
                ]
            ]
        ]);
        $this->dispatch("/a-page");

        $baseController = $app->services()->get("UpCloo\\Test\\BaseController");

        $this->assertTrue($baseController->nonStaticProperty);
    }

    public function testHaltEventIsFired()
    {
        $this->appendConfig([
            "router" => [
                "routes" => [
                    "elb" => [
                        "type" => "Literal",
                        "options" => [
                            "route" => "/halt",
                            "defaults" => [
                                "controller" => "UpCloo\\Test\\HaltController",
                                "action" => "haltMe",
                            ]
                        ],
                        "may_terminate" => true,
                    ],
                ]
            ],
            "services" => [
                "invokables" => [
                    "UpCloo\\Test\\HaltController" => "UpCloo\\Test\\HaltController",
                ]
            ]
        ]);

        $isFired = false;
        $this->appendConfig([
            "listeners" => [
                "halt" => [
                    function($e) use (&$isFired) {
                        $isFired = true;
                    }
                ]
            ]
        ]);

        $response = $this->dispatch("/halt");

        $this->assertTrue($isFired);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testApplicationErrorEventIsFired()
    {
        $this->appendConfig([
            "router" => [
                "routes" => [
                    "elb" => [
                        "type" => "Literal",
                        "options" => [
                            "route" => "/halt",
                            "defaults" => [
                                "controller" => "UpCloo\\Test\\HaltController",
                                "action" => "genericException",
                            ]
                        ],
                        "may_terminate" => true,
                    ],
                ]
            ]
        ]);

        $isFired = false;
        $this->appendConfig([
            "listeners" => [
                "500" => [
                    function($e) use (&$isFired) {
                        $isFired = true;
                    }
                ]
            ]
        ]);

        $response = $this->dispatch("/halt");

        $this->assertTrue($isFired);
        $this->assertEquals(500, $response->getStatusCode());

    }

    public function testResponseIsSentToBrowser()
    {
        $this->markTestSkipped("Not useful now...");
        $app = new App([]);
        $this->setApp($app, false);

        $responseMock = $this->getMock("Zend\\Http\\PhpEnvironment\\Response", ["send"]);
        $responseMock->expects($this->once())
            ->method("send")
            ->will($this->returnValue(true));
        $app->setResponse($responseMock);

        $this->dispatch("/a-page");
    }

    public function testGetTheDefaultRequest()
    {
        $this->markTestSkipped("not in the right place");
        $app = new App([]);
        $this->assertInstanceOf("Zend\\Http\\PhpEnvironment\\Request", $app->request());
    }
}

