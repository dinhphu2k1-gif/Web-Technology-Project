<?php
require_once(ROOT . "/api/model/Bill.php");
require_once(ROOT . "/api/model/Bill_Detail.php");
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$BILL = new Bill_Detail();

$url = $_SERVER['REQUEST_URI'];
// nếu url chưa có dấu "/" thì thêm vào đầu.
if (strpos($url, "/") !== 0) {
    $url = "/$url";
}

/**
 * API lấy danh sách các đơn đặt hàng (dành cho Amin)
 */
if ($url == '/bills' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $BILL->checkIsAdmin();

    $bills = $BILL->getAll($connect);

    http_response_code(200);
    echo json_encode([
        "data" => $bills,
        "status" => "200",
        "message" => "ok",
        "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
    ]);
}

/**
 * API lấy danh sách các đơn đặt hàng (dành cho User)
 */
if (preg_match("/bills\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $userId = $matches[1];
    $BILL->checkUser($userId);

    $bills = $BILL->getAllBills($connect, $userId);

    for ($i = 0; $i < count($bills); $i++) {
        $billId = $bills[$i]['id'];
        $products = $BILL->getProducts($connect, $billId);
        $bills[$i]['list_products'] = $products;
    }

    http_response_code(200);
    echo json_encode([
        "data" => $bills,
        "status" => "200",
        "message" => "ok",
        "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
    ]);
}

/**
 * API tạo hoá đơn
 * Note: - tạo hoá đơn
 *       - sao chép các sản phẩm từ bảng order_details sang bảng bill_details
 */
if (preg_match("/bills\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $matches[1];
    $BILL->checkUser($userId);

    $input = json_decode(file_get_contents("php://input"), true);
    $billId = $BILL->create($connect, $input);


}