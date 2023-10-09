<?php 

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/config/app.php';

$app->db->applySeeders();