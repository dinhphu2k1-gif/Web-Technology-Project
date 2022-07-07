<?php

class Response{
    public static function responseInfo($status, $message){
        http_response_code($status);
        echo json_encode([
            "status" => $status,
            "message" => $message,
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    }
    public static function responseMergedData($status, $message,$data){
        http_response_code($status);
        $smt = array(
            "status" => $status,
            "message" => $message,
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]);
        $jsondata = array_merge($data, $smt);
        echo json_encode($jsondata);
    }
    public static function responseData($status, $message,$data){
        http_response_code($status);
        echo json_encode([
            "data" => $data,
            "status" => $status,
            "message" => $message,
            "time" => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        ]);
    }
}