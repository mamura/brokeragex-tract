<?php

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteParser\Std;
use miuxa\App;
use miuxa\Database\Database;
use miuxa\Http\Request;
use miuxa\Http\Response;
use miuxa\Route\Router;
use Psr\Container\ContainerInterface;

return [
    'settings' => function () {
        return require __DIR__ . '/settings.php';
    },

    Request::class => function () {
        return new Request();
    },

    Response::class => function () {
        return new Response();
    },

    Router::class => function (ContainerInterface $container) {
        return new Router(new Std(), new GroupCountBased(), $container->get(Request::class));
    },

    App::class => function (ContainerInterface $container, Router $touter) {
        return new App($container);
    },
    
    Database::class => function (ContainerInterface $container) {
        return new Database($container->settings['db']);
    }
];
