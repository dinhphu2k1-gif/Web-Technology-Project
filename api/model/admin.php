<?php
class Admin extends Model {
    protected $username;
    protected $password;
    protected $level;

    /**
     * Đăng nhập
     * @param $connect
     * @param $input
     * @return bool
     */
    function signIn($connect, $input) {
        $sql = "SELECT * FROM admins WHERE username=:username AND password=:password;";

        $statement = $connect->prepare($sql);
        $statement->execute($input);

        if ($statement->fetch(PDO::FETCH_ASSOC)) return true;
        else return false;
    }
}