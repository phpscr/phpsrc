<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=rightset";

if(empty($action)){
	$groupdb=array();
	$query=$db->query("SELECT gid,grouptitle AS gptitle FROM  pw_usergroups WHERE gptype<>'member' AND (allowadmincp='1' OR gid='3' OR gid='4' OR gid='5')");
	while($group=$db->fetch_array($query)){
		$groupdb[]=$group;
	}
	include PrintEot('rightset');exit;
} elseif($action=='edit'){
	if(!$_POST['step']){
		require  GetLang('left');
		@include GetLang('c_left');
		$rightdb=$lang;
		unset($lang);
		$rightselect='';
		$right=$db->get_one("SELECT value FROM pw_adminset WHERE gid='$gid'");
		$right=P_unserialize($right['value']);
		foreach($rightdb as $key1=>$value1){
			$rightselect.="<tr class=\"head_2\"><td width=60% colspan=2>$key1</td></tr>";
			foreach($value1 as $key2=>$value2){
				ifcheck($right[$key2],$key2);
				if(is_array($value2)){
					$value2 = $key1;
				}
				$rightselect.="<tr class=\"b\"><td width=60%>$value2</td>
				<td><input type=radio value=1 ${$key2.'_Y'} name=rightdb[$key2]>$_yes 
				<input type=radio value=0 ${$key2.'_N'} name=rightdb[$key2]>$_no </td></tr>";
			}
		}
		include PrintEot('rightset');exit;
	} else{
		$rightdb=P_serialize($rightdb);
		$rt=$db->get_one("SELECT gid FROM pw_adminset WHERE gid='$gid'");
		if($rt['gid']){
			$db->update("UPDATE pw_adminset SET value='$rightdb' WHERE gid='$gid'");
		} else{
			$db->update("INSERT INTO pw_adminset VALUES('$gid','$rightdb')");
		}
		adminmsg('operate_success');
	}
}