<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=share";
if(empty($action)){
	$threaddb=array();
	$query=$db->query("SELECT * FROM pw_sharelinks ORDER BY threadorder");
	while($share=$db->fetch_array($query)){
		strlen($share['name'])>30 && $share['name']=substrs($share['name'],30);
		strlen($share['url'])>30 && $share['url']=substrs($share['url'],30);
		strlen($share['descrip'])>30 && $share['descrip']=substrs($share['descrip'],30);
		$threaddb[]=$share;
	}
	include PrintEot('sharelink');exit;
} elseif($action=="add"){
	if(!$_POST['step']){
		include PrintEot('sharelink');exit;
	} elseif(!empty($name) && !empty($url)) {
		$name    = Char_cv($name);
		$url     = Char_cv($url);
		$descrip = Char_cv($descrip);
		$logo    = Char_cv($logo);
		$db->update("INSERT INTO pw_sharelinks (threadorder ,name ,url ,descrip ,logo ) VALUES ('$threadorder', '$name', '$url', '$descrip', '$logo');");
		updatecache_i();
		adminmsg('operate_success');
	} else{
		adminmsg('operate_fail');
	}
} elseif($action=="edit"){
	if(!$_POST['step']){
		@extract($db->get_one("SELECT * FROM pw_sharelinks WHERE sid='$sid'"));
		include PrintEot('sharelink');exit;
	} else{
		$name    = Char_cv($name);
		$url     = Char_cv($url);
		$descrip = Char_cv($descrip);
		$logo    = Char_cv($logo);
		$db->update("UPDATE pw_sharelinks SET threadorder='$threadorder',name='$name',url='$url',descrip='$descrip',logo='$logo' WHERE sid='$sid'");
		updatecache_i();
		adminmsg('operate_success');
	}
} elseif($_POST['action']=="del"){
	if(!$deiaid) adminmsg('operate_error');
	foreach($deiaid as $sid){
		$db->update("DELETE FROM pw_sharelinks WHERE sid='$sid'");
	}
	updatecache_i();
	adminmsg('operate_success');
}
?>