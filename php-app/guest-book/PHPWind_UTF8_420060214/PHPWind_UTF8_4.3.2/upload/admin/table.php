<?php
!function_exists('adminmsg') && exit('Forbidden');

$tabledb=array(
	'pw_actions',
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
);

$othortable=array();
$query = $db->query("SHOW TABLES");
while ($rt = $db->fetch_array($query)){
	$value=trim(current($rt));
	if(!in_array($value,$tabledb)){
		$othortable[]=$value;
	}
}
?>