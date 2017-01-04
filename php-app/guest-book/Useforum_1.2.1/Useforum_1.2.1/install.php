<?php
/**
 * Useforum  Copyright (C) 2010-2013 安装程序
 * www.gw269.com All Rights Reserved.
 */

// 定义当前目录
define("APP_PATH",dirname(__FILE__));
if(true == @file_exists(APP_PATH.'/install.lock')){
	echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" /><center>Useforum看起来已经安装过了，请删除install.lock再试。</center>";
    exit();
}
//默认的参数 
$defaults = array(
	
	"DB_HOST" => "localhost",
	"DB_USER" => "root",
	"DB_PASSWORD" => "",
	"DB_DBNAME" => "useforum",
	"DB_PREFIX" => "useforum_",
		
	"uname" => "admin",
	"email" => "",
	"upass" => ""
);

function ins_checkdblink($configs){//用获得的数据库参数来检验数据库是否可以正常了连接
	global $dblink,$err;
	$dblink = mysql_connect($configs['DB_HOST'], $configs['DB_USER'], $configs['DB_PASSWORD']);
	if(false == $dblink){$err = '无法链接网站数据库，请检查网站数据库设置！';return false;}
	if(! mysql_select_db($configs['DB_DBNAME'], $dblink)){$err = '无法选择网站数据库，请确定网站数据库名称正确！'; return false;}
	ins_query("SET NAMES UTF8");
	return true;
}

function ins_query($sql,$prefix = ""){// 本地数据库入库
	global $dblink,$err;
	$sqlarr = explode(";", $sql);
	foreach($sqlarr as $single){
		if( !empty($single) && strlen($single) > 5 ){
			$single = str_replace("\n",'',$single);
			$single = str_replace("#DBPREFIX#",$prefix,$single );
			if( !mysql_query($single, $dblink) ){$err = "数据库执行错误：".mysql_error();return false;}
		}
	}
}

function ins_registeruser($configs, $prefix = ""){//增加管理员用户和第一个话题
	global $dblink,$err,$adminsql;
	$ctime = time();
	$md5 = md5($configs["upass"]);
        $adminsql = "insert into `{$prefix}user` (`uid`,`uname`,`upass`,`email`,`ctime`,`acl`) values(1,'{$configs["uname"]}','{$md5}','{$configs["email"]}','{$ctime}','GBADMIN');
		INSERT INTO `{$prefix}topic` ( `gid` ,`title` ,`contents` ,`ctime` ,`rtime` ,`uname` ,`forum` ,`ip` ,`top` ,`digest` ,`view`  ) VALUES 
 (1,'欢迎使用Useforum轻论坛','<p> <img src=\"./template/common/logo.png\" width=\"120\" height=\"120\" align=\"left\" />这是Useforum的第一个话题！ </p> <p> Useforum是一套简单的论坛系统，介于普通论坛和微博之间，Useforum首创轻论坛概念，致力于打造新的论坛体验，为论坛提供了一个轻量级的解决方案。 </p> <p> <br /> </p> <p> <br /> </p> <p> <br /> </p> <p> 1.1.0更新列表： </p> <p> 1.增加QQ互联登陆功能； </p> <p> 2.改变话题显示模式，允许标题留空； </p> <p> 3.增加话题权限相关设置； </p> <p> 4.增加用户间收听功能，为下一版本做准备； </p> <p> 5.增加高级搜索功能。 </p> <p> <br /> </p> <p> <br /> </p> <p> 1.0.0 更新列表： </p> <p> 1.附件隐藏真实地址输出，数据库记录，防盗链，方便了日后版本功能增加； </p> <p> 2.导航栏自由添加，免去修改模板的麻烦； </p> <p> 3.增加屏蔽用户功能； </p> <p> 4.首页板块列表和实时动态可选，便于站长选择运营模式(动态暂时默认300条)； </p> <p> 5.E-mail用户名双登陆； </p> <p> 6.强化安全过滤； </p> <p> 7.增加话题锁定，增加移动话题； </p> <p> 8.实名认证功能改进，认证通过后不再允许修改资料； </p> <p> 9.自动登录细节优化； </p> <p> 10.对MVC进行规范化处理，程序结构更加明晰。 </p> <p> <br /> </p> <p> <br /> </p> <p> 欢迎大家到<a href=\"http://www.gw269.com/u\" target=\"_blank\">官方网站</a>交流使用经验或反馈问题！ </p>',{$ctime},{$ctime},'{$configs["uname"]}',1,'127.0.0.1',0,1,24);";
		
	//return $adminsql;
        return True;

}

