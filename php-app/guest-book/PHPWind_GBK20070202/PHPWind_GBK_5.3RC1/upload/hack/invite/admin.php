<?php
!function_exists('adminmsg') && exit('Forbidden');
@include_once(D_P.'data/bbscache/inv_config.php');
if(!$action){
	ifcheck($inv_open,'open');
	$usergroup   = "";
	$num         = 0;
	foreach($ltitle as $key=>$value){
		if($key != 1 && $key != 2){
			$checked = '';
			if(strpos($inv_groups,','.$key.',') !== false){
				$checked = 'checked';
			}
			$num++;
			$htm_tr = $num%4 == 0 ?  '</tr><tr>' : '';
			$usergroup .=" <td width='20%'><input type='checkbox' name='groups[]' value='$key' $checked>$value</td>$htm_tr";
		}
	}
	$rvrc_st=$money_st=$credit_st=$currency_st='';
	${$inv_credit.'_st'}='selected';
	include PrintHack('admin');exit;
}elseif($_POST['action']=='unsubmit'){
	if (!is_numeric($config['open'])) $config['open']=1;
	if (!is_numeric($config['days'])) $config['days']=30;
	if (!is_numeric($config['limitdays'])) $config['limitdays']=0;
	if (!is_numeric($config['costs'])) $config['costs']=100;
	if (is_array($groups)){
		$config['groups'] = ','.implode(',',$groups).',';
	} else {
		$config['groups'] = '';
	}
	foreach($config as $key=>$value){
		$db->pw_update(
			"SELECT hk_name FROM pw_hack WHERE hk_name='inv_$key'",
			"UPDATE pw_hack SET hk_value='$value' WHERE hk_name='inv_$key'",
			"INSERT INTO pw_hack(hk_name,hk_value) VALUES ('inv_$key','$value')"
		);
	}
	updatecache_inv();
	adminmsg('operate_success');
}elseif($action=='manager'){
	require_once(R_P.'require/forum.php');
	!$type && $type='1';
	$sel_1=$sel_2=$sel_3=$sel_4='';
	$inv_days*=86400;
	if($type=='1'){
		$sql="WHERE i.ifused='0' AND $timestamp-i.createtime<'$inv_days'";
		$sel_1='selected';
	}elseif($type=='2'){
		$sql="WHERE i.ifused='1'";
		$sel_2='selected';
	}elseif($type=='3'){
		$sql="WHERE i.ifused<'2' AND $timestamp-i.createtime>'$inv_days'";
		$sel_3='selected';
	}else{
		$sql="WHERE i.ifused='2'";
		$sel_4='selected';		
	}
	$db_showperpage = 20;
	(!is_numeric($page) || $page<1) && $page = 1;
	$limit = "LIMIT ".($page-1)*$db_showperpage.",$db_showperpage";
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_invitecode i $sql");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_showperpage),"$basename&action=manager&type=$type&");

	$query=$db->query("SELECT i.*,m.username FROM pw_invitecode i LEFT JOIN pw_members m USING(uid) $sql $limit");
	$invdb=array();
	$i=1;
	while($rt=$db->fetch_array($query)){
		$rt['num']=($page-1)*$db_showperpage+$i++;
		$rt['createtime']=get_date($rt['createtime'],'Y-m-d H:i:s');
		$invdb[]=$rt;
	}
	include PrintHack('admin');exit;
}elseif($_POST['action']=='delete'){
	if(!$selid=checkselid($selid)){
		adminmsg('operate_error');
	}
	$db->update("DELETE FROM pw_invitecode WHERE id IN ($selid)");
	adminmsg('operate_success');
}

?>