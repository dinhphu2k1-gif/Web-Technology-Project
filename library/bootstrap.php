<?php

require_once (ROOT . "/config/config.php");
require_once (ROOT . "/library/database.php");
require_once (ROOT . "/library/router.php");
require_once (ROOT . "/library/model.php");
require_once (ROOT . "/routes.php");

$router = new Router();
$router->setRoutes($routes);

$url = $_SERVER['REQUEST_URI'];

require_once (ROOT . "/api/controller/" . $router->getFileName($url));