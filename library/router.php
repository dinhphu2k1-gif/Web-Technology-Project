<?php
class Router {
    private $routes = array();

    function setRoutes($routes) {
        $this->routes = $routes;
    }

    function getFileName($url) {
        foreach ($this->routes as $route => $file) {
            if (strpos($url, $route)) {
                return $file;
            }
        }
    }
}