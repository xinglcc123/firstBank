<?php

class CreditCardApi
{
	public function __Construct($provider = 'creditCard')
	{
		$this->appkey = '';	//密鑰
		$this->online = 'https://www.focas.fisc.com.tw/FOCAS_WEBPOS/online/';	//信用卡網路收單網址
		$this->notifyUrl = 'https://www.abeito.com/onlinepayment/paymentResult05.php';	//回調
	}

	public function createOrder($orderNo, $amount) { 
		$postData = array(
			"merID" => '80740127',							//(型態:N,最大長度10位)網站特店自訂代碼(請注意merID與MerchantID不同)
			"MerchantID" => '007807401279001',				//(型態:AN,固定長度15位)收單銀行授權使用的特店代號(由收單銀行編製提供)
			"TerminalID" => '90010001',						//(型態:AN,固定長度8位)收單銀行授權使用的機台代號(由收單銀行編製提供)
			"MerchantName" => 'biofast',					//(型態:ANS,最大長度60位)特店網站或公司名稱，僅供顯示。行動支付交易，最大長度12位。銀聯交易限定僅能為英、數字、空白及『-』，最大長度25位。
			// "customize" => 0,							//(型態:AN,固定長度1位)客製化付款授權網頁辨識碼。0表不使用客製化授權頁,1表使用第一個版本,2表使用第二個版本,3表使用第三個版本,4表使用第四個版本
			"lidm" => $orderNo,								//交易訂單編號，建議訂單編號不可重複編號。
			"purchAmt" => $amount,							//交易金額。
			// "CurrencyNote" => 'test',					//註記說明，僅供顯示。
			// "AutoCap" => '0',							//是否自動轉入請款檔作業。0 表示不轉入請款檔(預設)1 表示自動轉入請款檔
			"AuthResURL" => $this->notifyUrl,				//授權結果回傳網址。
			// "frontFailUrl" => $this->notifyUrl,			//銀聯網路UPOP交易失敗，返回商戶跳轉網址。
			// "PayType" => 0,								//交易類別碼。0 表示本筆交易為一般交易(預設) 1 表示本筆交易為分期交易 2 表示本筆交易為紅利交易
			// "PeriodNum" => 1,							//分期交易之期數。
			// "BonusActionCode" => 1,						//紅利交易活動代碼。
			"LocalDate" => date("Ymd"),						//購買地交易日期(yyyymmdd)(預設為系統日期)。
			"LocalTime" => date("His"),						//購買地交易時間(HHMMSS)(預設為系統時間)。
			// "reqToken" => '',							//交易驗證碼。
			// "subMerchID" => 'no',						//次特店代號。
			"enCodeType" => 'UTF-8',						//網頁編碼格式(預設為BIG5)，若使用UTF-8進行編碼，請再傳送的頁面中增加一輸入欄位enCodeType，值設定為UTF-8。
			"timeoutDate" => date("Ymd"),					//設定交易逾時日期(yyyymmdd)。
			"timeoutTime" => date("His", strtotime('+3 mouth')),				//設定交易逾時起始時間(HHMMSS)。
			"timeoutSecs" => 600,							//交易逾時秒數，最大值為600秒。
			// "mobileNbr" => '0912345678',					//電話號碼。
			// "walletVerifyCode" => date("His", strtotime('+11 second')),		//網路交易驗證碼。
			// "isSynchronism" => '0',						//同步/非同步標記。 0 表示本筆交易為同步交易 1 表示本筆交易為非同步交易
			// "lagSelect" => '0',							//預設顯示語系。 0或其他 表示中文(繁) 1 表示中文(简) 2 表示English 3 表示日本語
		);

		// SHA-256(訂單編號&交易金額&驗證參數&特店代號&端末代號&交易時間)
		$sha256Str = $postData['lidm'] . '&' . $postData['purchAmt'] . '&' . $this->appkey . '&' . $postData['MerchantID'] . '&' . $postData['TerminalID'] . '&' . $postData['LocalDate'] . $postData['LocalTime'];
		// echo hash('sha256', $sha256Str);
		$postData['reqToken'] = hash('sha256', $sha256Str);
		
		$userIp = $this->getIp();
		file_put_contents('abeito_log_'.date('Ymd').'.txt', date('H:i:s') . " _ " . $userIp . "\r\n簽名前：" . $sha256Str  . "\r\n送出：" . json_encode($postData) . "\r\n\r\n", FILE_APPEND); 

		// $strHtml = '<html>
		// <head>
		// 	<meta charset="utf-8">
		// </head>
		// 	<form method="post" action="' . $this->online . '">';
		// 		foreach ($postData as $key => $value){
		// 			if ($key == 'lidm') {
		// 				$strHtml .= '您的訂單編號為：<input type="text" name="' . $key . '" value="' . $value . '"><br>';
		// 			} else {
		// 				$strHtml .= '<input type="hidden" name="' . $key . '" value="' . $value . '">';
		// 			}
		// 		}
		// 	$strHtml .= '<p><input type="submit" value="前往付款" ><br/></p>
		// 	</form>
		// </html>';
		// return $strHtml;

		$myFormData = '<form id="myForm" action="'.$this->online.'" method="POST">';
			foreach ($postData as $key => $value) {
				$myFormData .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
			}
		$myFormData .= '<script>document.getElementById("myForm").submit();</script></form>';
		echo $myFormData;
	}
		
	public function getIp(){
		if (!empty($_SERVER["HTTP_CLIENT_IP"])){
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		}elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}elseif(!empty($_SERVER["REMOTE_ADDR"])){
			$ip = $_SERVER["REMOTE_ADDR"];
		}else{
			$ip = "Unknown";
		}
		return $ip;
	}
	
}
