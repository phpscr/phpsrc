<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=level";

if (empty($action)){
	$memberdb=$vipdb=$sysdb=$defaultdb=array();
	$query=$db->query("SELECT gid,gptype,grouptitle,groupimg,grouppost FROM pw_usergroups ORDER BY grouppost,gid");
	while($level=$db->fetch_array($query)){
		if($level['gptype']=='member'){
			$memberdb[]=$level;
		} elseif($level['gptype']=='special'){
			$vipdb[]=$level;
		} elseif($level['gptype']=='system'){
			$sysdb[]=$level;
		} elseif($level['gptype']=='default'){
			$defaultdb[]=$level;
		}
	}
	include PrintEot('level');exit;
} elseif($action=="menedit"){
	@asort($mempost);
	foreach($mempost as $key=>$value){		
		if(!is_numeric($value)){
			$value=20*pow(2,$key);
			$mempost[$key]=$value;
		}
		$db->update("UPDATE pw_usergroups SET grouptitle='$memtitle[$key]',groupimg='$mempic[$key]',grouppost='".(int)$mempost[$key]."' WHERE gptype='member' AND gid='$key'");
	}
	updatecache_l();
	adminmsg('operate_success');
} elseif($action=="defedit"){
	foreach($deftitle as $key=>$value){
		$db->update("UPDATE pw_usergroups SET grouptitle='$value',groupimg='$defpic[$key]' WHERE gptype='default' AND gid='$key'");
	}
	updatecache_l();
	adminmsg('operate_success');
} elseif($action=="sysedit"){
	foreach($systitle as $key=>$value){
		$db->update("UPDATE pw_usergroups SET grouptitle='$value',groupimg='$syspic[$key]' WHERE gptype='system' AND gid='$key'");
	}
	updatecache_l();
	adminmsg('operate_success');
} elseif($action=="vipedit"){
	foreach($viptitle as $key=>$value){
		$db->update("UPDATE pw_usergroups SET grouptitle='$value',groupimg='$vippic[$key]' WHERE gptype='special' AND gid='$key'");
	}
	updatecache_l();
	adminmsg('operate_success');
} elseif($action=="addmengroup"){
	$db->update("INSERT INTO pw_usergroups(gptype,grouptitle,groupimg,grouppost) VALUES ('member', '$newtitle', '$newpic','".(int)$newpost."')");
	updatecache_l();
	$gid=$db->insert_id();
	$basename="$admin_file?adminjob=level&action=editgroup&gid=$gid";
	adminmsg('operate_success');
} elseif($action=="addadmingroup"){
	$db->update("INSERT INTO pw_usergroups(gptype,grouptitle,groupimg,ifdefault) VALUES ('system', '$newtitle', '$newpic','0')");
	$gid=$db->insert_id();
	updatecache_g($gid);
	updatecache_l();
	$basename="$admin_file?adminjob=level&action=editgroup&gid=$gid";
	adminmsg('operate_success');
} elseif($action=="addvipgroup"){
	$db->update("INSERT INTO pw_usergroups(gptype,grouptitle,groupimg,ifdefault) VALUES ('special', '$newtitle', '$newpic','0')");
	$gid=$db->insert_id();
	updatecache_g($gid);
	updatecache_l();
	$basename="$admin_file?adminjob=level&action=editgroup&gid=$gid";
	adminmsg('operate_success');
} elseif($action=="delgroup"){
	if($delid<7){
		adminmsg('level_del');
	}
	$db->update("DELETE FROM pw_usergroups WHERE gid='$delid'");
	updatecache_l();
	adminmsg('operate_success');
} elseif($action=="editgroup"){
	$basename="$admin_file?adminjob=level&action=editgroup&gid=$gid";
	if(empty($step)){
		if(file_exists(D_P."data/groupdb/group_$gid.php") && $gid!=1){
			include_once(D_P."data/groupdb/group_$gid.php");
			$default=0;
		} else{
			include_once(D_P."data/groupdb/group_1.php");
			$default=1;
		}
		@extract($SYSTEM);
		@extract($_G);
		$uploadmaxsize=ceil($uploadmaxsize/1024);
		$selected_g[$gid]='selected';
		//unset($ltitle['banned']);
		foreach($ltitle as $key=>$value){
			$groupselect.="<option value=$key $selected_g[$key]>$value</option>";
		}

		list($maxcredit,$minper,$maxper,$credittype)=explode("|",$markdb);
		$credits=explode(',',$credittype);
		foreach($credits as $value){
			$check_c[$value]='checked';
		}
		require_once GetLang('all');
		$credittypes=array('rvrc'=>$lang['record_rvrc'],'money'=>$lang['record_money'],'credit'=>$lang['record_credit']);
		include(D_P."data/bbscache/creditdb.php");
		foreach($_CREDITDB as $key=>$value){
			$credittypes[$key]=$value[0];
		}
		$credit_type='';
		foreach($credittypes as $key=>$value){
			$credit_type.="<input type=checkbox name=c_type[] value='$key' $check_c[$key]>".$value;
		}
		!$banmax && $banmax!=0 && $banmax=1;
		/*
		* 基本权限
		*/
		ifcheck($gp_allowread,'read');
		if ($gp_allowsearch==0) $search_N="CHECKED"; elseif($gp_allowsearch==1) $search_1="CHECKED";else $search_2="CHECKED";
		ifcheck($gp_allowmember,'member');
		ifcheck($gp_allowprofile,'profile');
		ifcheck($gp_allowreport,'report');
		ifcheck($gp_allowmessege,'messege');
		ifcheck($gp_allowsort,'sort');
		ifcheck($gp_alloworder,'order');
		ifcheck($gp_allowpost,'post');
		ifcheck($gp_allowrp,'reply');
		ifcheck($gp_allownewvote,'postvote');
		ifcheck($gp_allowvote,'vote');
		ifcheck($gp_htmlcode,'html');
		ifcheck($gp_wysiwyg,'wysiwyg');
		ifcheck($gp_allowhidden,'hidden');
		ifcheck($gp_allowencode,'encode');
		ifcheck($gp_allowsell,'sell');
		ifcheck($gp_allowupload,'upload');
		ifcheck($gp_allowdownload,'download');
		ifcheck($gp_allowloadrvrc,'uploadrvrc');
		ifcheck($gp_allowhide,'hide');
		ifcheck($gp_upload,'uploadimg');
		ifcheck($gp_allowportait,'portait');
		ifcheck($gp_allowhonor,'honor');
		ifcheck($gp_allowdelatc,'delatc');

		ifcheck($msggroup,'msggroup');
		ifcheck($viewvote,'viewvote');
		ifcheck($viewipfrom,'viewipfrom');
		ifcheck($atclog,'atclog');
		ifcheck($show,'show');
		ifcheck($atccheck,'atccheck');
		/*
		* 管理权限
		*/
		ifcheck($allowadmincp,'allowadmincp');
		ifcheck($visithide,'visithide');
		ifcheck($rzforum,'rzforum');
		ifcheck($tpctype,'tpctype');
		ifcheck($tpccheck,'tpccheck');
		ifcheck($delatc,'adelatc');
		ifcheck($moveatc,'moveatc');
		ifcheck($copyatc,'copyatc');
		ifcheck($typeadmin,'typeadmin');
		ifcheck($viewcheck,'viewcheck');
		ifcheck($viewclose,'viewclose');
		ifcheck($attachper,'attachper');
		ifcheck($delattach,'delattach');
		if ($viewip==0) $viewip_N="CHECKED"; elseif($viewip==1) $viewip_1="CHECKED";else $viewip_2="CHECKED";
		$topped==3 ? $topped_3="checked" : ($topped==2 ? $topped_2="checked" : ($topped==1 ? $topped_1="checked" : $topped_0="checked"));
		//ifcheck($viewip,'viewip');
		ifcheck($markable,'markable');
		ifcheck($banuser,'banuser');
		ifcheck($bantype,'bantype');
		ifcheck($viewhide,'viewhide');
		ifcheck($postpers,'postpers');
		ifcheck($replylock,'replylock');
		ifcheck($modown,'modown');
		ifcheck($modother,'modother');
		ifcheck($deltpcs,'deltpcs');
		include PrintEot('level');exit;
	} elseif($step==2){
		!isset($group['maxmsg'])		&& $group['maxmsg']=10;
		!isset($group['allownum'])	    && $group['allownum']=5;
		!isset($group['uploadmoney'])	&& $group['uploadmoney']=0;
		!isset($group['edittime'])		&& $group['edittime']=0;
		!isset($group['postpertime'])	&& $group['postpertime']=0;
		!isset($group['searchtime'])	&& $group['searchtime']=0;
		!isset($group['signnum'])		&& $group['signnum']=0;
		!isset($markdb['maxcredit'])	&& $markdb['maxcredit']=10;
		if($markdb['maxcredit']<max(abs($markdb['minper']),$markdb['maxper']) || $markdb['minper']>$markdb['maxper']){
			adminmsg('level_credit_error');
		}
		if (!is_numeric($mright['uploadmaxsize'])){
			$mright['uploadmaxsize']=0;
		} else{
			$mright['uploadmaxsize']*=1024;
		}
		$c_type=is_array($c_type) ? ','.implode(',',$c_type).',':'';
		$mright['markdb']=$markdb['maxcredit']."|".$markdb['minper']."|".$markdb['maxper']."|".$c_type;

		$group['mright'] = P_serialize($mright);
		$group['ifdefault'] = $gid !=1 ? 0 : 1;

		if($gptype=='system' || $gptype=='special'){
			$sright && $sysgroup['sright'] = P_serialize($sright);
			foreach($sysgroup as $key => $value){
				$group[$key] = $value;
			}
		}
		$sql = "gid='$gid'";
		foreach($group as $key => $value){
			$sql .=",$key='$value'";
		}
		$db->update("UPDATE pw_usergroups SET $sql WHERE gid='$gid'");

		$othersql  = $othergids = $extra = $update_m = $update_s = '';
		if(is_array($othergid)){
			$othergids = "'".implode("','",$othergid)."'";
		}
		if(is_array($othergroup)){
			foreach($othergroup as $key => $value){
				if($key === 'mright'){
					$update_m = 1;
					continue;
				}elseif($key === 'sright'){
					$update_s = 1;
					continue;
				}
				$othersql .= "$extra$value='".$group[$value]."'";
				$extra = ',';
				
			}
		}
		if($othersql && $othergids){
			$db->update("UPDATE pw_usergroups SET $othersql WHERE gid IN($othergids)");
		}
		if($othergids && ($update_m || $update_s)){
			$query = $db->query("SELECT gid,mright,sright FROM pw_usergroups WHERE gid IN($othergids)");
			while($rt = $db->fetch_array($query)){
				$sql = "gid='$rt[gid]'";
				if($update_m){
					$newmright = P_unserialize($rt['mright']);
					foreach($othergroup['mright'] as $key => $value){
						$newmright[$value] = $mright[$value];
					}
					$newmright = P_serialize($newmright);
					$sql .= ",mright='$newmright'";
				}
				if($update_s){
					$newsright = P_unserialize($rt['sright']);
					foreach($othergroup['sright'] as $key => $value){
						$newsright[$value] = $sright[$value];
					}
					$newsright = P_serialize($newsright);
					$sql .= ",sright='$newsright'";
				}
				$db->update("UPDATE pw_usergroups SET $sql WHERE gid='$rt[gid]'");
			}
		}
		if($othergids && ($othersql || $update_m || $update_s)){
			updatecache_g();
		}else{
			updatecache_g($gid);
		}
		updatecache_gr();
		adminmsg('operate_success');
	} elseif($step==3){
		$db->update("UPDATE pw_usergroups SET ifdefault='1' WHERE gid='$gid'");
		P_unlink(D_P."data/groupdb/group_$gid.php");
		adminmsg('operate_success');
	}
}
?>