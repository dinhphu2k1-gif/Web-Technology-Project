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
            Response::responseInfo(500, "Some thing wrong when execute statement!!");
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
                WHERE user_id = :userId
                ORDER BY time_create DESC;
        ";
        $statement = $connect->prepare($sql);
        $statement->bindValue(':userId', $userId);
        try {
            $statement->execute();
        } catch (PDOException $e) {
            Response::responseInfo(500, "Some thing wrong when execute statement!!");
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
                WHERE bill_id = :billId;
                ";

        $statement = $connect->prepare($sql);
        $statement->bindValue(':billId', $billId);
        try {
            $statement->execute();
        } catch (PDOException $e) {
            Response::responseInfo(500, "Some thing wrong when execute statement!!");
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
                SELECT :billId, cd.product_id, cd.quantity, cd.total_price
                FROM carts AS c 
                INNER JOIN cart_details AS cd ON c.id = cd.cart_id
                WHERE c.user_id = :userId;
        ";
        $statement = $connect->prepare($sql);
        $statement->bindValue(':billId', $billId);
        $statement->bindValue(':userId', $userId);
        try {
            $statement->execute();
        } catch (PDOException $e) {
            Response::responseInfo(500, "Some thing wrong when execute statement!!");
            exit();
        }
    }

//    function getOrder($connect, $userId, $orderId)
//    {
//        $sql = "SELECT * FROM bills
//                WHERE
//        ";
//    }
    /**
     * cập nhật trạng thái đơn đặt hàng.
     * @param $connect
     * @param $id
     * @param $status
     * @return boolean
     */
    public function updateStatus($connect, $id, $status): bool{
        $bill = $this->get($connect, $id);
        if($bill){
            $sql = "UPDATE `bills` SET status = :status where id = :id;";
            $statement = $connect->prepare($sql);
            try {
                $statement->bindValue(':status', $status);
                $statement->bindValue(':id', $id);
                $statement->execute();
                return true;
            } catch (Exception $e){
                echo $e->getMessage();
                Response::responseInfo(404, "Bill are not updated!!");
                return false;
            }
        } else {
            Response::responseInfo(404, "Bill not found!!");
            return false;
        }
    }
}