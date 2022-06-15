<?php

class Bill_Detail extends Model
{
    protected $id;

    protected $orderId;

    protected $productId;

    protected $quantity;

    protected $totalPrice;

    /**
     * Lấy thông tin của các đơn hàng dành cho Admin
     * @param $connect
     * @return mixed|void
     */
    function getAll($connect)
    {
        $sql = "SELECT *
                FROM bills 
                ORDER BY status DESC, time_create DESC;
            ";
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

    /**
     * Lấy thông tin của các đơn hàng dành cho User
     * @param $connect
     * @param $userId
     * @return void
     */
    function getAllBills($connect, $userId) {
        $sql = "SELECT * FROM bills 
                WHERE user_id = {$userId}
                ORDER BY time_create DESC;
        ";
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

    /**
     * Lấy danh sách sản phẩm trong order
     * @param $connect
     * @param $billId
     * @return void
     */
    function getProducts($connect, $billId)
    {
        $sql = "SELECT * FROM bill_details
                WHERE bill_id = {$billId};
                ";

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

    function getOrder($connect, $userId, $orderId)
    {
        $sql = "SELECT * FROM bills 
                WHERE 
        ";
    }
}