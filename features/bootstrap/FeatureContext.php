<?php
$loader = require __DIR__ . '/../../vendor/autoload.php';

$loader->add("UpCloo", __DIR__ . '/../../src');
$loader->add("UpCloo", __DIR__ . '/../../tests');

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use UpCloo\Test\AppContext;

class BaseController
{
    public function indexAction()
    {
        return [
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
     * @Given /^a Name, Email service$/
     */
    public function aNameEmailService()
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
                                    'action' => 'indexAction',
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
        $rows = $table->getRows();
        array_shift($rows);

        foreach ($rows as $row) {
            $content = array_shift($remote);
            if ($row[0] != $content->name) {
                throw new \RuntimeException("Not equals! " . $row[0] . " != " . $content->name);
            }

            if ($row[1] != $content->email) {
                throw new \RuntimeException("Not equals! " . $row[1] . " != " . $content->email);
            }
        }
    }
}
