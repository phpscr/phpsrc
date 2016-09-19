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
	require_once(R_P.'require/forum.php');
	$js_path=geteditor();
	include PrintEot('send');exit;
} elseif ($action=="send"){
	$step && PostCheck($verify);
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
	$uids=$pruids='';
	$query=$db->query("SELECT uid,username,email,newpm FROM pw_members WHERE $sqlwhere $limit");
	while(@extract($db->fetch_array($query))){
		if($by==1 || $a_type=='mailuser'){
			//$subject     = addslashes(Char_cv($subject));
			$sendmessage = str_replace("\$email",$email,$atc_content);
			$sendmessage = str_replace("\$windid",$username,$sendmessage);
			//$sendmessage = addslashes(Char_cv($sendmessage));
		}
		if($a_type=='giveuser' || $a_type=='send_msg'){
			if(is_numeric($uid)){
				$uids.=$extra.$uid;
				$extra=',';
				if($by==0 && ($newpm==0 || $newpm==1) || $by==1 && ($newpm==0 || $newpm==2)){
					$pruids .= $pruids ? ','.$uid : $uid;
				}
			}
			if($a_type=='giveuser' && $by==1){
				$sendmessage=str_replace("\$money",$send_money,$sendmessage);
				$sendmessage=str_replace("\$rvrc",$send_rvrc,$sendmessage);
			}
			if($by==1){
				$db->update("INSERT INTO pw_msg (touid,fromuid,username,type,ifnew,title,mdate,content) VALUES ('$uid','0','System','rebox','1','$subject','$timestamp','$sendmessage')");
			}
		} elseif($a_type=='mailuser'){
			sendemail($email,$subject,$sendmessage,'email_additional');
		}
	}
	if(($a_type=='giveuser' || $a_type=='send_msg') && $uids){
		if($by==0 && $step==1){
			$subject     = addslashes(Char_cv($subject));
			$sendmessage = addslashes(Char_cv($atc_content));
			if($a_type=='giveuser'){
				$sendmessage=str_replace("\$money",$send_money,$sendmessage);
				$sendmessage=str_replace("\$rvrc",$send_rvrc,$sendmessage);
			}
			$db->update("INSERT INTO pw_msg (togroups,fromuid,username,type,ifnew,title,mdate,content) VALUES (',$sendto,','0','SYSTEM','public','0','$subject','$timestamp','$sendmessage')");
		}
		$rvrc=$send_rvrc*10;
		$ifnew = $by==0 ? 2 : 1;
		$pruids && $db->update("UPDATE pw_members SET newpm=newpm+'$ifnew' WHERE uid IN($pruids)");
		if($a_type=='giveuser'){
			$uids && $db->update("UPDATE pw_memberdata SET rvrc=rvrc+'$rvrc',money=money+'$send_money' WHERE uid IN($uids)");
		}
	}

	$havesend=$step*$percount;
	if($count>($step*$percount)){
		$step++;
		$j_url="$basename&action=$action&step=$step&count=$count&percount=$percount&send_money=$send_money&send_rvrc=$send_rvrc&subject=".rawurlencode($subject)."&sendto=$sendto&by=$by";
		adminmsg("sendmsg_step",EncodeUrl($j_url),'3');
	} else{
		P_unlink($cache_file);
		adminmsg('sendmsg_success');
	}
}
?>