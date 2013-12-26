<?php
namespace UpCloo\Test\Factory\Request;

use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Uri\Http as HttpUri;
use Zend\Stdlib\Parameters;

class PutRequest extends HttpRequest
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
            array_walk($params, function (&$item, $key) {
                $item = $key . '=' . $item;
            });
            $content = implode('&', $params);
            $request->setContent($content);
        }
        $this->setMethod(self::METHOD_PUT);
        $this->setQuery(new Parameters($query));
        $this->setPost(new Parameters($post));
        $this->setUri($uri);
    }
}
