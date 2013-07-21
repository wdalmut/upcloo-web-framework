# UpCloo MicroFramework with ZF2 components

This is just a simple microframework based on ZF2 components.

## Considerations

Actually the source code is quite a bit messy... I'm working on features.

## License

This project uses the MIT license.


#Getting started


In the `scenario` folder you can find an example but a tipical starting point
can be something like this...

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

$conf = include __DIR__ . '/../configs/app.php';
$app = new \UpCloo\App($conf);
$app->bootstrap()->run();
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
                        'renderer' => 'UpCloo\\Renderer\\Jsonp',
                        'controller' => 'Your\\Controller\\Name',
                        'action' => 'hello'
                    )
                ),
                'may_terminate' => true,
            )
        )
    )
);
```

Start with a controller (src/Your/Controller/Name.php)

```
<?php
namespace Your\Controller;

class Name
{
    public function hello()
    {
        return "world";
    }
}
```

Start your web service

```
php -S localhost:8080 -t web
```

Go to your page `-> localhost:8080/walter`

