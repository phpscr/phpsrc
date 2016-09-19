<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=manager";

if(!$_POST['action']){
	include PrintEot('manager');exit;
} else{
	$rs=$db->get_one("SELECT uid FROM pw_members WHERE username='$username'");
	if(!$rs && $username!=$admin_name){
		$errorname=$username;
		adminmsg('user_not_exists');
	}
	if($password){
		$check_pwd!=$password && adminmsg('password_confirm');
		$S_key=array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#');
		foreach($S_key as $value){
			if (strpos($password,$value)!==false){ 
				adminmsg('illegal_password'); 
			}
		}
		$password=md5($password);
	} else{
		$password=$manager_pwd;
	}
	require_once(R_P.'require/updateset.php');

	$setting = array();
	$setting['user'] = $username;
	$setting['pwd'] = $password;
	write_config($setting);

	if(!$rs && $username==$admin_name){
		$db->update("INSERT INTO pw_members SET username='$username',password='$password',groupid='3',regdate='$timestamp'");
	} else{
		$db->update("UPDATE pw_members SET password='$password',groupid='3' WHERE username='$username'");
	}
	adminmsg('operate_success');
}
function writesqlcinfig(){

}
?>