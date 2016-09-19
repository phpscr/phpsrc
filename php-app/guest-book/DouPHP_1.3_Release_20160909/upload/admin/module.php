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

// 载入语言文件
require ('../languages/zh_cn/admin/module.lang.php');
require (dirname(__FILE__) . '/include/init.php');

// rec操作项的初始化
$rec = $check->is_rec($_REQUEST['rec']) ? $_REQUEST['rec'] : 'default';

// 压缩包上传
include_once (ROOT_PATH . 'include/upload.class.php');
$cache_dir = ROOT_PATH . 'cache/';
$dou_upload = new Upload($cache_dir, '', 'zip', 5120);

$smarty->assign('rec', $rec);
$smarty->assign('cur', 'module');

/**
 * +----------------------------------------------------------
 * 扩展列表
 * +----------------------------------------------------------
 */
if ($rec == 'default') {
    $smarty->assign('ur_here', $_LANG['module']);

    $smarty->assign('get', urlencode(serialize($_GET)));
    $smarty->assign('localsite', $dou->dou_localsite('module'));

    $smarty->display('module.htm');
} 

/**
 * +----------------------------------------------------------
 * 安装本地模块
 * +----------------------------------------------------------
 */
if ($rec == 'local') {
    $smarty->assign('ur_here', $_LANG['module']);

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->get_token());

    $smarty->display('module.htm');
} 

/**
 * +----------------------------------------------------------
 * 安装本地模块
 * +----------------------------------------------------------
 */
if ($rec == 'install') {
    // 判断是否有上传文件
    if ($_FILES['zipfile']['name'] == '') {
        $dou->dou_msg($_LANG['module_file_empty'], 'module.php?rec=local');
    } else {
        $zipfile_name = preg_replace('/.zip/i', '', $_FILES['zipfile']['name']);
    }

    // CSRF防御令牌验证
    $firewall->check_token($_POST['token']);
    
    if ($dou_upload->upload_image('zipfile', $zipfile_name))
        $dou->dou_header('cloud.php?rec=handle&type=module&mode=local&cloud_id=' . $zipfile_name);
} 

/**
 * +----------------------------------------------------------
 * 模板卸载页面
 * +----------------------------------------------------------
 */
if ($rec == 'uninstall') {
    $smarty->assign('ur_here', $_LANG['module']);
    
    // 载入待删除模块
    $zipfile_list = glob($cache_dir . '*.zip');
    if (is_array($zipfile_list)) {
        foreach ($zipfile_list as $zipfile) {
            $uninstall_list[] = preg_replace('/.zip/i', '', basename($zipfile));
        }
    }

    // CSRF防御令牌生成
    $smarty->assign('token', $firewall->get_token());

    $smarty->assign('uninstall_list', $uninstall_list);
    $smarty->display('module.htm');
} 

/**
 * +----------------------------------------------------------
 * 卸载模块
 * +----------------------------------------------------------
 */
if ($rec == 'del') {
    // 载入扩展功能
    include_once (ROOT_PATH . ADMIN_PATH . '/include/cloud.class.php');
    $dou_cloud = new Cloud('cache');

    // CSRF防御令牌验证
    $firewall->check_token($_GET['token']);
    
    if ($check->is_extend_id($extend_id = $_REQUEST['extend_id'])) {
        $module_zip = $cache_dir . $extend_id . '.zip'; // 模块压缩包
        $module_dir = $cache_dir . $extend_id; // 模块目录
        
        if ($dou_cloud->file_unzip($module_zip, $module_dir)) {
            $dou_cloud->modify_theme_dir($module_dir); // 将解压得到的扩展目录中的模板文件夹名改成当前启用的模板文件夹名
            $dou_cloud->clear_module($extend_id);
            $dou_cloud->change_updatedate('module', $extend_id, true); // 删除更新时间记录
            $dou->del_dir($module_dir);
            @unlink($module_zip);
            $dou->query("DELETE FROM " . $dou->table('nav') . " WHERE module = '$extend_id'");
            $dou->query("DELETE FROM " . $dou->table('nav') . " WHERE module = '$extend_id" . "_category'");
            $dou->create_admin_log($_LANG['module_uninstall_success'] . ': ' . $extend_id);
            
            $dou->dou_header('module.php?rec=uninstall');
        } else {
            $dou->dou_msg($_LANG['module_unzip_wrong'], 'module.php?rec=uninstall');
        }
    } else {
        $dou->dou_msg($_LANG['module_uninstall_wrong'], 'module.php?rec=uninstall');
    }
} 
?>