<?php

require_once (ROOT . "/config/config.php");
require_once (ROOT . "/library/database.php");
require_once (ROOT . "/library/router.php");
require_once (ROOT . "/library/model.php");
require_once (ROOT . "/routes.php");
require_once (ROOT . "/vendor/autoload.php");
require_once (ROOT . "/library/Response.php");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 1000");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");
header("Access-Control-Allow-Methods: PUT, POST, GET, OPTIONS, DELETE");
header('Content-Type: application/json');

$router = new Router();
$router->setRoutes($routes);

$db = new Database();
$connect = $db->connectDB();

$url = $_SERVER['REQUEST_URI'];
echo "/api/controller/" . $router->getFileName($url);
require_once (ROOT . "/api/controller/" . $router->getFileName($url));

