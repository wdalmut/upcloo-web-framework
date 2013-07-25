<?php
namespace UpCloo\Test;

use UpCloo\Controller\Request;
use UpCloo\Controller\Response;
use UpCloo\Controller\ServiceManager;
use UpCloo\Controller\EventManager;

class AllTraitsController
{
    use Request, Response, ServiceManager, EventManager;
}

