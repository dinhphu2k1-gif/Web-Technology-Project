<?php
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class Model
{
    protected $_model;
    protected $_table;

    public function __construct()
    {
        $this->_model = get_class($this);
        $this->_table = strtolower($this->_model) . "s";
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * @param mixed $model
     */
    public function setModel($model)
    {
        $this->_model = $model;
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     * @param mixed $table
     */
    public function setTable($table)
    {
        $this->_table = $table;
    }

    /**
     * Lấy toàn bộ thông tin từ các đối tượng
     * @param $connect
     * @return mixed
     */
    function getAll($connect)
    {
        $sql = "SELECT * FROM {$this->_table};";
        $statement = $connect->prepare($sql);

        try {
            $statement->execute();
        } catch (PDOException $e) {
            Response::responseInfo(500, "Something wrong when executing statement");
            exit();
        }
        $statement->setFetchMode(PDO::FETCH_ASSOC);

        return $statement->fetchAll();
    }

    /**
     * Lấy thông tin từ 1 đối tượng dựa trên id
     * @param $connect
     * @param $id
     * @return array|string[]
     */
    function get($connect, $id)
    {
        $sql = "SELECT * FROM {$this->_table} WHERE id=:id;";
        $statement = $connect->prepare($sql);
        $statement->bindValue(':id', $id);
        try {
            $statement->execute();
        } catch (PDOException $e) {
            Response::responseInfo(500, "Something wrong when executing statement");
            exit();
        }

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Tạo 1 đối tượng mới
     * @param $connect
     * @param $input
     * @return mixed|string
     */
    function create($connect, $input)
    {
        $params = array();
        foreach ($input as $key => $value) {
            $params[] = ':' . $key;
        }

        $sql = "INSERT INTO $this->_table
                VALUES (NULL," . implode(', ', $params) . ");";
        $statement = $connect->prepare($sql);
        try {
            $statement->execute($input);
        } catch (PDOException $e) {
            echo $e->getMessage();
            Response::responseInfo(500, "Something wrong when executing statement");
            exit();
        }

        return $connect->lastInsertId();
    }

    /**
     * @param $connect
     * @param $id
     * @param $input
     * @return void
     */
    function update($connect, $id, $input)
    {
        $params = array();
        foreach ($input as $key => $value) {
            $params[] = "$key=:$key";
        }

        $sql = "UPDATE $this->_table SET "
            . implode(', ', $params)
            . " WHERE id= :id;";

        $statement = $connect->prepare($sql);
        $input = array_merge($input, array("id" => $id));
        try {
            $statement->execute($input);
        } catch (PDOException $e) {
            Response::responseInfo(500, "Updated failed!!");
            exit();
        }
    }

    /**
     * Xoá một đối tượng
     * @param $connect
     * @param $id
     * @return void
     */
    function delete($connect, $id)
    {
        $sql = "DELETE FROM $this->_table WHERE id=:id;";
        $statement = $connect->prepare($sql);
        $statement->bindValue(':id', $id);
        try {
            $statement->execute();
        } catch (PDOException $e) {
            Response::responseInfo(500, "Something wrong when executing statement");
            exit();
        }
    }

    /**
     * Đăng nhập
     * @param $connect
     * @param $input
     * @return bool
     */
    function signIn($connect, $input) {
        $sql = "SELECT * FROM $this->_table WHERE username=:username;";

        $statement = $connect->prepare($sql);
        $statement->bindValue(":username", $input['username']);
        try {
            $statement->execute();
        } catch (PDOException $e) {
            Response::responseInfo(500, "Something wrong when executing statement");
            exit();
        }

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if (empty($result['password'])) return false;

        if (password_verify($input['password'], $result['password'])) return $result;
        else return false;
    }

    /**
     * Tìm thông in về user hoặc admin
     * @param $connect
     * @param $username
     * @return mixed
     */
    function findByUser($connect, $username)
    {
        $sql = "SELECT * FROM $this->_table WHERE username = :username;";
        $statement = $connect->prepare($sql);
        $statement->bindValue('username', $username);
        try {
            $statement->execute();
        } catch (PDOException $e) {
            Response::responseInfo(500, "Something wrong when executing statement");
            exit();
        }
        return $statement->fetch(PDO::FETCH_ASSOC);
    }
    /**
     * Hàm lấy id của người dùng nếu có
     * @return id
     */
    function getIdUser() {
        $header = getallheaders();

        if (!empty($header['Authorization'])) {
            $jwt = $header['Authorization'];
        }
        else {
            return false;
        }
        try {
            $decode_data = JWT::decode($jwt, new Key(JWT_KEY, JWT_ALG));

            if (!$decode_data->is_admin) {
                return $decode_data->id;
            }
        } catch (Exception $e) {
            return false;
        }
    }
    /**
     * Kiểm tra xem User có phải là Admin hay không
     * @return void
     */
    function checkIsAdmin() {
        $header = getallheaders();

        if (!empty($header['Authorization'])) {
            $jwt = $header['Authorization'];
        }
        else {
            Response::responseInfo(401, "Need access token!!");
            exit();
        }

        try {
            $decode_data = JWT::decode($jwt, new Key(JWT_KEY, JWT_ALG));

            if (!$decode_data->is_admin) {
                Response::responseInfo(401, "Access Denied!!");
                exit();
            }
        } catch (Exception $e) {
            Response::responseInfo(401, "Decode date occur error");
            exit();
        }
        return true;
    }

    /**
     * Kiểm tra xem User có quyền sử dụng API hay không
     * @param $id
     * @return void
     */
    function checkUser($id) {
        $header = getallheaders();

        if (!empty($header['Authorization'])) {
            $jwt = $header['Authorization'];
        }
        else {
            Response::responseInfo(401, "Need access token!!");
            exit();
        }

        try {
            $decode_data = JWT::decode($jwt, new Key(JWT_KEY, JWT_ALG));

            // Nếu User không phải là Admin hoặc cùng ID thì không được phép lấy dữ liệu
            if (!$decode_data->is_admin) {
                if ($decode_data->id != $id) {
                    Response::responseInfo(401, "Access Denied!!");
                    exit();
                }
            }
        } catch (Exception $e) {
            Response::responseInfo(401, "Decode data occurred error!!");
            exit();
        }
    }
}