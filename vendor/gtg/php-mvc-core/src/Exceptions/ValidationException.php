<?php

namespace GTG\MVC\Exceptions;

use GTG\MVC\Exceptions\AppException;
use Throwable;

class ValidationException extends AppException 
{
    private $errors = [];

    public function __construct(
        array $errors, 
        string $message, 
        int $code = 0, 
        ?Throwable $previous = null
    ) 
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array 
    {
        return $this->errors;
    }

    public function get(string $att): string 
    {
        return $this->errors[$att];
    }
}