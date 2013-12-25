<?php
namespace UpCloo\Test\Factory;

class RequestFactory
{
    const GET = "GET";
    const POST = "POST";
    const PUT = "PUT";

    public static function createRequest($path, $method="GET", $params)
    {
        switch (trim(strtoupper($method))) {
        case self::GET:
            return new Request\GetRequest($path, $params);
            break;
        case self::POST:
            return new Request\PostRequest($path, $params);
            break;
        case self::PUT:
            return new Request\PutRequest($path, $params);
            break;
        default:
            throw new \InvalidArgumentException("Invalid method request");
        }
    }
}
