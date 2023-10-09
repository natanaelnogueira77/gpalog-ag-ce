<?php 

namespace GTG\MVC;

use GTG\MVC\Controller;
use GTG\MVC\DB\Database;
use GTG\MVC\Middleware;
use GTG\MVC\Request;
use GTG\MVC\Response;
use GTG\MVC\Router;
use GTG\MVC\Session;
use GTG\MVC\View;

class Application 
{
    public static string $ROOT_DIR;
    public static ?array $DB_INFO;
    public static ?array $SMTP_INFO;
    public static Application $app;
    public ?array $appData;
    public ?array $errorHandler;
    public Controller $controller;
    public ?Database $db;
    public Request $request;
    public Response $response;
    public ?Router $router;
    public ?Session $session;
    public ?View $view;

    public function __construct(string $rootPath, array $config) 
    {
        self::$ROOT_DIR = $rootPath;
        self::$DB_INFO = isset($config['db']['pdo']) ? $config['db']['pdo'] : null;
        self::$SMTP_INFO = isset($config['smtp']) ? $config['smtp'] : null;
        self::$app = $this;
        $this->appData = $config['data'] ?? null;
        $this->errorHandler = $config['errorHandler'] ?? null;
        $this->request = new Request();
        $this->response = new Response();
        $this->session = isset($config['session']) ? new Session($config['session']) : null;
        $this->router = isset($config['projectUrl']) && isset($_SERVER['REQUEST_METHOD']) ? new Router($config['projectUrl']) : null;
        $this->db = isset($config['db']) ? new Database($config['db']) : null;
        $this->view = isset($config['view']) ? new View($config['view']) : null;
        $this->controller = new Controller();
    }

    public function run(): void 
    {
        if($this->router) {
            $this->router->dispatch();
            if($this->router->error()) {
                $this->response->setStatusCode($this->router->error());
                if($this->errorHandler && $this->errorHandler['url']) {
                    $this->router->redirect(sprintf($this->errorHandler['url'], $this->router->error()));
                }
            }
        }
    }

    public function getController(): Controller 
    {
        return $this->controller;
    }

    public function setController(Controller $controller): void 
    {
        $this->controller = $controller;
    }
}