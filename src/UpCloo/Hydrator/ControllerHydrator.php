<?php
namespace UpCloo\Hydrator;

trait ControllerHydrator
{
    /**
     * @todo proxy objects limits the traits usage
     */
    public function hydrate(\UpCloo\App $app, $controller)
    {
        if ($controller instanceOf \Zend\Cache\Pattern\ObjectCache) {
            $controller = $controller->getOptions()->getObject();
        }

        $traits = $this->class_uses_deep($controller);
        $this->hydrateServices($traits, $app->services(), $controller);
        $this->hydrateEvents($traits, $app->events(), $controller);
        $this->hydrateRequest($traits, $app->request(), $controller);
        $this->hydrateResponse($traits, $app->response(), $controller);
    }

    private function hydrateServices($traits, $serviceManager, $controller)
    {
        if (in_array("UpCloo\\Controller\\ServiceManager", $traits)) {
            $controller->setServiceManager($serviceManager);
        }
    }

    private function hydrateEvents($traits, $eventManager, $controller)
    {
        if (in_array("UpCloo\\Controller\\EventManager", $traits)) {
            $controller->setEventManager($eventManager);
        }
    }

    private function hydrateRequest($traits, $request, $controller)
    {
        if (in_array("UpCloo\\Controller\\Request", $traits)) {
            $controller->setRequest($request);
        }
    }

    private function hydrateResponse($traits, $response, $controller)
    {
        if (in_array("UpCloo\\Controller\\Response", $traits)) {
            $controller->setResponse($response);
        }
    }

    /**
     * Check traits that uses traits!
     *
     * @param mixed $class class to check
     * @param boolean $autoload The autoload
     * @return array The list of traits used
     */
    private function class_uses_deep($class, $autoload = true) {
        $traits = [];
        do {
            $traits = array_merge(class_uses($class, $autoload), $traits);
        } while($class = get_parent_class($class));

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, $autoload), $traits);
        }
        return array_unique($traits);
    }
}
