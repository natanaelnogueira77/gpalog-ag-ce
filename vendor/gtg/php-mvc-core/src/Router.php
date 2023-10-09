<?php 

namespace GTG\MVC;

use CoffeeCode\Router\Router as CoffeeCodeRouter;

class Router extends CoffeeCodeRouter
{
    public function __construct(string $root) 
    {
        return parent::__construct($root);
    }
}