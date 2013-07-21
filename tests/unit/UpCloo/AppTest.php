<?php
namespace UpCloo;
use Zend\Http\Request;
use Zend\Mvc\Router\Http\TreeRouteStack;

class AppTest extends \PHPUnit_Framework_TestCase
{
    private $object;

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
                                'controller' => 'Application\Controller\IndexController',
                                'action' => 'index',
                            )
                        ),
                        'may_terminate' => true
                    )
                )
            )
        );
        $this->object = new App($conf);
        $this->object->bootstrap();
    }

    public function testRoutes()
    {
        $router = $this->object->getRouter();

        $this->assertInstanceOf("\\Zend\\Mvc\\Router\\Http\\TreeRouteStack", $router);
        $request = Request::fromString(<<<EOS
POST /walter HTTP/1.1
    \r\n
HeaderField1: header-field-value1
HeaderField2: header-field-value2
\r\n\r\n
foo=bar&
EOS
        );

        $match = $router->match($request);
        $this->assertNotNull($match);

        $params = $match->getParams();
        $this->assertEquals("Application\\Controller\\IndexController", $params["controller"]);
        $this->assertEquals("index", $params["action"]);
    }
}
