<?php
require_once('global.php');
require_once(R_P.'require/tool.php');
include_once(D_P.'data/bbscache/creditdb.php');
!$windid && Showmsg('not_login');
list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);
$userdb   = $db->get_one("SELECT md.postnum,md.digests,md.rvrc,md.money,md.credit,md.currency,mb.deposit,mb.ddeposit FROM pw_memberdata md LEFT JOIN pw_memberinfo mb USING(uid) WHERE md.uid='$winduid'");
require_once(R_P.'require/credit.php');
$creditdb = GetCredit($winduid);
if(!$action){
	include_once(D_P.'data/bbscache/ol_config.php');
	if(!$ol_onlinepay){
		Showmsg($ol_whycolse);
	}
	if((!$ol_payto || !$ol_md5code) && (!$ol_paypal || !$ol_paypalcode) && (!$ol_99bill || !$ol_99billcode)){
		Showmsg('olpay_seterror');
	}
	require_once(R_P.'require/header.php');
	$rt = $db->get_one("SELECT hk_value FROM pw_hack WHERE hk_name='adminbankinfo'");
	$adminbankinfo = str_replace("\n","<br>",$rt['hk_value']);
	require_once PrintEot('userpay');footer();
}elseif($action == 'change'){
	require_once(R_P.'require/header.php');
	$query = $db->query("SELECT hk_name,hk_value FROM pw_hack WHERE hk_name='currrate1' OR hk_name='currrate2'");
	while($rt=$db->fetch_array($query)){
		$$rt['hk_name'] = unserialize($rt['hk_value']);
	}
	$rvrc_1 = $currrate1['rvrc']/10;
	$rvrc_2 = $currrate2['rvrc']/10;
	if(!$step){
		require_once(R_P.'require/header.php');
		list($db_moneyname,,$db_rvrcname,,$db_creditname,)=explode("\t",$db_credits);
		require_once PrintEot('userpay');footer();
	} elseif($type == 'currency1'){
		!is_array($changenum) && Showmsg('undefined_action');
		foreach($changenum as $key => $value){
			if($value && (!is_numeric($value) || $value < 0)){
				Showmsg('numerics_checkfailed');
			} else{
				$changenum[$key] = (int)$value;
			}
		}
		$sum = array_sum($changenum);
		$sum == 0 && Showmsg('empty_credit');
		$sum >  $userdb['currency'] && Showmsg('noenough_currency');
		$creditinfo = '';
		foreach($changenum as $key => $value){
			if($currrate1[$key] && $value){
				$addpoint = $value*$currrate1[$key];
				if(is_numeric($key)){
					$creditinfo .= $creditdb[$key][0].':'.$addpoint.' ';
					$db->pw_update(
						"SELECT uid FROM pw_membercredit WHERE uid='$winduid' AND cid='$key'",
						"UPDATE pw_membercredit SET value=value+'$addpoint' WHERE uid='$winduid' AND cid='$key'",
						"INSERT INTO pw_membercredit SET value='$addpoint',uid='$winduid',cid='$key'"
					);
					$db->update("UPDATE pw_memberdata SET currency=currency-'$value' WHERE uid='$winduid'");
				} elseif(in_array($key,array('rvrc','money','credit'))){
					$db->update("UPDATE pw_memberdata SET currency=currency-'$value',$key=$key+'$addpoint' WHERE uid='$winduid'");
					$key == 'rvrc' && $addpoint /= 10;
					$creditinfo .= ${'db_'.$key.'name'}.':'.$addpoint.' ';
				}
			}
		}
		$logdata=array(
			'type'		=>	'change',
			'descrip'	=>	'change_descrip_1',
			'creditinfo'=>	$creditinfo,
			'currency'	=>	$sum,
			'uid'		=>	$winduid,
			'username'	=>	$windid,
			'ip'		=>	$onlineip,
			'time'		=>	$timestamp,
		);
		writetoollog($logdata);
		refreshto("userpay.php?action=change",'operate_success');
	} elseif($type == 'currency2'){
		!is_array($changenum) && Showmsg('undefined_action');
		foreach($changenum as $key => $value){
			if($value && (!is_numeric($value) || $value < 0)){
				Showmsg('numerics_checkfailed');
			} else{
				$changenum[$key] = (int)$value;
			}
		}
		@array_sum($changenum) == 0 && Showmsg('empty_credit');
		foreach($changenum as $key => $value){
			if($currrate2[$key] && $value){
				$key == 'rvrc' && $value *= 10;
				$value = floor($value/$currrate2[$key])*$currrate2[$key];
				if(is_numeric($key)){
					$value > $creditdb[$key][1] && Showmsg('change_credit_error');
				} elseif(in_array($key,array('rvrc','money','credit'))){
					$value > $userdb[$key] && Showmsg('change_credit_error');
				}
			}
		}

		$creditinfo = '';
		foreach($changenum as $key => $value){
			if($currrate2[$key] && $value){
				$key == 'rvrc' && $value *= 10;
				$addpoint  = floor($value/$currrate2[$key]);
				$sum      += $addpoint;
				$value     = $addpoint*$currrate2[$key];
				if(is_numeric($key)){
					$creditinfo .= $creditdb[$key][0].':'.$value.' ';
					$db->update("UPDATE pw_membercredit SET value=value-'$value' WHERE uid='$winduid' AND cid='$key'");
					$db->update("UPDATE pw_memberdata SET currency=currency+'$addpoint' WHERE uid='$winduid'");
				} elseif(in_array($key,array('rvrc','money','credit'))){
					$db->update("UPDATE pw_memberdata SET currency=currency+'$addpoint',$key=$key-'$value' WHERE uid='$winduid'");
					$key == 'rvrc' && $value /= 10;
					$creditinfo .= ${'db_'.$key.'name'}.':'.$value.' ';
				}
			}
		}

		$logdata=array(
			'type'		=>	'change',
			'descrip'	=>	'change_descrip_2',
			'creditinfo'=>	$creditinfo,
			'currency'	=>	$sum,
			'uid'		=>	$winduid,
			'username'	=>	$windid,
			'ip'		=>	$onlineip,
			'time'		=>	$timestamp,
		);
		writetoollog($logdata);
		refreshto("userpay.php?action=change",'operate_success');
	}
} elseif ($action == 'pay'){

	include_once(D_P.'data/bbscache/ol_config.php');
	include(GetLang('other'));
	if(!$ol_onlinepay){
		Showmsg($ol_whycolse);
	}
	$number = (int)$number;
	if (!is_numeric($number) || $number < $db_rmblest){
		Showmsg('olpay_numerror');
	}
	$order_no = ($method-1).str_pad($winduid,10, "0",STR_PAD_LEFT).get_date($timestamp,'YmdHis').num_rand(5);

	$db->update("INSERT INTO pw_clientorder(order_no,uid,subject,body,price,number,date,state,descrip) VALUES('$order_no','$winduid','$lang[currency]','$lang[buy_currency]','1','$number','$timestamp','0','$lang[unpay_list]')");

	if($method==1){
		if(!$ol_paypal || !$ol_paypalcode){
			Showmsg('olpay_paypalerror');
		}
		$url  = "https://www.paypal.com/cgi-bin/webscr?";
		$para = array(
			'cmd'=>'_xclick',
			'invoice'=>$order_no,
			'business'=>$ol_paypal,
			'item_name'=>$lang['buy_currency'],
			'item_number'=>'phpw*',
			'amount'=>$number,
			'no_shipping'=>0,
			'no_note'=>1,
			'currency_code'=>'CNY',
			'bn'=>'phpwind',
			'charset'=>$db_charset
		);
		foreach($para as $key => $value){
			$url .= $key."=".urlencode($value)."&";
		}
		ObHeader($url);
	}elseif($method==2){
		if(!$ol_payto || !$ol_md5code){
			Showmsg('olpay_alipayerror');
		}
		$url  = "https://www.alipay.com/trade/direct_pay.htm?";
		$para = array(
			'cmd' => '0001',
			'subject' => $lang['currency'],
			'body' => $lang['buy_currency'],
			'order_no' => $order_no,
			'date'	=> get_date($timestamp),
			'price' => '1',
			'url' => '',
			'type' => '1',
			'number' => $number,
			'transport' => '3',
			'ordinary_fee' => '',
			'express_fee' => '',
			'readonly' => '',
			'buyer_msg' => '',
			'seller' => $ol_payto,
			'buyer' => '',
			'buyer_name' => '',
			'buyer_address' => '',
			'buyer_zipcode' => '',
			'buyer_tel' => '',
			'buyer_mobile' => '',
			'partner' => '8868',
		);
		foreach($para as $key => $value){
			if($value){
				$url     .= "$key=$value&";
				$acsouce .="$key$value";
			}
		}
		$url  .= 'ac='.md5($acsouce.$ol_md5code);
		ObHeader($url);
	}elseif($method==3){
		if(!$ol_99bill || !$ol_99billcode){
			Showmsg('olpay_pay99error');
		}
		$abillpost='https://www.99bill.com/webapp/receiveMerchantInfoAction.do';
		$merchant_id = $ol_99bill;
		$orderid = $order_no.$timestamp;
		$amount = $number;
		$currency = "1";
		$commodity_info =urlencode($lang['buy_currency']);
		$pname = urlencode($windid);
		$merchant_url = "{$db_bbsurl}/pay99bill.php";
	   //$pid_99billaccount="";
		$text="merchant_id=".$merchant_id."&orderid=".$orderid."&amount=".$amount."&merchant_url=".$merchant_url."&merchant_key=".$ol_99billcode;

	   $mac = strtoupper(md5($text));
		require_once(R_P.'require/header.php');
		require_once PrintEot('userpay');footer();
	}
} elseif($action == 'list'){
	$sqladd = "WHERE uid='$winduid'";
	if($state == 1){
		$sqladd .= " AND state=0 OR state=1";
	} elseif($state == 2){
		$sqladd .= " AND state=2";
	}

	include_once(R_P.'require/forum.php');
	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_clientorder $sqladd");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"userpay.php?action=list&state=$state&");

	$query = $db->query("SELECT * FROM pw_clientorder $sqladd ORDER BY date DESC $limit");
	while($rt=$db->fetch_array($query)){
		$rt['date'] = get_date($rt['date']);
		$orderdb[] = $rt;
	}
	require_once(R_P.'require/header.php');
	require_once PrintEot('userpay');footer();
} elseif($action == 'log'){
	if($keyword){
		$sqladd = " AND descrip LIKE '%$keyword%'";
		$urladd = 'keyword='.rawurlencode($keyword)."&";
	} else{
		$sqladd=$urladd='';
	}
	require_once(R_P.'require/forum.php');
	require_once(R_P.'require/bbscode.php');
	(!is_numeric($page) || $page<1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt	= $db->get_one("SELECT COUNT(*) AS sum FROM pw_toollog WHERE uid='$winduid' $sqladd");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"userpay.php?action=log&$urladd");

	$query = $db->query("SELECT * FROM pw_toollog WHERE uid='$winduid' $sqladd ORDER BY time DESC $limit");
	while($rt = $db->fetch_array($query)){
		$rt['time']   = get_date($rt['time']);
		$rt['descrip']= convert($rt['descrip'],array());
		$logdb[]	  = $rt;
	}
	require_once(R_P.'require/header.php');
	require_once PrintEot('userpay');footer();
} elseif($action == 'virement'){
	$query = $db->query("SELECT db_name,db_value FROM pw_config WHERE db_name LIKE 'cy\_%'");
	while($rt = $db->fetch_array($query)){
		$$rt['db_name'] = $rt['db_value'];
	}
	!$cy_virement && Showmsg('virement_closed');

	if(!$_POST['step']){
		require_once(R_P.'require/header.php');
		require_once PrintEot('userpay');footer();
	}elseif($_POST['step']==2){
		$rt		= $db->get_one("SELECT uid FROM pw_members WHERE username='$pwuser'");
		$touid	= $rt['uid'];
		if(!$rt){
			$errorname=$pwuser;
			Showmsg('user_not_exists');
		}
		if(!is_numeric($currency) || $currency < 0){
			Showmsg('illegal_nums');
		}
		if(!$pwpwd){
			Showmsg('empty_password');
		}
		if($cy_virelimit && $currency < $cy_virelimit){
			Showmsg('currency_limit');
		}
		$rt = $db->get_one("SELECT m.password,md.currency FROM pw_members m LEFT JOIN pw_memberdata md USING(uid) WHERE m.uid='$winduid'");
		if(md5($pwpwd) != $rt['password']){
			Showmsg('password_error');
		}
		$tax = round($currency * $cy_virerate/100);
		$needcurrency = $currency + $tax;
		if($rt['currency'] < $needcurrency){
			Showmsg('noenough_currency');
		}
		$db->update("UPDATE pw_memberdata SET currency=currency-'$needcurrency' WHERE uid='$winduid'");
		$db->update("UPDATE pw_memberdata SET currency=currency+'$currency' WHERE uid='$touid'");
		require_once(R_P.'require/tool.php');
		$logdata=array(
			'type'		=>	'vire',
			'nums'		=>	0,
			'money'		=>	0,
			'descrip'	=>	'vire_descrip',
			'uid'		=>	$winduid,
			'username'	=>	$windid,
			'ip'		=>	$onlineip,
			'time'		=>	$timestamp,
			'toname'	=>	$pwuser,
			'currency'	=>	$currency,
			'tax'		=>	$tax
		);
		writetoollog($logdata);
		require_once(R_P.'require/msg.php');
		$message=array(
			$pwuser,
			$winduid,
			'vire_title',
			$timestamp,
			'vire_content',
			'',
			$windid
		);
		writenewmsg($message,1);

		Showmsg('virement_success');
	}
}
?>