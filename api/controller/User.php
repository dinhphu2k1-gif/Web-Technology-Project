<?php
require_once(ROOT . "/api/model/User.php");
require_once(ROOT . "/api/model/Cart.php");

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
    // Kiểm tra User có phải Admin hay không
    $USER->checkIsAdmin();
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

    // Nếu là Admin hoặc chủ sở hữu mới có thể xem được thông tin
    $USER->checkUser($userId);

    $user = $USER->get($connect, $userId);
    // Kiểm tra xem user có tồn tại hay không
    if ($user) {
        Response::responseData(200, "ok", $user);
    } else {
        Response::responseInfo(404, "User not found!!");
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
        Response::responseInfo(409, "User already exist!!");
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
            "id" => $userId,
            "is_admin" => false
        ];

        $jwt = JWT::encode($payload, JWT_KEY, JWT_ALG);

        http_response_code(201);
        echo json_encode([
            "jwt" => $jwt,
            "user_id" => $userId,
            "status" => "201",
            "message" => "created",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
        Response::responseMergedData(201, "created", array("jwt" => $jwt, "user_id" => $userId));
        // Sau khi tạo 1 user mới, đồng thời tạo 1 giỏ hàng
        $CART = new Cart();
        $CART->create($connect, ["user_id" => $userId]);
    }
}

/**
 * Cập nhật 1 users
 */
if (preg_match("/users\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'PUT') {
    $userId = $matches[1];
    $input = json_decode(file_get_contents("php://input"), true);

    $USER->checkUser($userId);

    // Kiểm tra xem người dùng có tồn tại hay không
    $user = $USER->get($connect, $userId);
    if (!$user) {
        Response::responseInfo(404, "User not found!!");
        exit();
    }

    // Kiểm tra nếu tên người dùng đã được sử dụng thì không cho đổi
    if (!empty($input['username'])) {
        $user = $USER->findByUser($connect, "username='{$input['username']}'");
        if ($user) {
            Response::responseInfo(409, "User already exist!!");
            exit();
        }
    }

    // Thực hiện update thông tin người dùng
    $USER->update($connect, $userId, $input);
    Response::responseInfo(200, "ok");
}

/**
 * Xoá 1 users
 */
if (preg_match("/users\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $userId = $matches[1];
    $USER->checkIsAdmin();

    $user = $USER->get($connect, $userId);
    if (!$user) {
        Response::responseInfo(404, "User not found!!");
    } else {
        $USER->delete($connect, $userId);
        Response::responseInfo(200, "ok");
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
            "id" => $user['id'],
            "is_admin" => false
        ];

        $jwt = JWT::encode($payload, JWT_KEY, JWT_ALG);

        Response::responseMergedData(200, "ok", array("jwt" => $jwt, "user_id" => $user['id']));
    } else {
        Response::responseInfo(401, "Wrong username or password!!");
    }
}
