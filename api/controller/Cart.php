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
 * API lấy danh sách sản phẩm trong giở hàng của 1 user
 */
if (preg_match("/carts\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $userId = $matches[1];
    $CART->checkUser($userId);

    $products = $CART->getAllProducts($connect, $userId);

    Response::responseData(200, "ok", $products);
}

/**
 * API thêm sản phẩm vào giỏ hàng
 */
if (preg_match("/carts\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $matches[1];
    $CART->checkUser($userId);

    $input = json_decode(file_get_contents('php://input'), true);

    // Kiểm tra xem sản phẩm đã tồn tại trong giỏ hàng hay chưa
    $productExist = $CART->findByProductId($connect, $input['cart_id'], $input['product_id']);
    if ($productExist) {
        Response::responseInfo(409, "Product already existed in your cart!!");
        exit();
    }

    $productId = $CART->create($connect, $input);
    if ($productId) {
        Response::responseInfo(201, "created");
    } else {
        Response::responseInfo(500, "Fail to save Product to Cart!!");
    }
}

/**
 * API cập nhật thông tin giỏ hàng
 * Note: chỉ cập nhật số lượng và giá sản phẩm
 */
if (preg_match("/carts\/(\d+)\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'PUT') {
    $userId = $matches[1];
    $CART->checkUser($userId);

    $input = json_decode(file_get_contents('php://input'), true);
    // ID trong bảng cart_detail
    $productId = $matches[2];
    $CART->update($connect, $productId, $input);
    Response::responseInfo(200, "ok");
}

/**
 * API xoá sản phẩm trong giỏ hàng
 */
if (preg_match("/carts\/(\d+)\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $userId = $matches[1];
    $CART->checkUser($userId);

    $productId = $matches[2];
    $CART->delete($connect, $productId);
    Response::responseInfo(200, "ok");
}

/**
 *API xoá toàn bộ sản phẩm trong giỏ hàng
 */
else if (preg_match("/carts\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $userId = $matches[1];
    $CART->checkUser($userId);

    $CART->deleteAll($connect, $userId);
    Response::responseInfo(200, "ok");
}