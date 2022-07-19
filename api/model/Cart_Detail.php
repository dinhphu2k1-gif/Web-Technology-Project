<?php
class Cart_Detail extends Model
{
    protected $id;

    protected $cart_id;

    protected $product_id;

    protected $quantity;

    protected $price;

    /**
     * Lấy thông tin các sản phẩm trong 1 giỏ hàng của người dùng
     * @param $connect
     * @param $userId
     * @return mixed
     */
    function getAllProducts($connect, $cartId)
    {
        $sql = "SELECT cd.*
                FROM carts AS c
                INNER JOIN cart_details AS cd ON c.id = cd.cart_id
                WHERE c.id = :cartId;
                ";
        $statement = $connect->prepare($sql);
        $statement->bindValue(':cartId', $cartId);
        try {
            $statement->execute();
            $statement->setFetchMode(PDO::FETCH_ASSOC);
        } catch (Exception $e){
            echo $e->getMessage();
            Response::responseInfo(500, "Can't get cartDetail");
            exit();
        }

        return $statement->fetchAll();
    }

    /**
     * Tìm sản phẩm trong giỏ hàng bằng Product ID
     * @param $connect
     * @param $cartId
     * @param $productId
     * @return mixed
     */
    function findByProductId($connect, $cartId, $productId) {
        $sql = "SELECT cd.*
                FROM cart_details AS cd
                WHERE cd.cart_id = :cartId AND cd.product_id = :productId;
                ";
        $statement = $connect->prepare($sql);
        $statement->bindValue(":cartId", $cartId);
        $statement->bindValue(":productId", $productId);
        try {
            $statement->execute();
        } catch (Exception $e){
            Response::responseInfo(500, "Some thing wrong!!");
            exit();
        }
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy cartId bằng userId
     * @param $connect
     * @param $userId
     * @return void
     */
    function getCartId($connect, $userId){
        $sql = "select id from carts where user_id = :userId";
        $statement = $connect->prepare($sql);
        $statement->bindValue(':userId', $userId);
        try {
            $statement->execute();
        } catch (Exception $e){
            Response::responseInfo(500, "Something error when execute");
            exit();
        }
        return $statement->fetch();
    }


    /**
     * Xoá toàn bộ sản phẩm trong giỏ hàng
     * @param $connect
     * @param $userId
     * @return void
     */
    function deleteAll($connect, $cartId) {
        $sql = "DELETE cd.*
                FROM cart_details AS cd 
                INNER JOIN carts AS c ON cd.cart_id = c.id
                WHERE c.id = :cartId;
                ";
        $statement = $connect->prepare($sql);
        try {
            $statement->bindValue(":cartId", $cartId);
            $statement->execute();
        } catch (Exception $e) {
            Response::responseInfo(500, "Delete failed");
            exit();
        }
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ
     * @param $connect
     * @param $cartId
     * @param $productId
     * @param $input
     * @return void
     */
    function updateCart($connect, $cartId, $productId, $input){
        $params = array();
        foreach ($input as $key => $value) {
            $params[] = "$key=:$key";
        }

        $sql = "UPDATE {$this->getTable()} SET "
            . implode(', ', $params)
            . " WHERE cart_id=:cartId and product_id = :productId;";
        $values = array_merge($input, array("cart_id" => $cartId, "product_id" => $productId));
        $statement = $connect->prepare($sql);
        try {
            $statement->execute($values);
        } catch (Exception $e) {
            Response::responseInfo(500, "Update failed");
            exit();
        }
    }

    /**
     * Xóa một sản phẩm ra khỏi giỏ
     * @param $connect
     * @param $cartId
     * @param $productId
     * @return void
     */
    function removeProduct($connect, $cartId, $productId){
        $sql = "DELETE FROM {$this->getTable()} WHERE cart_id = :cartId and product_id = :productId;";
        $statement = $connect->prepare($sql);
        $statement->bindValue(":cartId", $cartId);
        $statement->bindValue(":productId", $productId);
        try {
            $statement->execute();
        } catch (Exception $e) {
            Response::responseInfo(500, "Remove failed");
        }
    }
}