function ins_writeconfig($configs){//生成配置文件
	$configex = file_get_contents(APP_PATH."/config.php");
	foreach( $configs as $skey => $value ){
		$skey = "#".$skey."#";
		$configex = str_replace($skey, $value, $configex);
	}
	file_put_contents (APP_PATH."/config.php" ,$configex);
	file_put_contents (APP_PATH."/install.lock" ,"");
}

$sql = "
DROP TABLE IF EXISTS #DBPREFIX#acl;

DROP TABLE IF EXISTS #DBPREFIX#user;

DROP TABLE IF EXISTS #DBPREFIX#topic;

DROP TABLE IF EXISTS #DBPREFIX#reply;

DROP TABLE IF EXISTS #DBPREFIX#profilegb;

DROP TABLE IF EXISTS #DBPREFIX#option;

DROP TABLE IF EXISTS #DBPREFIX#score;

DROP TABLE IF EXISTS #DBPREFIX#forum;

DROP TABLE IF EXISTS #DBPREFIX#menu;

DROP TABLE IF EXISTS #DBPREFIX#follow;

DROP TABLE IF EXISTS #DBPREFIX#feed;

DROP TABLE IF EXISTS #DBPREFIX#attachment;

DROP TABLE IF EXISTS #DBPREFIX#access_cache;

