<?php
class Bill extends Model {
    protected $id;

    protected $userId;

    protected $name; // tên người nhận

    protected $telephone;

    protected $address;

    protected $timeCreate; // thời gian tạo đơn hàng

    protected $status; // tình trạng đơn hàng
}