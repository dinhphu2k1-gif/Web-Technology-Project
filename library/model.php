<?php

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
        $statement->execute();
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
        $sql = "SELECT * FROM {$this->_table} WHERE id=$id;";
        $statement = $connect->prepare($sql);
        $statement->execute();

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
                VALUE (NULL, " . implode(', ', $params) . ");";

        $statement = $connect->prepare($sql);
        try {
            $statement->execute($input);
        } catch (PDOException $e) {
            return null;
        }

        return $connect->lastInsertId();
    }

    function update($connect, $id, $input)
    {
        $params = array();
        foreach ($input as $key => $value) {
            $params[] = "$key=:$key";
        }

        $sql = "UPDATE $this->_table SET "
            . implode(', ', $params)
            . " WHERE id=$id;";

        $statement = $connect->prepare($sql);
        $statement->execute($input);
    }

    /**
     * Xoá một đối tượng
     * @param $connect
     * @param $id
     * @return void
     */
    function delete($connect, $id)
    {
        $sql = "DELETE FROM $this->_table WHERE id=$id;";
        $statement = $connect->prepare($sql);

        $statement->execute();
    }

}