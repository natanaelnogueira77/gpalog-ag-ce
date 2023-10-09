<?php 

namespace GTG\MVC\DB;

use GTG\MVC\Application;

abstract class Seeder 
{
    abstract public function run(): void;

    protected function exec(string $sql): void  
    {
        Application::$app->db->exec($sql);
    }
}