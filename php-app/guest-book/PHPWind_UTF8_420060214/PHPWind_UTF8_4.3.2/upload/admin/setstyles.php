<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=setstyles";
if(!$action){
	if($job!="submit"){
		empty($skin) && $skin=$db_defaultstyle;
		empty($db_defaultstyle) && $skin='wind';
		
		$fg=opendir(D_P.'data/style/');
		$get_style="<select name='stylefile'>";
		$get_style.="<option value=$db_defaultstyle>$db_defaultstyle</option>";
		while ($userskin=readdir($fg)) {
			if (eregi("\.php$",$userskin)) {
				$userskin=str_replace(".php","",$userskin);
				if($userskin!=$db_defaultstyle)
					$get_style.="<option value=$userskin>$userskin</option>";
			}
		}closedir($fg);
		$get_style.="</select>";

		if(!$seecheck){
			@extract($db->get_one("SELECT tablewidth AS seecheck FROM pw_styles WHERE name='wind'"));
		}
		strpos($seecheck,"%")!==false ? $open_seecheck="checked" : $close_seecheck="checked";
		include_once(D_P."data/style/$skin.php");
		if(!is_writeable(R_P."template/$tplpath/header.htm")) adminmsg('style_777');
		$style_css=readover(R_P."template/$tplpath/header.htm");
		$style_css=explode('<!--css-->',$style_css);
		$style_css=str_replace('$',"\$",$style_css[1]); //显示 $ 
		include PrintEot('setstyles');exit;
	} else{
		if ($job=="submit"){
			if($stylefile==$skin){
				adminmsg('style_del_error');
			}
			$db->update("DELETE FROM pw_styles WHERE name='$stylefile'");
			if(file_exists(D_P."data/style/$stylefile.php")){
				if(P_unlink(D_P."data/style/$stylefile.php")){
					adminmsg('operate_success');
				}else{
					adminmsg('operate_fail');
				}
			}else{
				adminmsg('style_not_exists');
			}
		}
	}
}elseif($action=='editcss'){
	$cssadd    = readover(R_P."template/$tplpath/header.htm");
	$cssadd    = explode('<!--css-->',$cssadd);
	$style_css = str_replace('EOT','',$style_css);
	$style_css = str_replace("$","\$",$cssadd[0].'<!--css-->'.$style_css.'<!--css-->'.$cssadd[2]);//从html里得到$字符串
	$style_css = stripslashes($style_css);
	writeover(R_P."template/$tplpath/header.htm",$style_css);
	adminmsg('operate_success');
}elseif($action=='see'){
	if($setting_seecheck==1){
		$setsee='98%';
		$msetsee='98%';
	}else{
		$setsee=900;
		$msetsee=925;
	}
	$db->update("UPDATE pw_styles SET tablewidth='$setsee',mtablewidth='$msetsee'");
	updatecache_sy();
	adminmsg('operate_success');
}elseif($action=='edit'){
	if ($job!="submit"){
		include_once(D_P."data/style/$stylefile.php");
		include PrintEot('setstyles');exit;
	} elseif ($job=="submit") {
		$rs=$db->get_one("SELECT sid FROM pw_styles WHERE name='$stylefile'");
		if($rs){
			$db->update("UPDATE pw_styles SET stylepath='$setting[0]',tplpath='$setting[1]',yeyestyle='$setting[2]',tablecolor='$setting[3]',tablewidth='$setting[4]',mtablewidth='$setting[5]',forumcolorone='$setting[6]',forumcolortwo='$setting[7]',threadcolorone='$setting[8]',threadcolortwo='$setting[9]',readcolorone='$setting[10]',readcolortwo='$setting[11]',maincolor='$setting[12]' WHERE name='$stylefile'");
		} else{
			$db->update("INSERT INTO pw_styles SET name='$stylefile',stylepath='$setting[0]',tplpath='$setting[1]',yeyestyle='$setting[2]',tablecolor='$setting[3]',tablewidth='$setting[4]',mtablewidth='$setting[5]',forumcolorone='$setting[6]',forumcolortwo='$setting[7]',threadcolorone='$setting[8]',threadcolortwo='$setting[9]',readcolorone='$setting[10]',readcolortwo='$setting[11]',maincolor='$setting[12]'");
		}
		updatecache_sy($stylefile);
		adminmsg('operate_success');
	}
}elseif($action=='add'){
	if ($job!="submit") {
		include PrintEot('setstyles');exit;
	} elseif($job=="submit"){
		if (empty($setting[0])){
			adminmsg('style_empty');
		}elseif (file_exists(D_P."data/style/$setting[0].php")){
			adminmsg('style_exists');
		}
		$db->update("INSERT INTO pw_styles (name,stylepath,tplpath,yeyestyle,tablecolor,tablewidth,mtablewidth,forumcolorone,forumcolortwo,threadcolorone,threadcolortwo,readcolorone,readcolortwo,maincolor) VALUES ('$setting[0]','$setting[0]','$setting[1]','$setting[2]','$setting[3]','$setting[4]','$setting[5]','$setting[6]','$setting[7]','$setting[8]','$setting[9]','$setting[10]','$setting[11]','$setting[12]')");
		updatecache_sy($setting[0]);
		adminmsg('style_add_success');
	}
}
?>