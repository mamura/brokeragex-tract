<?php
namespace miuxa\Route;

use FastRoute\RouteCollector;
use FastRoute\RouteParser;
use FastRoute\DataGenerator;
use miuxa\Route\Route;
use miuxa\Http\Request;

class Router extends RouteCollector
{
    private $dispatcher;
    private $request;
    private $collector;

    public function __construct(RouteParser $routeParser, DataGenerator $dataGenerator, Request $request)
    {
        parent::__construct($routeParser, $dataGenerator);
        $this->request      = $request;
    }

    public function get($route, $handler)
    {
        parent::get($route, $handler);
        return new Route($route, $handler);
    }

    public function post($route, $handler)
    {
        parent::post($route, $handler);
        return new Route($route, $handler);
    }

    public function put($route, $handler)
    {
        parent::put($route, $handler);
        return new Route($route, $handler);
    }

    public function delete($route, $handler)
    {
        parent::delete($route, $handler);
        return new Route($route, $handler);
    }

    public function getCollector()
    {
        return $this->collector;
    }

    public function resource($name)
    {
        $routeBaseName = '/'. preg_replace('/[^a-z_\-0-9]/i', '', $name);

        parent::addGroup($routeBaseName, function (RouteCollector $r) {
            
            $resourceName = ucfirst(preg_replace('/[^a-z_\-0-9]/i', '', $this->currentGroupPrefix));
            $basePath = '\\App\\' . $resourceName . '\\Action';

            $r->get('', $basePath . '\\' . $resourceName.'Action'::class);
            $r->get('/', $basePath . '\\' . $resourceName.'Action'::class);
            $r->get('/{id}', $basePath . '\\' . $resourceName.'FindAction'::class);
            $r->post('/save', $basePath . '\\' . $resourceName.'SaveAction'::class);
            $r->put('/update', $basePath . '\\' . $resourceName.'UpdateAction'::class);
            $r->delete('/delete', $basePath . '\\' . $resourceName.'DeleteAction'::class);
        });
    }
}
