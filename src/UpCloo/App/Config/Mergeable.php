<?php
namespace UpCloo\App\Config;

interface Mergeable
{
    /**
     * @return array The configured single array
     */
    public function merge();
}
