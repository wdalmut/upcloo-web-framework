<?php
namespace UpCloo\App;

use Zend\ServiceManager\ServiceManager;
use Zend\EventManager\EventManager;

use Zend\EventManager\Event;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;

use UpCloo\Exception\HaltException;
use UpCloo\Exception\PageNotFoundException;

class Engine
{
    private $serviceManager;
    private $eventManager;
    private $request;
    private $response;

    public function setServiceManager($serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function services()
    {
        return $this->serviceManager;
    }

    public function setEventManager($eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function events()
    {
        return $this->eventManager;
    }

    public function event()
    {
        $event = new Event();
        $event->setTarget($this);
        return $event;
    }

    public function trigger($name, array $params = array())
    {
        $event = $this->event();
        $event->setParams($params);
        return $this->events()->trigger($name, $event);
    }

    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function request()
    {
        if (!$this->request instanceof Request) {
            $this->request = new Request();
        }

        return $this->request;
    }

    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function response()
    {
        if (!($this->response instanceof Response)) {
            $this->response = new Response();
        }

        return $this->response;
    }

    public function run()
    {
        $this->trigger("begin");

        $controllerExecution = $this->dispatchUserRequestRelatedEvents();

        $this->trigger("renderer", ["data" => $controllerExecution,]);

        $this->trigger("finish");
        $this->trigger("send.response", ['response' => $this->response()]);
    }

    private function dispatchUserRequestRelatedEvents()
    {
       try {
            $controllerExecution = $this->dispatchUserRequest();
        } catch (HaltException $e) {
            $controllerExecution = $this->trigger("halt");
        } catch (PageNotFoundException $e) {
            $this->response()->setStatusCode(Response::STATUS_CODE_404);
            $controllerExecution = $this->trigger("404");
        } catch (\Exception $e) {
            $this->response()->setStatusCode(Response::STATUS_CODE_500);
            $controllerExecution = $this->trigger("500", array("exception" => $e));
        }

       return $controllerExecution;
    }

    private function dispatchUserRequest()
    {
        $request = $this->request();

        $eventCollection = $this->trigger("route", array("request" => $request));
        $routeMatch = $eventCollection->last();

        if (null === $routeMatch) {
            throw new PageNotFoundException("page not found");
        }

        $this->response()->setStatusCode(Response::STATUS_CODE_200);

        $this->trigger("pre.fetch", [ "routeMatch" => $routeMatch ]);
        $controllerExecution = $this->events()->trigger("execute", $routeMatch);

        return $controllerExecution;
    }
}
