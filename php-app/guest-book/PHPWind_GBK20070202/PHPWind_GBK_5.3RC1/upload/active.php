<?php
require_once('global.php');

!$winduid && Showmsg('not_login');
(!$action || !$tid) && Showmsg('undefined_action');

if($action=='apply'){
	if(!$step){
		$sql=" LEFT JOIN pw_tmsgs tm ON t.tid=tm.tid";
		$sqlsel=",tm.ifconvert,tm.content";
	}else{
		$sql=$sqlsel='';
	}
	$act=$db->get_one("SELECT a.*,t.authorid,t.subject as tsubject $sqlsel FROM pw_activity a LEFT JOIN  pw_threads t ON t.tid=a.tid $sql WHERE a.tid='$tid'");
	!$act && Showmsg('data_error');
	$act['deadline']<$timestamp && Showmsg('time_out');
	if($act['num']){
		@extract($db->get_one("SELECT COUNT(*) AS count FROM pw_actmember WHERE actid='$tid' AND state='1'"));
		$count>=$act['num'] && Showmsg('num_full');
	}
	if($act['sexneed']){
		$member=$db->get_one("SELECT gender FROM pw_members WHERE uid='$winduid'");
		$member['gender']!=$act['sexneed'] && Showmsg('apply_gender_error');
	}
	$rt=$db->get_one("SELECT state FROM pw_actmember WHERE winduid='$winduid' AND actid='$tid'");
	$rt && Showmsg('have_act');
	if(!$step){
		require_once(R_P.'require/header.php');
		require_once(R_P.'require/bbscode.php');
		$read=array();
		$act['starttime'] = get_date($act['starttime'],'Y-m-d');
		$act['endtime']   = get_date($act['endtime'],'Y-m-d');
		$act['deadline']  = get_date($act['deadline'],'Y-m-d');
		$act['content']   = str_replace("\n","<br />",$act['content']);
		$act['ifconvert']==2 && $act['content'] = convert($act['content'],$db_windpost,2);

		$query=$db->query("SELECT COUNT(*) AS count,state FROM pw_actmember WHERE actid='$tid' GROUP BY state");
		$act_total=$act_y=0;
		while($rt=$db->fetch_array($query)){
			$act_total+=$rt['count'];
			$rt['state']==1 && $act_y+=$rt['count'];
		}
		require_once PrintEot('active');footer();
	}elseif($_POST['step']==2){
		!$contact && Showmsg('contact_empty');
		$contact = Char_cv($contact);
		$message = Char_cv($message);
		$state = $act['admin']==$winduid ? 1 : 0;
		$db->update("INSERT INTO pw_actmember (actid,winduid,state,applydate,contact,message) VALUES ('$tid','$winduid','$state','$timestamp','$contact','$message')");
		refreshto("read.php?tid=$tid&fpage=$fpage",'operate_success');
	}
}elseif($action=='view'){
	require_once(R_P.'require/header.php');
	require_once(R_P.'require/forum.php');
	require_once(R_P.'require/bbscode.php');

	$act=$db->get_one("SELECT a.*,t.authorid,t.subject as tsubject,tm.ifconvert,tm.content FROM pw_activity a LEFT JOIN pw_threads t ON a.tid=t.tid LEFT JOIN pw_tmsgs tm ON a.tid=tm.tid WHERE a.tid='$tid'");
	!$act && Showmsg('data_error');
	$admincheck = $act['admin']==$winduid ? 1 : 0;

	if(!$admincheck){
		$ifact=$db->get_one("SELECT * FROM pw_actmember WHERE actid='$tid' AND winduid='$winduid' AND state='1'");
		!$ifact && Showmsg("actid_view_error");
	}

	$act['starttime'] = get_date($act['starttime'],'Y-m-d');
	$act['endtime']   = get_date($act['endtime'],'Y-m-d');
	$act['deadline']  = get_date($act['deadline'],'Y-m-d');
	$act['content']   = str_replace("\n","<br />",$act['content']);
	$act['ifconvert']==2 && $act['content'] = convert($act['content'],$db_windpost,2);
	
	if($admincheck){
		$sql = "";
	}else{
		$sql = "AND a.state='1'";
	}
	$query=$db->query("SELECT COUNT(*) AS count,state FROM pw_actmember WHERE actid='$tid' GROUP BY state");
	$act_total=$act_y=0;
	while($rt=$db->fetch_array($query)){
		$act_total+=$rt['count'];
		$rt['state']==1 && $act_y+=$rt['count'];
	}
	$act_show = $admincheck ? $act_total : $act_y;
	$db_showperpage=30;
	(!is_numeric($page) || $page<1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_showperpage.",$db_showperpage";
	$pages = numofpage($act_show,$page,ceil($act_show/$db_showperpage),"active.php?action=view&tid=$tid&");

	$actdb=array();
	$query=$db->query("SELECT a.*,m.username,m.gender FROM pw_actmember a LEFT JOIN pw_members m ON a.winduid=m.uid WHERE a.actid='$tid' $sql ORDER BY applydate DESC $limit");
	while($rt=$db->fetch_array($query)){
		$rt['applydate'] = get_date($rt['applydate']);
		$actdb[]=$rt;
	}
	require_once PrintEot('active');footer();
}elseif($action=='pass' || $action=='unpass'){
	$selids='';
	foreach($selid as $key=>$val){
		is_numeric($val) && $selids .= $selids ? ','.$val : $val;
	}
	!$selids && Showmsg('selid_illegal');

	$read=$db->get_one("SELECT admin FROM pw_activity WHERE tid='$tid'");
	!$read && Showmsg('data_error');
	$read['admin']!=$winduid && Showmsg('active_manager_right');

	$state = $action=='pass' ? 1 : 2;
	$db->update("UPDATE pw_actmember SET state='$state' WHERE actid='$tid' AND id IN($selids)");
	refreshto("active.php?action=view&tid=$tid",'operate_success');
}elseif($action=='exit'){
	$db->update("DELETE FROM pw_actmember WHERE actid='$tid' AND winduid='$winduid'");
	refreshto("read.php?tid=$tid",'operate_success');
}elseif($action=='cancle'){
	$read=$db->get_one("SELECT admin FROM pw_activity WHERE tid='$tid'");
	!$read && Showmsg('data_error');
	$read['admin']!=$winduid && Showmsg('active_manager_right');
	$db->update("DELETE FROM pw_activity WHERE tid='$tid'");
	$db->update("DELETE FROM pw_actmember WHERE actid='$tid'");
	$db->update("UPDATE pw_threads SET activeid='0' WHERE tid='$tid'");
	refreshto("read.php?tid=$tid",'operate_success');
}
?>