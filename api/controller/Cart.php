<?php
require_once (ROOT . "/api/model/Cart.php");
require_once (ROOT . "/api/model/Cart_Detail.php");
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$CART = new Cart_Detail();

$url = $_SERVER['REQUEST_URI'];
// nếu url chưa có dấu "/" thì thêm vào đầu.
if (strpos($url, "/") !== 0) {
    $url = "/$url";
}

/**
 * API lấy danh sách sản phẩm của giỏ hàng
 */
if ($url == '/carts' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $header = getallheaders();
    $jwt = $header['Authorization'];
    $userId = '';
    try {
        $decode_data = JWT::decode($jwt, new Key(JWT_KEY, JWT_ALG));
        $userId =  $decode_data->id;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode([
            "status" => 401,
            "message" => $e->getMessage()
        ]);
    }

    $products = $CART->getAllProducts($connect, $userId);

    http_response_code(200);
    echo json_encode([
        "data" => $products,
        "status" => "200",
        "message" => "ok",
        "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
    ]);
}

