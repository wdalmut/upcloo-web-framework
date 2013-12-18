<?php
$loader = require __DIR__ . "/../../vendor/autoload.php";
$loader->add("My", __DIR__ . '/../src');

$conf = include __DIR__ . "/../configs/app.php";
$app = new \UpCloo\App([$conf]);
$app->run();

