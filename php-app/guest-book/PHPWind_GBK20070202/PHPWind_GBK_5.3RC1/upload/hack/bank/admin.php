<?php
!function_exists('adminmsg') && exit('Forbidden');

include(D_P.'data/bbscache/bk_config.php');
if (!$action || $action=="add"){
	require_once GetLang('all');
	list($db_moneyname,,$db_rvrcname,,$db_creditname,)=explode("\t",$db_credits);

	$c_types=array('rvrc'=>$db_rvrcname,'money'=>$db_moneyname,'credit'=>$db_creditname);
	include(D_P."data/bbscache/creditdb.php");
	foreach($_CREDITDB as $key=>$value){
		$c_types[$key]=$value[0];
	}
	$jifen='';	
	foreach($c_types as $key=>$value){
		$jifen.="<option value=$key>$value</option>";
	}
}
if (!$action) {
	if($bk_open)$bk_open_1="checked";else $bk_open_0="checked"; 
	if($bk_rvrc)$bk_rvrc_1="checked";else $bk_rvrc_0="checked";
	if($bk_virement)$bk_virement_1="checked";else $bk_virement_0="checked";
	include PrintHack('admin');exit;
}elseif($action=="log"){
	require_once GetLang('log');
	include_once(R_P.'require/forum.php');
	$sqladd = '';
	$select = array();
	if ($type && in_array($type,array('bk_save','bk_draw','bk_vire','bk_credit'))){
		$sqladd = "AND type='$type'";
		$select[$type] = "selected";
	}
	$username1 && $sqladd .= " AND username1='$username1'";
	$keyword   && $sqladd .= " AND descrip LIKE '%$keyword%'";

	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_forumlog WHERE type LIKE 'bk\_%' $sqladd");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&action=log&type=$type&username1=$username1&keyword=$keyword&");
	$query = $db->query("SELECT * FROM pw_forumlog WHERE type LIKE 'bk\_%' $sqladd ORDER BY id DESC $limit");
	while($rt = $db->fetch_array($query)){
		$rt['date']  = get_date($rt['timestamp']);
		$rt['descrip']= str_replace(array('[b]','[/b]'),array('<b>','</b>'),$rt['descrip']);
		$logdb[] = $rt;
	}

	include PrintHack('admin');exit;
}elseif($action=="dellog"){
	$basename="$basename&action=log";
	if(!$selid = checkselid($selid)){
		$basename="javascript:history.go(-1);";
		adminmsg('operate_error');	
	}
	$db->update("DELETE FROM pw_forumlog WHERE id IN($selid) AND type IN('bk_save','bk_draw','bk_vire','bk_credit')");
	adminmsg('operate_success');
}elseif($action=="unsubmit"){
	
	if (!is_numeric($config['open'])) $config['open']=1;
	if (!is_numeric($config['virement'])) $config['virement']=0;
	if (!is_numeric($config['timelimit'])) $config['timelimit']=60;
	if (!is_numeric($config['virelimit'])) $config['virelimit']=500;
	if (!is_numeric($config['virerate'])) $config['virerate']=10;
	if (!is_numeric($config['rate'])) $config['rate']=1;
	if (!is_numeric($config['drate'])) $config['drate']=1;
	if (!is_numeric($config['ddate'])) $config['ddate']=12;
	foreach($config as $key=>$value){
		$rt=$db->get_one("SELECT * FROM pw_hack WHERE hk_name='bk_$key'");
		if($rt){
			$db->update("UPDATE pw_hack SET hk_value='$value' WHERE hk_name='bk_$key'");
		}else{
			$db->update("INSERT INTO pw_hack(hk_name,hk_value) VALUES ('bk_$key','$value')");
		}
	}
	updatecache_bk_A($bk_A);
	adminmsg('operate_success');
} elseif($action=="add"){
	if($c_types[$jifen1]==$c_types[$jifen2]){
		adminmsg('bankset_save');
	}
	if(isset($bk_A[$jifen1.'_'.$jifen2])){
		adminmsg('bankset_exists');
	}
	if(empty($rate1) || $rate1<=0 || empty($rate2) || $rate2<=0){
		adminmsg('bankset_rate_error');
	}
	$bk_A[$jifen1.'_'.$jifen2]=array($c_types[$jifen1],$c_types[$jifen2],$rate1,$rate2,$ifuse);
	updatecache_bk_A($bk_A);
	adminmsg('operate_success');
} elseif($action=="del"){
	unset($bk_A[$delid]);
	updatecache_bk_A($bk_A);
	adminmsg('operate_success');
} elseif($action=="change"){
	$bk_A[$changeid][4]=$bk_A[$changeid][4]==1 ? 0 : 1 ;
	updatecache_bk_A($bk_A);
	adminmsg('operate_success');
}
function updatecache_bk_A($array){
	global $db;
	$bk_A=serialize($array);
	$rt=$db->get_one("SELECT * FROM pw_hack WHERE hk_name='bk_A'");
	if($rt){
		$db->update("UPDATE pw_hack SET hk_value='$bk_A' WHERE hk_name='bk_A'");
	}else{
		$db->update("INSERT INTO pw_hack(hk_name,hk_value) VALUES ('bk_A','$bk_A')");
	}
	updatecache_bk();
}
?>