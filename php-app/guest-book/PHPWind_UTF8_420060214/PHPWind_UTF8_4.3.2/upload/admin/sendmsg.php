<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=sendmsg&a_type=$a_type";

if (empty($action)){
	require_once GetLang('all');
	if($a_type=='send_msg' || $a_type=='mailuser'){
		$atc_content=$lang['send_welcome'];
	} elseif($a_type=='giveuser'){
		$atc_content=$lang['send_feast'];
	}
	$js_path=file_exists(D_P."data/{$stylepath}_editor.js") ? "data/{$stylepath}_editor.js" : "data/wind_editor.js";
	include PrintEot('send');exit;
} elseif ($action=="send"){
	if($a_type=='mailuser'){
		require_once(R_P.'require/sendemail.php');
	}
	$cache_file=D_P."data/bbscache/".substr($admin_pwd,10,10).".txt";
	if(!$step){
		if ($by == 0){
			$sendto=implode(",",$sendto);
		}
		writeover($cache_file,$atc_content);
	}else{
		$atc_content=readover($cache_file);
	}
	if (empty($subject) || empty($atc_content)){
		adminmsg('sendmsg_empty');
	}
	empty($percount) && $percount=100;
	empty($step) && $step=1;

	if ($by == 0){
		!$sendto && adminmsg('operate_error');
		$sqlwhere="groupid IN('".str_replace(",","','",$sendto)."')";
	} elseif ($by == 1){
		require_once(R_P.'require/GetOnlineUser.php');
		$onlineuser = GetOnlineUser();
		$uids=0;
		foreach ($onlineuser as $key => $value){
			is_numeric($key) && $uids .= ",$key";
		}
		$sqlwhere = "uid IN($uids)";
	} else{
		adminmsg('operate_error');
	}
	$start=($step-1)*$percount;
	$limit="LIMIT $start,$percount";
	if($step==1){
		$rs=$db->get_one("SELECT count(*) AS count FROM pw_members WHERE $sqlwhere");
		$count=$rs['count'];
	}
	$uids='';
	$query=$db->query("SELECT uid,username,email FROM pw_members WHERE $sqlwhere $limit");
	while(@extract($db->fetch_array($query))){
		$subject     = addslashes(Char_cv($subject));
		$sendmessage = str_replace("\$email",$email,$atc_content);
		$sendmessage = str_replace("\$windid",$username,$sendmessage);
		$sendmessage = addslashes(Char_cv($sendmessage));
		if($a_type=='giveuser' || $a_type=='send_msg'){
			if(is_numeric($uid)){
				$uids.=$extra.$uid;
				$extra=',';
			}
			if($a_type=='giveuser'){
				$sendmessage=str_replace("\$money",$send_money,$sendmessage);
				$sendmessage=str_replace("\$rvrc",$send_rvrc,$sendmessage);
			}
			$db->update("INSERT INTO pw_msg (touid,fromuid,username,type,ifnew,title,mdate,content) VALUES ('$uid','0','System','rebox','1','$subject','$timestamp','$sendmessage')");
		} elseif($a_type=='mailuser'){
			sendemail($email,$subject,$sendmessage,'email_additional');
		}
	}
	if(($a_type=='giveuser' || $a_type=='send_msg') && $uids){
		$rvrc=$send_rvrc*10;
		$db->update("UPDATE pw_members SET newpm=1 WHERE uid IN($uids)");
		$db->update("UPDATE pw_memberdata SET rvrc=rvrc+'$rvrc',money=money+'$send_money' WHERE uid IN($uids)");
	}

	$havesend=$step*$percount;
	if($count>($step*$percount)){
		$step++;
		adminmsg("sendmsg_step","$basename&action=$action&step=$step&count=$count&percount=$percount&send_money=$send_money&send_rvrc=$send_rvrc&subject=".rawurlencode($subject)."&sendto=$sendto&by=$by",'3');
	} else{
		P_unlink($cache_file);
		adminmsg('sendmsg_success');
	}
}
?>