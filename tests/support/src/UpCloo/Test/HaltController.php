<?php
namespace UpCloo\Test;

use UpCloo\Exception\HaltException;

class HaltController
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

