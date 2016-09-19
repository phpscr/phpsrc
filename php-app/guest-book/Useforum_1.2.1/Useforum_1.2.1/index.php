<?php
/**
 * Useforum  Copyright (C) 2010-2013 入口文件
 * 添加日期 2011 GW
 * U.gw269.com All Rights Reserved.
 * @author GW<i@gw269.com>
 * @site http://www.gw269.com/
 * @licence GPLv2
 */
define('APP_PATH', dirname(__FILE__));
define("SP_PATH",dirname(__FILE__)."/include");
require(APP_PATH."/config.php");
$spConfig = array(
	"db" => $mysql,
	'view' => array(
		'enabled' => TRUE, // 开启视图
		'config' =>array(
			'template_dir' => TEMPLATE_PATH, // 模板目录
			'compile_dir' => APP_PATH.'/cache', // 编译目录
			'cache_dir' => APP_PATH.'/cache', // 缓存目录
			'left_delimiter' => '{',  // smarty左限定符
			'right_delimiter' => '}', // smarty右限定符
		),
		'auto_display' => TRUE, // 使用自动输出模板功能
		'auto_display_sep' => "_", // 自动输出模板的拼装模式，/为按目录方式拼装，_为按下划线方式
	),
	'launch' => array( 
		 'router_prefilter' => array( 
			array('spAcl','mincheck') // 开启有限的权限控制
			// array('spAcl','maxcheck') // 开启强制的权限控制
		),
	),
	'ext' => array( // 扩展设置
	 	'spAcl' => array( // acl扩展设置		 
	 		// 在acl中，设置无权限执行将lib_user类的acljump函数
	 		'prompt' => array("useforum", "acljump"),
	 	),
	),
);

require(SP_PATH."/SpeedPHP.php");
import(APP_PATH.'/controller/useforum.php'); // 需要先载入useforum控制器父类
import(SP_PATH.'/Extensions/feedcreator.php'); 
import(SP_PATH.'/useFunctions.php'); 
spRun();