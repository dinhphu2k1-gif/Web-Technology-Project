<?php

class User extends Model
{
    private $username; //string
    private $password; //string
    private $birthday;
    private $telephone;
    private $email;
    private $address;

    /**
     * Đăng nhập
     * @param $connect
     * @param $input
     * @return bool
     */
    function signIn($connect, $input) {
        $sql = "SELECT * FROM users WHERE username=:username AND password=:password;";

        $statement = $connect->prepare($sql);
        $statement->execute($input);

        if ($statement->fetch(PDO::FETCH_ASSOC)) return true;
        else return false;
    }
}