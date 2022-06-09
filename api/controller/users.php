<?php
require_once(ROOT . "/api/model/user.php");

header("Content-Type:application/json");

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

    echo json_encode([
        "data" => $users,
        "numberRecords" => count($users),
        "message" => "ok"
    ]);
}

/**
 * API lấy thông tin của 1 User
 */
if (preg_match("/users\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $userId = $matches[1];
    $user = $USER->get($connect, $userId);

    if ($user) {
        echo json_encode([
            "data" => $user,
            "message" => "ok"
        ]);
    }
    else {
        echo json_encode([
           "message" => "User not found!!"
        ]);
    }
}

/**
 * API tạo User mới
 */
if ($url == "/users" &&  $_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $userId = $USER->create($connect, $input);

    if ($userId) {
        $input['id'] = $userId;
        $input['link'] = "/users/$userId";

        echo json_encode([
            "data" => $input,
            "message" => "ok"
        ]);
    }
    else {
        echo json_encode([
            "message" => "User already exist!!"
        ]);
    }
}

/**
 * Cập nhật 1 users
 */
if (preg_match("/users\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'PATCH') {
    $userId = $matches[1];
    $input = json_decode(file_get_contents("php://input"), true);

    $USER->update($connect, $userId, $input);

    echo json_encode([
        "message" => "ok"
    ]);
}

/**
 * Xoá 1 users
 */
if (preg_match("/users\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $userId = $matches[1];
    $USER->delete($connect, $userId);

    echo json_encode([
        "message" => "ok"
    ]);
}

/**
 * Đăng nhập
 */
if ($url == "/users/sign_in" &&  $_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $user = $USER->signIn($connect, $input);

    if ($user) {
        echo json_encode([
            "data" => $user,
           "message" => "ok"
        ]);
    }
    else {
        echo json_encode([
           "message" => "Wrong username or password!!"
        ]);
    }
}
