<?php
/**
 * DouPHP
 * --------------------------------------------------------------------------------------------------
 * 版权所有 2013-2015 漳州豆壳网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.douco.com
 * --------------------------------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在遵守授权协议前提下对程序代码进行修改和使用；不允许对程序代码以任何形式任何目的的再发布。
 * 授权协议：http://www.douco.com/license.html
 * --------------------------------------------------------------------------------------------------
 * Author: DouCo
 * Release Date: 2015-10-16
 */
define('IN_DOUCO', true);

require (dirname(__FILE__) . '/include/init.php');

// rec操作项的初始化
$rec = $check->is_rec($_REQUEST['rec']) ? $_REQUEST['rec'] : 'default';

// 赋值给模板
$smarty->assign('rec', $rec);
$smarty->assign('cur', 'theme');

/**
 * +----------------------------------------------------------
 * 模板列表
 * +----------------------------------------------------------
 */
if ($rec == 'default') {
    $smarty->assign('ur_here', $_LANG['theme']);

    $theme_enable = $dou->get_theme_info($_CFG['site_theme']);
    $theme_array = $dou->get_subdirs(ROOT_PATH . 'theme/');
    foreach ($theme_array as $unique_id) {
        if ($unique_id == $_CFG['site_theme']) continue;
        $theme_list[] = $dou->get_theme_info($unique_id);
    }
    
    $smarty->assign('theme_enable', $theme_enable);
    $smarty->assign('theme_list', $theme_list);

    $smarty->display('theme.htm');
} 

/**
 * +----------------------------------------------------------
 * 在线安装模板
 * +----------------------------------------------------------
 */
if ($rec == 'install') {
    $smarty->assign('ur_here', $_LANG['theme']);

    $smarty->assign('get', urlencode(serialize($_GET)));
    $smarty->assign('localsite', $dou->dou_localsite('theme'));

    $smarty->display('theme.htm');
} 

/**
 * +----------------------------------------------------------
 * 模板启用
 * +----------------------------------------------------------
 */
if ($rec == 'enable') {
    if ($check->is_extend_id($unique_id = $_REQUEST['unique_id'])) {
        $theme_array = $dou->get_subdirs(ROOT_PATH . 'theme/');
        if (in_array($unique_id, $theme_array)) { // 判断删除操作的模板是否真实存在
            // 替换系统设置中模板值
            $dou->query("UPDATE " . $dou->table('config') . " SET value = '$unique_id' WHERE name = 'site_theme'");
            $dou->dou_clear_cache(ROOT_PATH . "cache"); // 更新缓存
        }
    }
    
    $dou->dou_header('theme.php');
} 

/**
 * +----------------------------------------------------------
 * 删除模板
 * +----------------------------------------------------------
 */
elseif ($rec == 'del') {
    // 载入扩展功能
    include_once (ROOT_PATH . ADMIN_PATH . '/include/cloud.class.php');
    $dou_cloud = new Cloud('cache');

    if ($check->is_extend_id($unique_id = $_REQUEST['unique_id'])) {
        $theme_array = $dou->get_subdirs(ROOT_PATH . 'theme/');
        if (in_array($unique_id, $theme_array)) { // 判断删除操作的模板是否真实存在
            $dou->del_dir(ROOT_PATH . 'theme/' . $unique_id);
            $dou_cloud->change_updatedate('theme', $unique_id, true); // 删除更新时间记录
            $dou->create_admin_log($_LANG['theme_del'] . ': ' . $unique_id);
        }
    }
    
    $dou->dou_header('theme.php');
}
?>