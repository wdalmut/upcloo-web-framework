<?php
namespace UpCloo\App\Config;

class ArrayProcessor implements Mergeable
{
    private $configs;

    public function __construct()
    {
        $this->configs = [];
    }

    public function appendConfig(array $config)
    {
        $this->configs[] = $config;
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

