<?php
// adminpanel

$options = array (
	"globalsetting||caneditsettings" => array (
		array ( 'globalsetting', 'settings.php' ),
		array ( 'addnewsetting', 'settings.php?do=settingnew' ),
		array ( 'phpinfo', 'settings.php?do=phpinfo' ),
		array ( 'fileperms', 'settings.php?do=check_perms' ),
	),
	"forummanage||caneditforums" => array (
		array ( 'manageforum', 'forums.php' ),
		array ( 'addnewforum', 'forums.php?do=new' ),
		array ( 'moderatesetting', 'moderate.php' ),
		array ( 'sptopicsetting', 'sptopic.php' ),
		array ( 'recyclesetting', 'settings.php?do=setting_view&amp;groupid=14' ),
	),
	"usermanage||caneditusers" => array (
		array ( 'finduser', 'user.php' ),
		array ( 'adduser', 'user.php?do=newuser' ),
		array ( 'joinuser', 'user.php?do=joinuser' ),
		array ( 'rankmanage', 'user.php?do=rankform' ),
		array ( 'valusermanage', 'user.php?do=mod' ),
		array ( 'banusermanage', 'user.php?do=ban' ),
		array ( 'adminlist||caneditadmins', 'user.php?do=adminlist' ),
		array ( 'ipsearch', 'iptools.php' ),
		array ( 'usersetting', 'settings.php?do=setting_view&amp;groupid=6' ),
		array ( 'olrankmanage', 'user.php?do=olrankform' ),
	),
	"usergroupmanage||caneditusergroups" => array (
		array ( 'usergroupmanage', 'usergroup.php' ),
		array ( 'usergrouppromotion', 'usergroup.php?do=promotions' ),
		array ( 'usergrouppermission', 'usergroup.php?do=permission' ),
	),
	"awardmanage||caneditusers" => array (
		array ( 'awardmanage', 'award.php' ),
		array ( 'awardlist', 'award.php?do=showlist' ),
	),
	"creditmanage||caneditusers" => array (
		array ( 'creditlist', 'credit.php' ),
		array ( 'addnewcredit', 'credit.php?do=add' ),
	),
	"pmsystem||cansendpms" => array (
		array ( 'pmmanage', 'usertools.php?do=pmstats' ),
		array ( 'pmlist', 'pms.php?do=pmlist' ),
		array ( 'newpmlist', 'pms.php?do=newpm' ),
		array ( 'mailsystem', 'usertools.php' ),
	),
	"jsmanage||caneditjs" => array (
		array ( 'jslist', 'javascript.php' ),
		array ( 'addjs', 'javascript.php?do=new' ),
	),
	"threadmanage" => array (
		array ( 'massprune||canmassprunethreads', 'threads.php?do=massprune' ),
		array ( 'massmove||canmassmovethreads', 'threads.php?do=massmove' ),
	),
	"bankmanage||caneditbank" => array (
		array ( 'loanmanage', 'bank.php' ),
		array ( 'repmanage', 'reputation.php' ),
	),
	"attachmanage||caneditattachments" => array (
		array ( 'attachtype', 'attachment.php?do=types' ),
		array ( 'attachstatus', 'attachment.php?do=stats' ),
		array ( 'searchattach', 'attachment.php?do=search' ),
	),
	"bbcodemanage||caneditbbcodes" => array (
		array ( 'custombbcode', 'bbcode.php' ),
		array ( 'addbbcode', 'bbcode.php?do=add' ),
	),
	"imagemanage||caneditimages" => array (
		array ( 'smiliesmanage', 'image.php?do=smile' ),
		array ( 'iconsmanage', 'image.php?do=icon' ),
	),
	"badandban||caneditbans" => array (
		array ( 'badword', 'filter.php?do=badword' ),
		array ( 'banlist', 'filter.php?do=ban' ),
	),
	"stylemanage||caneditstyles" => array (
		array ( 'stylemanage', 'style.php' ),
		array ( 'styletools', 'style.php?do=tools' ),
		array ( 'importexport', 'style.php?do=files' ),
	),
	"cachemanage||caneditcaches" => array (
		array ( 'cachemanage', 'cache.php' ),
	),
	"systemsetting||caneditothers" => array (
		array ( 'faqmanage', 'faq.php' ),
		array ( 'rebuildcount', 'rebuild.php' ),
		array ( 'importsystem', 'importers.php' ),
	),
	"cron||caneditcrons" => array (
		array ( 'cronmanage', 'cronadmin.php' ),
		array ( 'viewcron', 'cronlog.php' ),
	),
	"sqltools||caneditmysql" => array (
		array ( 'sqltoolbox', 'mysql.php' ),
		array ( 'sqlbackup', 'mysql.php?do=backup' ),
		array ( 'sqlrestore', 'mysql.php?do=restore' ),
		array ( 'sqlruntime', 'mysql.php?do=runtime' ),
		array ( 'sqlinfo', 'mysql.php?do=system' ),
		array ( 'sqlprocesses', 'mysql.php?do=processes' ),
	),
	"statisticsmanage" => array (
		array ( 'statistics', 'statistics.php' ),
		array ( 'modlog||canviewmodlogs', 'modlog.php' ),
		array ( 'adminlog||canviewadminlogs', 'adminlog.php' ),
		array ( 'cronlog', 'cronlog.php' ),
	),
	"inviteregister||caneditinvite" =>array (
		array( 'inviteregister','inviteset.php?do=list'),
		array( 'userinvitelist','inviteset.php?do=usersendinvitelist'),
		array( 'inviteregset','settings.php?do=setting_view&amp;groupid=20'),
		array( 'sendinvite','inviteset.php?do=sendinvite'),
	),
	"admanage||caneditads" => array (
		array ( 'adlist', 'bill.php' ),
		array ( 'addad', 'bill.php?do=add' ),
		array ( 'adsetting', 'settings.php?do=setting_view&amp;groupid=22' ),
	),
	"leaguemanage||caneditleagues" => array (
		array ( 'leaguelist', 'league.php' ),
		array ( 'addleague', 'league.php?do=addleague' ),
	),
);

?>