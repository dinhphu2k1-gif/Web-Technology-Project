<?php
require_once(ROOT . "/api/model/Admin.php");
use \Firebase\JWT\JWT;

$ADMIN = new Admin();

$url = $_SERVER['REQUEST_URI'];
// nếu url chưa có dấu "/" thì thêm vào đầu.
if (strpos($url, "/") !== 0) {
    $url = "/$url";
}

/**
 * API lấy toàn bộ thông tin các Admin
 */
if ($url == '/admins' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    // Kiểm tra xem có phải Admin hay không
    $ADMIN->checkIsAdmin();
    $admins = $ADMIN->getAll($connect);
    Response::responseData(200, "ok", $admins);
}

/**
 * API lấy thông tin của 1 Admin
 */
if (preg_match("/admins\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $ADMIN->checkIsAdmin();

    $adminId = $matches[1];
    $admin = $ADMIN->get($connect, $adminId);

    if ($admin) {
        Response::responseData(200, "ok", $admin);
    } else {
        Response::responseInfo(404, "Admin not found!!");
    }
}

/**
 * API tạo Admin mới
 */
if ($url == "/admins" &&  $_SERVER['REQUEST_METHOD'] == 'POST') {
    $ADMIN->checkIsAdmin();

    $input = json_decode(file_get_contents('php://input'), true);

    $admin = $ADMIN->findByUser($connect, "username='{$input['username']}'");
    if ($admin) {
        Response::responseInfo(409, "Admin already exist!!");
        exit();
    }

    $input['password'] = password_hash($input['password'], PASSWORD_DEFAULT);
    $adminId = $ADMIN->create($connect, $input);

    if ($adminId) {
        $payload = [
            "iss" => "localhost",
            "iat" => time(),
            "exp" => time() + 86400,
            "aud" => "myadmins",
            "id" => $adminId,
            "is_admin" => true
        ];

        $jwt = JWT::encode($payload , JWT_KEY, JWT_ALG);

        $data = array("jwt" => $jwt, "admin_id" => $adminId);
        Response::responseMergedData(201, "created", $data);
    }
    else {
        Response::responseInfo(500, "Fail to save admin");
    }
}

/**
 * Cập nhật 1 admin
 */
if (preg_match("/users\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'PUT') {
    $ADMIN->checkIsAdmin();

    $adminId = $matches[1];
    $input = json_decode(file_get_contents("php://input"), true);

    $admin = $ADMIN->get($connect, $adminId);
    if (!$admin) {
        Response::responseInfo(404, "Admin not found!!");
        exit();
    }

    if (!empty($input['username'])) {
        $admin = $ADMIN->findByUser($connect, "username='{$input['username']}'");
        if ($admin) {
            Response::responseInfo(409, "Admin already exist!!");
            exit();
        }
    }

    $ADMIN->update($connect, $adminId, $input);

    Response::responseInfo(200, "ok");
}

/**
 * Xoá 1 admin
 */
if (preg_match("/admins\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $ADMIN->checkIsAdmin();

    $adminId = $matches[1];

    $admin = $ADMIN->get($connect, $adminId);
    if (!$admin) {
        Response::responseInfo(404, "Admin not found!!");
    } else {
        $ADMIN->delete($connect, $adminId);
        Response::responseInfo(200, ok);
    }
}

/**
 * Đăng nhập
 */
if ($url == "/admins/sign_in" &&  $_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $admin = $ADMIN->signIn($connect, $input);

    if ($admin) {
        $payload = [
            "iss" => "localhost",
            "iat" => time(),
            "exp" => time() + 86400,
            "aud" => "myadmins",
            "id" => $admin['id'],
            "is_admin" => true
        ];

        $jwt = JWT::encode($payload , JWT_KEY, JWT_ALG);

        $data = array("jwt" => $jwt, "admin_id" => $admin['id']);
        Response::responseMergedData(100, "ok", $data);
    }
    else {
        Response::responseInfo(401, "Wrong username or password!!");
    }
}
