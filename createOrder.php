<?php
include 'creditCardApi.php';
$api = new CreditCardApi;

// 提單
$lidm = $_POST['lidm'];
$return = $api -> createOrder('80740127', '007807401279001', '90010001', 'biofast', $lidm, '10');
echo $return;

?>