<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=repair";
@set_time_limit(0);

if (empty($action)){
	require_once(R_P."admin/table.php");
	unset($tabledb['5']);
	include PrintEot('repair');exit;
} elseif($_POST['action'] == 'repair'){
	$table=implode(',',$tabledb);
	$query = $db->query("REPAIR TABLE $table EXTENDED ");
	while($rt = $db->fetch_array($query)){
		$rt['Table']  = substr(strrchr($rt['Table'] ,'.'),1);
		$msgdb[] = $rt;
	}
	include PrintEot('repair');exit;
} elseif($_POST['action'] == 'optimize'){
	$table=implode(',',$tabledb);
	$query = $db->query("OPTIMIZE TABLE $table EXTENDED ");
	while($rt = $db->fetch_array($query)){
		$rt[Table]  = substr(strrchr($rt[Table] ,'.'),1);
		$msgdb[] = $rt;
	}
	include PrintEot('repair');exit;
}
?>