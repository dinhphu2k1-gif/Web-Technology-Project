<?php
require_once (ROOT . "/api/model/admin.php");
header("Content-Type:application/json");

$ADMIN = new Admin();
$db = new Database();
$connect = $db->connectDB();

$url = $_SERVER['REQUEST_URI'];
// nếu url chưa có dấu "/" thì thêm vào đầu.
if (strpos($url, "/") !== 0) {
    $url = "/$url";
}

/**
 * API lấy toàn bộ thông tin các Admin
 */
if ($url == '/admins' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $admins = $ADMIN->getAll($connect);

    echo json_encode([
        "data" => $admins,
        "numberRecords" => count($admins),
        "message" => "ok"
    ]);
}

/**
 * API lấy thông tin của 1 Admin
 */
if (preg_match("/admins\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $adminId = $matches[1];
    $admin = $ADMIN->get($connect, $adminId);

    if ($admin) {
        echo json_encode([
            "data" => $admin,
            "message" => "ok"
        ]);
    }
    else {
        echo json_encode([
            "message" => "Admin not found!!"
        ]);
    }
}

/**
 * API tạo Admin mới
 */
if ($url == "/admins" &&  $_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $adminId = $ADMIN->create($connect, $input);

    if ($adminId) {
        $input['id'] = $adminId;
        $input['link'] = "/admins/$adminId";

        echo json_encode([
            "data" => $input,
            "message" => "ok"
        ]);
    }
    else {
        echo json_encode([
            "message" => "Admin already exist!!"
        ]);
    }
}

/**
 * Cập nhật 1 admin
 */
if (preg_match("/users\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'PATCH') {
    $adminId = $matches[1];
    $input = json_decode(file_get_contents("php://input"), true);

    $ADMIN->update($connect, $adminId, $input);

    echo json_encode([
        "message" => "ok"
    ]);
}

/**
 * Xoá 1 users
 */
if (preg_match("/admins\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $adminId = $matches[1];
    $ADMIN->delete($connect, $adminId);

    echo json_encode([
        "message" => "ok"
    ]);
}

/**
 * Đăng nhập
 */
if ($url == "/admins/sign_in" &&  $_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);

    if ($ADMIN->signIn($connect, $input)) {
        echo json_encode([
            "message" => "ok"
        ]);
    }
    else {
        echo json_encode([
            "message" => "Wrong username or password!!"
        ]);
    }
}
