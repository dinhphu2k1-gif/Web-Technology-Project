<?php

class User extends Model
{
    private $username; //string
    private $password; //string
    private $birthday; // format yyyy-MM-dd
    private $telephone;
    private $email;
    private $address;

    /**
     * Lấy thông tin của 1 khách hàng
     * @param $connect
     * @param $id
     * @return array|string[]
     */
    function get($connect, $id)
    {
        $sql = "SELECT users.*, carts.id AS cart_id
                FROM users 
                INNER JOIN carts ON users.id = carts.user_id
                WHERE users.id=$id;
                ";
        $statement = $connect->prepare($sql);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }
}