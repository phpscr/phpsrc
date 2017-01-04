<?php
!function_exists('readover') && exit('Forbidden');
require_once(R_P."require/forum.php");
require_once(D_P."data/bbscache/inv_config.php");

!$windid && Showmsg('not_login');
$inv_open!='1' && Showmsg('inv_close');
list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);

$usrecredit = ${'db_'.$inv_credit.'name'};
$creditto=array(
	'rvrc'    => $userrvrc,
	'money'   => $winddb['money'],
	'credit'  => $winddb['credit'],
	'currency'=> $winddb['currency']
	);
!array_key_exists($inv_credit,$creditto) && exit('Forbidden');

$allowinvite=0;
if(allowcheck($inv_groups,$groupid,$winddb['groups'])){
	$allowinvite=1;
}
if(!$action){
	$db_perpage=10;
	(!is_numeric($page) || $page<1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_invitecode WHERE uid='$winduid'");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&");

	$query=$db->query("SELECT * FROM pw_invitecode WHERE uid='$winduid' ORDER BY id DESC $limit");
	$invdb=array();
	while($rt=$db->fetch_array($query)){
		$rt['uselate']=0;
		if($rt['ifused']!=2 && $timestamp-$rt['createtime']>$inv_days*86400){
			$rt['uselate']=1;
		}
		$rt['createtime']=get_date($rt['createtime'],'Y-m-d H:i:s');
		$rt['usetime'] = $rt['usetime'] ? get_date($rt['usetime'],'Y-m-d H:i:s') : '';
		$invdb[]=$rt;
	}
	require_once PrintHack('index');footer();
}elseif($action=='send'){
	if(!$step){
		$inv_dayss=$inv_days*86400;
		if($id){
			$invcode=$db->get_one("SELECT * FROM pw_invitecode WHERE id='$id' AND ifused='0' AND uid='$winduid'");
			if($timestamp-$invcode['createtime']>$inv_dayss){
				Showmsg('days_limit');
			}
		}else{
			$invcode=$db->get_one("SELECT * FROM pw_invitecode WHERE uid='$winduid' AND ifused='0' AND $timestamp-createtime<'$inv_dayss' ORDER BY id ASC limit 0,1");
		}
		!$invcode && Showmsg('invcode_error');
		include(GetLang('other'));
		$subject=$lang['invite'];
		$atc_content=$lang['invite_content'];
		require_once PrintHack('index');footer();
	}elseif($_POST['step']=='3'){
		if (empty($subject)){
			Showmsg('sendeamil_subject_limit');
		}
		if (empty($atc_content) || strlen($atc_content)<=20){
			Showmsg('sendeamil_content_limit');
		} elseif (!ereg("^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$",$sendtoemail)){
			Showmsg('illegal_email');
		}
		require_once(R_P.'require/sendemail.php');
		$additional="From:{$winddb[email]}\r\nReply-To:{$winddb[email]}\r\nX-Mailer: PHPWind mailer";
		if (sendemail($sendtoemail,$subject,$atc_content,$additional)) {
			$db->update("UPDATE pw_invitecode SET ifused='1' WHERE id='$id' AND uid='$winduid'");
			refreshto($basename,'mail_success');
		} else {
			Showmsg('mail_failed');
		}
	}
}elseif($action=='buy'){
	$allowinvite==0 && Showmsg('group_invite');
	if($inv_limitdays){
		$rt=$db->get_one("SELECT createtime FROM pw_invitecode WHERE uid='$winduid' ORDER BY createtime DESC LIMIT 0,1");
		if($timestamp-$rt['createtime']<$inv_limitdays*86400){
			Showmsg('inv_limitdays');
		}

	}
	if(!$_POST['step']){
		require_once PrintHack('index');footer();
	} else {
		(!is_numeric($invnum) || $invnum<1) && $invnum=1;
		$invnum>10 && $invnum=10;
		if($creditto[$inv_credit]<$invnum*$inv_costs){
			Showmsg('invite_costs');
		}
		for($i=0;$i<$invnum;$i++){
			$invcode=randstr(16);
			$db->update("INSERT INTO pw_invitecode(invcode,uid,createtime) VALUES ('$invcode','$winduid','$timestamp')");
		}
		$cutcredit=$invnum*$inv_costs;
		$inv_credit=='rvrc' && $cutcredit*=10;
		$db->update("UPDATE pw_memberdata SET $inv_credit=$inv_credit-'$cutcredit' WHERE uid='$winduid'");
		refreshto($basename,'operate_success');
	}	
}elseif($_POST['action']=='delete'){
	(!$selid || !is_array($selid)) && Showmsg('del_error');
	$delids='';
	foreach($selid as $value){
		is_numeric($value) && $delids.= $delids ? ','.$value : $value;
	}
	$db->update("DELETE FROM pw_invitecode WHERE id IN ($delids) AND uid='$winduid'");
	refreshto($basename,'operate_success');
}
?>