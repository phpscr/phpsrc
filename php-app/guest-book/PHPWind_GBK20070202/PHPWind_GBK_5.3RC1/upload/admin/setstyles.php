<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=setstyles";
require_once Pcv(R_P.'require/forum.php');

if(!$action){
	$styles = array();
	$fp=opendir(R_P."data/style/");
	while($skinfile=readdir($fp)){
		if(eregi("\.php$",$skinfile)){
			$skinfile = str_replace(".php","",$skinfile);
			$styles[] = $skinfile;
		}
	}
	closedir($fp);
	include PrintEot('setstyles');exit;

} elseif($action=='edit'){

	if(!$_POST['step']){
		include_once Pcv(R_P."data/style/$sid.php");
		ifcheck($yeyestyle,'yes');
		$css_777=is_writeable(R_P."template/$tplpath/header.htm") ? 1 : 0;
		$style_css = readover(R_P."template/$tplpath/header.htm");
		$style_css = explode('<!--css-->',$style_css);
		$style_css = str_replace('$',"\$",$style_css[1]);

		include PrintEot('setstyles');exit;
	} else{
		$basename .= "&action=edit&sid=$sid";
		strpos($setting[7],'%')===false && strpos(strtolower($setting[7]),'px')===false && $setting[5].='px';
		strpos($setting[8],'%')===false && strpos(strtolower($setting[8]),'px')===false && $setting[4].='px';
		$rs=$db->get_one("SELECT sid FROM pw_styles WHERE name='$sid'");
		if($rs){
			$db->update("UPDATE pw_styles SET stylepath='$setting[0]',tplpath='$setting[1]',yeyestyle='$setting[2]',bgcolor='$setting[3]',linkcolor='$setting[4]',tablecolor='$setting[5]',tdcolor='$setting[6]',tablewidth='$setting[7]',mtablewidth='$setting[8]',headcolor='$setting[9]',headborder='$setting[10]',headfontone='$setting[11]',headfonttwo='$setting[12]',cbgcolor='$setting[13]',cbgborder='$setting[14]',cbgfont='$setting[15]',forumcolorone='$setting[16]',forumcolortwo='$setting[17]' WHERE name='$sid'");
		} else{
			$db->update("INSERT INTO pw_styles SET name='$sid',stylepath='$setting[0]',tplpath='$setting[1]',yeyestyle='$setting[2]',bgcolor='$setting[3]',linkcolor='$setting[4]',tablecolor='$setting[5]',tdcolor='$setting[6]',tablewidth='$setting[7]',mtablewidth='$setting[8]',headcolor='$setting[9]',headborder='$setting[10]',headfontone='$setting[11]',headfonttwo='$setting[12]',cbgcolor='$setting[13]',cbgborder='$setting[14]',cbgfont='$setting[15]',forumcolorone='$setting[16]',forumcolortwo='$setting[17]'");
		}
		updatecache_sy($sid);
		adminmsg('operate_success');
	}
} elseif($_POST['action']=='editcss'){
	$basename .= "&action=edit&sid=$sid";
	include_once Pcv(R_P."data/style/$sid.php");
	if(!is_writeable(R_P."template/$tplpath/header.htm")){
		adminmsg('style_777');
	}
	$cssadd    = readover(R_P."template/$tplpath/header.htm");
	$cssadd    = explode('<!--css-->',$cssadd);
	$style_css = str_replace('EOT','',$style_css);
	$style_css = str_replace("$","\$",$cssadd[0].'<!--css-->'.$style_css.'<!--css-->'.$cssadd[2]);
	$style_css = stripslashes($style_css);
	writeover(R_P."template/$tplpath/header.htm",$style_css);

	adminmsg('operate_success');
} elseif($action=='add'){

	if(!$_POST['step']){
		$yes_Y = 'checked';
		include PrintEot('setstyles');exit;
	} else{
		if(empty($setting[0])){
			adminmsg('style_empty');
		} elseif(file_exists(R_P."data/style/$setting[0].php")){
			adminmsg('style_exists');
		}
		strpos($setting[5],'%')===false && strpos(strtolower($setting[5]),'px')===false && $setting[5].='px';
		strpos($setting[6],'%')===false && strpos(strtolower($setting[6]),'px')===false && $setting[4].='px';
		$db->update("INSERT INTO pw_styles (name,stylepath,tplpath,yeyestyle,bgcolor,linkcolor,tablecolor,tdcolor,tablewidth,mtablewidth,headcolor,headborder,headfontone,headfonttwo,cbgcolor,cbgborder,cbgfont,forumcolorone,forumcolortwo) VALUES ('$setting[0]','$setting[0]','$setting[1]','$setting[2]','$setting[3]','$setting[4]','$setting[5]','$setting[6]','$setting[7]','$setting[8]','$setting[9]','$setting[10]','$setting[11]','$setting[12]','$setting[13]','$setting[14]','$setting[15]','$setting[16]','$setting[17]')");
		updatecache_sy($setting[0]);
		adminmsg('style_add_success');
	}
} elseif($action=='del'){
	PostCheck($verify);
	if($sid==$skin){
		adminmsg('style_del_error');
	}
	$db->update("DELETE FROM pw_styles WHERE name='$sid'");
	if(file_exists(R_P."data/style/$sid.php")){
		if(P_unlink(R_P."data/style/$sid.php")){
			adminmsg('operate_success');
		} else{
			adminmsg('operate_fail');
		}
	} else{
		adminmsg('style_not_exists');
	}
}
?>