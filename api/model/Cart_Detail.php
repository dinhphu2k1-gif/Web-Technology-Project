<?php

class Cart_Detail extends Model
{
    protected $id;

    protected $cart_id;

    protected $product_id;

    protected $quantity;

    protected $price;

    //Override
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
}