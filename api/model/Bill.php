<?php
class Bill extends Model {
    protected $id;

    protected $userId;

    protected $name; // tên người nhận

    protected $telephone;

    protected $address;

    protected $timeCreate; // thời gian tạo đơn hàng

    protected $status; // tình trạng đơn hàng

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

    /**
     * Chuyển các sản phẩm từ giỏ hàng sang hoá đơn
     * @param $connect
     * @param $userId
     * @param $billId
     * @return void
     */
    function addProducts($connect, $userId, $billId) {
        $sql = "INSERT INTO bill_details (bill_id, product_id, quantity, total_price)
                SELECT {$billId}, cd.product_id, cd.quantity, cd.total_price
                FROM carts AS c 
                INNER JOIN cart_details AS cd ON c.id = cd.cart_id
                WHERE c.user_id = $userId;
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
    }

    function getOrder($connect, $userId, $orderId)
    {
        $sql = "SELECT * FROM bills 
                WHERE 
        ";
    }
}