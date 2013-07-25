<?php

define("_BROWSER_URL_", "http://localhost:8081");

$loader = require __DIR__ . '/../vendor/autoload.php';

$loader->add("UpCloo", __DIR__ . '/unit');
$loader->add("UpCloo", __DIR__ . '/functional');
$loader->add("UpCloo", __DIR__ . '/support/src');
$loader->add("UpCloo", __DIR__ . '/../src');

