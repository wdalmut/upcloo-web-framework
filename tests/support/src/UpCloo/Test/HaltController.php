<?php
namespace UpCloo\Test;

use UpCloo\Exception\HaltException;

class Haltcontroller
{
    public function haltMe()
    {
        throw new HaltException("Stop it!");
    }

    public function genericException()
    {
        throw new \RuntimeException("A runtime exception");
    }
}

