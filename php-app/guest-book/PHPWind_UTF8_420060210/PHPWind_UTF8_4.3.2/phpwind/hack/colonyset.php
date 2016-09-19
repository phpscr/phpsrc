<?php
!function_exists('readover') && exit('Forbidden');
require_once(D_P."data/bbscache/cn_config.php");
$db_perpage=20;

if (!$action){
	$basename="$amind_file?adminjob=hack&hackset=colony&id=colony";

	ifcheck($cn_open,'open');
	ifcheck($cn_remove,'remove');
	ifcheck($cn_newcolony,'newcolony');
	ifcheck($cn_virement,'virement');
	$cn_imgsize /= 1024;
	$usergroup   = "";
	$num         = 0;
	foreach($ltitle as $key=>$value){
		if($key != 1 && $key != 2){
			$checked = '';
			if(strpos($cn_groups,','.$key.',') !== false){
				$checked = 'checked';
			}
			$num++;
			$htm_tr = $num%4 == 0 ?  '</tr><tr>' : '';
			$usergroup .=" <td width='20%'><input type='checkbox' name='groups[]' value='$key' $checked>$value</td>$htm_tr";
		}
	}
	require_once PrintEot('colonyset');
}elseif($action=='classset'){
	$basename="$amind_file?adminjob=hack&hackset=colony&id=colony&action=classset";

	$query = $db->query("SELECT * FROM pw_cnclass");
	while ($rt = $db->fetch_array($query)){
		$cnclass[]=$rt;
	}
	require_once PrintEot('colonyset');
}elseif($action=='colonyset'){
	$basename="$amind_file?adminjob=hack&hackset=colony&id=colony&action=colonyset";

	require_once(R_P.'require/forum.php');
	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_colonys");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&");

	$query = $db->query("SELECT c.id,c.cname,cl.cname AS classname FROM pw_colonys c LEFT JOIN pw_cnclass cl ON cl.cid=c.classid $limit");
	while ($rt = $db->fetch_array($query)){
		$colonys[]=$rt;
	}
	require_once PrintEot('colonyset');
}elseif($action=='log'){
	$basename="$amind_file?adminjob=hack&hackset=colony&id=colony&action=log";
	if(!$job){
		require_once GetLang('log');
		include_once(R_P.'require/forum.php');

		if($keyword){
			$sqladd = " AND descrip LIKE '%$keyword%'";
			$urladd = "&keyword=".rawurlencode($keyword);
		}else{
			$sqladd = $urladd = '';
		}
		(!is_numeric($page) || $page < 1) && $page = 1;
		$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
		$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_forumlog WHERE type LIKE 'cy\_%' $sqladd");
		$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename$urladd&");

		$logdb=array();
		$query = $db->query("SELECT * FROM pw_forumlog WHERE type LIKE 'cy\_%' $sqladd ORDER BY id DESC $limit");
		while($rt = $db->fetch_array($query)){
			$rt['date']  = get_date($rt['timestamp']);
			$logdb[] = $rt;
		}
		require_once PrintEot('colonyset');
	}elseif($job=='del'){
		if(!$selid = checkselid($selid)){
			$basename="javascript:history.go(-1);";
			adminmsg('operate_error');	
		}
		$db->update("DELETE FROM pw_forumlog WHERE type LIKE 'cy\_%' AND id IN($selid)");
		adminmsg("operate_success");
	}
} elseif ($action == 'submit'){
	$config['cn_createmoney'] = (int)$config['cn_createmoney'];
	$config['cn_allowcreate'] = (int)$config['cn_allowcreate'];
	$config['cn_allowjoin']   = (int)$config['cn_allowjoin'];
	$config['cn_memberfull']  = (int)$config['cn_memberfull'];
	$config['cn_imgsize']     = (int)$config['cn_imgsize'] * 1024;
	if (is_array($groups)){
		$config['cn_groups'] = ','.implode(',',$groups).',';
	} else {
		$config['cn_groups'] = '';
	}
	foreach($config as $key => $value){
		$db->pw_update(
			"SELECT hk_name FROM pw_hack WHERE hk_name='$key'",
			"UPDATE pw_hack SET hk_value='$value' WHERE hk_name='$key'",
			"INSERT INTO pw_hack(hk_name,hk_value) VALUES ('$key','$value')"
		);
	}
	updatecache_cy();
	adminmsg("operate_success");
}elseif($action=='addclass'){
	!$cname && adminmsg("colonyset_empty");
	$cname = Char_cv($cname);
	$rt = $db->get_one("SELECT cid FROM pw_cnclass WHERE cname='$cname'");
	if($rt['cid']){
		adminmsg('colonyset_same');
	}
	$db->update("INSERT INTO pw_cnclass(cname) VALUES('$cname')");
	adminmsg("colonyset_addsuccess");
}elseif($action=='delclass'){
	$basename="$amind_file?adminjob=hack&hackset=colony&id=colony&action=classset";
	$db->update("UPDATE pw_colonys SET classid='' WHERE classid='$id'");
	$db->update("DELETE FROM pw_cnclass WHERE cid='$id'");
	adminmsg("分类删除成功。");
}elseif($action=='delcolony'){
	$basename="$amind_file?adminjob=hack&hackset=colony&id=colony&action=colonyset";
	$rt = $db->get_one("SELECT id,cnimg FROM pw_colonys WHERE id='$id'");
	if(!$rt){
		adminmsg("colonyset_noclass");
	}
	if($rt['cnimg'] && file_exists("$imgdir/cn_img/$rt[cnimg]")){
		P_unlink("$imgdir/cn_img/$rt[cnimg]");
	}
	$db->update("DELETE FROM pw_argument WHERE gid='$rt[id]'");
	$db->update("DELETE FROM pw_cmembers WHERE colonyid='$rt[id]'");
	$db->update("DELETE FROM pw_colonys  WHERE id='$rt[id]'");
	adminmsg("colonyset_delsuccess");
}
?>