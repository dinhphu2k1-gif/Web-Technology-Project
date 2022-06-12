<?php
require_once(ROOT . "/api/model/User.php");
use \Firebase\JWT\JWT;

$USER = new User();
$db = new Database();
$connect = $db->connectDB();

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

    $input['password'] = password_hash($input['password'], PASSWORD_DEFAULT);
    $userId = $USER->create($connect, $input);

    if ($userId) {
        $input['id'] = $userId;
        $input['link'] = "/users/$userId";

        http_response_code(201);
        echo json_encode([
            "data" => $input,
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
 * Cập nhật 1 users
 */
if (preg_match("/users\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'PATCH') {
    $userId = $matches[1];
    $input = json_decode(file_get_contents("php://input"), true);

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
        $payload = [
            "iss" => "localhost",
            "iat" => time(),
            "exp" => time() + 86400,
            "aud" => "myusers",
            "user_data" => [
                "id" => $user['id'],
                "username" => $user['username'],
                "email" => $user['email']
            ]
        ];

        $secret_key = "jwt123";
        $algorithm = "HS256";

        $jwt = JWT::encode($payload , $secret_key, $algorithm);

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
