<?php
namespace UpCloo\App\Config;

interface Mergeable
{
    public function prependConfig(array $config);
    public function appendConfig(array $config);
    public function merge();
}
