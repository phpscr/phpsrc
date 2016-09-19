<?php
/**
 * DOUCO TEAM
 * ============================================================================
 * COPYRIGHT DOUCO 2014-2015.
 * http://www.douco.com;
 * ----------------------------------------------------------------------------
 * Author:DouCo
 * Release Date: 2014-06-05
 */
if (!defined('IN_DOUCO')) {
    die('Hacking attempt');
}

// error_reporting
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

// 关闭 set_magic_quotes_runtime
@ set_magic_quotes_runtime(0);

// 调整时区
if (PHP_VERSION >= '5.1') {
    date_default_timezone_set('PRC');
}

/* 取得当前站点所在的根目录 */
define('U_PATH', str_replace('include/init.php', '', str_replace('\\', '/', __FILE__)));
define('ROOT_PATH', str_replace('upgrade/include/init.php', '', str_replace('\\', '/', __FILE__)));
define('ROOT_URL', preg_replace('/install\//Ums', '', dirname('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']) . '/'));

require (ROOT_PATH . 'data/config.php');
require (ROOT_PATH . 'include/smarty/Smarty.class.php');
require (ROOT_PATH . 'include/mysql.class.php');
require (ROOT_PATH . 'include/common.class.php');
require (ROOT_PATH . 'include/action.class.php');
require (U_PATH . 'include/upgrade.class.php');
require (U_PATH . 'include/language.class.php');

// 实例化类
$dou = new Action($dbhost, $dbuser, $dbpass, $dbname, $prefix, DOU_CHARSET);

// 读取站点信息
$_CFG = $dou->get_config();

// 设置页面编码
header('Content-type: text/html; charset=' . DOU_CHARSET);

/* 初始化 */
$upgrade = new Upgrade(str_replace('-', '', DOU_CHARSET));
$system_file = ROOT_PATH . 'data/system.dou';

// SMARTY配置
$smarty = new smarty();
$smarty->config_dir = ROOT_PATH . "/include/smarty/Config_File.class.php"; // 目录变量
$smarty->template_dir = U_PATH . "template"; // 模板存放目录
$smarty->compile_dir = U_PATH . "data/cache"; // 编译目录
$smarty->left_delimiter = "{"; // 左定界符
$smarty->right_delimiter = "}"; // 右定界符
                                
// 如果编译和缓存目录不存在则建立
if (!file_exists($smarty->compile_dir))
    mkdir($smarty->compile_dir, 0777);
    
// 载入语言文件
require (U_PATH . 'include/language.class.php');

// 系统模块
if (file_exists($system_file)) $_MODULE = $dou->dou_module();

// 通用信息调用
$smarty->assign("lang", $_LANG);

// Smarty 过滤器
function remove_html_comments($source, & $smarty) {
    return $source = preg_replace('/<!--.*{(.*)}.*-->/U', '{$1}', $source);
}
$smarty->register_prefilter('remove_html_comments');

?>