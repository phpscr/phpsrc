<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=credit";
if(empty($action)){
	$credit=$db->query("SELECT * FROM pw_credits");
	include PrintEot('credit');exit;
} elseif($action=='edit'){
	if(!$_POST['step']){
		$creditdb=$db->get_one("SELECT * FROM pw_credits WHERE cid='$cid'");
		if(!$creditdb)adminmsg('credit_error');
		include PrintEot('credit');exit;
	} else{
		$db->update("UPDATE pw_credits SET name='$name',unit='$unit',description='$description' WHERE cid='$cid'");
		updatecache_cr();
		adminmsg('operate_success');
	}
}elseif($action=='newcredit'){
	if(!$_POST['step']){
		include PrintEot('credit');exit;
	} else{
		$db->update("INSERT INTO pw_credits(name,unit,description) VALUES('$name','$unit','$description')");
		updatecache_cr();
		adminmsg('operate_success');
	}
}elseif($_POST['action']=='delete'){
	$delcids='';
	if(!$delcid)adminmsg('operate_error');
	foreach($delcid as $id){
		is_numeric($id) && $delcids.=$id.',';
	}
	if($delcids){
		$delcids=substr($delcids,0,-1);
		$db->update("DELETE FROM pw_credits WHERE cid IN($delcids)");
		$db->update("DELETE FROM pw_membercredit WHERE cid IN($delcids)");
		updatecache_cr();
		adminmsg('operate_success');
	} else{
		adminmsg('operate_fail');
	}
}
?>
