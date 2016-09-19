<?php
!function_exists('readover') && exit('Forbidden');

function wap_header($id,$title,$url="",$t=""){
	header("Content-type: text/vnd.wap.wml;");
	require PrintEot('wap_header');
}
function wap_footer(){
	global $wind_version,$db_obstart,$windid,$charset;
	require_once PrintEot('wap_footer');	
	$output = str_replace(array('<!--<!---->','<!---->'),array('',''),ob_get_contents());
	if($charset != 'utf8'){
		$chs = new Chinese($charset,'UTF8');
		$output=$chs->Convert($output);
	}
	ob_end_clean();
	$db_obstart == 1 && function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();
	echo $output;
	flush;
	exit;
}
function wap_output($output){
	echo $output;
}
function wap_msg($msg,$url="",$t="30"){
	global $db_bbsname,$db_obstart;
	ob_end_clean();
	$db_obstart == 1 && function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();
	wap_header('msg',$db_bbsname,$url,$t);
	require_once GetLang('wap');
	$lang[$msg] && $msg=$lang[$msg];
	wap_output("<p>$msg</p>\n");
	wap_footer();
}
function wap_login($username,$password){
	global $db,$timestamp,$onlineip,$db_ckpath,$db_ckdomain,$db_bbsurl;

	$men=$db->get_one("SELECT m.uid,m.password,m.groupid,m.yz,md.onlineip FROM pw_members m LEFT JOIN pw_memberdata md ON md.uid=m.uid WHERE username='$username'");
	if($men){
		$e_login=explode("|",$men['onlineip']);
		if($e_login[0]!=$onlineip.' *' || ($timestamp-$e_login[1])>600 || $e_login[2]>1 ){
			$men_uid=$men['uid'];
			$men_pwd=$men['password'];
			$check_pwd=$password;
			if($men['yz'] > 2){
				wap_msg('c');
			}
			if(strlen($men_pwd)==16){
				$check_pwd=substr($password,8,16);/*支持 16 位 md5截取密码*/
			}
			if($men_pwd==$check_pwd){
				if(strlen($men_pwd)==16){
					$db->update("UPDATE pw_members SET password='$password' WHERE uid='$men_uid'");
				}
				$L_groupid=(int)$men['groupid'];
				Cookie("ck_info",$db_ckpath."\t".$db_ckdomain);
			}else{
				global $L_T;
				$L_T=$e_login[2];
				$L_T ? $L_T--:$L_T=5;
				$F_login="$onlineip *|$timestamp|$L_T";
				$db->update("UPDATE pw_memberdata SET onlineip='$F_login' WHERE uid='$men_uid'");
				wap_msg('login_pwd_error');
			}
		}else{
			global $L_T;
			$L_T=600-($timestamp-$e_login[1]);
			wap_msg('login_forbid');
		}
	} else {
		global $errorname;
		$errorname=$username;
		wap_msg('user_not_exists');
	}
	Cookie("winduser",StrCode($men_uid."\t".PwdCode($password)));
	Cookie('lastvisit','',0);
	wap_msg('wap_login','index.php');
}
?>