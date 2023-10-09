<?php 

namespace GTG\MVC;

use GTG\MVC\Application;
use GTG\MVC\Controller;
use GTG\MVC\Router;
use GTG\MVC\Session;

class Middleware 
{
    protected Controller $controller;
    protected ?Router $router;
    protected ?Session $session;

    public function __construct() 
    {
        $this->controller = Application::$app->controller;
        $this->router = Application::$app->router;
        $this->session = Application::$app->session;
    }

    protected function getRoute(string $route, array $params = []): ?string 
    {
        return $this->router?->route($route, $params);
    }

    protected function redirect(string $routeKey, array $params = []): void 
    {
        header("Location: {$this->getRoute($routeKey, $params)}");
        exit();
    }
}