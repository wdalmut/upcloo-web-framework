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


