<?php
!function_exists('db_cv') && exit('Forbidden');

$query=$db->query("SELECT uid FROM pw_banuser WHERE type='1' AND $timestamp-startdate>days*86400");
$uids='';
while($rt=$db->fetch_array($query)){
	$uids .= $uids ? ','.$rt['uid'] : $rt['uid'];
}
if($uids){
	$db->update("DELETE FROM pw_banuser WHERE uid IN($uids)");
	$db->update("UPDATE pw_members SET groupid='-1' WHERE groupid='6' AND uid IN($uids)");
}
?>