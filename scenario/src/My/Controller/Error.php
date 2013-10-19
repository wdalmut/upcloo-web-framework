<?php
namespace My\Controller;

class Error
{
    public function error($e)
    {
        return array(
            'error' => 'Missing page'
        );
    }
}
