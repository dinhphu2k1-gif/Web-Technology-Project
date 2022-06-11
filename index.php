<?php

// Địa chỉ gốc của project
define("ROOT", dirname(__FILE__));

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once(ROOT . "/library/bootstrap.php");

Router::post('/posts', function (Request $req, Response $res) {
    $post = Posts::add($req->getJSON());
    $res->status(201)->toJSON($post);
});

Router::get('/users/([0-9]*)', function (Request $req, Response $res) {
    $res->toJSON([
        'post' =>  ['id' => $req->params[0]],
        'status' => 'ok'
    ]);
});