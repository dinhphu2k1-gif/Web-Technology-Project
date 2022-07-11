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

    Response::responseData(200, "ok", $products);
}

/**
 * API lấy thông tin 1 sản phẩm
 */
if (preg_match("/products\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $productId = $matches[1];
    $product = $PRODUCT->get($connect, $productId);

    if ($product) {
        Response::responseData(200, "ok", $product);
    } else {
        Response::responseInfo(404, "Product not found!!");
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
        Response::responseInfo(201, "created");
    }
    else {
        Response::responseInfo(500, "Fail to save product");
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
        Response::responseInfo(404, "Product not found!!");
        exit();
    }

    $PRODUCT->update($connect, $productId, $input);
    Response::responseInfo(200, "ok");
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
        Response::responseInfo(404, "Product not found!!");
    } else {
        $PRODUCT->delete($connect, $productId);
        Response::responseInfo(200, "ok");
    }
}

