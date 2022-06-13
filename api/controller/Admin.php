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
    $admins = $ADMIN->getAll($connect);

    http_response_code(200);
    echo json_encode([
        "data" => $admins,
        "status" => "200",
        "message" => "ok",
        "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
    ]);
}

/**
 * API lấy thông tin của 1 Admin
 */
if (preg_match("/admins\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $adminId = $matches[1];
    $admin = $ADMIN->get($connect, $adminId);

    if ($admin) {
        http_response_code(200);
        echo json_encode([
            "data" => $admin,
            "status" => "200",
            "message" => "ok",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            "status" => "404",
            "message" => "Admin not found!!",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    }
}

/**
 * API tạo Admin mới
 */
if ($url == "/admins" &&  $_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $admin = $ADMIN->findByUser($connect, "username='{$input['username']}'");
    if ($admin) {
        http_response_code(409);
        echo json_encode([
            "status" => "409",
            "message" => "Admin already exist!!",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
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
            "admin_id" => $adminId,
            "is_admin" => true
        ];

        $jwt = JWT::encode($payload , JWT_KEY, JWT_ALG);

        http_response_code(201);
        echo json_encode([
            "jwt" => $jwt,
            "status" => "201",
            "message" => "created",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    }
    else {
        http_response_code(500);
        echo json_encode([
            "status" => 500,
            "message" => "Fail to save user",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    }
}

/**
 * Cập nhật 1 admin
 */
if (preg_match("/users\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'PATCH') {
    $adminId = $matches[1];
    $input = json_decode(file_get_contents("php://input"), true);

    $admin = $ADMIN->get($connect, $adminId);
    if (!$admin) {
        http_response_code(404);
        echo json_encode([
            "status" => "404",
            "message" => "Admin not found!!",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
        exit();
    }

    if (!empty($input['username'])) {
        $admin = $ADMIN->findByUser($connect, "username='{$input['username']}'");
        if ($admin) {
            http_response_code(409);
            echo json_encode([
                "status" => "409",
                "message" => "Admin already exist!!",
                "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
            ]);
            exit();
        }
    }

    $ADMIN->update($connect, $adminId, $input);
    http_response_code(200);
    echo json_encode([
        "status" => "200",
        "message" => "ok",
        "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
    ]);
}

/**
 * Xoá 1 users
 */
if (preg_match("/admins\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $adminId = $matches[1];

    $admin = $ADMIN->get($connect, $adminId);
    if (!$admin) {
        http_response_code(404);
        echo json_encode([
            "status" => "404",
            "message" => "Admin not found!!",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    } else {
        $ADMIN->delete($connect, $adminId);
        http_response_code(200);
        echo json_encode([
            "status" => "200",
            "message" => "ok",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
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
            "admin_id" => $admin['id'],
            "is_admin" => true
        ];

        $jwt = JWT::encode($payload , JWT_KEY, JWT_ALG);

        http_response_code(200);
        echo json_encode([
            "jwt" => $jwt,
            "status" => "200",
            "message" => "ok",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    }
    else {
        http_response_code(401);
        echo json_encode([
            "status" => "401",
            "message" => "Wrong username or password!!",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    }
}
