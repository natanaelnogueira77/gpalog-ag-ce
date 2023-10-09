<?php 

namespace GTG\MVC;

class Request 
{
    public function getPath(): string 
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        if($position === false) {
            return $path;
        }
        return substr($path, 0, $position);
    }

    public function method(): string 
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function isGet(): bool 
    {
        return $this->method() === 'get';
    }

    public function isPost(): bool 
    {
        return $this->method() === 'post';
    }

    public function isPut(): bool 
    {
        return $this->method() === 'put';
    }

    public function isDelete(): bool 
    {
        return $this->method() === 'delete';
    }

    public function isPatch(): bool 
    {
        return $this->method() === 'patch';
    }

    public function getBody(): array 
    {
        $body = [];
        if($this->method() === 'get') {
            foreach($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $value, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        } elseif($this->method() === 'post') {
            foreach($_POST as $key => $value) {
                $body[$key] = filter_input(INPUT_POST, $value, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        return $body;
    }
}