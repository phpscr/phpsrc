<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=help";

if(!$action){
	$helpdb=array();
	$query=$db->query("SELECT * FROM pw_help");
	while($rt=$db->fetch_array($query)){
		$helpdb[]=$rt;
	}
	include PrintEot('help');exit;
}elseif($action=='add'){
	if(!$step){
		include PrintEot('help');exit;
	}elseif($_POST['step']==3){
		(!$title || !$content) && adminmsg('help_empty');
		//$title=Quot_cv($title);
		//$content = Quot_cv($content);
		$content = str_replace('<iframe','&lt;iframe',$content);
		$db->update("INSERT INTO pw_help (title,content) VALUES('$title','$content')");
		adminmsg("operate_success");
	}
}elseif($action=='edit'){
	if(!$_POST['step']){
		extract($db->get_one("SELECT title,content FROM pw_help WHERE id='$id'"));
		!$title && !$content && adminmsg('operate_error');
		include PrintEot('help');exit;
	}elseif($_POST['step']==3){
		(!$title || !$content) && adminmsg('help_empty');
		//$title=Quot_cv($title);
		//$content = Quot_cv($content);
		$content = str_replace('<iframe','&lt;iframe',$content);
		$db->update("UPDATE pw_help SET title='$title',content='$content' WHERE id='$id'");
		adminmsg("operate_success");
	}
}elseif($_POST['action']=='del'){
	if(!$selid=checkselid($selid)){
		adminmsg('operate_error');
	}
	$db->update("DELETE FROM pw_help WHERE id IN($selid)");
	adminmsg("operate_success");
}
?>