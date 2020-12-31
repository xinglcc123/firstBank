<?php
include 'creditCardApi.php';
$api = new CreditCardApi;

// 提單
$orderNo = $_GET['orderNo'];
$amount = $_GET['amount'];
$return = $api -> createOrder($orderNo, $amount);
echo $return;

?>