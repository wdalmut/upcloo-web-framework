<?php
namespace UpCloo\Controller\Action;

use Zend\Uri\UriInterface;
use UpCloo\Controller\Response;
use UpCloo\Exception\HaltException;

trait Redirector
{
    use Response;

    public function redirect($uri, $status = 302)
    {
        if ($uri instanceof UriInterface) {
            $uri = $uri->toString();
        }

        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Location', $uri);
        $response->setStatusCode($status);

        throw new HaltException();
    }
}

