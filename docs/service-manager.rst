The ServiceManager
=================

As mentioned before, the ZF2 ServiceManger is used. You can configure your
services and require for them in your controller.

In your configuration: ::

    <?php
    return array(
        "services" => array(
            "factories" => array(
                "example" => function($sl) {
                    return new stdClass();
                }
            )
        )
    )

In your controller you have to require the ServiceManager trait ::

    <?php
    namespace My\NM;

    use UpCloo\Controller\ServiceManager;

    class My
    {
        use ServiceManager;

        public function hello($event)
        {
            $service = $this->get("example");

            return $service;
        }
    }


