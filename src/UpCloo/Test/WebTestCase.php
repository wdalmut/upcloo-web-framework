<?php
namespace UpCloo\Test;

use Zend\Http\Request as HttpRequest;
use Zend\Uri\Http as HttpUri;
use Zend\Stdlib\Parameters;

use UpCloo\App;

class WebTestCase extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setApp($app)
    {
        $this->app = $app;
    }

    public function getApp()
    {
        return $this->app;
    }

    public function dispatch($url, $method = "GET", array $params = array())
    {
        $request = new HttpRequest();

        $query       = $request->getQuery()->toArray();
        $post        = $request->getPost()->toArray();
        $uri = new HttpUri($url);
        $queryString = $uri->getQuery();
        if ($queryString) {
            parse_str($queryString, $query);
        }

        if ($method == HttpRequest::METHOD_POST) {
            if (count($params) != 0) {
                $post = $params;
            }
        } elseif ($method == HttpRequest::METHOD_GET) {
            $query = array_merge($query, $params);
        } elseif ($method == HttpRequest::METHOD_PUT) {
            if (count($params) != 0) {
                array_walk($params,
                    function (&$item, $key) { $item = $key . '=' . $item; }
                );
                $content = implode('&', $params);
                $request->setContent($content);
            }
        }

        $request->setMethod($method);
        $request->setQuery(new Parameters($query));
        $request->setPost(new Parameters($post));
        $request->setUri($uri);
        $this->getApp()->setRequest($request);

        $this->getApp()->run();
    }
}

