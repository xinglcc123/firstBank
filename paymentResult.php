<?php
include 'creditCardApi.php';
$api = new CreditCardApi;
echo '付款資料認證中請稍等。<br><br>';

// 回調
$string = $_POST['bankReturn'];
$return = $api -> paymentResult($string);
echo $return;

?>
