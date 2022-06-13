<?php
require_once(ROOT . "/api/model/User.php");

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$USER = new User();

$url = $_SERVER['REQUEST_URI'];
// nếu url chưa có dấu "/" thì thêm vào đầu.
if (strpos($url, "/") !== 0) {
    $url = "/$url";
}

/**
 * API lấy toàn bộ thông tin các Users
 */
if ($url == '/users' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $users = $USER->getAll($connect);

    $header = getallheaders();
    $jwt = $header['Authorization'];

    try {
        $decode_data = JWT::decode($jwt, new Key(JWT_KEY, JWT_ALG));

        // Chỉ có Admin mới được xem dữ liệu
        if (!$decode_data->is_admin) {
            http_response_code(401);
            echo json_encode([
                "status" => 401,
                "message" => "Access Denied"
            ]);
            exit();
        }
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode([
            "status" => 401,
            "message" => $e->getMessage()
        ]);
    }

    http_response_code(200);
    echo json_encode([
        "data" => $users,
        "status" => "200",
        "message" => "ok",
        "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
    ]);
}

/**
 * API lấy thông tin của 1 User
 */
if (preg_match("/users\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $userId = $matches[1];
    $user = $USER->get($connect, $userId);

    // Kiểm tra xem user có tồn tại hay không
    if ($user) {
        http_response_code(200);
        echo json_encode([
            "data" => $user,
            "status" => "200",
            "message" => "ok",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            "status" => "404",
            "message" => "User not found!!",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    }
}

/**
 * API tạo User mới
 */
if ($url == "/users" && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Nếu người dùng thay đổi username bằng 1 username đã tồn tại thì báo lỗi
    $user = $USER->findByUser($connect, "username='{$input['username']}'");
    if ($user) {
        http_response_code(409);
        echo json_encode([
            "status" => "409",
            "message" => "User already exist!!",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
        exit();
    }

    // mã hoá mật khẩu
    $input['password'] = password_hash($input['password'], PASSWORD_DEFAULT);
    $userId = $USER->create($connect, $input);

    if ($userId) {
        $payload = [
            "iss" => "localhost",
            "iat" => time(),
            "exp" => time() + 86400,
            "aud" => "myusers",
            "user_id" => $userId,
            "is_admin" => false
        ];

        $jwt = JWT::encode($payload, JWT_KEY, JWT_ALG);

        http_response_code(201);
        echo json_encode([
            "jwt" => $jwt,
            "status" => "201",
            "message" => "created",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => 500,
            "message" => "Fail to save User!!",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    }
}

/**
 * Cập nhật 1 users
 */
if (preg_match("/users\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'PATCH') {
    $userId = $matches[1];
    $input = json_decode(file_get_contents("php://input"), true);

    $header = getallheaders();
    $jwt = $header['Authorization'];

    try {
        $decode_data = JWT::decode($jwt, new Key(JWT_KEY, JWT_ALG));

        // Kiểm tra có phải cùng 1 người hay không?
        if ($decode_data->user_id != $userId) {
            http_response_code(401);
            echo json_encode([
                "status" => 401,
                "message" => "Access Denied"
            ]);
            exit();
        }
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode([
            "status" => 401,
            "message" => $e->getMessage()
        ]);

    }

    // Kiểm tra xem người dùng có tồn tại hay không
    $user = $USER->get($connect, $userId);
    if (!$user) {
        http_response_code(404);
        echo json_encode([
            "status" => "404",
            "message" => "User not found!!",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
        exit();
    }

    // Kiểm tra nếu tên người dùng đã được sử dụng thì không cho đổi
    if (!empty($input['username'])) {
        $user = $USER->findByUser($connect, "username='{$input['username']}'");
        if ($user) {
            http_response_code(409);
            echo json_encode([
                "status" => "409",
                "message" => "User already exist!!",
                "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
            ]);
            exit();
        }
    }

    // Thực hiện update thông tin người dùng
    $USER->update($connect, $userId, $input);
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
if (preg_match("/users\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $userId = $matches[1];

    $user = $USER->get($connect, $userId);
    if (!$user) {
        http_response_code(404);
        echo json_encode([
            "status" => "404",
            "message" => "User not found!!",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    } else {
        $USER->delete($connect, $userId);
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
if ($url == "/users/sign_in" && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $user = $USER->signIn($connect, $input);

    if ($user) {
        // Tạo JWT
        $payload = [
            "iss" => "localhost",
            "iat" => time(),
            "exp" => time() + 86400,
            "aud" => "myusers",
            "user_id" => $user['id'],
            "is_admin" => false
        ];

        $jwt = JWT::encode($payload, JWT_KEY, JWT_ALG);

        http_response_code(200);
        echo json_encode([
            "jwt" => $jwt,
            "status" => "200",
            "message" => "ok",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            "status" => "401",
            "message" => "Wrong username or password!!",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    }
}
