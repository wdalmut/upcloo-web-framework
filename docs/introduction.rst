Introduction
============

UpCloo framework is based on ZF2 components and in particular:

 * TreeRouteStack (Router)
 * EventManager
 * ServiceManager

Renderers
---------

You have to define "renderers" (who render your data). The framework
provides two default renderers that are:

 * UpCloo\\Renderer\\Json
 * UpCloo\\Renderer\\Jsonp

Events
------

The framework flow is event driven and the execution depends in
your actions. In a valid request you reach this events list

 * begin
 * route
 * pre.fetch
 * execute
 * renderer
 * finish

The default flow can change on errors, redirections and exceptions,
for example if a route is missing the "404" event is thrown and the
flow is like this:

 * begin
 * route
 * 404
 * finish

You have to attach a listener on the "404" event in order to handle this error
situation.

Services
--------

The `ServiceManager` is responsible to provide objects to your application and
is widly used into the App framework in order to select the right controller
and renderer.

Configuration
-------------

The framework uses your configuration in order to bootstrap and run.

