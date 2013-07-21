Controllers and Actions
=======================

Controllers are simply `POPO` object, like this: ::

    class Me
    {
        public function hello()
        {
            return "hello!";
        }
    }

Controllers and actions are mapped thank to the ``TreeRouteStack`` as you can
see in the :doc:`getting-started` section.

Interact with the event data
----------------------------

When your action is called, the event is passed as method argument and you can
interact with the `RouteMatch` in this way: ::

    class Me
    {
        public function hello($event)
        {
            //Play with $event
        }
    }

The `$event` object is a `Zend\\EventManager\\Event` object, in few words something
like this: ::

    object(Zend\EventManager\Event)[31]
        protected 'name' => string 'execute' (length=7)
        protected 'target' =>
            object(Zend\Mvc\Router\Http\RouteMatch)[35]
            protected 'length' => int 7
            protected 'params' =>
                array (size=3)
                'renderer' => string 'UpCloo\Renderer\Jsonp' (length=21)
                'controller' => string 'exampleController' (length=17)
                'action' => string 'method' (length=6)
            protected 'matchedRouteName' => string 'home' (length=4)
        protected 'params' =>
            array (size=0)
            empty
        protected 'stopPropagation' => boolean false

The "param" contains the "RouteMatch" structure.

Interact with the Request object
--------------------------------

Many times you need to interact with Request (Zend\\Http\\PhpEnvironment\\Request) object.
When you need to use the http request you can use "UpCloo\\Controller\\Request"
trait. ::

    <?php
    namespace Your\NM;

    use UpCloo\Controller\Request;

    class Me
    {
        use Request;

        public function hello($event)
        {
            $request = $this->getRequest();
        }
    }

The framework hydrate your controller with the Request object only if you
declare that you need it using the trait!

Interact with the Response object
---------------------------------

The Response object (Zend\\Http\\PhpEnvironment\\Response) follow the same of
Request. ::

    <?php
    namespace Your\NM;

    use UpCloo\Controller\Response;

    class Me
    {
        use Response;

        public function hello($event)
        {
            $response = $this->getResponse();
        }
    }

Redirections
------------

As before you have to use traits, the UpCloo\\Controller\\Action\\Redirector
to be clear ::

    <?php
    namespace Your\NM;

    use UpCloo\Controller\Action\Redirector;

    class Me
    {
        use Redirector;

        public function hello($event)
        {
            $this->redirect("http://walterdalmut.com", 302);
        }
    }

The second argument of "redirect" method is optional (302 by default) and
the first argument is the redirect location.

The Redirector traits uses the "Response trait" by itself, for that reason when you use
the redirector the Response traits is automatically added to your controller.

EventManager
------------

Inside an event you can attach and fire other events adding the EventManager
trait::

    <?php
    namespace Your\\NM;

    use UpCloo\Controller\EventManager;

    class TheHookContainer
    {
        use EventManager;

        public function anHook()
        {
            $this->events()->attach("finish", function() {
                //Good bye cruel world!
            });
        }
    }


