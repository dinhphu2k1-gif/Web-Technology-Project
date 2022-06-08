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
 * API lấy toàn bộ thông tin Users
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
    $input = $_POST;

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

if (preg_match("/users\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $userId = $matches[1];
    $message = $USER->delete($connect, $userId);

    echo json_encode($message);
}

if ($url == "/sign_in" &&  $_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = $_POST;

    if ($USER->signIn($connect, $input)) {
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
