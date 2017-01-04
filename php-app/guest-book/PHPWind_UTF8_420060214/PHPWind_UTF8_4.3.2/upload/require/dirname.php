<?php
!function_exists('readover') && exit('Forbidden');

$dirname = '';
if($db_forumdir == '1'){
	$dirname = substr($_SERVER['PHP_SELF'],0,strrpos($_SERVER['PHP_SELF'],'/'));
	$dirname = substr($dirname,strrpos($dirname,'/')+1);
}elseif($db_forumdir=='2'){
	$dirname=substr($_SERVER['HTTP_HOST'],0,strpos($_SERVER['HTTP_HOST'],'.'));
}
if($dirname){
	$fids=$ext='';
	$query = $db->query("SELECT fid FROM pw_forums WHERE type='category' AND dirname='$dirname'");
	while ($rt = $db->fetch_array($query)){
		$fids .= $ext.$rt['fid'];
		$ext   = ',';
	}
	if($fids){
		$threadcate=" AND (f.fid IN($fids) OR f.fup IN($fids))";
	}else{
		$threadcate=" AND (f.type!='category' OR f.type='category' AND f.dirname='')";
	}
}else{
	$threadcate=" AND (f.type!='category' OR f.type='category' AND f.dirname='')";
}
?>