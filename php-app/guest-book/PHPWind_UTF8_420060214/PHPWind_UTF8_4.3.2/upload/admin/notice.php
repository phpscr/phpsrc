<?php
!function_exists('SafeFunc') && exit('Forbidden');

$timestamp = time();
if(@filemtime(D_P.'data/bbscache/file_lock.txt')<$timestamp-3600*24){
	require_once(R_P."admin/admincp.php");
	$wget=@file('http://www.phpwind.com/src/pw432/free/utf8_union.php?url=$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]');
	$wget=@implode('',$wget);
	$wget_a=explode("\t",$wget);
	$rt=$db->get_one("SELECT higholnum FROM pw_bbsinfo WHERE id=1");
	$rt['higholnum']<(int)$wget_a[4] && $wget_a[1]='';
	$rt['higholnum']<(int)$wget_a[5] && $wget_a[2]='';
	$rt['higholnum']<(int)$wget_a[6] && $wget_a[3]='';
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
	writenotice(D_P.'data/bbscache/file_lock.txt','');
}elseif(@filemtime(D_P.'data/bbscache/info.txt')<$timestamp-3600*24){
	$wget=@file('http://www.phpwind.com/src/pw432/free/utf8_info.php');
	$wget=@implode('',$wget);
	writenotice(D_P.'data/bbscache/info.txt',$wget);
}elseif(@filemtime(D_P.'data/bbscache/notice.txt')<$timestamp-3600*24){
	$wget=@file("http://www.phpwind.com/src/pw432/free/utf8_notice.php?url=$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]");
	$wget=@implode('',$wget);
	writenotice(D_P.'data/bbscache/notice.txt',$wget);
}elseif(@filemtime(D_P.'data/bbscache/userpay.txt')<$timestamp-3600*24){
	$wget=@file('http://www.phpwind.com/src/pw432/free/utf8_userpay2.php');
	$wget=@implode('',$wget);
	if($wget){
		writenotice(D_P.'data/bbscache/userpay.txt',$wget);
	}
}elseif(@filemtime(D_P.'data/bbscache/check_sess.txt')<$timestamp-3600*24){
	$dir=opendir(D_P.'data/bbscache');
	while(false !== ($file = readdir($dir))){
		if(ereg("^sess_",$file) && strpos($file,'.')===false){
			if(filemtime(D_P."data/bbscache/$file")<$timestamp-3600){
				unlink(D_P."data/bbscache/$file");
			}
		}
	}
	writenotice(D_P.'data/bbscache/check_sess.txt','');
}
function readnotice($filename,$method="rb"){
    if($handle=@fopen($filename,$method)){
        flock($handle,LOCK_SH);
        $filedata=@fread($handle,filesize($filename));
        fclose($handle);
    }
    return $filedata;
}
function writenotice($filename,$data,$method="rb+",$iflock=1,$chmod=1){
	$check && strpos($filename,'..')!==false && exit('Forbidden');
	touch($filename);
	$handle=fopen($filename,$method);
	if($iflock){
		flock($handle,LOCK_EX);
	}
	fwrite($handle,$data);
	if($method=="rb+") ftruncate($handle,strlen($data));
	fclose($handle);
	$chmod && @chmod($filename,0777);
}
exit;
?>