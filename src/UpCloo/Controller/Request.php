<?php
namespace UpCloo\Controller;

trait Request
{
    private $request;

    public function getRequest()
    {
        return $this->request;
    }

    public function setRequest($request)
    {
        $this->request = $request;
    }
}
