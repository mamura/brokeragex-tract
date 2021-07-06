<?php
declare(strict_types=1);

namespace miuxa;

use FastRoute\Dispatcher\GroupCountBased as Dispatcher;
use miuxa\Http\Request;
use miuxa\Route\Router;
use Psr\Container\ContainerInterface;
use miuxa\Http\ServerRequest;
use miuxa\Http\ServerRequestFactory;

class App
{
    private $container;
    private $request;
    private $router;
    private $dispatcher;
    private $db;

    public function __construct(
        ?ContainerInterface $container = null,
        ?Router $router = null,
        ?ServerRequest $request = null
    ) {
        $request = $request ?? new ServerRequestFactory();
        $this->container    = $container;
        $this->request      = $request->fromGlobals();
        $this->router       = $router ?? $container->get(Router::class);
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getDb()
    {
        return $this->db;
    }

    public function run() : void
    {
        $this->dispatcher   = $this->dispatcher ?? new Dispatcher($this->router->getData());
        $dispatch           = $this->dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

        switch ($dispatch[0]) {
            case 0:
                //404
                break;

            case 1:
                $handler    = $dispatch[1];
                $vars       = $dispatch[2];

                $request = new Request();

                call_user_func_array($this->container->get($handler), [$request->bindParams($vars)])->send();
                break;

            case 2:
                //MEthod not allowed
                break;
        }
    }
}
