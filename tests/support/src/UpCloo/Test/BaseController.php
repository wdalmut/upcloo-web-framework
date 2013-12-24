<?php
namespace UpCloo\Test;

class BaseController
{
    public static $call = false;

    public $nonStaticProperty = false;

    public function indexAction()
    {
        return ["ok" => true];
    }

    public function nonStaticMethod()
    {
        $this->nonStaticProperty = true;
    }

    public static function aStaticListener()
    {
        self::$call = true;
    }
}

