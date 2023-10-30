<?php

namespace GTG\MVC\Exceptions;

use GTG\MVC\Application;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ErrorHandler 
{
    protected $monolog;
    private $type = 0;
    protected $msg = '';
    protected $file = '';
    protected $line = 0;
    private static ?string $errorUrl = null;
    
    public function __construct(
        int $type, 
        string $msg, 
        ?string $file = null, 
        ?int $line = null
    ) 
    {
        $this->monolog = new Logger('web');
        $this->monolog->pushHandler(new StreamHandler(Application::$ROOT_DIR . '/errors.log', Logger::ERROR));
        $this->monolog->pushProcessor(function ($record) {
            $record['extra']['HTTP_HOST'] = $_SERVER['HTTP_HOST'];
            $record['extra']['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
            $record['extra']['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'];
            $record['extra']['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];

            return $record;
        });

        $this->type = $type;
        $this->msg = $msg;
        $this->file = $file;
        $this->line = $line;
    }

    public static function control(
        int $type, 
        string $msg, 
        ?string $file = null, 
        ?int $line = null
    ): string 
    {
        $instance = new self($type, $msg, $file, $line);
        if(in_array($type, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_USER_ERROR, E_STRICT, E_RECOVERABLE_ERROR, E_DEPRECATED, E_USER_DEPRECATED])) {
            $instance->errorControl();
        } elseif(in_array($type, [E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING])) {
            $instance->warningControl();
        } elseif(in_array($type, [E_NOTICE, E_USER_NOTICE])) {
            $instance->noticeControl();
        }

        return $type;
    }

    public static function shutdown(): void 
    {
        $last_error = error_get_last();
        if($last_error['type'] === E_ERROR) {
            self::control(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
        }
    }

    protected function errorControl(): void  
    {
        $content = "File: {$this->file}, Line: {$this->line}, Message: {$this->msg}";
        $this->monolog->error($content, ['logger' => true]);
        $this->errorRedirect();
        return;
    }

    protected function warningControl(): void 
    {
        $content = "File: {$this->file}, Line: {$this->line}, Message: {$this->msg}";
        $this->monolog->warning($content, ['logger' => true]);
        return;
    }
    
    protected function noticeControl(): void 
    {
        $content = "File: {$this->file}, Line: {$this->line}, Message: {$this->msg}";
        $this->monolog->notice($content, ['logger' => true]);
        return;
    }

    private function errorRedirect(): void
    {
        if(self::$errorUrl) {
            header('Location: ' . sprintf(self::$errorUrl, '500'));
            exit();
        }
        return;
    }

    public static function setErrorUrl(string $url): void 
    {
        self::$errorUrl = $url;
    }
}