CREATE TABLE IF NOT EXISTS `useforum_access_cache` (
  `cacheid` bigint(20) NOT NULL auto_increment,
  `cachename` varchar(100) NOT NULL,
  `cachevalue` text,
  PRIMARY KEY  (`cacheid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `useforum_acl` (
  `aclid` int(11) NOT NULL auto_increment,
  `name` varchar(200) collate utf8_unicode_ci NOT NULL,
  `controller` varchar(50) collate utf8_unicode_ci NOT NULL,
  `action` varchar(50) collate utf8_unicode_ci NOT NULL,
  `acl_name` varchar(50) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`aclid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=93 ;

INSERT INTO `useforum_acl` (`aclid`, `name`, `controller`, `action`, `acl_name`) VALUES
(1, '留言管理后台', 'admin', 'index', 'GBADMIN'),
(2, '删除留言', 'admin', 'del', 'GBADMIN'),
(3, '提交留言', 'main', 'post', 'GBUSER'),
(4, '提交留言', 'main', 'post', 'GBADMIN'),
(5, '首页', 'main', 'index', 'SPANONYMOUS'),
(6, '登录', 'main', 'login', 'SPANONYMOUS'),
(7, '查看留言', 'main', 'view', 'SPANONYMOUS'),
(8, '登出', 'main', 'logout', 'SPANONYMOUS'),
(9, '回复主题', 'main', 'rpost', 'GBUSER'),
(10, '回复主题', 'main', 'rpost', 'GBADMIN'),
(11, '注册', 'user', 'register', 'SPANONYMOUS'),
(12, '查看用户资料', 'user', 'profiler', 'GBUSER'),
(13, '查看用户资料', 'user', 'profiler', 'GBADMIN'),
(14, '删除用户', 'admin', 'deluser', 'GBADMIN'),
(15, '用户管理首页', 'admin', 'user', 'GBADMIN'),
(16, '编辑话题', 'main', 'edit', 'GBUSER'),
(17, '编辑话题', 'main', 'edit', 'GBADMIN'),
(18, '留言给用户 ', 'user', 'rpost', 'GBADMIN'),
(19, '留言给用户 ', 'user', 'rpost', 'GBUSER'),
(20, '后台查看话题 ', 'admin', 'view', 'GBADMIN'),
(21, '后台删除回复 ', 'admin', 'delreply', 'GBADMIN'),
(22, '后台用户单页 ', 'admin', 'profile', 'GBADMIN'),
(23, '后台删除用户留言 ', 'admin', 'delpg', 'GBADMIN'),
(24, '版块管理', 'admin', 'forum', 'GBADMIN'),
(50, '删除权限', 'admin', 'delacl', 'GBADMIN'),
(27, '后台帖子列表', 'admin', 'thread', 'GBADMIN'),
(28, '编辑用户资料', 'user', 'editprofile', 'GBADMIN'),
(29, '编辑用户资料执行', 'user', 'editnow', 'GBADMIN'),
(30, '用户编辑', 'user', 'editprofile', 'GBUSER'),
(31, '用户编辑', 'user', 'editnow', 'GBUSER'),
(48, '权限管理页面', 'admin', 'acl', 'GBADMIN'),
(41, '后台全局编辑', 'admin', 'optionedited', 'GBADMIN'),
(49, '排行榜', 'user', 'userlist', 'SPANONYMOUS'),
(42, '后台全局变量', 'admin', 'option', 'GBADMIN'),
(43, '板块编辑', 'admin', 'editforum', 'GBADMIN'),
(44, '板块编辑执行', 'admin', 'forumedited', 'GBADMIN'),
(45, '板块添加', 'admin', 'addforum', 'GBADMIN'),
(46, '板块删除', 'admin', 'delforum', 'GBADMIN'),
(51, '添加权限', 'admin', 'addacl', 'GBADMIN'),
(52, '置顶', 'admin', 'totop', 'GBADMIN'),
(54, '精华', 'admin', 'digest', 'GBADMIN'),
(55, '删除', 'admin', 'del', 'GBUSER'),
(56, '精华', 'admin', 'digest', 'GBUSER'),
(57, '置顶', 'admin', 'totop', 'GBUSER'),
(58, '删除回复', 'admin', 'delreply', 'GBUSER'),
(59, '删除留言', 'admin', 'delpg', 'GBADMIN'),
(62, '修改密码', 'user', 'changepwd', 'GBUSER'),
(63, '修改密码', 'user', 'changepwd', 'GBADMIN'),
(64, '搜索', 'main', 'search', 'SPANONYMOUS'),
(65, '批量删除', 'admin', 'multidel', 'GBADMIN'),
(66, '数据备份', 'admin', 'backup', 'GBADMIN'),
(67, '用户信息导出', 'admin', 'derivecsv', 'GBADMIN'),
(68, '话题评分', 'main', 'score', 'GBUSER'),
(69, '话题评分', 'main', 'score', 'GBADMIN'),
(70, '安全设置', 'user', 'secure', 'GBADMIN'),
(71, '安全设置', 'user', 'secure', 'GBUSER'),
(72, '安全设置', 'user', 'secure', 'shield'),
(73, '找回密码', 'user', 'findpwd', 'SPANONYMOUS'),
(74, '删除附件', 'admin', 'delattach', 'GBADMIN'),
(75, '附件管理', 'admin', 'attachment', 'GBADMIN'),
(76, '导航设置', 'admin', 'menu', 'GBADMIN'),
(77, '删除菜单', 'admin', 'delmenu', 'GBADMIN'),
(78, '添加菜单', 'admin', 'addmenu', 'GBADMIN'),
(79, '菜单编辑', 'admin', 'editmenu', 'GBADMIN'),
(80, '隐藏菜单', 'admin', 'hidden', 'GBADMIN'),
(81, '导航排序', 'admin', 'reordermenu', 'GBADMIN'),
(82, '上传', 'main', 'upload', 'GBUSER'),
(83, '上传', 'main', 'upload', 'GBADMIN'),
(85, '销毁用户', 'admin', 'destroy', 'GBADMIN'),
(86, '身份认证设置', 'user', 'identify', 'GBADMIN'),
(87, '身份认证设置', 'user', 'identify', 'GBUSER'),
(88, 'API设置', 'admin', 'api', 'GBADMIN'),
(89, '高级搜索', 'main', 'advanced_search', 'GBADMIN'),
(90, '高级搜索', 'main', 'advanced_search', 'GBUSER'),
(91, 'QQ绑定或解绑', 'api', 'qq_binding', 'GBUSER'),
(92, 'QQ绑定或解绑', 'api', 'qq_binding', 'GBADMIN');

CREATE TABLE IF NOT EXISTS `useforum_attachment` (
  `aid` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `uid` int(11) NOT NULL,
  `size` int(11) NOT NULL,
  `ctime` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY  (`aid`),
  UNIQUE KEY `aid` (`aid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `useforum_feed` (
  `fid` int(11) NOT NULL auto_increment,
  `content` text NOT NULL,
  `url` varchar(255) NOT NULL,
  `uname` varchar(255) NOT NULL,
  `avatar` varchar(255) NOT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY  (`fid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `useforum_follow` (
  `fid` int(11) NOT NULL auto_increment,
  `follower` int(11) NOT NULL,
  `following` int(11) NOT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY  (`fid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `useforum_forum` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(20) NOT NULL,
  `instruc` varchar(300) NOT NULL,
  `color` varchar(8) character set latin1 NOT NULL,
  `order` int(11) NOT NULL default '0',
  `rule` text NOT NULL,
  `icon` varchar(1000) NOT NULL,
  `authority` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `useforum_forum`
--

INSERT INTO `useforum_forum` (`id`, `name`, `instruc`, `color`, `order`, `rule`, `icon`, `authority`) VALUES
(1, '默认博客', '这是由Useforum自动创建的版块。', '#B8D100', 0, '', '', 0);

CREATE TABLE IF NOT EXISTS `useforum_menu` (
  `mid` int(6) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `target` tinyint(1) NOT NULL,
  `hidden` tinyint(1) NOT NULL,
  `order` int(6) NOT NULL,
  PRIMARY KEY  (`mid`),
  UNIQUE KEY `mid` (`mid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `useforum_option` (
  `name` varchar(20) character set latin1 NOT NULL,
  `val` varchar(1000) NOT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `useforum_option` (`name`, `val`) VALUES
('sitename', 'Useforum 1.1.0'),
('siteinstruction', 'Useforum,让论坛更轻松！'),
('listpager', '30'),
('replypager', '20'),
('keywords', 'Useforum,轻论坛,论坛'),
('description', '使用Useforum轻论坛搭建的网站'),
('logo', './template/green/images/logo.png'),
('newtopic', '2'),
('newreply', '1'),
('digest', '5'),
('waittime', '900'),
('posttime', '0'),
('regcode', '1'),
('postcode', '0'),
('logincode', '0'),
('is_close', '0'),
('allow_reg', '1'),
('use_logo', '1'),
('close_reason', '运营维护'),
('domain_attachment', ''),
('allow_image_types', 'jpg,jpeg,gif,png'),
('allow_file_types', 'doc,docx,xls,xlsx,ppt,pptx,pdf,txt,rar,zip,7z,gz,csv,bz2'),
('max_upload_size', '256'),
('default_index', '0');

CREATE TABLE IF NOT EXISTS `useforum_profilegb` (
  `pgid` int(11) NOT NULL auto_increment,
  `content` varchar(1000) NOT NULL,
  `ctime` int(15) NOT NULL,
  `uname` varchar(30) NOT NULL,
  `uid` int(11) NOT NULL,
  `secret` varchar(5) character set latin1 NOT NULL,
  PRIMARY KEY  (`pgid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `useforum_reply` (
  `rid` int(11) NOT NULL auto_increment,
  `content` varchar(2000) NOT NULL,
  `uname` varchar(20) NOT NULL,
  `gid` int(11) NOT NULL,
  `ctime` int(11) NOT NULL,
  `ip` varchar(20) character set latin1 NOT NULL,
  PRIMARY KEY  (`rid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `useforum_score` (
  `sid` int(11) NOT NULL auto_increment,
  `uname` varchar(100) NOT NULL,
  `gid` int(11) NOT NULL,
  `reason` text NOT NULL,
  `score` int(11) NOT NULL,
  `ctime` int(20) NOT NULL,
  PRIMARY KEY  (`sid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `useforum_topic` (
  `gid` int(11) NOT NULL auto_increment,
  `title` varchar(90) NOT NULL,
  `contents` varchar(2000) NOT NULL,
  `ctime` int(12) NOT NULL,
  `rtime` int(12) NOT NULL,
  `uname` varchar(20) NOT NULL,
  `forum` int(11) NOT NULL default '1',
  `ip` varchar(20) character set latin1 NOT NULL,
  `top` int(1) NOT NULL,
  `digest` int(1) NOT NULL,
  `view` int(11) NOT NULL,
  `close` int(1) NOT NULL,
  `authority` int(11) NOT NULL,
  PRIMARY KEY  (`gid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=2 ;

CREATE TABLE IF NOT EXISTS `useforum_user` (
  `uid` int(11) NOT NULL auto_increment,
  `uname` varchar(20) NOT NULL,
  `upass` varchar(32) character set latin1 NOT NULL,
  `acl` varchar(10) NOT NULL default 'GBUSER',
  `ctime` int(12) NOT NULL,
  `email` varchar(30) NOT NULL,
  `ip` varchar(15) character set latin1 NOT NULL,
  `live` varchar(15) NOT NULL default '火星',
  `introduce` varchar(300) NOT NULL default '这家伙很懒，什么也没留下~~~',
  `qq` int(10) default NULL,
  `male` int(1) NOT NULL,
  `birth` date default NULL,
  `phone` int(20) default NULL,
  `avatar` varchar(666) NOT NULL,
  `signature` text NOT NULL,
  `address` text NOT NULL,
  `photo` text NOT NULL,
  `truename` varchar(20) NOT NULL,
  `admit` int(2) NOT NULL,
  `credits` int(11) NOT NULL,
  `post` int(11) NOT NULL,
  `digestpost` int(11) NOT NULL,
  `forum` int(11) NOT NULL,
  `q1` varchar(255) NOT NULL,
  `a1` varchar(255) NOT NULL,
  `q2` varchar(255) NOT NULL,
  `a2` varchar(255) NOT NULL,
  `admissiondata` varchar(255) NOT NULL,
  `homepage` varchar(255) NOT NULL,
  `openid` varchar(255) NOT NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=2 ;
";

if(empty($_GET['step']) || $_GET['step'] == 1){
    $tips = $defaults;
    require(APP_PATH.'/template/install/step1.html');
}else{
	// 第三步，验证资料，写入资料，完成安装
	$dblink = null;$err=null;$adminsql = null;
	while(1){
		// 检查本地数据库设置
		ins_checkdblink($_POST);if( null != $err )break;
		// 增加管理员用户
		ins_registeruser($_POST,$_POST["DB_PREFIX"]);if( null != $err )break;
		// 本地数据库入库
		$sql .= $adminsql;
		ins_query($sql,$_POST["DB_PREFIX"]);if( null != $err )break;
		// 改写本地配置文件
		ins_writeconfig($_POST);if( null != $err )break;
		break;
	}
        if( null != $err ){ // 有错误则覆盖
		$tips = array_merge($defaults, $_POST); // 显示原值或新值
		require(APP_PATH.'/template/install/step1.html');
	}else{
		require(APP_PATH.'/template/install/step2.html');
	}
}

