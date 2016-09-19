<?php
!function_exists('adminmsg') && exit('Forbidden');

if(!$job){
	$basename="$admin_file?adminjob=hack&hackset=toolcenter";
	if(!$step){
		ifcheck($db_toolifopen,'toolifopen');
		ifcheck($db_allowtrade,'allowtrade');
		include PrintHack('admin');exit;
	} else{
		$db->pw_update(
			"SELECT db_name FROM pw_config WHERE db_name='db_toolifopen'",
			"UPDATE pw_config SET db_value='$toolifopen' WHERE db_name='db_toolifopen'",
			"INSERT INTO pw_config SET db_value='$toolifopen',db_name='db_toolifopen'"
		);
		$db->pw_update(
			"SELECT db_name FROM pw_config WHERE db_name='db_allowtrade'",
			"UPDATE pw_config SET db_value='$allowtrade' WHERE db_name='db_allowtrade'",
			"INSERT INTO pw_config SET db_value='$allowtrade',db_name='db_allowtrade'"
		);
		updatecache_c();
		adminmsg('operate_success');
	}
}elseif($job=='toolinfo'){
	$basename="$admin_file?adminjob=hack&hackset=toolcenter&job=toolinfo";
	if(!$action){
		$query = $db->query("SELECT * FROM pw_tools");
		while($rt = $db->fetch_array($query)){
			$tooldb[] = $rt;
		}
		include PrintHack('admin');exit;
	} elseif($action == 'submit'){
		$toolids = 0;
		if(is_array($tools)){
			foreach($tools as $key => $value){
				is_numeric($key) && $toolids .= ','.$key;
			}		
		}
		if($toolids){
			$db->update("UPDATE pw_tools SET state='1' WHERE id IN($toolids)");
			$db->update("UPDATE pw_tools SET state='0' WHERE id NOT IN($toolids)");
		} else{
			$db->update("UPDATE pw_tools SET state='0'");
		}
		adminmsg('operate_success');
	} elseif($action == 'edit'){
		if(!$step){
			$rt = $db->get_one("SELECT * FROM pw_tools WHERE id='$id'");
			if($rt){
				$condition = unserialize($rt['conditions']);
				$groupids  = $condition['group'];
				$fids      = $condition['forum'];
				ifcheck($rt['state'],'state');
				foreach($condition['credit'] as $key => $value){
					$key == 'rvrc' && $value /= 10;
					$condition['credit'][$key] = (int)$value;
				}

				include_once(D_P."data/bbscache/creditdb.php");
				$usergroup="<table cellspacing='0' cellpadding='0' border='0' width='100%' align='center'><tr>";
				foreach($ltitle as $key=>$value){
					if($key != 1 && $key != 2){
						$num++;
						$htm_tr = $num%5 == 0 ?  '</tr><tr>' : '';
						if(strpos($groupids,','.$key.',') !== false){
							$checked = 'checked';
						} else{
							$checked = '';
						}
						$usergroup .=" <td width='20%'><input type='checkbox' name='groupids[]' value='$key' $checked>$value</td>$htm_tr";
					}
				}
				$usergroup .= "</tr></table>";

				$num        = 0;
				$forumcheck = "<table cellspacing='0' cellpadding='0' border='0' width='100%' align='center'><tr>";
				$sqladd     = " AND f_type!='hidden' AND cms='0'";
				$query      = $db->query("SELECT fid,name FROM pw_forums WHERE type<>'category' $sqladd");
				while($fm = $db->fetch_array($query)){
					if(!$db_recycle || $fm['fid'] != $db_recycle){
						$num ++;
						$htm_tr = $num % 5 == 0 ? '</tr><tr>' : '';
						if(strpos($fids,','.$fm['fid'].',') !== false){
							$checked = 'checked';
						} else{
							$checked = '';
						}
						$forumcheck .= "<td width='20%'><input type='checkbox' name='fids[]' value='$fm[fid]' $checked>$fm[name]</td>$htm_tr";
					}
				}
				$forumcheck.="</tr></table>";
				include PrintHack('admin');exit;
			} else{
				adminmsg('operate_fail');
			}
		} else{
			if($groupids){
				$condition['group'] = ','.implode(',',$groupids).',';
			}
			if($fids){
				$condition['forum'] = ','.implode(',',$fids).',';
			}
			foreach($condition['credit'] as $key => $value){
				$key == 'rvrc' && $value *= 10;
				$condition['credit'][$key] = (int)$value;
			}
			$condition = addslashes(serialize($condition));
			$db->update("UPDATE pw_tools SET name='$name',vieworder='$vieworder',descrip='$descrip',logo='$logo',state='$state',price='$price',stock='$stock',conditions='$condition' WHERE id='$id'");
			adminmsg('operate_success');
		}
	}
}elseif($job=='usertool'){
	$basename="$admin_file?adminjob=hack&hackset=toolcenter&job=usertool";
	require_once(R_P."require/forum.php");
	if(!$action || $action == 'search'){
		if($action == 'search' && $username){
			$rt     = $db->get_one("SELECT uid FROM pw_members WHERE username='$username'");
			$sqladd = "WHERE u.uid='$rt[uid]'";
		} else{
			$sqladd = '';
		}

		if (!is_numeric($page) || $page<1){
			$page = 1;
		}
		$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
		$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_usertool u $sqladd");
		$sum   = $rt['sum'];
		$total = ceil($sum/$db_perpage);
		$pages = numofpage($sum,$page,$total,"$basename&action=search&username=".rawurlencode($username)."&");

		$tooldb=array();
		$query = $db->query("SELECT u.*,t.name,t.stock,t.price,m.username FROM pw_usertool u LEFT JOIN pw_members m USING(uid) LEFT JOIN pw_tools t ON t.id=u.toolid $sqladd ORDER BY uid $limit");
		while($rt = $db->fetch_array($query)){
			$tooldb[] = $rt;
		}
		include PrintHack('admin');exit;
	} elseif($action == 'edit'){
		(!is_numeric($uid) || !is_numeric($id)) && adminmsg('numerics_checkfailed');
		if(!$step){
			$rt=$db->get_one("SELECT u.*,t.name,t.stock,t.price,m.username FROM pw_usertool u LEFT JOIN pw_members m USING(uid) LEFT JOIN pw_tools t ON t.id=u.toolid WHERE u.uid='$uid' AND u.toolid='$id'");
			include PrintHack('admin');exit;
		} else{
			$db->update("UPDATE pw_usertool SET nums='$nums',sellnums='$sellnums',sellprice='$sellprice' WHERE uid='$uid' AND toolid='$id'");
			adminmsg('operate_success');
		}
	} elseif($action == 'del'){
		(!is_numeric($uid) || !is_numeric($id)) && adminmsg('numerics_checkfailed');
		$db->update("DELETE FROM pw_usertool WHERE uid='$uid' AND toolid='$id'");
		adminmsg('operate_success');
	}
}elseif($job=='tradelog'){
	$basename="$admin_file?adminjob=hack&hackset=toolcenter&job=tradelog";
	require_once(R_P."require/forum.php");
	if($action == 'search' && $username){
		$rt     = $db->get_one("SELECT uid FROM pw_members WHERE username='$username'");
		$sqladd = "AND u.uid='$rt[uid]'";
	} else{
		$sqladd = '';
	}

	if (!is_numeric($page) || $page<1){
		$page = 1;
	}
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_usertool u WHERE sellnums!=0 $sqladd");
	$sum   = $rt['sum'];
	$total = ceil($sum/$db_perpage);
	$pages = numofpage($sum,$page,$total,"$basename&action=search&username=".rawurlencode($username)."&");

	$tooldb=array();
	$query = $db->query("SELECT u.*,t.name,t.descrip,t.logo,m.username FROM pw_usertool u LEFT JOIN pw_members m USING(uid) LEFT JOIN pw_tools t ON t.id=u.toolid WHERE sellnums!=0 $sqladd $limit");
	while($rt = $db->fetch_array($query)){
		$rt['descrip'] = substrs($rt['descrip'],45);
		$tooldb[]      = $rt;
	}
	include PrintHack('admin');exit;
}
?>