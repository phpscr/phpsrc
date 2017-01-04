<?PHP
/**
*
*  Copyright (c) 2006  phpwind  All rights reserved.
*  Support : xuanyan1983@gmail.com
*  lastedit: 
*/
require_once('global.php');
require_once(R_P.'require/header.php');
if($type=='user'){
	require_once PrintEot('pay99bill');footer();
}else{
	include_once(D_P.'data/bbscache/ol_config.php');
	if(!$ol_onlinepay){
		Showmsg($ol_whycolse);
	}
	if(!$ol_99bill || !$ol_99billcode){
		Showmsg('olpay_seterror');
	}
	$merchant_id = trim($merchant_id);	//商户编号
	$orderid=trim($orderid);
	$orderidtrue = substr($orderid,0,30);			//交易订单编号[商户网站]
	$amount = trim($amount);				//交易金额
	$date = trim($date);					//交易日期
	$succeed = trim($succeed);			//交易结果，"Y"表示成功，"N"表示失败
	$mymac = trim($mac);
	$text = "merchant_id=".$merchant_id."&orderid=".$orderid."&amount=".$amount."&date=".$date."&succeed=".$succeed."&merchant_key=".$ol_99billcode;  
	$mac = md5($text); 
	if (strtoupper($mac)==strtoupper($mymac) && $succeed=='Y'){
		$rt = $db->get_one("SELECT c.*,m.username FROM pw_clientorder c LEFT JOIN pw_members m USING(uid) WHERE order_no='$orderidtrue'");
		if($rt['state']=='0'){
			if($rt['number'] != $amount){
				Showmsg('gross_error');
			}
			!$db_rmbrate && $db_rmbrate=10;
			$currency = $rt['number'] * $db_rmbrate;
			$number   = $rt['number'];
			$db->update("UPDATE pw_memberdata SET currency=currency+'$currency' WHERE uid='$rt[uid]'");
			$db->update("UPDATE pw_clientorder SET state=2,descrip='已完成订单' WHERE order_no='$orderidtrue'");
			require_once(R_P.'require/tool.php');
			$logdata=array(
				'type'		=>	'olpay',
				'nums'		=>	0,
				'money'		=>	0,
				'descrip'	=>	'olpay_descrip',
				'uid'		=>	$rt['uid'],
				'username'	=>	$rt['username'],
				'ip'		=>	$onlineip,
				'time'		=>	$timestamp,
				'number'	=>	$number,
				'currency'	=>	$currency,
			);
			writetoollog($logdata);

			require_once(R_P.'require/msg.php');
			$message=array(
				$rt['username'],
				'',
				'olpay_title',
				$timestamp,
				"olpay_content_2",
				'',
				'SYSTEM'
			);
			writenewmsg($message,1);
			$getdb='';
			foreach($_POST as $key=>$value){
				$getdb .= $key."=".urlencode($value)."&";
			}
			$getdb .= 'date='.get_date($timestamp,'Y-m-d-H:i:s');
			$getdb .= '&site='.$_SERVER['HTTP_HOST'];
			@file("http://www.phpwind.com/pay/paypal.php?$getdb");
			require_once PrintEot('pay99bill');footer();
		}else{
			refreshto('userpay.php?','complete_list');
		}
	}else{
		exit("认证失败");
	}

}

?>



		
