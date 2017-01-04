<?php
!function_exists('db_cv') && exit('Forbidden');
$query=$db->query("SELECT id,awardee,level FROM pw_medalslogs WHERE action='1' AND state='0' AND timelimit>0 AND $timestamp-awardtime>timelimit*2592000");
$medaldb=$namedb=array();
while($rt=$db->fetch_array($query)){
	$medaldb[$rt['awardee']][]=array($rt['id'],$rt['level']);
	$namedb[]=$rt['awardee'];
}
if($namedb){
	include_once(R_P.'require/msg.php');
	include_once(D_P.'data/bbscache/medaldb.php');
	$namedb=array_unique($namedb);
	$usernames="'".implode("','",$namedb)."'";
	include(GetLang('msg'));
	$lang['medal_reason'] && $reason = Char_cv($lang['medal_reason']);
	$query=$db->query("SELECT uid,username,medals FROM pw_members WHERE username IN($usernames)");
	$ids='';
	while($rt=$db->fetch_array($query)){
		Add_S($rt);
		$medals=",".$rt['medals'].",";
		$medalname='';
		foreach($medaldb[$rt['username']] as $key=>$value){
			$ids .= $ids ? ','.$value[0] : $value[0];
			$medal=$value[1];
			$db->update("INSERT INTO pw_medalslogs(awardee,awarder,awardtime,level,action,why) VALUES('$rt[username]','SYSTEM','$timestamp','$medal','2','$reason')");
			$medals=str_replace(",$medal,",',',$medals);
			$medalname .= $medalname ? ','.$_MEDALDB[$medal]['name'] : $_MEDALDB[$medal]['name'];
		}
		$message=array(
			$rt['username'],
			0,
			'metal_cancel',
			$timestamp,
			"metal_cancel_text",
			'',
			''
		);
		writenewmsg($message,1);
		$medals=substr($medals,1,-1);
		$db->update("UPDATE pw_members SET medals='$medals' WHERE uid='$rt[uid]'");
	}
	$ids && $db->update("UPDATE pw_medalslogs SET state='1' WHERE id IN($ids)");
	updatemedal_list();
}
function updatemedal_list(){
	global $db;
	$query   = $db->query("SELECT uid,medals FROM pw_members WHERE medals!=''");
	$medaldb = '<?die;?>0';
	while($rt=$db->fetch_array($query)){
		if(str_replace(',','',$rt['medals'])){
			$medaldb .= ','.$rt['uid'];
		}
	}
	writeover(D_P.'data/bbscache/medals_list.php',$medaldb);
}
?>