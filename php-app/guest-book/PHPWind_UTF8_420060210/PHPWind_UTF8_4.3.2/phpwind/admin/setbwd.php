<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=setbwd";
if (empty($action)){
	$replacedb=$wordfbdb=array();
	$query = $db->query("SELECT * FROM pw_wordfb");
	while($rt = $db->fetch_array($query)){
		HtmlConvert($rt);
		if($rt['type'] == 0){
			$replacedb[]=$rt;
		} else{
			$wordfbdb[]=$rt;
		}
	}
	include PrintEot('setbwd');exit;
} elseif($action=="add") {
	if(empty($addword) || empty($addreplace))adminmsg('operate_fail');
	$db->update("INSERT INTO pw_wordfb(word,wordreplace,type) VALUES('$addword','$addreplace','$type')");
	updatecache_w();
	adminmsg('operate_success');
} elseif($action=="update"){
	if(is_array($worddel)){
		$ids = '';
		foreach($worddel as $key => $value){
			is_numeric($value) && $ids .= $value.',';
		}
		$ids = substr($ids,0,-1);
		$db->update("DELETE FROM pw_wordfb WHERE id IN($ids)");
	}
	if(is_array($wordfind)){
		foreach($wordfind as $key=>$value){
			$db->update("UPDATE pw_wordfb SET word='$value',wordreplace='$wordreplace[$key]' WHERE id='$key'");
		}
	}

	updatecache_w();
	adminmsg('operate_success');
}
?>