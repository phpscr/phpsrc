<?php
!function_exists('db_cv') && exit('Forbidden');
$get_today=get_date($timestamp,'Y-m-d');
$todayyear=substr($get_today,0,strpos($get_today,'-'));
$gettoday=substr($get_today,strpos($get_today,'-')+1);
$query = $db->query("SELECT uid,username,bday,gender,newpm FROM pw_members WHERE RIGHT(bday,5)='$gettoday' LIMIT 100");
$uids='';
include(GetLang('other'));
while($rt = $db->fetch_array($query)){
	$birthday=substr($rt['bday'],strpos($rt['bday'],'-')+1);
	if($gettoday==$birthday){
		$birthyear=substr($rt['bday'],0,strpos($rt['bday'],'-'));
		$age=$todayyear-$birthyear;
		$writemsg=$rt['username'];
		if($rt['gender']==1){
			$writemsg.=$lang['men'];
		}elseif($rt['gender']==2){
			$writemsg.=$lang['women'];
		}
		$writemsg.=str_replace('age',$age,$lang['send_content']);
		$msg_title=$lang['send_title'];
		$db->update("INSERT INTO pw_msg (touid,fromuid,username,type,ifnew,title,mdate,content) VALUES ('$rt[uid]','0','System','rebox','1','$msg_title','$timestamp','$writemsg')");
		$uids .= $uids ? ','.$rt['uid'] : $rt['uid'];
	}
}
$uids && $db->update("UPDATE pw_members SET newpm=newpm+1 WHERE uid IN($uids) AND (newpm='0' OR newpm='2')");
?>