Configuration
=============

Basically only the `router` section is a must. ::

    <?php
    return array(
        "router" => array(
            "routes" => array(
                "home" => array(
                    "type" => "literal",
                    "options" => array(
                        "route" => "/"
                        "defaults" => array(
                            "controller" => "Your\\NS\\Controller",
                            "action" => "myAction"
                        )
                    ),
                    "may_terminate" => true
                )
            )
        )
    );

The configuration is practically identical to ZF2 standard router configuration

Services
--------

In addition you can configure services: ::

    "services" => array(
        "invokables" => array(
            "My\\Controller\\Example" => "My\\Controller\\Example",
            "UpCloo\\Renderer\\Jsonp" => "UpCloo\\Renderer\\Jsonp",
        ),
        "factories" => array(
            "example" => function(\Zend\ServiceManager\ServiceLocatorInterface $sl) {
                return "that-service";
            }
        ),
        "aliases" => array(
            "exampleController" => "My\\Controller\\Example",
            "renderer" = "UpCloo\\Renderer\\Jsonp"
        )
    ),

The configuration is the same for ZF2 services

Listeners
---------

When you need to hook your code on events you can specify through the
`listeners` section: ::

    "listeners" => array(
        "404" => array(
            array("My\\Controller\\Error", "error")
        )
    )

Any `callable` hook is valid ::

     "listeners" => array(
        "404" => array(
            function() {
                // handle 404
            }
        )
    )

Overload your configuration
---------------------------

You can pass to your `App` different configurations. The framework merge those
together in order to obtain a single configuration.

This thing could be useful in order to obtain the right configuration for the
current environment.

For example see something like this: ::

    $app = new \UpCloo\App([
        include __DIR__ . '/../config/app.php',
        include __DIR__ . "/../config/app.{$env}.php",
    ]);

In this way the conf loaded from `app.php` is overwritten by the second configuration
and so on. You can load how many conf you need.


