<?php
!function_exists('SafeFunc') && exit('Forbidden');

$timestamp = time();
if(@filemtime(D_P.'data/bbscache/file_lock.txt')<$timestamp-3600*24){
	$wget=PostHost("http://nt.phpwind.com/src/pw53/union1.php?url=$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]&repair=$wind_repair&charset=$db_charset","");
	$wget=preg_replace("/(.+)(\<pwret\>)(.+)?(\<\/pwret\>)(.*?)/is","\\3",$wget);
	$wget=explode("\t<windtag>\t",$wget);
	$wget_a=explode("\t",$wget[0]);
	$wget_b=explode("\t",$wget[1]);
	$rt=$db->get_one("SELECT higholnum FROM pw_bbsinfo WHERE id=1");
	foreach($wget_a as $key=>$value){
		$rt['higholnum']<(int)$wget_b[$key] && $wget_a[$key]='';
	}
	$wget=implode("\t",$wget_a);
	if($db_union!=$wget){
		$rt=$db->get_one("SELECT db_name FROM pw_config WHERE db_name='db_union'");
		if($rt['db_name']){
			$db->update("UPDATE pw_config SET db_value='$wget' WHERE db_name='db_union'");
		}else{
			$db->update("INSERT INTO pw_config(db_name,db_value) VALUES ('db_union','$wget')");
		}
		updatecache_c();
	}
	writeover(D_P.'data/bbscache/file_lock.txt','');
}elseif(@filemtime(D_P.'data/bbscache/info.txt')<$timestamp-3600*24){
	$wget=PostHost("http://nt.phpwind.com/src/pw53/info1.php?charset=$db_charset","");
	$wget=preg_replace("/(.+)(\<pwret\>)(.+)?(\<\/pwret\>)(.*?)/is","\\3",$wget);
	writeover(D_P.'data/bbscache/info.txt',$wget);
}elseif(@filemtime(D_P.'data/bbscache/userpay.txt')<$timestamp-3600*24){
	$wget=PostHost("http://nt.phpwind.com/src/pw53/userpay1.php?charset=$db_charset","");
	$wget=preg_replace("/(.+)(\<pwret\>)(.+)?(\<\/pwret\>)(.*?)/is","\\3",$wget);
	writeover(D_P.'data/bbscache/userpay.txt',$wget);
}
exit;
?>