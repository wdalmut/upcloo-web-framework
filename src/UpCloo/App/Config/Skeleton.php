<?php
namespace UpCloo\App\Config;

abstract class Skeleton
{
    public static final function getBaseConfig()
    {
        return [
            "router" => [],
            "services" => [
                "invokables" => [
                    "UpCloo\\Listener\\Renderer\\Json" => "UpCloo\\Listener\\Renderer\\Json",
                    "UpCloo\\Listener\\Renderer\\Jsonp" => "UpCloo\\Listener\\Renderer\\Jsonp",
                    "Zend\\Stdlib\\Hydrator\\ClassMethods" => "Zend\\Stdlib\\Hydrator\\ClassMethods",
                    "UpCloo\\Listener\\SendResponseListener" => "UpCloo\\Listener\\SendResponseListener",
                ],
                "factories" => [
                    "UpCloo\\Listener\\RouteListener" => "UpCloo\\Service\\RouteListenerFactory",
                ],
                "aliases" => [
                    "renderer.listener" => "UpCloo\\Listener\\Renderer\\Jsonp",
                    "route.listener" => "UpCloo\\Listener\\RouteListener",
                    "response.listener" => "UpCloo\\Listener\\SendResponseListener",
                    "hydrator" => "Zend\\Stdlib\\Hydrator\\ClassMethods",
                ]
            ],
            "listeners" => [
                "route" => [
                    ["route.listener", "prepareControllerToBeExecuted"]
                ],
                "pre.fetch" => [],
                "execute" => [],
                "renderer" => [
                    ["renderer.listener", "render"]
                ],
                "send.response" => [
                    ["response.listener", "sendResponse"]
                ],
            ]
        ];
    }
}
