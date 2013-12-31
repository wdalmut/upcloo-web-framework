<?php
namespace UpCloo\App\Config;

class ArrayProcessor implements Mergeable
{
    private $configs;

    public function __construct()
    {
        $this->configs = [];
    }

    public function prependConfig(array $config)
    {
        array_unshift($this->configs, $config);
    }

    public function appendConfig(array $config)
    {
        array_push($this->configs, $config);
    }

    public function merge()
    {
        $resultConf = [];
        foreach ($this->configs as $confFile) {
            $resultConf = array_replace_recursive($resultConf, $confFile);
        }
        return $resultConf;
    }
}

