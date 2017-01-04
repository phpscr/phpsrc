<?php
!function_exists('readover') && exit('Forbidden');
$wind_in='bank';
require_once(D_P.'data/bbscache/bk_config.php');

$groupid == 'guest' && Showmsg('not_login');
$bk_open == '0'     && Showmsg('bk_close');

list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);
$bankdb   = $db->get_one("SELECT deposit,startdate,ddeposit,dstartdate FROM pw_memberinfo WHERE uid='$winduid'");
require_once(R_P.'require/credit.php');
$creditdb = GetCredit($winduid);
if(empty($action) || $_POST['action']=='credit'){
	include(D_P."data/bbscache/creditdb.php");
	$showdb=array();
	foreach($creditdb as $key => $value){
		$_CREDITDB[$key] && $showdb[$key]=array($value[0],$value[1]);
	}
}
if(empty($action)){
	if(!$bankdb){
		$bankdb['deposit']=$bankdb['ddeposit']=$bankdb['startdate']=$bankdb['dstartdate']=0;
	}
	if($bankdb['startdate'] && $timestamp>$bankdb['startdate']){
		$accrual=round((floor(($timestamp-$bankdb['startdate'])/86400))*$bankdb['deposit']*$bk_rate/100);
	} else{
		$accrual=0;
	}
	$ddates=floor(($timestamp-$bankdb['dstartdate'])/($bk_ddate*30*86400));
	if($bankdb['dstartdate'] && $ddates){
		$daccrual=round($ddates*$bk_ddate*30*$bankdb['ddeposit']*$bk_drate/100);
	} else{
		$daccrual=0;
	}

	$allmoney=$winddb['money']+$bankdb['deposit']+$bankdb['ddeposit'];
	if(!$bankdb['deposit'] || !$bankdb['startdate']){
		$bankdb['savetime']="--";
	} else{
		$bankdb['savetime']=get_date($bankdb['startdate']);
	}
	if(!$bankdb['ddeposit'] || !$bankdb['dstartdate']){
		$bankdb['dsavetime']="--";
	} else{
		$bankdb['dsavetime']=get_date($bankdb['dstartdate'],'Y-m-d');
		$endtime=get_date($bankdb['dstartdate']+$bk_ddate*30*86400,'Y-m-d');
	}
	foreach($_CREDITDB as $key=>$value){
		if(!$showdb[$key]){
			$showdb[$key][0]=$value[0];
			$showdb[$key][1]=0;
		}
	}
	!$bk_num && $bk_num=10;
	if(!$bk_per || $timestamp - @filemtime(D_P."data/bbscache/bank_sort.php") > $bk_per*3600){
		$_DESPOSTDB=array();
		$query=$db->query("SELECT i.uid,m.username,i.deposit,i.startdate FROM pw_memberinfo i LEFT JOIN pw_members m ON m.uid=i.uid ORDER BY i.deposit DESC LIMIT $bk_num");
		while($deposit=$db->fetch_array($query)){
			if($deposit['deposit']){
				$deposit['startdate']=get_date($deposit['startdate']);
				$_DESPOSTDB[]=array($deposit['uid'],$deposit['username'],$deposit['deposit'],$deposit['startdate']);
			}
		}
		$_DDESPOSTDB=array();
		$query=$db->query("SELECT i.uid,username,ddeposit,dstartdate FROM pw_memberinfo i LEFT JOIN pw_members m ON m.uid=i.uid ORDER BY ddeposit DESC LIMIT $bk_num");
		while($deposit=$db->fetch_array($query)){
			if($deposit['ddeposit']){
				$deposit['dstartdate']=get_date($deposit['dstartdate']);
				$_DDESPOSTDB[]=array($deposit['uid'],$deposit['username'],$deposit['ddeposit'],$deposit['dstartdate']);
			}
		}
		$wirtedb=savearray('_DESPOSTDB',$_DESPOSTDB);
		$wirtedb.="\n".savearray('_DDESPOSTDB',$_DDESPOSTDB);
		writeover(D_P.'data/bbscache/bank_sort.php',"<?php\r\n".$wirtedb.'?>');
	}
	include(D_P."data/bbscache/bank_sort.php");
	require_once PrintHack('index');footer();
}

