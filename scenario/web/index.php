<?php
$loader = require __DIR__ . "/../../vendor/autoload.php";
$loader->add("My", __DIR__ . '/../src');

$conf = include __DIR__ . "/../configs/app.php";

$config = new UpCloo\App\Config\ArrayProcessor();
$config->appendConfig($conf);

$engine = new UpCloo\App\Engine();
$boot = new UpCloo\App\Boot($config);

$app = new UpCloo\App($engine, $boot);
$app->run();

