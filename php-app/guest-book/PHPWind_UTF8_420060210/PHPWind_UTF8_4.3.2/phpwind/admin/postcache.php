<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=postcache";
include D_P."data/bbscache/dbset.php";

if(empty($action)){	
	$motiondb=$facedb=array();
	$query=$db->query("SELECT * FROM pw_actions");
	while($postcache=$db->fetch_array($query)){
		$motiondb[]=$postcache;
	}
	$query=$db->query("SELECT * FROM pw_smiles");
	while($postcache=$db->fetch_array($query)){
		$facedb[]=$postcache;
	}
	include PrintEot('postcache');exit;
} elseif($action=='addact'){
	if (empty($motion1) || empty($motion2) || empty($motion3)){
		adminmsg('postcache_emmpty');
	}
	$motion1=Char_cv($motion1);
	$motion2=Char_cv($motion2);	
	$motion3=Char_cv($motion3);
	$db->update("INSERT INTO pw_actions(images,name,descrip) VALUES('$motion3','$motion1','$motion2') ");
	updatecache_p();
	adminmsg('operate_success');
} elseif($action=='addface'){
	if (empty($face1)){
		adminmsg('postcache_emmpty');
	}
	$face1=Char_cv(ieconvert($face1));
	$db->update("INSERT INTO pw_smiles(image) VALUES('$face1') ");
	updatecache_p();
	adminmsg('operate_success');
} elseif($action=='delete'){
	$delids='';
	if(is_array($delid)){
		foreach($delid as $value){
			is_numeric($value) && $delids.=$value.',';
		}
	}
	$delids=substr($delids,0,-1);
	!$delids && adminmsg('operate_error');
	$db->update("DELETE FROM $table WHERE id IN($delids)");
	updatecache_p();
	adminmsg('operate_success');
}
?>