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
        $sql = "Select * from notifications, bills where bills.id = notifications.bill_id and isadmin = 'no' and bills.user_id = '{$userId}'";
        $statement = $connect->prepare($sql);

        try {
            $statement->execute();
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "status" => 500,
                "message" => $e->getMessage()
            ]);
            exit();
        }
        $statement->setFetchMode(PDO::FETCH_ASSOC);

//        $notifications =  $statement->fetchAll();
        http_response_code(200);
        echo json_encode([
            "data" => $statement->fetchAll(),
            "status" => "200",
            "message" => "ok",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    }
    if($Notification->checkIsAdmin()){
        $sql = "Select * from notifications, bills where bills.id = notifications.bill_id and isadmin = 'yes'";
        $statement = $connect->prepare($sql);

        try {
            $statement->execute();
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "status" => 500,
                "message" => $e->getMessage()
            ]);
            exit();
        }
        $statement->setFetchMode(PDO::FETCH_ASSOC);

//        $notifications =  $statement->fetchAll();
        http_response_code(200);
        echo json_encode([
            "data" => $statement->fetchAll(),
            "status" => "200",
            "message" => "ok",
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    } else {

    }
}
