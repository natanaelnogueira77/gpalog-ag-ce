<?php

namespace GTG\MVC;

use DateTime;
use GTG\MVC\Application;
use GTG\MVC\Request;
use GTG\MVC\Response;
use GTG\MVC\Router;
use GTG\MVC\Session;
use GTG\MVC\View;

class Controller 
{
    protected ?array $appData;
    protected Request $request;
    protected Response $response;
    protected ?Router $router;
    protected ?Session $session;
    protected ?View $view;
    protected ?array $errors = null;
    protected ?array $message = null;

    public function __construct() 
    {
        $this->appData = Application::$app->appData;
        $this->request = Application::$app->request;
        $this->response = Application::$app->response;
        $this->router = Application::$app->router;
        $this->session = Application::$app->session;
        $this->view = Application::$app->view;
    }

    protected function render(string $view, array $params = []): void  
    {
        $this->view->addData([
            'appData' => $this->appData,
            'router' => $this->router,
            'session' => $this->session
        ]);
        echo $this->view->render($view, $params);
    }

    protected function getView(string $view, array $params = []): string  
    {
        $this->view->addData([
            'appData' => $this->appData,
            'router' => $this->router,
            'session' => $this->session
        ]);
        return $this->view->render($view, $params);
    }

    protected function getRoute(string $route, array $params = []): ?string 
    {
        return $this->router?->route($route, $params);
    }

    protected function addViewData(array $params): void 
    {
        $this->view->addData($params);
    }

    protected function redirect(string $routeKey, array $params = []): void 
    {
        header("Location: {$this->getRoute($routeKey, $params)}");
        exit();
    }

    protected function getDateTime(?string $date = null): DateTime 
    {
        return new DateTime($date ? $date : '');
    }

    protected function APIResponse(array $callback, int $statusCode = 200): void 
    {
        if($this->errors) {
            $callback['errors'] = $this->errors;
        }

        if($this->message) {
            $callback['message'] = $this->message;
        }

        $this->response->setStatusCode($statusCode);
        echo json_encode($callback);
    }

    protected function setMessage(string $type, string $message): static 
    {
        $this->message = [$type, $message];
        return $this;
    }

    protected function setErrors(array $errors): static 
    {
        $this->errors = $errors;
        return $this;
    }

    protected function addError(string $key, string $message): static 
    {
        $this->errors[$key] = $message;
        return $this;
    }
}