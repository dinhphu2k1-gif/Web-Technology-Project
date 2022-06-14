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

/**
 * API lấy thông tin 1 sản phẩm
 */
if (preg_match("/products\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $productId = $matches[1];
    $product = $PRODUCT->get($connect, $productId);

    if ($product) {
        http_response_code(200);
        echo json_encode([
            "data" => $product,
            "status" => "200",
            "message" => "ok",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            "status" => "404",
            "message" => "Product not found!!",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    }
}

/**
 * API tạo Product mới
 */
if ($url == "/products" &&  $_SERVER['REQUEST_METHOD'] == 'POST') {
    // Kiểm tra xem có phải Admin hay không
    $PRODUCT->checkIsAdmin();

    $input = json_decode(file_get_contents('php://input'), true);

    $productId = $PRODUCT->create($connect, $input);

    if ($productId) {
        http_response_code(201);
        echo json_encode([
            "status" => "201",
            "message" => "created",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    }
    else {
        http_response_code(500);
        echo json_encode([
            "status" => 500,
            "message" => "Fail to save product",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    }
}

/**
 * Cập nhật 1 product
 */
if (preg_match("/products\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'PUT') {
    // Kiểm tra xem có phải Admin hay không
    $PRODUCT->checkIsAdmin();

    $productId = $matches[1];
    $input = json_decode(file_get_contents("php://input"), true);

    $product = $PRODUCT->get($connect, $productId);
    if (!$product) {
        http_response_code(404);
        echo json_encode([
            "status" => "404",
            "message" => "Product not found!!",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
        exit();
    }

    $PRODUCT->update($connect, $productId, $input);
    http_response_code(200);
    echo json_encode([
        "status" => "200",
        "message" => "ok",
        "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
    ]);
}

/**
 * Xoá 1 Product
 */
if (preg_match("/products\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'DELETE') {
    // Kiểm tra xem có phải Admin hay không
    $PRODUCT->checkIsAdmin();

    $productId = $matches[1];
    $product = $PRODUCT->get($connect, $productId);
    if (!$product) {
        http_response_code(404);
        echo json_encode([
            "status" => "404",
            "message" => "Product not found!!",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    } else {
        $PRODUCT->delete($connect, $productId);
        http_response_code(200);
        echo json_encode([
            "status" => "200",
            "message" => "ok",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    }
}

