<?php 

namespace GTG\MVC\DB;

use GTG\MVC\Application;
use GTG\MVC\DB\Database;

abstract class Migration 
{
    protected Database $db;

    public function __construct() 
    {
        $this->db = Application::$app->db;
    }

    abstract public function up(): void;

    abstract public function down(): void;

    protected function exec(string $sql): void  
    {
        $this->db->exec($sql);
    }
}