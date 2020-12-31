<?php
require('public_include.php');
// Func::msg_box('系統維護中，請見諒！');
// Func::go_to(-1);
// exit;
$member_id = $_SESSION[SESSION_NAME]['website']['member_id'];
if($member_id==''){
  Func::msg_box('請先登入會員！');
  Func::go_to('index.php?func=login');
  exit;
}
$strSQL = "SELECT * FROM website_member WHERE id='$member_id'";
$website_member = $db->Execute($strSQL);
// print_r($website_member);
// exit;
if($website_member[0]['id']==''){
  Func::msg_box('帳號不存在！');
  Func::go_to('index.php?func=register');
  exit;
}

$strSQL = "SELECT * FROM orders WHERE cust_id='$member_id' AND seccode='".$_GET['orderNo']."'";
$orders = $db->Execute($strSQL);
if($orders[0]['sn']==''){
    Func::msg_box('訂單不存在或無權限觀看！');
    Func::go_to('index.php');
    exit;
}
// $at = '20131024T009&200&1qaz2wsx3edc4rfv&950876543219001&90010001&20131024141500';
// echo strtoupper(hash('sha256', $at));
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
	<?php include 'include/meta.php'; ?>
</head>
<body>
	<!-- templateA header -->
	<?php include 'include/header.php'; ?>

	<!-- content -->
	<div class="l-container">
		<header class="l-header">
			<h1 class="c-pagetitle c-pagetitle--s1 l-pagetitle"><span>此為測試付款頁面</span></h1>
			<?php if ($orders[0]['payment'] == '信用卡付款' && $orders[0]['pay_state'] == 1) { ?>
			<h1 class="c-pagetitle c-pagetitle--s1 l-pagetitle"><span>已完成信用卡付款</span></h1>
			<?php } else { ?>
			<h1 class="c-pagetitle c-pagetitle--s1 l-pagetitle"><span>尚未完成付款</span></h1>
			<div class="thanks">
				<p>付款失敗可能原因：<?=$orders[0]['error_desc']?></p>
			</div>
			<?php } ?>
		</header>
		<main class="l-main">
			<div class="thanks">
				<h3>購買清單</h3>
				<?php if($orders[0]['payment']=='ATM轉帳' || $orders[0]['payment']=='郵政劃撥'): ?>
				<div class="thanks__box box">
					<div class="box__title">付款資訊</div>
					<div class="box__info">
						<?php if($orders[0]['payment']=='ATM轉帳'): ?>
							<?=nl2br($web_setting[0]['atm_shop_pay']) ?>
						<?php endif; ?>
						<?php if($orders[0]['payment']=='郵政劃撥'): ?>
							<?=nl2br($web_setting[0]['shop_pay']) ?>
						<?php endif; ?>
					</div>
				</div>
				<?php endif; ?>
			</div>

			<ul class="shopList01">
			    <li class="shopList01__head">
			        <div class="shopList01__photo">
			            <div class="shopList01__pic">商品名稱</div>
			        </div>
			        <div class="shopList01__others">
			            <div class="shopList01__name">&nbsp;</div>
                        <div class="shopList01__size">尺寸</div>
			            <div class="shopList01__num">數量</div>
			            <div class="shopList01__price">單價</div>
			            <div class="shopList01__count">小計</div>
			        </div>
			    </li>
			    <?php
                    $strSQL = "SELECT * FROM orders_item WHERE order_sn='".$orders[0]['sn']."' AND ipp='0'";
                    $orders_item = $db->Execute($strSQL);
                    foreach ($orders_item as $orders_items) :
                        //商品
                        $strSQL = "SELECT * FROM product WHERE id='".$orders_items['item_sn']."'";
                        $product = $db->Execute($strSQL);
                ?>
			    <li class="shopList01__item">
			        <div class="shopList01__photo">
			            <div><a href="products_view?id=<?=$product[0]['id']?>" target="_blank"><img alt="<?=$product[0]['title']?>" src="webimages/<?=$product[0]['img']?>"></a></div>
			        </div>
			        <div class="shopList01__others">
			            <div class="shopList01__name" data-title="商品名稱 :"><a href="products_view?id=<?=$product[0]['id']?>"><?=$product[0]['title']?></a></div>
						<div class="shopList01__size" data-title="尺寸 :"><?=$orders_items['size']?></div>
						<div class="shopList01__num" data-title="數量 :"><?=$orders_items['num']?></div>
			            <!-- mymodify02 -->
						<?php if ($_SESSION[SESSION_NAME]['website']['member_companyNo']!=''){?>
							<div class="shopList01__price" data-title="單價 :"><span>NT.<?=($orders_items['price'])?></span></div>
			         		<div class="shopList01__count" data-title="小計 :"><span>NT.<?=($orders_items['price']*$orders_items['num'])?></span></div>
						<?php }else{?>
							<div class="shopList01__price" data-title="單價 :"><span>NT.<?=($orders_items['price'])?></span></div>
			            	<div class="shopList01__count" data-title="小計 :"><span>NT.<?=($orders_items['price']*$orders_items['num'])?></span></div>
						<?php } ?>
			            <!-- <div class="shopList01__price" data-title="單價 :"><span>NT.<?=($orders_items['price'])?></span></div>
			            <div class="shopList01__count" data-title="小計 :"><span>NT.<?=($orders_items['price']*$orders_items['num'])?></span></div> -->
						<!-- mymodify02 -->
			        </div>
			    </li>
				<?php
					// mymodify02
					if ($_SESSION[SESSION_NAME]['website']['member_companyNo']!=''){
						$money += $orders_items['price']*$orders_items['num'];
					} else {
						$money += $orders_items['price']*$orders_items['num'];
					}
					// $money += $orders_items['price']*$orders_items['num'];
					// mymodify02
                    endforeach;
                ?>
			</ul>
			<hr class="hr hr--default" />
			<!-- total -->
			<div class="c-total">
				<div class="c-total__tr">
					<span class="c-total__th">總金額</span>
					<span class="c-total__td">NT.<?=$money?></span>
				</div>
				<div class="c-total__tr">
					<span class="c-total__th">運費</span>
					<span class="c-total__td">NT.<?=$orders[0]['ship']?></span>
				</div>
				<div class="c-total__tr c-total__tr--ft">
					<span class="c-total__th">應付金額 </span>
					<span class="c-total__td"><i class="c-total__price">NT.<?=$orders[0]['total']?></i></span>
				</div>
			</div>
			<!-- buyInfo -->
			<div class="buyInfo">
				<h2 class="c-pagetitle c-pagetitle--s2">
					<span>收件人資訊</span>
				</h2>
				<div class="l-box">
				<!-- form -->
				<div class="registerForm form form--outter">
			      <div class="formGroup">
			        <label>
			          <span class="formGroup__label">收件人姓名</span>
			          <div class="formGroup__input"><?=$orders[0]['for_name']?></div>
			        </label>
			      </div>
			      <div class="formGroup">
			        <label>
			          <span class="formGroup__label">電話</span>
			          <div class="formGroup__input"><?=trim(Encryption::ZxingCrypt($orders[0]['for_phone'],'decode'))?></div>
			        </label>
			      </div>
			      <div class="formGroup">
			        <label>
			          <span class="formGroup__label">E-mail</span>
			          <div class="formGroup__input"><?=$orders[0]['for_email']?></div>
			        </label>
			      </div>
			      <div class="formGroup">
			      	<label>
			      	  <span class="formGroup__label">地址</span>
						<?php if ($orders[0]['delivery_method'] == '超商(7-11,全家,萊爾富,OK)') { ?>
							<div class="formGroup__input"><?=$orders[0]['stName']?>：<?=$orders[0]['stAddr']?></div>
						<?php } else { ?>
							<div class="formGroup__input"><?=trim(Encryption::ZxingCrypt($orders[0]['for_address'],'decode'))?></div>
						<?php } ?>
			      	</label>
			      </div>
			      <div class="formGroup">
			      	<label>
			      	  <span class="formGroup__label">備註</span>
			      	  <div class="formGroup__input"><?=nl2br($orders[0]['note'])?></div>
			      	</label>
			      </div>
				</div>
				<!-- form end -->
				</div>
			</div>
			
			<h3 class="c-pagetitle c-pagetitle--s3"><span>付款方式</span></h3>
			<div class="l-box"><?=$orders[0]['payment']?></div>
			<h3 class="c-pagetitle c-pagetitle--s3"><span>配送方式</span></h3>
			<div class="l-box"><?=$orders[0]['delivery_method']?></div>
			<?php if ($orders[0]['delivery_method'] == '超商(7-11,全家,萊爾富,OK)') { ?>
				<div class="l-box"><?=$orders[0]['stName']?></div>
				<div class="l-box"><?=$orders[0]['stAddr']?></div>
				<div class="l-box"><?=$orders[0]['stTel']?></div>
			<?php } ?>
			<h3 class="c-pagetitle c-pagetitle--s3"><span>配送時段</span></h3>
			<div class="l-box"><?=$orders[0]['shipTime']?></div>
			<h3 class="c-pagetitle c-pagetitle--s3"><span>發票資訊</span></h3>
			<div class="l-box">
			<?php
				echo $orders[0]['ticktype'];
				if($orders[0]['ticktype']=='三聯式發票'){
					echo '&nbsp;&nbsp;&nbsp;&nbsp;公司抬頭:&nbsp;&nbsp;'.$orders[0]['tickName'];
					echo '&nbsp;&nbsp;&nbsp;&nbsp;統一編號:&nbsp;&nbsp;'.$orders[0]['tickNo'];
				}
			?>
			</div>

			<div class="l-control l-control--single text-center">
				<form name="createOrder" method="post" action="onlinepayment/createOrder.php">
					<a href="products.php" class="c-btn c-btn--s2">繼續購物</a>
					<?php if ($orders[0]['payment'] == '信用卡付款' && $orders[0]['pay_state'] == 0) { ?>
						<input type="hidden" name="orderNo" value="<?=$_GET['orderNo']?>">
						<input type="hidden" name="amount" value="<?=$orders[0]['total']?>">
						<input type="submit" value="重新付款" class="c-btn c-btn--s2">
					<?php } ?>
				</form>  
				<!-- <a href="onlinepayment/createOrder.php" class="c-btn c-btn--s2">立即付款</a> -->
			</div>
		</main>
	</div>

	<!-- templateA footer -->
	<?php include 'include/footer.php'; ?>
	<?php include 'include/f2e.php'; ?>
</body>
</html>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script language="javascript">
function ShowMeDate() {
　var Today=new Date();
　alert("今天日期是 " + Today.getFullYear()+ " 年 " + (Today.getMonth()+1) + " 月 " + Today.getDate() + " 日");
}
</script>

<script language="javascript">
function chk() {
}
</script>
