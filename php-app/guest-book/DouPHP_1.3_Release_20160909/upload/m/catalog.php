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
$rec = $check->is_letter($_REQUEST['rec']) ? $_REQUEST['rec'] : 'default';

// 赋值给模板-meta和title信息
$smarty->assign('page_title', $dou->page_title('catalog'));
$smarty->assign('keywords', $_LANG['catalog']);
$smarty->assign('description', $_LANG['catalog']);

// 赋值给模板-导航栏
$smarty->assign('nav_top_list', $dou->get_nav('top'));
$smarty->assign('nav_middle_list', $dou->get_nav('middle'));
$smarty->assign('nav_bottom_list', $dou->get_nav('bottom'));

// 赋值给模板-数据
$smarty->assign('catalog', get_catalog());
$smarty->assign('head', $dou->head($_LANG['catalog']));

$smarty->display('catalog.dwt');

/**
 * +----------------------------------------------------------
 * 获取整站目录数据
 * +----------------------------------------------------------
 */
function get_catalog() {
    // 单页面列表
    foreach ($GLOBALS['dou']->get_page_nolevel() as $row) {
        $catalog[] = array (
                "name" => $row['page_name'],
                "mark" => '-' . $row['mark'],
                "url" => $row['url'] 
        );
    }

    // 栏目模块
    foreach ($GLOBALS['_MODULE']['column'] as $module_id) {
        $catalog[] = array (
                "name" => $GLOBALS['_LANG'][$module_id . '_category'],
                "url" => $GLOBALS['dou']->rewrite_url($module_id . '_category') 
        );
        foreach ($GLOBALS['dou']->get_category_nolevel($module_id . '_category') as $row) {
            $catalog[] = array (
                    "name" => $row['cat_name'],
                    "mark" => '-' . $row['mark'],
                    "url" => $row['url'] 
            );
        }
    }

    // 简单模块
    foreach ($GLOBALS['_MODULE']['single'] as $module_id) {
        // 不显示的模块
        $no_show = 'plugin|mobile|link';
        if (!in_array($module_id, explode('|', $no_show))) {
            $catalog[] = array (
                    "name" => $GLOBALS['_LANG'][$module_id],
                    "url" => $GLOBALS['dou']->rewrite_url($module_id) 
            );
        }
    }
    
    return $catalog;
}

?>