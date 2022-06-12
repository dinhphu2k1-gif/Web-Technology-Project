<?php

// Địa chỉ gốc của project
define("ROOT", dirname(__FILE__));

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE");

require_once(ROOT . "/library/bootstrap.php");