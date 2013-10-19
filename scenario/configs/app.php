<?php
return array(
    "router" => array(
        "routes" => array(
            "home" => array(
                "type" => "Literal",
                "options" => array(
                    "route" => "/walter",
                    'defaults' => array(
                        'controller' => 'exampleController',
                        'action' => 'method'
                    )
                ),
                'may_terminate' => true,
                "child_routes" => array(
                    "say_hello" => array(
                        "type" => "Segment",
                        "options" => array(
                            "route" => "/:param[/:param2]"
                        )
                    )
                )
            )
        )
    ),
    "services" => array(
        "invokables" => array(
            "My\\Controller\\Example" => "My\\Controller\\Example",
            "UpCloo\\Renderer\\Jsonp" => "UpCloo\\Renderer\\Jsonp"
        ),
        "factories" => array(
            "example" => function(\Zend\ServiceManager\ServiceLocatorInterface $sl) {
                return "that-service";
            }
        ),
        "aliases" => array(
            "exampleController" => "My\\Controller\\Example",
            "renderer" => "UpCloo\\Renderer\\Jsonp"
        )
    ),
    "listeners" => array(
        "404" => array(
            array("My\\Controller\\Error", "error")
        )
    )
);

