<?php
!function_exists('db_cv') && exit('Forbidden');

$rs=$db->get_one("SELECT config FROM pw_plan WHERE id='$key'");
$config=unserialize($rs['config']);
$gids=$config['groups'];
$credittype=$config['credittype'];
include_once(D_P.'data/bbscache/creditdb.php');
$creditname = is_numeric($credittype) ? $_CREDITDB[$credittype][0] : ${'db_'.$credittype.'name'};
$sendcredit=$config['credit'];
unset($config);
$admindb=array();
$uidss='';
$query=$db->query("SELECT uid,groupid FROM pw_members WHERE groupid IN($gids) ORDER BY groupid");
while($rt=$db->fetch_array($query)){
	$admindb[$rt['groupid']][]=$rt['uid'];
	$uidss .= $uidss ? ','.$rt['uid'] : $rt['uid'];
}
$admindate=get_date($timestamp,"Y-m-d H:i");
include_once GetLang('writemsg');
foreach($admindb as $gid=>$uid){
	$uids = implode(',',$uid);
	if(array_key_exists($gid,$sendcredit)){
		$addpoint=$sendcredit[$gid];
		addcredits($uids,$credittype,$addpoint);
		$title="send_credit";
		$content="send_credit_content";
		$lang[$title] && $title = Char_cv($lang[$title]);
		$lang[$content] && $content = Char_cv($lang[$content]);
		$content=str_replace(array('ltitle','addpoint'),array($ltitle[$gid],$addpoint),$content);
		$db->update("INSERT INTO pw_msg(togroups,fromuid,username,type,ifnew,title,mdate,content) VALUES(',$gid,','0','SYSTEM','public','0','$title','$timestamp','$content')");
	}
}
$uidss && $db->update("UPDATE pw_members SET newpm=newpm+2 WHERE uid IN($uidss) AND newpm<'2'");

function addcredits($uids,$cid,$addpoint){
	global $db;
	if($cid=='rvrc'){
		$addpoint*=10;
		$db->update("UPDATE pw_memberdata SET rvrc=rvrc+'$addpoint' WHERE uid IN($uids)");
	}elseif($cid=='money'){
		$db->update("UPDATE pw_memberdata SET money=money+'$addpoint' WHERE uid IN($uids)");
	}elseif($cid=='credit'){
		$db->update("UPDATE pw_memberdata SET credit=credit+'$addpoint' WHERE uid IN($uids)");
	}elseif($cid=='currency'){
		$db->update("UPDATE pw_memberdata SET currency=currency+'$addpoint' WHERE uid IN($uids)");
	}elseif(is_numeric($cid)){
		$uid_update=array();
		$query=$db->query("SELECT uid FROM pw_membercredit WHERE cid='$cid' AND uid IN($uids)");
		while($rt=$db->fetch_array($query)){
			$uid_update[]=$rt['uid'];
		}
		$uid_all=explode(',',$uids);
		$uid_insert=array_diff($uid_all,$uid_update);
		$uidss=implode(',',$uid_update);
		$uidss && $db->update("UPDATE pw_membercredit SET value=value+'$addpoint' WHERE cid='$cid' AND uid IN($uidss)");
		if($uid_insert){
			foreach($uid_insert as $key=>$uid){
				$db->update("INSERT INTO pw_membercredit SET uid='$uid',cid='$cid',value='$addpoint'");
			}
		}
	}
}
?>