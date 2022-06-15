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
    function getAllProducts($connect, $userId)
    {
        $sql = "SELECT cd.*
                FROM carts AS c
                INNER JOIN cart_details AS cd ON c.id = cd.cart_id
                WHERE c.user_id = {$userId};
                ";
        $statement = $connect->prepare($sql);
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_ASSOC);

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
                WHERE cd.cart_id = {$cartId} AND cd.product_id = {$productId};
                ";
        $statement = $connect->prepare($sql);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }


    /**
     * Xoá toàn bộ sản phẩm trong giỏ hàng
     * @param $connect
     * @param $userId
     * @return void
     */
    function deleteAll($connect, $userId) {
        $sql = "DELETE cd.*
                FROM cart_details AS cd 
                INNER JOIN carts AS c ON cd.cart_id = c.id
                WHERE c.user_id = {$userId};
                ";

        $statement = $connect->prepare($sql);
        $statement->execute();
    }
}