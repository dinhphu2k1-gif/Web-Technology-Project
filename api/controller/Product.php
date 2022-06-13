<?php
require_once (ROOT . "/api/model/Product.php");

$PRODUCT = new Product();

$url = $_SERVER['REQUEST_URI'];
// nếu url chưa có dấu "/" thì thêm vào đầu.
if (strpos($url, "/") !== 0) {
    $url = "/$url";
}

/**
 * API lấy toàn bộ thông tin sản phẩm
 */
if ($url == '/products' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $products = $PRODUCT->getAll($connect);

    http_response_code(200);
    echo json_encode([
        "data" => $products,
        "status" => "200",
        "message" => "ok",
        "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
    ]);
}

