<?php
!function_exists('adminmsg') && exit('Forbidden');

$tabledb=array(
	'pw_activity',
	'pw_actions',
	'pw_actmember',
	'pw_advert',
	'pw_cmsmodule',
	'pw_customfield',
	'pw_extragroups',
	'pw_smiles',
	'pw_adminset',
	'pw_announce',
	'pw_recycle',
	'pw_toollog',
	'pw_tools',
	'pw_usertool',
	'pw_bbsinfo',
	'pw_config',
	'pw_hack',
	'pw_ipstates',
	'pw_schcache',
	'pw_sharelinks',
	'pw_styles',
	'pw_usergroups',
	'pw_wordfb',
	'pw_credits',
	'pw_medalinfo',
	'pw_medalslogs',
	'pw_favors',
	'pw_friends',
	'pw_help',
	'pw_pidtmp',
	'pw_plan',
	'pw_singleright',
	'pw_invitecode',
	'pw_banuser',
	'pw_argument',
	'pw_clientorder',
	'pw_cmembers',
	'pw_cnclass',
	'pw_colonys',
	'pw_ks',
	'pw_keyword',
	'pw_modules',
	'pw_report',
	'pw_forumlog',
	'pw_adminlog',
	'pw_msg',
	'pw_forums',
	'pw_forumdata',
	'pw_forumsextra',
	'pw_members',
	'pw_memberdata',
	'pw_membercredit',
	'pw_memberinfo',
	'pw_threads',
	'pw_tmsgs',
	'pw_posts',
	'pw_polls',
	'pw_attachs',
	'pw_cnalbum',
	'pw_cnphoto',
	'pw_nav'
);
if($db_plist){
	$p_list = explode(',',$db_plist);
	foreach($p_list as $val){
		$tabledb[]='pw_posts'.$val;
	}
}
if($PW!='pw_'){
	foreach($tabledb as $key=>$value){
		$tabledb[$key] = str_replace('pw_',$PW,$value);
	}
}

$othortable=array();
$query = $db->query("SHOW TABLES");
while ($rt = $db->fetch_array($query)){
	$value = trim(current($rt));
	if(!in_array($value,$tabledb)){
		$othortable[]=$value;
	}
}
?>