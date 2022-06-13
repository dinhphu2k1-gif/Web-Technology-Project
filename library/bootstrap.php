<?php

require_once (ROOT . "/config/config.php");
require_once (ROOT . "/library/database.php");
require_once (ROOT . "/library/router.php");
require_once (ROOT . "/library/model.php");
require_once (ROOT . "/routes.php");
require_once (ROOT . "/vendor/autoload.php");

$router = new Router();
$router->setRoutes($routes);

$db = new Database();
$connect = $db->connectDB();

$url = $_SERVER['REQUEST_URI'];

require_once (ROOT . "/api/controller/" . $router->getFileName($url));

