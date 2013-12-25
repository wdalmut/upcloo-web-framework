<?php
namespace UpCloo\Test\Factory\Request;

use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Uri\Http as HttpUri;
use Zend\Stdlib\Parameters;

class PostRequest extends HttpRequest
{
    public function __construct($url, array $params = array())
    {
        parent::__construct();

        $query = $this->getQuery()->toArray();
        $uri = new HttpUri($url);
        $queryString = $uri->getQuery();
        if ($queryString) {
            parse_str($queryString, $query);
        }

        $post = array();
        if (count($params) != 0) {
            $post = $params;
        }

        $this->setMethod(self::METHOD_POST);
        $this->setQuery(new Parameters($query));
        $this->setPost(new Parameters($post));
        $this->setUri($uri);
    }
}
