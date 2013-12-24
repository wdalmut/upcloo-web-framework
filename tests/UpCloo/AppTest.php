<?php
namespace UpCloo;

class AppTest extends Test\WebTestCase
{
    private $app;

    public function setUp()
    {
        $conf = array(
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
                    "UpCloo\\Renderer\\Json" => "UpCloo\\Renderer\\Json",
                ),
                "aliases" => array(
                    "renderer" => "UpCloo\\Renderer\\Json",
                )
            )
        );
        $app = new App([$conf]);
        $this->setApp($app);
    }

    public function testRouteEventIsFired()
    {
        $routeIsFired = false;
        $this->getApp()->events()->attach("route", function() use (&$routeIsFired) {
            $routeIsFired = true;
        });
        $this->dispatch("/walter");

        $this->assertTrue($routeIsFired);
    }

    public function testRequestedRouteAreCorrectlyParsed()
    {
        $event = null;
        $this->getApp()->events()->attach("execute", function($e) use (&$event) {
            $event = $e;
            $e->stopPropagation(true);
        }, 100);

        $this->dispatch("/walter");

        $this->assertInstanceOf("Zend\\EventManager\\Event", $event);
        $this->assertInstanceOf("Zend\Mvc\Router\Http\RouteMatch", $event->getTarget());

        $routeMatch = $event->getTarget();
        $this->assertEquals("UpCloo\\Test\\BaseController", $routeMatch->getParam("controller"));
        $this->assertEquals("indexAction", $routeMatch->getParam("action"));
    }

    public function testMissingPage()
    {
        $this->dispatch("/missing-page");

        $response = $this->getApp()->response();

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testAppWorksAlsoWithEmptyConf()
    {
        $app = new App([]);
        $this->setApp($app);

        $this->dispatch("/a-page");
        $this->assertEquals(404, $app->response()->getStatusCode());
    }

    public function testRendererEventIsFired()
    {
        $rendererIsFired = false;
        $this->getApp()->events()->attach("renderer", function() use (&$rendererIsFired) {
            $rendererIsFired = true;
        });
        $this->dispatch("/walter");

        $this->assertTrue($rendererIsFired);
    }

    public function testListenersAreAttachedToInternalEvents()
    {
        $attached = false;
        $app = new App([[
            "listeners" => [
                "begin" => [
                    function () use (&$attached) {
                        $attached = true;
                    }
                ]
            ]
        ]]);
        $this->setApp($app);

        $this->dispatch("/a-page");

        $this->assertTrue($attached);
    }

    public function testAttachAStaticListener()
    {
        $app = new App([[
            "listeners" => [
                "404" => [
                    ["UpCloo\\Test\\BaseController", "aStaticListener"]
                ]
            ]
        ]]);
        $this->setApp($app);
        $this->dispatch("/missing-page");

        $this->assertTrue(Test\BaseController::$call);
    }

    public function testAttachServicesToListeners()
    {
        $app = new App([[
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
        ]]);
        $this->setApp($app);

        $this->dispatch("/a-page");

        $baseController = $app->services()->get("UpCloo\\Test\\BaseController");

        $this->assertTrue($baseController->nonStaticProperty);
    }

    public function testHaltEventIsFired()
    {
        $app = new App([[
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
            ]
        ]]);
        $this->setApp($app);

        $isFired = false;
        $app->events()->attach("halt", function($e) use (&$isFired) {
            $isFired = true;
        });

        $this->dispatch("/halt");

        $this->assertTrue($isFired);
        $this->assertEquals(200, $app->response()->getStatusCode());
    }

    public function testApplicationErrorEventIsFired()
    {
        $app = new App([[
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
        ]]);
        $this->setApp($app);

        $isFired = false;
        $app->events()->attach("500", function($e) use (&$isFired) {
            $isFired = true;
        });

        $this->dispatch("/halt");

        $this->assertTrue($isFired);
        $this->assertEquals(500, $app->response()->getStatusCode());

    }

    public function testGetEmptyServiceManagerOnMissingConfiguration()
    {
        $app = new App([]);

        $this->assertInstanceOf("Zend\\ServiceManager\\ServiceManager", $app->services());
    }

    public function testResponseIsSentToBrowser()
    {
        $app = new App([]);
        $this->setApp($app, false);

        $responseStub = $this->getMock("Zend\\Http\\PhpEnvironment\\Response");
        $responseStub->expects($this->once())
            ->method("send")
            ->will($this->returnValue(true));
        $app->setResponse($responseStub);

        $this->dispatch("/a-page");
    }

}
