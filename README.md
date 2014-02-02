# UpCloo MicroFramework with ZF2 components

This is just a simple microframework based on ZF2 components.

 * [Read The Docs](https://upcloo-web-framework.readthedocs.org/en/latest/)

## License

This project is released under MIT license.

##Getting started

In the `scenario` folder you can find not only an example but a typical starting
point...
You can use a project folder that you prefer but a good starting point is:

     - src
       - Your
         - Namespace
     - tests
       - Your
         - Namespace
     - web
     - configs

The entry point (web/index.php)

```
<?php
$loader = include __DIR__ . '/vendor/autoload.php';

$loader->add("Your", __DIR__ . '/../src');

$config = new UpCloo\App\Config\ArrayProcessor();
$config->appendConfig(include __DIR__ . '/../configs/app.php');

$engine = new UpCloo\App\Engine();
$boot = new UpCloo\App\Boot($config);

$app = new UpCloo\App($engine, $boot);
$app->run();

```

Here is a config (configs/app.php)

```
<?php
return array(
    "router" => array(
        "routes" => array(
            "home" => array(
                "type" => "Literal",
                "options" => array(
                    "route" => "/walter",
                    'defaults' => array(
                        'controller' => 'Your\\Controller\\Name',
                        'action' => 'hello'
                    )
                ),
                'may_terminate' => true,
            )
        )
    ),
    "services" => array(
        "invokables" => array(
            "Your\\Controller\\Name" => "Your\\Controller\\Name",
        ),
        "factories" => array(
            "example" => function(\Zend\ServiceManager\ServiceLocatorInterface $sl) {
                return "hello";
            }
        ),
    )
);
```

Start with a controller (src/Your/Controller/Name.php)

```
<?php
namespace Your\Controller;

use UpCloo\Controller\ServiceManager;

class Name
{
    use ServiceManager;

    public function hello()
    {
        $hello = $this->services()->get("example");
        return $hello . " " . "world";
    }
}
```

Start your web service

```
php -S localhost:8080 -t web/ web/index.php
```

Go to your page `-> localhost:8080/walter`

## Build Status

* Master branch
  * [![Build Status](https://secure.travis-ci.org/wdalmut/upcloo-web-framework.png?branch=master)](http://travis-ci.org/wdalmut/upcloo-web-framework?branch=master)
* Develop branch
  * [![Build Status](https://secure.travis-ci.org/wdalmut/upcloo-web-framework.png?branch=develop)](http://travis-ci.org/wdalmut/upcloo-web-framework?branch=develop)


