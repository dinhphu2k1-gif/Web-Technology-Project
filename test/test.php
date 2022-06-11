<?php
$data = "data";

$data = htmlspecialchars($data, ENT_COMPAT, 'UTF-8');

var_dump($data);
var_dump($_COOKIE);