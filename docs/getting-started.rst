Getting Started with UpCloo Framework
=====================================

The base folder structure is: whatever you want...

I suggest something like this: ::

    - configs
    - src
      - Your
        - Project
          - Namespace
    - tests
      - Your
        - Project
          - Namespace
    - web
        - js
        - css
        - img

That is similar to a standard ZF2 module.

Your entry point
----------------

Into `web` direction you have to place your single entry point for your application
the `index.php` file. ::

    <?php
    // web/index.php

    $loader = require __DIR__ . "/../../vendor/autoload.php";
    $loader->add("My", __DIR__ . '/../src');

    $conf = include __DIR__ . "/../configs/app.php";
    $app = new \UpCloo\App([$conf]);
    $app->run();

As you can see the first to line uses the composer autoloader in order to
satisfy all your dependencies.

The configuration is loaded through the inclusion. Subsequently we create
the application and after that we `run` it.

Your base configuration
-----------------------

We want to create a `json` response at the `/` address. So, we need
the router and at least one controller. ::

    <?php
    // configs/app.php

    return array(
        "router" => array(
            "routes" => array(
                "home" => array(
                    "type" => "Literal",
                    "options" => array(
                        "route" => "/"
                        'defaults' => array(
                            'controller' => 'My\\NM\\Index',
                            'action' => 'aMethod'
                        )
                    ),
                    'may_terminate' => true,
                )
             )
          )
      ),
      "services" => array(
        "aliases" => array(
          "renderer" => "UpCloo\\Renderer\\Json"
        )
      )

Now the ActionController
------------------------

The controller class is simply a `POPO` definition with just the action
declared. ::

    <?php
    // src/My/NM/Index.php

    namespace My\\NM;

    class Index
    {
        public function aMethod()
        {
            return array(
                "hello" => "world"
            );
        }
    }

As you can see the method should return the value that the renderer will
serialize into the response.

Test you business logic
-----------------------

The goal of this structure is oriented to testing. For that reason the test
section is not optional! ::

    // tests/My/NM/IndexTest.php

    namespace My\\NM;

    class IndexTest extends \PHPUnit_Framework_TestCase
    {
        private $object;

        public function setUp()
        {
            $this->object = new Index();
        }

        public function testSimpleIndexMethod()
        {
            $oracleData = array(
                "hello" => "world"
            );

            $this->assertEquals($oracleData, $this->object->aMethod());
        }
    }

Obviously this is just a simple action! Before run tests correctly we need
to load classes and framework, for that use a bootstrap file. ::

    <?php
    // tests/bootstrap.php

    $loader = require __DIR__ . '/../vendor/autoload.php'; //composer load the framework

    $loader->add("My", __DIR__ . '/../src'); //Your source
    $loader->add("My", __DIR__); // tests folder

Now run your tests: ::

    phpunit --bootstrap tests/bootstrap.php tests/

The output should be something similar to this: ::

    PHPUnit 3.7.22 by Sebastian Bergmann.

    .

    Time: 1 seconds, Memory: 1.25Mb

    OK (1 tests, 1 assertions)

Now you can continue with more interesting things!

