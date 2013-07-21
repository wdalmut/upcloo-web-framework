# UpCloo MicroFramework with ZF2 components

This is just a simple microframework based on ZF2 components.

## Considerations

Actually the source code is quite a bit messy... I'm working on features.

## License

This project uses the MIT license.


#Getting started

```
<?php
$loader = include __DIR__ . '/vendor/autoload.php';

$loader->add("Your", __DIR__ . '/../src');

$conf = include __DIR__ . '/../configs/app.php';
$app = new \UpCloo\App($conf);
$app->bootstrap()->run();
```

In the `scenario` folder you can find an example.

