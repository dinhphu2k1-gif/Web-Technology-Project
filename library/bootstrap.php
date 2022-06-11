<?php

require_once (ROOT . "/config/config.php");
require_once (ROOT . "/library/database.php");
require_once (ROOT . "/library/router.php");
require_once (ROOT . "/library/model.php");
require_once (ROOT . "/routes.php");
require_once (ROOT . "/library/request.php");
require_once(ROOT . "/library/Response.php");

$router = new Router();
$router->setRoutes($routes);

$url = $_SERVER['REQUEST_URI'];

echo $url ;
echo strpos($url, "users");

require_once (ROOT . "/api/model/" . $router->getFileName($url));