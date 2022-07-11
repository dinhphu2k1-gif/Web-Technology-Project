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
        Response::responseInfo(500, "Access denied!!");
        exit();
    }

    Response::responseData(200, "ok", $data);
}
