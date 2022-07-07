<?php
require_once(ROOT . "/api/model/Bill.php");
require_once(ROOT . "/api/model/Bill_Detail.php");
require_once(ROOT . "/api/model/Cart_Detail.php");
require_once(ROOT . "/api/model/Notification.php");
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$BILL = new Bill();
$CART = new Cart_Detail();
$NOTIFICATION = new Notification();
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
 * API lấy chi tiết 1 đơn hàng bằng id
 */
if (preg_match("/bills\/(\d+)\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $userId = $matches[1];
    $billId = $matches[2];
    $BILL->checkUser($userId);

    $bill = $BILL->get($connect, $billId);
    $products = $BILL->getProducts($connect, $billId);
    $bill['list_product'] = $products;
    http_response_code(200);
    echo json_encode([
        "data" => $bill,
        "status" => "200",
        "message" => "ok",
        "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
    ]);
    exit();

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
 * API chỉnh sửa trạng thái đơn hàng
 */
if (preg_match("/bills\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'PUT') {
    $billId = $matches[1];
    $input = json_decode(file_get_contents("php://input"), true);
    if(array_key_exists('status', $input)){
        $update = $BILL->updateStatus($connect, $billId, $input['status']);
        if($update){
            $NOTIFICATION->init($billId, "Bạn vừa thay đổi trạng thái đơn hàng(Mã đơn hàng: {$billId})", true);
            $NOTIFICATION->insertNotification($connect);
            $NOTIFICATION->init($billId, "Đơn hàng được thay đổi thành: '{$input['status']}'(Mã đơn hàng: {$billId})", true);
            $NOTIFICATION->insertNotification($connect);
            Response::responseInfo(200, "bill is updated!!");
        }
    }
}

/**
 * API tạo hoá đơn
 * Note: - tạo hoá đơn
 *       - sao chép các sản phẩm từ bảng order_details sang bảng bill_details
 *       - xoá các sản phẩm ở giỏ hàng
 */
if (preg_match("/bills\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $matches[1];
    $BILL->checkUser($userId);
    $input = json_decode(file_get_contents("php://input"), true);
    $input['time_create'] = date("Y/m/d h:i:s",time());
    $billId = $BILL->create($connect, $input);

    if ($billId) {
        http_response_code(201);
        echo json_encode([
            "status" => "201",
            "message" => "created",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);

        // chuyển các sản phẩm từ giỏ hàng sang hoá đơn
        $BILL->addProducts($connect, $userId, $billId);

        // xoá các sản phẩm trong giỏ hàng
        $CART->deleteAll($connect, $userId);
        $NOTIFICATION->init($billId, "Có một đơn đặt hàng mới (Mã đơn hàng {$billId})", 'yes');
        $NOTIFICATION->insertNotification($connect);
        $NOTIFICATION->init($billId, "Bạn đã đặt đơn hàng thành công (Mã đơn hàng {$billId})", 'no');
        $NOTIFICATION->insertNotification($connect);
    }


}

/**
 * API xoá đơn hàng
 */
if (preg_match("/bills\/(\d+)\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $userId = $matches[1];
    $BILL->checkUser($userId);

    $billId = $matches[2];
    $BILL->delete($connect, $billId);
    http_response_code(200);
    echo json_encode([
        "status" => "200",
        "message" => "ok",
        "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
    ]);
}