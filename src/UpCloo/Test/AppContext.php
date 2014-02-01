<?php
namespace UpCloo\Test;

use Behat\Behat\Context\BehatContext;

use UpCloo\App,
    UpCloo\App\Engine,
    UpCloo\App\Boot,
    UpCloo\App\Config\ArrayProcessor,
    UpCloo\Test\Factory;

class AppContext extends BehatContext
{
    use WebTestUtils;
}
