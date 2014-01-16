Feature: Consume a JSON web service with the framework

    Background:
        Given the Name, Email service

    Scenario: Try to consume the Name, Email service
        When I ask for the Name, Email service
        Then Name, Email service replies with:
            | Name              | Email                     |
            | Walter            | walter.dalmut@gmail.com   |
            | Walter Corley     | walter.dalmut@corley.it   |

