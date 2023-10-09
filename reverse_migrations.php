<?php 

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/config/app.php';

$app->db->reverseMigrations(isset($argv) && isset($argv[1]) ? $argv[1] : null);