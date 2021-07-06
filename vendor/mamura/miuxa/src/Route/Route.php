<?php
namespace miuxa\Route;

class Route
{
    private $route;
    private $handler;
    private $name;

    public function __construct($route, $handler)
    {
        $this->route = $route;
        $this->handler = $handler;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}
