Listeners
=========

Listeners are object that are used when an event is fired! ::

    <?php
    namespace My\NM;

    class Error
    {
        public function error()
        {

        }
    }

Of course we have to link listeners through the configuration: ::

    // configs/app.php

    "services" => arrray(
        "invokables" => array(
            "My\\NM\\Error" => "My\\NM\\Error",
        )
    ),
    "listeners" => array(
        "404" => array(
            array("My\\NM\\Error", "error")
        )
    )


