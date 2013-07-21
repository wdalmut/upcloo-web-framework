<?php
namespace UpCloo\Functional;

class AppTest extends \PHPUnit_Extensions_Selenium2TestCase
{
    public function setUp()
    {
        $this->setBrowser('firefox');
        $this->setBrowserUrl(_BROWSER_URL_);
    }

    public function testDefaultJson()
    {
        $this->url("/walter");
        $response = $this->byCssSelector('body')->text();
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                array(
                    "hello" => false,
                    "param2" => false,
                    "service" => "that-service"
                )
            ),
            $response
        );
    }

    public function testCallbackDefaultJson()
    {
        $this->url("/walter?callback=h725");
        $response = $this->byCssSelector('body')->text();

        $this->assertStringMatchesFormat("%s(%s)", $response);
        $this->assertSame(1, preg_match_all("/h725\\((\\S+)\\)/", $response, $matches));
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                array(
                    "hello" => false,
                    "param2" => false,
                    "service" => "that-service"
                )
            ),
            $matches[1][0]
        );

    }
}
