<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=setuser";

require_once GetLang('all');
if (empty($action)){
	$groupselect="<option value='-1'>$lang[reg_member]</option>";
	$query=$db->query("SELECT gid,gptype,grouptitle FROM pw_usergroups WHERE gptype<>'member' AND gptype<>'default' ORDER BY gid");
	while($group=$db->fetch_array($query)){
		$groupselect.="<option value=$group[gid]>$group[grouptitle]</option>";
	}
	include PrintEot('setuser');exit;
} elseif($_POST['action']=='addnew'){
	if(!$groupid)$groupid='-1';
	if(!$username ||!$password||!$email){
		adminmsg('setuser_empty');
	} else{
		$username=trim($username);
		$S_key=array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#');
		foreach($S_key as $value){
			if (strpos($username,$value)!==false){
				adminmsg('illegal_username'); 
			}
			if (strpos($password,$value)!==false){ 
				adminmsg('illegal_password'); 
			}
		}
		if(strlen($username)>14 || strrpos($username,"|")!==false || strrpos($username,'.')!==false || strrpos($username,' ')!==false || strrpos($username,"'")!==false || strrpos($username,'/')!==false || strrpos($username,'*')!==false || strrpos($username,";")!==false || strrpos($username,",")!==false || strrpos($username,"<")!==false || strrpos($username,">")!==false){
			adminmsg('illegal_username');
		}
		if (strrpos($password,"\r")!==false || strrpos($password,"\t")!==false || strrpos($password,"|")!==false || strrpos($password,"<")!==false || strrpos($password,">")!==false){
			adminmsg('illegal_password'); 
		} else{
			$password=md5($password);
		}
		if ($email&&!ereg("^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,3}$",$email)){
			adminmsg('illegal_email'); 
		}
		$rs = $db->get_one("SELECT COUNT(*) AS count FROM pw_members WHERE username='$username'");
		if($rs['count']>0) {
			adminmsg('username_exists'); 
		}
		if($groupid=='3'&& !If_manager){
			adminmsg('manager_right');
		}
	}
	asort($lneed);
	$memberid=key($lneed);
	$db->update("INSERT INTO pw_members(username,password,email,groupid,memberid,regdate) VALUES('$username','$password','$email','$groupid','$memberid','$timestamp')");
	$winduid=$db->insert_id();
	$db->update("INSERT INTO pw_memberdata (uid,lastvisit,thisvisit) VALUES ('$winduid','$timestamp','$timestamp')");
	$db->update("UPDATE pw_bbsinfo SET newmember='$username',totalmember=totalmember+1 WHERE id='1'");
	adminmsg('operate_success');
} elseif($action=='search'){
	require_once(R_P.'require/forum.php');
	if(!$groups && !$schname && !$schemail && !$groupid && !$userip && $regdate=='all' && $schlastvisit=='all'){
		adminmsg('noenough_condition');
	} else{
		$sql = is_numeric($groupid) ? "m.groupid='$groupid'" : 1;
		//is_numeric($memberid) && $sql .= " AND m.memberid='$memberid'";
		$schname = trim($schname);
		if($schname!=''){
			$schname=addslashes(str_replace('*','%',$schname));
			$sql.=$schname_s==1 ? " AND m.username LIKE '$schname'" : " AND (m.username LIKE '%$schname%')" ;
		}
		if($schemail!=''){
			$schemail=str_replace('*','%',$schemail);
			$sql.=" AND (m.email LIKE '%$schemail%')";
		}
		if($userip!=''){
			$userip=str_replace('*','%',$userip);
			$sql.=" AND (md.onlineip LIKE '%$userip%')";
		}
		if($regdate!='all' && is_numeric($regdate)){
			$schtime=$timestamp-$regdate;
			$sql.=" AND m.regdate<'$schtime'";
		}
		if($schlastvisit!='all' && is_numeric($schlastvisit)){
			$schtime=$timestamp-$schlastvisit;
			$sql.=" AND md.thisvisit<'$schtime'";
		}
		if($orderway){
			$order="ORDER BY '$orderway'";
			 $asc && $order.=$asc;
		}

		$rs=$db->get_one("SELECT COUNT(*) AS count FROM pw_members m LEFT JOIN pw_memberdata md ON md.uid=m.uid WHERE $sql");
		$count=$rs['count'];

		if(!is_numeric($lines))$lines=100;
		(!is_numeric($page) || $page < 1) && $page=1;
		$numofpage=ceil($count/$lines);
		if($numofpage&&$page>$numofpage){
			$page=$numofpage;
		}
		$pages=numofpage($count,$page,$numofpage,"$admin_file?adminjob=setuser&action=$action&schname=".rawurlencode($schname)."&groupid=$groupid&schemail=$schemail&regdate=$regdate&schlastvisit=$schlastvisit&orderway=$orderway&lines=$lines&");
		$start=($page-1)*$lines;
		$limit="LIMIT $start,$lines";
		$groupselect="<option value='-1'>$lang[reg_member]</option>";
		$query=$db->query("SELECT gid,gptype,grouptitle FROM pw_usergroups WHERE gid>2 AND gptype<>'member' ORDER BY gid");
		while($group=$db->fetch_array($query)){
			$gid=$group['gid'];
			$groupselect.="<option value='$gid'>$group[grouptitle]</option>";
		}
		$schdb=array();
		$query=$db->query("SELECT m.uid,m.username,m.email,m.groupid,m.memberid,m.regdate,md.postnum,md.onlineip FROM pw_members m LEFT JOIN pw_memberdata md ON md.uid=m.uid WHERE $sql $order $limit");
		while($sch=$db->fetch_array($query)){
			$sch['regdate']= get_date($sch['regdate']);
			strpos($sch['onlineip'],'|') && $sch['onlineip']=substr($sch['onlineip'],0,strpos($sch['onlineip'],'|'));
			if($sch['groupid']=='-1'){
				$sch['groupselect']=str_replace("<option value='-1'>$lang[reg_member]</option>","<option value='-1' selected>$lang[reg_member]</option>",$groupselect);
			} else{
				$sch['groupselect']=str_replace("<option value='$sch[groupid]'>".$ltitle[$sch['groupid']]."</option>","<option value='$sch[groupid]' selected>".$ltitle[$sch['groupid']]."</option>",$groupselect);
			}
			$schdb[]=$sch;
		}
		include PrintEot('setuser');exit;
	}
} elseif ($action == 'groups'){
	$sql = is_numeric($groupid) ? "groups LIKE '%,$groupid,%'" : "groups!=''";
	$schname = trim($schname);
	if($schname!=''){
		$schname=addslashes(str_replace('*','%',$schname));
		$sql.=$schname_s==1 ? " AND username LIKE '$schname'" : " AND (username LIKE '%$schname%')" ;
	}
	$query=$db->query("SELECT uid,username,groupid,groups,memberid FROM pw_members WHERE $sql");
	while($rt = $db->fetch_array($query)){
		$rt['system'] = $rt['groupid']=='-1' ? $ltitle[$rt['memberid']] : $ltitle[$rt['groupid']];
		$groupds = explode(',',$rt['groups']);
		foreach($groupds as $key => $value){
			if($value){
				$rt['gtitle'] .= $ltitle[$value].' ';
			}
		}
		$schdb[] = $rt;
	}
	include PrintEot('setuser');exit;

} elseif($_POST['action']=='edutgroup'){
	if(!$gid)adminmsg('operate_error');
	foreach($gid as $uid=>$groupid){
		if($uid){
			$rt=$db->get_one("SELECT groupid,groups FROM pw_members WHERE uid='$uid'");
			if($rt['groupid']==3 && $groupid!=3 && !If_manager){
				adminmsg('manager_right');
			}elseif($rt['groupid']!=3 && $groupid==3 && !If_manager){
				adminmsg('manager_right');
			}elseif($rt['groupid']==5 && $groupid==-1 || $rt['groupid']!=5 && $groupid==5){
				adminmsg('setuser_forumadmin');
			}elseif($rt['groupid']==6 && $groupid!=6){
				$db->update("DELETE FROM pw_banuser WHERE uid='$uid'");
			}elseif($rt['groupid']!=6 && $groupid==6){
				$db->update("REPLACE INTO pw_banuser VALUES('$uid','2','$timestamp','','".addslashes($admin_name)."','')");
			}
			if($rt['groups'] && strpos($rt['groups'],','.$groupid.',')!==false){
				$newgroups=str_replace(','.$groupid.',',',',$rt['groups']);
				$newgroups = $newgroups==',' ? ",groups=''" : ",groups='$newgroups'";
			}else{
				$newgroups='';
			}   // 2006.8.17
			$db->update("UPDATE pw_members SET groupid='$groupid' $newgroups WHERE uid='$uid'");
		}
	}
	adminmsg('operate_success');
} elseif($action=='edit'){
	include_once(D_P.'data/bbscache/customfield.php');
	$fieldadd='';
	foreach($customfield as $key=>$val){
		$val['id'] = (int) $val['id'];
		$fieldadd .= ",mb.field_$val[id]"; 
	}
	if(empty($_POST['step'])){
		@extract($db->get_one("SELECT m.*,md.onlinetime,md.postnum,md.rvrc,md.money,md.credit,md.lastvisit,md.thisvisit,md.lastpost,md.todaypost,md.onlineip,md.uploadtime,md.uploadnum,md.editor,mb.deposit,mb.ddeposit $fieldadd FROM pw_members m LEFT JOIN pw_memberinfo mb ON m.uid=mb.uid LEFT JOIN pw_memberdata md ON md.uid=m.uid WHERE m.uid='$uid'"));
		$rvrc=floor($rvrc/10);
		if(strpos($onlineip,'|')){
			$onlineip=substr($onlineip,0,strpos($onlineip,'|'));
		}
		$regdate=get_date($regdate);
		$ifchecked=$publicmail ? 'checked' : '';
		$receivemail ? $email_open='checked' : $email_close='checked';
		$sexselect[$gender]='selected';
		$selected[$groupid]='selected';
		$getbirthday = explode("-",$bday);
		$yearslect[(int)$getbirthday[0]]="selected";
        $monthslect[(int)$getbirthday[1]]="selected";
		$dayslect[(int)$getbirthday[2]]="selected";

		$groups=explode(',',$groups);
		foreach($groups as $key => $value){
			${'check_'.$value}='checked';
		}
		$usergroup="<table cellspacing='0' cellpadding='0' border='0' width='100%' align='center'><tr>";
		$groupselect="<option value='-1' $selected[member]>$lang[reg_member]</option>";

		$query=$db->query("SELECT gid,gptype,grouptitle FROM pw_usergroups WHERE gid>2 AND gptype<>'member' ORDER BY gid");
		while($rt=$db->fetch_array($query)){
			$gid=$rt['gid'];
			$groupselect.="<option value='$gid' $selected[$gid]>$rt[grouptitle]</option>";

			if($rt['gid'] != $groupid){
				$num++;
				$htm_tr=$num%3==0 ? '</tr><tr>' : '';
				$ifchecked=${'check_'.$rt['gid']};
				$usergroup.="<td><input type='checkbox' name='groups[]' value='$rt[gid]' $ifchecked>$rt[grouptitle]</td>$htm_tr";
			}
		}
		$usergroup.="</tr></table>";

		list($i_adr,$i_http,$i_w,$i_h)=explode("|",$icon);
		include PrintEot('setuser');exit;
	} elseif($_POST['step']==2){
		$basename.="&action=edit&uid=$uid";
		$oldinfo=$db->get_one("SELECT username,groupid,groups,icon FROM pw_members WHERE uid='$uid'");
		if($oldinfo['username']!=stripcslashes($username)){
			$rs = $db->get_one("SELECT COUNT(*) AS count FROM pw_members WHERE username='$username'");
			if($rs['count']>0) {
				adminmsg('username_exists'); 
			}
		}
		if($password!=''){
			$password!=$check_pwd && adminmsg('password_confirm');
			$password=md5($password);
			$setpassword=",password='$password'";
		} else{
			$setpassword='';
		}

		$newgroups=$groups ? ','.implode(',',$groups).',' : '';
		$newgroups=str_replace(','.$groupid.',',',',$newgroups);
		if(($oldinfo['groupid']=='3' || strpos($oldinfo['groups'],',3,')!==false) && !If_manager){
			adminmsg('manager_right');
		} elseif($oldinfo['groupid']!='3' && ($groupid=='3'  || strpos($newgroups,',3,')!==false) && !If_manager){
			adminmsg('manager_right');
		}
		if(ifadmin($oldinfo['username']) && $groupid!='5' && strpos($newgroups,',5,')===false){
			if(strpos($oldinfo['groups'],',5,')!==false){
				adminmsg('setuser_forumadmin');
			}else{
				$newgroups.=$newgroups ? '5,' : ',5,';
			}
		}elseif(!ifadmin($oldinfo['username']) && ($groupid=='5' || strpos($newgroups,',5,')!==false)){
			adminmsg('setuser_forumadmin');
		} elseif(($oldinfo['groupid']=='6' && $groupid != '6' && strpos($newgroups,',6,')===false) || ($oldinfo['groupid']!='6' && ($groupid == '6' || strpos($newgroups,',6,')!==false))){
			adminmsg('setuser_ban');
		}
		$newgroups=$newgroups!=$oldinfo['groups'] ?	",groups='$newgroups'" : '';

		list($c_adr,$c_http,$c_w,$c_h)=explode("|",$oldinfo['icon']);
		if($i_http && !ereg("^http",$i_http) && !ereg("^$uid",$i_http)){
			adminmsg('setuser_img');
		}
		$icon="$c_adr|$i_http|$i_w|$i_h|";
		$bday=$year."-".$month."-".$day;
		$rvrc*=10;
		$regdate=PwStrtoTime($regdate);

		if($oldinfo['username']!=stripcslashes($username)){
			$db->update("UPDATE pw_threads SET author='$username' WHERE authorid='$uid'");
			$ptable_a=array('pw_posts');
			if($db_plist){
				$p_list=explode(',',$db_plist);
				foreach($p_list as $val){
					$ptable_a[]='pw_posts'.$val;
				}
			}
			foreach($ptable_a as $val){
				$db->update("UPDATE $val SET author='$username' WHERE authorid='$uid'");
			}
			$db->update("UPDATE pw_cmembers SET username='$username' WHERE uid='$uid'");
			$db->update("UPDATE pw_argument SET author='$username' WHERE authorid='$uid'");
			$db->update("UPDATE pw_colonys SET admin='$username' WHERE admin='".addslashes($oldinfo['username'])."'");
			$db->update("UPDATE pw_announce SET author='$username' WHERE author='".addslashes($oldinfo['username'])."'");

			$query = $db->query("SELECT fid,forumadmin,fupadmin FROM pw_forums WHERE forumadmin LIKE '%,".addslashes($oldinfo['username']).",%' OR fupadmin LIKE '%,".addslashes($oldinfo['username']).",%'");
			while($rt = $db->fetch_array($query)){
				$rt['forumadmin']	= str_replace(",$oldinfo[username],",",$username,",$rt['forumadmin']);
				$rt['fupadmin']		= str_replace(",$oldinfo[username],",",$username,",$rt['fupadmin']);
				$db->update("UPDATE pw_forums SET forumadmin='".addslashes($rt['forumadmin'])."',fupadmin='".addslashes($rt['fupadmin'])."' WHERE fid='$rt[fid]'");
			}
		}
		$db->update("UPDATE pw_members SET username='$username' $setpassword,gender='$gender',email='$email' $newgroups,regdate='$regdate',publicmail='".(int)$publicmail."',receivemail='$receivemail',groupid='$groupid',icon='$icon',site='$site',oicq='$oicq',icq='$icq',msn='$msn',yahoo='$yahoo',location='$location',bday='$bday',honor='$honor',yz='$yz',signature='$signature',introduce='$introduce',banpm='$banpm' WHERE uid='$uid'");
		$db->update("UPDATE pw_memberdata SET rvrc='$rvrc',money='$money',credit='$credit',postnum='$postnum',onlinetime='$onlinetime',onlineip='$userip' WHERE uid='$uid'");


		$mi=$db->get_one("SELECT uid,deposit,ddeposit $fieldadd FROM pw_memberinfo mb WHERE uid='$uid'");
		if(!$mi){
			if($deposit || $ddeposit){
				$db->update("INSERT INTO pw_memberinfo SET uid='$uid',deposit='$deposit',ddeposit='$ddeposit'");
			}
		}elseif($deposit!=$mi['deposit'] || $ddeposit!=$mi['ddeposit']){
			$db->update("UPDATE pw_memberinfo SET deposit='$deposit',ddeposit='$ddeposit' WHERE uid='$uid'");
		}
		if($customfield){
			$fieldadd='';
			foreach($customfield as $key=>$val){
				$field="field_".(int)$val['id'];
				if($mi[$field] != $$field){
					$$field = Char_cv($$field);
					$fieldadd .= $fieldadd ? ",$field='{$$field}'" : "$field='{$$field}'";
				}
			}
			if($fieldadd){
				$db->pw_update(
					"SELECT uid FROM pw_memberinfo WHERE uid='$uid'",
					"UPDATE pw_memberinfo SET $fieldadd WHERE uid='$uid'",
					"INSERT INTO pw_memberinfo SET uid='$uid',$fieldadd"
				);
			}
		}
		adminmsg('operate_success');
	}
}
?>