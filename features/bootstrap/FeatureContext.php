<?php
$loader = require __DIR__ . '/../../vendor/autoload.php';

$loader->add("UpCloo", __DIR__ . '/../../src');
$loader->add("UpCloo", __DIR__ . '/../../tests');

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use UpCloo\Test\AppContext;

class BaseController
{
    private $database;

    public function __construct()
    {
        $this->database =  [
            [
                "name" => "Walter",
                "email" => "walter.dalmut@gmail.com",
            ],
            [
                "name" => "Walter Corley",
                "email" => "walter.dalmut@corley.it",
            ],
        ];
    }

    public function listAction()
    {
        return $this->database;
    }
}

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    private $response;

    public function __construct(array $parameters)
    {
        $this->useContext("upcloo_app", new UpCloo\Test\AppContext());
    }

    /**
     * @Given /^the Name, Email service$/
     */
    public function theNameEmailService()
    {
        $this->getMainContext()
            ->getSubcontext('upcloo_app')
            ->appendConfig([
                "router" => array(
                    "routes" => array(
                        "home" => array(
                            "type" => "Literal",
                            "options" => array(
                                "route" => "/name-email",
                                'defaults' => array(
                                    'controller' => 'BaseController',
                                    'action' => 'listAction',
                                )
                            ),
                            'may_terminate' => true
                        )
                    )
                ),
                "services" => array(
                    "invokables" => array(
                        "BaseController" => "BaseController",
                    ),
                )
        ]);
    }

    /**
     * @When /^I ask for the Name, Email service$/
     */
    public function AskForTheNameEmailService()
    {
        $this->response = $this->getMainContext()
            ->getSubcontext("upcloo_app")->dispatch("/name-email", "GET");
    }

    /**
     * @Then /^Name, Email service replies with:$/
     */
    public function nameEmailServiceRepliesWith(TableNode $table)
    {
        $remote = json_decode($this->response->getContent());
        $rows = $table->getHash();

        foreach ($rows as $row) {
            $content = array_shift($remote);

            assertEquals($row["Name"], $content->name);
            assertEquals($row["Email"], $content->email);
        }
    }
}
