<?php
require_once('global.php');

!($pre_url=$_SERVER['HTTP_REFERER']) && $pre_url = $db_bfn;

if(strpos($pre_url,'login.php')!==false || strpos($pre_url,'register.php')!==false) {
	$pre_url = $db_bfn;
}
if (!$passport_ifopen && $groupid!='guest' && $action!="quit"){
	Showmsg('login_have');
}
!$action && $action="login";

list(,$logingd)=explode("\t",$db_gdcheck);

if ($action=="login"){
	if (!$step){
		$jumpurl=$pre_url;		
		require_once(R_P.'require/header.php');
		require_once PrintEot('login');footer();	
	} elseif($_POST['step']==2){
		$logingd && GdConfirm($gdcode);
		require_once(R_P.'require/checkpass.php');
		include_once(D_P."data/bbscache/dbreg.php");

		unset($hp);
		if($pwuser && $pwpwd){
			$md5_pwpwd=md5($pwpwd);
			list($winduid,$groupid,$pwpwd)=checkpass($pwuser,$md5_pwpwd);
		} else{
			Showmsg('login_empty');
		}
		if(file_exists(D_P."data/groupdb/group_$groupid.php")){
			require_once Pcv(D_P."data/groupdb/group_$groupid.php");
		}else{
			require_once(D_P."data/groupdb/group_1.php");
		}
		$windpwd = $pwpwd;
		$cktime != 0 && $cktime += $timestamp;
		Cookie("winduser",StrCode($winduid."\t".$windpwd),$cktime);
		Cookie('lastvisit','',0);//��$lastvist����Խ���ע��Ļ�Ա������յ��û�Ա��
		if($db_autoban){
			require_once(R_P.'require/autoban.php');
			autoban($winduid);
		}
		$gp_allowhide && $hideid==1 ? Cookie('hideid',$hideid,$cktime) : Loginipwrite($winduid);
		empty($jumpurl) && $jumpurl=$db_bfn;

		//passport
		if($passport_ifopen && $passport_type == 'server'){
			require_once(R_P.'require/passport_server.php');
		}
		//passport
		refreshto($jumpurl,'have_login');
	}
} elseif($action=="quit"){
	require_once(R_P.'require/checkpass.php');
	if($groupid=='6'){
		$bandb=$db->get_one("SELECT type FROM pw_banuser WHERE uid='$winduid'");
		if($bandb['type']==3){
			Cookie('force',$winduid);
		}
	}
	Loginout();

	//passport
	if($passport_ifopen && $passport_type == 'server'){
		require_once(R_P.'require/passport_server.php');
	}
	//passport

	refreshto($pre_url,'login_out');/*�˳�url ��Ҫʹ��$pre_url ��Ϊ������޸����������һ��ѭ����ת*/
}
?>