if($_POST['action'] && $bk_timelimit && ($timestamp-$bankdb['startdate']<$bk_timelimit || $timestamp-$bankdb['dstartdate']<$bk_timelimit)){
	Showmsg('bk_time_limit');
}

if($_POST['action']=='save'){
	if(!is_numeric($savemoney) || $savemoney <= 0){
		Showmsg('bk_save_fillin_error');
	}
	$savemoney>$winddb['money'] && Showmsg('bk_save_error');
	$btype != 1 && $btype != 2 && Showmsg('undefined_action');
	banksave($winduid,$savemoney,$bankdb,$btype);
	$log = array(
		'type'      => 'bk_save',
		'username1' => $windid,
		'username2' => '',
		'field1'    => $savemoney,
		'field2'    => '',
		'field3'    => '',
		'descrip'   => 'bk_save_descrip_'.$btype,
		'timestamp' => $timestamp,
		'ip'        => $onlineip,
	);
	writeforumlog($log);
	refreshto($basename,'bank_savesuccess');
}elseif($_POST['action']=='draw'){
	if(!is_numeric($drawmoney) || $drawmoney <= 0){
		Showmsg('bk_draw_fillin_error');
	}
	$btype != 1 && $btype != 2 && Showmsg('undefined_action');
	if($btype==1){
		if($drawmoney>$bankdb['deposit']) Showmsg('bk_draw_error');
	} else{
		if($drawmoney>$bankdb['ddeposit']) Showmsg('bk_draw_error');
	}
	bankdraw($winduid,$drawmoney,$bankdb,$btype);
	$log = array(
		'type'      => 'bk_draw',
		'username1' => $windid,
		'username2' => '',
		'field1'    => $drawmoney,
		'field2'    => '',
		'field3'    => '',
		'descrip'   => 'bk_draw_descrip_'.$btype,
		'timestamp' => $timestamp,
		'ip'        => $onlineip,
	);
	writeforumlog($log);
	refreshto($basename,'bank_drawsuccess');
}elseif($_POST['action']=='virement'){
	require_once(R_P.'require/msg.php');
	if($bk_virement!=1){
		Showmsg('bk_virement_close');
	}
	if(!is_numeric($to_money)|| $to_money <= 0 || $to_money < $bk_virelimit){
		Showmsg('bk_virement_count_error');
	}
	$to_money=floor($to_money);
	$to_shouxu=round($bk_virerate*$to_money/100);
	$needmoney=$to_money+$to_shouxu;
	if($needmoney>$bankdb['deposit']+$bankdb['ddeposit']){
		Showmsg('bk_no_enough_deposit');
	}
	$pwuser=trim($pwuser);
	$userdb=$db->get_one("SELECT uid,username FROM pw_members WHERE username='$pwuser'");
	if(!$pwuser || !$userdb){
		$errorname=$pwuser;
		Showmsg('user_not_exists');
	}
	if($userdb['uid']==$winduid){
		Showmsg('bk_virement_error');
	}
	$rt=$db->get_one("SELECT uid FROM pw_members WHERE username='$pwuser'");
	$to_bankdb=$db->get_one("SELECT deposit,startdate FROM pw_memberinfo WHERE uid='$rt[uid]'");
	banksave($userdb['uid'],$to_money,$to_bankdb,1,0);
	if($needmoney<=$bankdb['deposit']){
		bankdraw($winduid,$needmoney,$bankdb,1,0);
	}else{
		bankdraw($winduid,$bankdb['deposit'],$bankdb,1,0);
		bankdraw($winduid,$needmoney-$bankdb['deposit'],$bankdb,2,0);
	}
	$message=array($pwuser,$winduid,'virement_title',$timestamp,'virement_content');
	writenewmsg($message,1);
	$log = array(
		'type'      => 'bk_vire',
		'username1' => $windid,
		'username2' => $pwuser,
		'field1'    => $to_money,
		'field2'    => '',
		'field3'    => '',
		'descrip'   => 'bk_vire_descrip',
		'timestamp' => $timestamp,
		'ip'        => $onlineip,
	);
	writeforumlog($log);
	refreshto($basename,'bank_viresuccess');
}elseif($_POST['action']=='credit'){
	if(!$bk_A[$type] || !$bk_A[$type][4]){
		Showmsg('bk_credit_type_error');
	}
	list($sell,$buy)=explode('_',$type);
	$credit1=$change;
	$credit2=intval($change/$bk_A[$type][2]*$bk_A[$type][3]);
 	
	if(!is_numeric($change)||$change <= 0) Showmsg('bk_credit_fillin_error');
	$showdb['rvrc'][1]=$userrvrc;
	$showdb['money'][1]=$winddb['money'];
	$showdb['credit'][1]=$winddb['credit'];
	if($credit1>$showdb[$sell][1]) Showmsg('bk_credit_change_error');
	if(is_numeric($sell)){
		$db->update("UPDATE pw_membercredit SET value=value-'$credit1' WHERE uid='$winduid' AND cid='$sell'");
		$sellname = $_CREDITDB[$sell][0];
	} elseif($sell=='rvrc' || $sell=='money' || $sell=='credit'){
		if($sell=='rvrc'){
			$credit1*=10;
		}
		switch($sell){
			case 'rvrc'   : $sellname = $db_rvrcname;break;
			case 'money'  : $sellname = $db_moneyname;break;
			case 'credit' : $sellname = $db_creditname;break;
		}
		$db->update("UPDATE pw_memberdata SET $sell=$sell-'$credit1' WHERE uid='$winduid'");
	} else{
		Showmsg('credit_error');
	}
	if(is_numeric($buy)){
		$db->pw_update(
					"SELECT uid FROM pw_membercredit WHERE uid='$winduid' AND cid='$buy'",
					"UPDATE pw_membercredit SET value=value+'$credit2' WHERE uid='$winduid' AND cid='$buy'",
					"INSERT INTO pw_membercredit SET uid='$winduid',cid='$buy',value='$credit2'"
				);
		$buyname = $_CREDITDB[$buy][0];
	} elseif($buy=='rvrc'||$buy=='money'||$buy=='credit'){
		if($buy=='rvrc'){
			$credit2*=10;
		}
		switch($buy){
			case 'rvrc'   : $buyname = $db_rvrcname;break;
			case 'money'  : $buyname = $db_moneyname;break;
			case 'credit' : $buyname = $db_creditname;break;
		}
		$db->update("UPDATE pw_memberdata SET $buy=$buy+'$credit2' WHERE uid='$winduid'");
	} else{
		Showmsg('credit_error');
	}
	$sell=='rvrc' && $credit1 /= 10;
	$buy=='rvrc'  && $credit2 /= 10;
	$log = array(
		'type'      => 'bk_credit',
		'username1' => $windid,
		'username2' => '',
		'field1'    => $credit1,
		'field2'    => $credit2,
		'field3'    => '',
		'descrip'   => 'bk_credit_descrip',
		'timestamp' => $timestamp,
		'ip'        => $onlineip,
		'sellname'	=> $sellname,
		'buyname'	=> $buyname,
	);
	writeforumlog($log);
	refreshto($basename,'bank_creditsuccess');
}elseif($action=='log'){
	require_once GetLang('log');
	include_once(R_P.'require/forum.php');
	$sqladd = '';
	$select = array();
	if ($type && in_array($type,array('bk_save','bk_draw','bk_vire','bk_credit'))){
		$sqladd = "AND type='$type'";
		$select[$type] = "selected";
	}
	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_forumlog WHERE type LIKE 'bk\_%' AND username1='$windid' $sqladd");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&action=log&type=$type&");
	$query = $db->query("SELECT * FROM pw_forumlog WHERE type LIKE 'bk\_%' AND username1='$windid' $sqladd ORDER BY id DESC $limit");
	while($rt = $db->fetch_array($query)){
		$rt['date']   = get_date($rt['timestamp']);
		$rt['descrip']= str_replace(array('[b]','[/b]'),array('<b>','</b>'),$rt['descrip']);
		$logdb[] = $rt;
	}
	require_once PrintHack('index');footer();
}
function banksave($uid,$money,$bankdb,$type,$vire=1){
	global $db,$timestamp,$bk_rate,$bk_ddate,$bk_drate;

	$vire && $db->update("UPDATE pw_memberdata SET money=money-'$money' WHERE uid='$uid'");
	if($type==1){
		if($bankdb['startdate'] && $timestamp>$bankdb['startdate']){
			$accrual=round((floor(($timestamp-$bankdb['startdate'])/86400))*$bankdb['deposit']*$bk_rate/100);//银行利息
		} else{
			$accrual=0;
		}
		if($bankdb){
			$db->update("UPDATE pw_memberinfo SET deposit=deposit+'$money'+'$accrual',startdate='$timestamp' WHERE uid='$uid'");
		} else{
			$db->update("INSERT INTO pw_memberinfo SET uid='$uid', deposit='$money',startdate='$timestamp'");
		}
	} else{
		$ddates=floor(($timestamp-$bankdb['dstartdate'])/($bk_ddate*30*86400));
		if($bankdb['dstartdate'] && $ddates){
			$daccrual=round($ddates*$bk_ddate*30*$bankdb['ddeposit']*$bk_drate/100);
		} elseif($bankdb['dstartdate'] && !$ddates){
			$daccrual=round((floor(($timestamp-$bankdb['dstartdate'])/86400))*$bankdb['ddeposit']*$bk_rate/100);
		}else{
			$daccrual=0;
		}
		if($bankdb){
			$db->update("UPDATE pw_memberinfo SET ddeposit=ddeposit+'$money'+'$daccrual',dstartdate='$timestamp' WHERE uid='$uid'");
		} else{
			$db->update("INSERT INTO pw_memberinfo SET uid='$uid', ddeposit='$money',dstartdate='$timestamp'");
		}
	}
}
function bankdraw($uid,$money,$bankdb,$type,$vire=1){
	global $db,$timestamp,$bk_rate,$bk_ddate,$bk_drate;
	$vire && $db->update("UPDATE pw_memberdata SET money=money+'$money' WHERE uid='$uid'");
	if($type==1){
		if($bankdb['startdate'] && $timestamp>$bankdb['startdate']){
			$accrual=round((floor(($timestamp-$bankdb['startdate'])/86400))*$bankdb['deposit']*$bk_rate/100);
		} else{
			$accrual=0;
		}
		$db->update("UPDATE pw_memberinfo SET deposit=deposit-'$money'+'$accrual',startdate='$timestamp' WHERE uid='$uid'");
	} else{
		$ddates=floor(($timestamp-$bankdb['dstartdate'])/($bk_ddate*30*86400));
		if($bankdb['dstartdate'] && $ddates){
			$daccrual=round($ddates*$bk_ddate*30*$bankdb['ddeposit']*$bk_drate/100);
		} else{
			$daccrual=0;
		}
		$db->update("UPDATE pw_memberinfo SET ddeposit=ddeposit-'$money'+'$daccrual',dstartdate='$timestamp' WHERE uid='$uid'");
	}
}

function savearray($name,$array){
	$arraydb="\$$name=array(\r\n\t\t";	
	foreach($array as $value1){
		$arraydb.='array(';
		foreach($value1 as $value2){
			$arraydb.='"'.addslashes($value2).'",';
		}
		$arraydb.="),\r\n\t\t";
	}
	$arraydb.=");\r\n";
	return $arraydb;
}
function writeforumlog($log){
	global $db,$db_moneyname;
	require GetLang('log');
	$log['username1'] = Char_cv($log['username1']);
	$log['username2'] = Char_cv($log['username2']);
	$log['field1']    = Char_cv($log['field1']);
	$log['field2']    = Char_cv($log['field2']);
	$log['field3']    = Char_cv($log['field3']);
	$log['descrip']   = Char_cv($lang[$log['descrip']]);
	$db->update("INSERT INTO pw_forumlog (type,username1,username2,field1,field2,field3,descrip,timestamp,ip) VALUES('$log[type]','$log[username1]','$log[username2]','$log[field1]','$log[field2]','$log[field3]','$log[descrip]','$log[timestamp]','$log[ip]')");
}
?>