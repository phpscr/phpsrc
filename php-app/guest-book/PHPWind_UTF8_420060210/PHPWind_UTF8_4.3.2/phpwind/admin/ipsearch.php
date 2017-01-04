<?php
!function_exists('adminmsg') && exit('Forbidden');
require_once(R_P.'require/forum.php');
$basename="$admin_file?adminjob=ipsearch";

if (empty($action)){
	include PrintEot('ipsearch');exit;
} elseif($action=='byname'){
	!$username && adminmsg('ipsearch_username');
	if($type){
		$rt=$db->get_one("SELECT uid FROM pw_members WHERE username='$username'");
		$uids=$rt['uid'];
	} else{
		$uids=0;
		$query=$db->query("SELECT uid FROM pw_members WHERE username LIKE '%$username%'");
		while($rt=$db->fetch_array($query)){
			if($rt['uid']){
				$uids.=','.$rt['uid'];
			}
		}
	}
	$pages='';
	$ipdb=array();
	if($uids){
		$query=$db->query("SELECT m.uid,m.username,md.onlineip AS userip,md.thisvisit AS lasttime FROM pw_memberdata md LEFT JOIN pw_members m ON m.uid=md.uid WHERE md.onlineip!='' AND md.uid IN($uids) GROUP BY md.onlineip");
		while($rt=$db->fetch_array($query)){
			$rt['lasttime']=get_date($rt['lasttime']);
			$rt['userip']=strpos($rt['userip'],'|') ? substr($rt['userip'],0,strpos($rt['userip'],'|')) : $rt['userip'];
			$ipdb[]=$rt;
		}

		$query=$db->query("SELECT tm.userip,t.postdate AS lasttime,t.authorid AS uid,t.author AS username FROM pw_threads t LEFT JOIN pw_tmsgs tm ON tm.tid=t.tid WHERE userip!='' AND t.authorid IN($uids) GROUP BY userip");
		while($rt=$db->fetch_array($query)){
			$rt['lasttime']=get_date($rt['lasttime']);
			$ipdb[]=$rt;
		}

		$query=$db->query("SELECT userip,postdate AS lasttime,author AS username,authorid AS uid FROM pw_posts WHERE userip!='' AND authorid IN($uids) GROUP BY userip");
		while($rt=$db->fetch_array($query)){
			$rt['lasttime']=get_date($rt['lasttime']);
			$ipdb[]=$rt;
		}
		$count=count($ipdb);
		(!is_numeric($page) || $page < 1) && $page=1;
		$start=($page-1)*50;
		$end=min($start+50,$count);
		$numofpage=ceil($count/50);
		$pages=numofpage($count,$page,$numofpage,"$basename&action=byname&username=".rawurlencode($username)."&type=$type&");
	}
	include PrintEot('ipsearch');exit;
} elseif($action=='byip'){

	!$userip && adminmsg('ipsearch_userip');
	$pages='';
	$userdb=array();

	$sql=$type ? "md.onlineip LIKE '$userip%'" : "md.onlineip LIKE '%$userip%'";
	$query=$db->query("SELECT m.uid,m.username,m.email,md.thisvisit AS lasttime,md.postnum,md.onlineip AS userip FROM pw_memberdata md LEFT JOIN pw_members m ON m.uid=md.uid WHERE $sql GROUP BY m.username");
	while($rt=$db->fetch_array($query)){
		if(strpos($rt['userip'],'|')!==false){
			$rt['userip']=substr($rt['userip'],0,strpos($rt['userip'],'|'));
		} else{
			$rt['userip']=$rt['userip'];
		}
		$rt['lasttime']=get_date($rt['lasttime']);
		$userdb[]=$rt;
	}

	$sql=$type ? "tm.userip='$userip'" : "tm.userip LIKE '%$userip%'";
	$query=$db->query("SELECT t.authorid AS uid,t.author AS username,t.postdate AS lasttime,tm.userip FROM pw_tmsgs tm LEFT JOIN pw_threads t ON t.tid=tm.tid WHERE $sql GROUP BY authorid");
	while($rt=$db->fetch_array($query)){
		$rt['lasttime']=get_date($rt['lasttime']);
		$userdb[]=$rt;
	}

	$query=$db->query("SELECT authorid AS uid,author AS username,postdate AS lasttime,userip FROM pw_posts tm WHERE $sql GROUP BY authorid");
	while($rt=$db->fetch_array($query)){
		$rt['lasttime']=get_date($rt['lasttime']);
		$userdb[]=$rt;
	}
	if($userdb){
		$count=count($userdb);
		(!is_numeric($page) || $page < 1) && $page=1;
		$start=($page-1)*50;
		$end=min($start+50,$count);
		$numofpage=ceil($count/50);
		$pages=numofpage($count,$page,$numofpage,"$basename&action=byip&userip=$userip&type=$type&");
	}

	include PrintEot('ipsearch');exit;
}
?>