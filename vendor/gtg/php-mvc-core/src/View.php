<?php 

namespace GTG\MVC;

use League\Plates\Engine;

class View extends Engine
{
    public function __construct(array $config) 
    {
        return parent::__construct($config['path'], 'php');
    }
}