<?php
!function_exists('readover') && exit('Forbidden');
$wind_in='medal';
include_once(D_P.'data/bbscache/md_config.php');
include_once(D_P.'data/bbscache/medaldb.php');
!$md_ifopen && Showmsg('medal_close');

$userdb = $db->get_one("SELECT medals FROM pw_members WHERE uid='$winduid'");
if($userdb['medals']){
	$userdb['medals'] = explode(',',$userdb['medals']);
}else{
	$userdb['medals'] = '';
}
if(!$action){
	if($userdb['medals']){
		$ifunset = 0;
		foreach($userdb['medals'] as $key=>$val){
			if(!array_key_exists($val,$_MEDALDB)){
				unset($userdb['medals'][$key]);
				$ifunset = 1;
			}
		}
		if($ifunset){
			$newmedals = implode(',',$userdb['medals']);
			$db->update("UPDATE pw_members SET medals='$newmedals' WHERE uid='$winduid'");
			!$newmedals && updatemedal_list();
		}
	}
	require_once PrintHack('index');footer();
}elseif($action=='list'){
	$uids = substr(readover(D_P.'data/bbscache/medals_list.php'),8);

	require_once(R_P.'require/forum.php');
	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_members WHERE uid IN($uids)");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&action=list&");

	$listdb=array();
	$query = $db->query("SELECT uid,username,medals FROM pw_members WHERE uid IN($uids) ORDER BY uid $limit");
	while ($rt = $db->fetch_array($query)){
		$medals='';
		$md_a = explode(',',$rt['medals']);
		foreach($md_a as $key=>$value){
			if($value){
				$medals.="<img src=\"$imgpath/medal/{$_MEDALDB[$value][picurl]}\" alt=\"{$_MEDALDB[$value][name]}\"> ";
			}
		}
		$rt['medals'] = $medals;
		$listdb[] = $rt;
	}
	require_once PrintHack('index');footer();
}elseif($action=='award'){
	if(strpos($md_groups,",$groupid,")===false){
		Showmsg('medal_groupright');
	}
	if(!$step){
		require_once PrintHack('index');footer();
	}elseif($step=="2"){
		$rt = $db->get_one("SELECT uid,username,medals FROM pw_members WHERE username='$pwuser'");
		Add_S($rt);
		!$rt && Showmsg('user_not_exists');
		!$reason && Showmsg('medal_noreason');
		$medals='';
		$medal=(int)$medal;
		!$medal && Showmsg('medal_nomedal');
		$reason = Char_cv($reason);

		require_once(R_P.'require/msg.php');
		if($type==1){
			if($rt['medals'] && strpos(",$rt[medals],",",$medal,")!==false){
				Showmsg('medal_alreadyhave');
			}elseif($rt['medals']){
				$medals="$rt[medals],$medal";
			}else{
				$medals=$medal;
			}
			if($md_ifmsg){
				$message=array(
					$pwuser,
					$winduid,
					'metal_add',
					$timestamp,
					"metal_add_content",
					'',
					$windid
				);
				writenewmsg($message,1);
			}
		}elseif($type==2){
			if(!$rt['medals'] || strpos(",$rt[medals],",",$medal,")===false){
				Showmsg('medal_none');
			}else{
				$medals=substr(str_replace(",$medal,",',',",$rt[medals],"),1,-1);
			}
			if($md_ifmsg){
				$message=array(
					$pwuser,
					$winduid,
					'metal_cancel',
					$timestamp,
					"metal_cancel_content",
					'',
					$windid
				);
				writenewmsg($message,1);
			}
			$timelimit=0;
			$db->update("UPDATE pw_medalslogs SET state='1' WHERE awardee='$pwuser' AND level='$medal'");
		}
		$medals==',' && $medals='';
		$db->update("UPDATE pw_members SET medals='$medals' WHERE uid='$rt[uid]'");
		$db->update("INSERT INTO pw_medalslogs(awardee,awarder,awardtime,timelimit,level,action,why) VALUES('$pwuser','$windid','$timestamp','$timelimit','$medal','$type','$reason')");
		updatemedal_list();
		refreshto("$basename&action=list",'operate_success');
	}
}elseif($action=='log'){
	if(!$job){
		require_once(R_P.'require/forum.php');
		(!is_numeric($page) || $page < 1) && $page = 1;
		$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
		$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_medalslogs");
		$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&action=log&");

		$logdb=array();
		$query = $db->query("SELECT * FROM pw_medalslogs ORDER BY id DESC $limit");
		while ($rt = $db->fetch_array($query)){
			$rt['awardtime'] = get_date($rt['awardtime']);
			$logdb[] = $rt;
		}
		require_once PrintHack('index');footer();
	}elseif($job=='del'){
		$groupid != '3' && Showmsg('medal_dellog');
		$id=(int)$id;
		$rt=$db->get_one("SELECT id,state,action,timelimit FROM pw_medalslogs WHERE id='$id'");
		if($rt['action']==1 && $rt['state']==0 && $rt['timelimit']>0){
			Showmsg('medallog_del_error');			
		}
		$db->update("DELETE FROM pw_medalslogs WHERE id='$id'");
		refreshto("$basename&action=log",'operate_success');
	}
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