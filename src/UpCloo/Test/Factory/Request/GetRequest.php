<?php
namespace UpCloo\Test\Factory\Request;

use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Uri\Http as HttpUri;
use Zend\Stdlib\Parameters;

class GetRequest extends HttpRequest
{
    public function __construct($url, $params)
    {
        parent::__construct();

        $query = $this->getQuery()->toArray();
        $uri = new HttpUri($url);
        $queryString = $uri->getQuery();
        if ($queryString) {
            parse_str($queryString, $query);
        }

        $query = array_merge($query, $params);

        $this->setMethod(self::METHOD_GET);
        $this->setQuery(new Parameters($query));
        $this->setPost(new Parameters(array()));
        $this->setUri($uri);
    }
}

