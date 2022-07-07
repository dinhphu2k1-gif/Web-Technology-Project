<?php

class Notification extends Model {
    protected $id;
    protected $bill_id;
    protected $isadmin;
    protected $message;

    public function init($bill_id, $message, $isadmin){
        $this->bill_id = $bill_id;
        $this->message = $message;
        $this->isadmin = $isadmin;
    }
    public function insertNotification($connect){
        $input = array("message" => $this->message, "bill_id" => $this->bill_id, "isadmin" => $this->isadmin, "CreatedAt" => date("Y/m/d h:i:s",time()));
        $this->create($connect, $input);
        return true;
    }
    /**
     * Hàm lấy tất cả thông báo.
     * @param $connect
     * @param $userId
     * @return void
     */
    public function getAllNotifications($connect, $userId){
        if(!$userId){
            $sql = "Select * from notifications, bills
                where bills.id = notifications.bill_id and isadmin = 'yes' 
                order by notifications.createdAt;";

        } else {
            $sql = "Select * from notifications, bills
                where bills.id = notifications.bill_id and isadmin = 'no' and bills.user_id = '{$userId}'
                order by notifications.createdAt;";
        }
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
        return $statement->fetchAll();
    }
}