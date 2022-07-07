<?php
require_once (ROOT. "/api/model/Notification.php");
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
$Notification = new Notification();

$url = $_SERVER['REQUEST_URI'];
// nếu url chưa có dấu "/" thì thêm vào đầu.
if (strpos($url, "/") !== 0) {
    $url = "/$url";
}

if ($url == '/notifications' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $userId = $Notification->getIdUser();
    if($userId){
        $data = $Notification->getAllNotifications($connect, $userId);
    } else if($Notification->checkIsAdmin()){
        $data =  $Notification->getAllNotifications($connect, null);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "500",
            "message" => "Access denied!!",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
        exit();
    }
    http_response_code(200);
    echo json_encode([
        "data" => $data,
        "status" => "200",
        "message" => "ok",
        "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
    ]);
}
