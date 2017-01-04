<?php
!function_exists('readover') && exit('Forbidden');

$a_sql=$u ? "uid='$u'" : "username='$a'";
$query=$db->query("SELECT uid AS u FROM pw_members WHERE $a_sql");// UB
if($db->num_rows($query)==1){
	$u_db=$db->fetch_array($query);
	$u=$u_db['u'];
	$query=$db->query("SELECT adsips FROM pw_memberinfo WHERE uid='$u'");
	if($db->num_rows($query)>0){
		$u_db=$db->fetch_array($query);
		$u_db['adsips']=strlen($u_db['adsips']) > 15000 ? '' : $u_db['adsips']."\t";
		if(strpos("\t".$u_db['adsips']."\t","\t".$onlineip."\t")===false){
			$db->update("UPDATE pw_memberinfo SET adsips='$u_db[adsips]$onlineip' WHERE uid='$u'");
			$db->update("UPDATE pw_memberdata SET credit=credit+1 WHERE uid='$u'");
		}
	}else{
		$db->update("INSERT INTO pw_memberinfo(uid,adsips) VALUES('$u','$onlineip')");
		$db->update("UPDATE pw_memberdata SET credit=credit+1 WHERE uid='$u'");
	}
}
Cookie('userads','',0);
?>