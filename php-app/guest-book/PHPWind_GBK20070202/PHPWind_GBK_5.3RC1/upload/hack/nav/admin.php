<?php
!function_exists("adminmsg") && exit("Forbidden");

require_once(H_P."color.php");

if ($action=="add" || $action=="edit") {
	empty($title) && adminmsg("nav_empty_title");
	empty($link) && adminmsg("nav_empty_link");
	!is_numeric($view) && !empty($view) && adminmsg("illegal_nums");
	$title=Char_cv($title);
	$style=$color."|".$b."|".$i."|".$u;
	$link=Char_cv($link);
	$alt=Char_cv($alt);
	if($action=="add"){
		$db->update("INSERT INTO pw_nav (title,style,link,alt,target,pos,view) VALUES('$title','$style','$link','$alt','$target','$pos','$view')");
	}elseif ($action=="edit") {
		!isset($nid) && adminmsg("undefine_action");
		$db->update("UPDATE pw_nav SET title='$title',style='$style',link='$link',alt='$alt',target='$target',pos='$pos',view='$view' WHERE nid='$nid'");
	}
	updatenav();
	$jumpto=$pos=="foot" ? "viewfoot" : "viewhead";
	adminmsg("operate_success","$basename&job=$jumpto");
}elseif ($action=="del"){
	!isset($nid) && adminmsg("undefine_action");
	$nid = (int)$nid;
	$db->update("DELETE FROM pw_nav WHERE nid='$nid'");
	updatenav();
	adminmsg("operate_success");
}elseif ($action=="editview"){
	foreach ($view as $key=>$val){
		!is_numeric($val) && alert("illegal_nums");
		$db->update("UPDATE pw_nav SET view='$val' WHERE nid='$key'");
	}
	updatenav();
	adminmsg("operate_success");
}elseif(!$job || $job=="add"){ ##job
	$actionvalue="add";
	$head_check="checked";
	$blank_check="checked";
	foreach ($colors as $c){
		$color_select.="<option value=\"$c\" style=\"background-color:$c;color:$c\"></option>";
	}
}elseif ($job=="edit"){
	$actionvalue="edit";
	!isset($nid) && adminmsg("undefine_action");
	@extract($db->get_one("SELECT * FROM pw_nav WHERE nid='$nid'"));
	$style_array=explode("|",$style);
	$style_array[1] && $b_check="checked";
	$style_array[2] && $i_check="checked";
	$style_array[3] && $u_check="checked";
	$pos=="foot" ? $foot_check="checked" : $head_check="checked";
	$target==1 ? $blank_check="checked" : $self_check="checked";
	foreach ($colors as $c){
		$ifselect=$c==$style_array[0] ? "selected" : "";
		$color_select.="<option value=\"$c\" style=\"background-color:$c;color:$c\" $ifselect></option>";
	}
}elseif ($job=="viewfoot" || $job=="viewhead"){
	if ($job=="viewfoot") {
		$pos="foot";
	}elseif ($job=="viewhead"){
		$pos="head";
	}
	$rs=$db->query("SELECT * FROM pw_nav WHERE pos='$pos' ORDER BY view");
	$nav=array();
	while ($navdb=$db->fetch_array($rs)) {
		$style_array=explode("|",$navdb['style']);
		$style_array[1] && $navdb['title']="<b>".$navdb['title']."</b>";
		$style_array[2] && $navdb['title']="<i>".$navdb['title']."</i>";
		$style_array[3] && $navdb['title']="<u>".$navdb['title']."</u>";
		$style_array[0] && $navdb['title']="<font color=\"$style_array[0]\">".$navdb['title']."</font>";
		$nav[]=$navdb;
	}
}
require PrintHack("admin");exit;

function updatenav(){
	global $db;
	$rs=$db->query("SELECT * FROM pw_nav ORDER BY view");
	$nav_head=$nav_foot=array();
	while ($navdb=$db->fetch_array($rs)){
		if($navdb['pos']=="foot"){
			$nav_foot[]=$navdb;
		}elseif ($navdb['pos']=="head"){
			$nav_head[]=$navdb;
		}
	}
	$head = getcache($nav_head,'head');
	$foot = getcache($nav_foot,'foot');
	$db->update("REPLACE INTO pw_config(db_name,db_value) VALUES ('db_head','$head')");
	$db->update("REPLACE INTO pw_config(db_name,db_value) VALUES ('db_foot','$foot')");

	updatecache_c();
}

function getcache($arrayvalue,$type){
	$navigation = '';
	foreach($arrayvalue as $nav){
		$text="";
		$text=$nav['title'];
		$nav_style=explode("|",$nav['style']);
		$nav_style[1] && $text="<b>$text</b>";
		$nav_style[2] && $text="<i>$text</i>";
		$nav_style[3] && $text="<u>$text</u>";
		$nav_style[0] && $text="<font color=\"$nav_style[0]\">$text</font>";
		$target=$nav['target'] ? "target=\"_blank\"" : "";
		$navigation.="<a href=\"$nav[link]\" title=\"$nav[alt]\" $target>$text</a> | ";
	}
	$type=='foot' && $navigation = substr($navigation,0,-3);
	return addslashes($navigation);
}
?>