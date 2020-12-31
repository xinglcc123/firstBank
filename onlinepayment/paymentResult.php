<?php
require('../public_include.php');

$ip = getIp();
$get = $_GET;
$post = $_POST;
$input = file_get_contents('php://input');

// echo '接收 '.($input).'<br><br>';
$str = urldecode($input);
$str = mb_convert_encoding($str, "utf-8", "big5");
// echo '解碼 '.($str).'<br><br>';
parse_str($str, $array);
// echo 'json '.(json_encode($array, JSON_UNESCAPED_UNICODE)).'<br><br>';

// 加密方法 公式：SHA-256(授權結果狀態&錯誤碼&訂單編號&驗證參數&交易回應時間&特店代號&端末代號)
$MerchantID = '007807401279001';
$TerminalID = '90010001';
$appkey = '';
$xid = $array['xid'];	//信用卡交易序號
$seccode = $array['lidm'];	//訂單號
$error_desc = $array['errDesc'];	//失敗訊息
$CreditCardNo = $array['pan'];	//信用卡號
$credit_card_return = $input;	//銀行端回傳
if ($array['status'] == 0) {
	// 成功
	$sha256Str = $array['status'] . '&' . $array['lidm'] . '&' . $appkey . '&' . $array['authCode'] . '&' . $array['authRespTime'] . '&' . $MerchantID . '&'  . $TerminalID;
	// 驗證簽名
	$strSQL = "SELECT * FROM `orders` WHERE `seccode` = '$seccode'";
	$orders_data = $db->Execute($strSQL);
	$token = strtoupper(hash('sha256', $sha256Str));
	if ($token == $array['respToken'] && $orders_data[0]['total'] == $array['authAmt']) {
		// 寫入3D認證交易編號 跟 修改訂單狀態 跟 付款時間
		$time= time();
		$strSQL = "UPDATE `orders` SET `xid` = '$xid', `CreditCardNo` = '$CreditCardNo', `pay_state` = '1', `pay_time` = '$time', `credit_card_return` = '$credit_card_return' WHERE `orders`.`seccode` = $seccode;";
		$db->Execute($strSQL);
		//官方寄件人email
		$strSQL = "SELECT `phpmailer_google`, `paymentResult` FROM `web_setting`";
		$web_setting = $db->Execute($strSQL);
		$phpmailer_google = $web_setting[0]['phpmailer_google'];
		$strSQL = "SELECT * FROM `contact_mail_business` WHERE `title` LIKE '$phpmailer_google'";
		$contact_mail_business = $db->Execute($strSQL);
		//發送信
		$message = "付款時間：" . date('Y/m/d H:i:s', $orders_data[0]['pay_time']) . '</a><br/>';
		$message .= "已付款訂單編號：" . '<a href="https://abeito.com/backend/orders.php?func=update&id=' . $orders_data[0]['sn'] . '">' . $seccode . '</a>';
		$out = '';
		include("./google_mail.php");
		$title="=?UTF-8?B?".base64_encode('愛倍多 - 完成付款')."?=";
		$out = PHPMailera_message($message);
		$return = PHPMailera($web_setting[0]['paymentResult'], $title, $out, '', $contact_mail_business[0]['title'], trim(Encryption::ZxingCrypt($contact_mail_business[0]['password'],'decode')));
		echo 1;
	}
} else {
	// 失敗
	$sha256Str = $array['status'] . '&' . $array['errcode'] . '&' . $array['lidm'] . '&' . $appkey . '&' . $array['authRespTime'] . '&' . $MerchantID . '&'  . $TerminalID;
	// 驗證簽名
	$token = strtoupper(hash('sha256', $sha256Str));
	if ($token == $array['respToken']) {
		// 寫入3D認證交易編號 跟 修改訂單狀態
		$strSQL = "UPDATE `orders` SET `xid` = '$xid', `CreditCardNo` = '$CreditCardNo', `error_desc` = '$error_desc', `credit_card_return` = '$credit_card_return' WHERE `orders`.`seccode` = $seccode;";
		$db->Execute($strSQL);
	}
}

// 紀錄
$json = json_encode($array, JSON_UNESCAPED_UNICODE);
file_put_contents('paymentResult_'.date('Ymd').'.txt', date("Y-m-d H:i:s").' : '.$ip."\r\n".'get: '.json_encode($get)."\r\n".'post: '.json_encode($post)."\r\n".'json: '.json_encode($input)."\r\n".'接收: '.$input."\r\n".'解碼: '.$str."\r\n".'json: '.$json."\r\n", FILE_APPEND);

$returnData['orderNo'] = $array['lidm'];
$shoppingcart_05 = '../shoppingcart_05.php';

// 自動轉跳頁面
$myFormData = '<form id="myForm" action="'.$shoppingcart_05.'" method="GET">';
foreach ($returnData as $key => $value) {
	$myFormData .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
}
$myFormData .= '<script>document.getElementById("myForm").submit();</script></form>';
echo $myFormData;

function getIp(){
	if (!empty($_SERVER["HTTP_CLIENT_IP"])){
		$ip = $_SERVER["HTTP_CLIENT_IP"];
	}elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
		$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	}else{
		$ip = $_SERVER["REMOTE_ADDR"];
	}
	return $ip;
}

?>
