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
include_once (ROOT_PATH . 'include/upload.class.php');

// rec操作项的初始化
$rec = $check->is_rec($_REQUEST['rec']) ? $_REQUEST['rec'] : 'system';

// 赋值给模板
$smarty->assign('rec', $rec);
$smarty->assign('cur', 'mobile');

/**
 * +----------------------------------------------------------
 * 系统设置
 * +----------------------------------------------------------
 */
if ($rec == 'system') {
    $smarty->assign('ur_here', $_LANG['mobile_system']);
    
    // act操作项的初始化
    $act = $check->is_rec($_REQUEST['act']) ? $_REQUEST['act'] : 'default';
    
    // 赋值给模板
    $smarty->assign('cfg_list_main', get_cfg_list());
    
    // 系统设置
    if ($act == 'default') {
        // CSRF防御令牌生成
        $smarty->assign('token', $firewall->get_token());
        
        $smarty->display('mobile.htm');
    }    

    // 系统设置数据更新
    elseif ($act == 'update') {
        // 上传图片生成
        if ($_FILES['mobile_logo']['name'] != '') {
            $logo_dir = ROOT_PATH . M_PATH . '/theme/' . $_CFG['mobile_theme'] . '/images/'; // logo上传路径,结尾加斜杠
            $logo = new Upload($logo_dir, ''); // 实例化类文件
            $upfile = $logo->upload_image('mobile_logo', 'logo'); // 上传的文件域
            $_POST['mobile_logo'] = $upfile;
        }
        
        // CSRF防御令牌验证
        $firewall->check_token($_POST['token']);
        
        foreach ($_POST as $name => $value) {
            if (is_array($value)) $value = serialize($value);
            $dou->query("UPDATE " . $dou->table('config') . " SET value = '$value' WHERE name = '$name'");
        }
        
        $dou->create_admin_log($_LANG['mobile'] . ' - ' . $_LANG['mobile_system'] . ': ' . $_LANG['edit_succes']);
        $dou->dou_msg($_LANG['edit_succes'], 'mobile.php');
    }

    // 删除手机版LOGO图片
    elseif ($act == 'clear_logo') {
        $image = $dou->get_one("SELECT value FROM " . $dou->table('config') . " WHERE name = 'mobile_logo'");
        $dou->del_image(M_PATH . '/theme/' . $_CFG['mobile_theme'] . '/images/' . $image);

        $dou->create_admin_log($_LANG['del'] . ': ' . M_PATH . '/theme/' . $_CFG['mobile_theme'] . '/images/' . $image);
        $dou->query("UPDATE " . $dou->table('config') . " SET value = '' WHERE name = 'mobile_logo'");
        $dou->dou_msg($_LANG['del_succes'], 'mobile.php');
    }
} 

/**
 * +----------------------------------------------------------
 * 自定义导航
 * +----------------------------------------------------------
 */
elseif ($rec == 'nav') {
    // act操作项的初始化
    $act = $check->is_rec($_REQUEST['act']) ? $_REQUEST['act'] : 'default';
    
    // 赋值给模板
    $smarty->assign('act', $act);
    $smarty->assign('nav_list', $dou->get_nav('mobile'));
    
    // 幻灯列表
    if ($act == 'default') {
        $smarty->assign('ur_here', $_LANG['mobile_nav']);
        $smarty->assign('action_link', array (
                'text' => $_LANG['nav_add'],
                'href' => 'mobile.php?rec=nav&act=add' 
        ));
        
        // 赋值给模板
        $smarty->assign('nav_list', $dou->get_nav('mobile'));
        
        $smarty->display('mobile.htm');
    }    

    // 导航添加
    elseif ($act == 'add') {
        $smarty->assign('ur_here', $_LANG['mobile_nav']);
        $smarty->assign('action_link', array (
                'text' => $_LANG['nav_list'],
                'href' => 'mobile.php?rec=nav' 
        ));
        
        // CSRF防御令牌生成
        $smarty->assign('token', $firewall->get_token());
        
        // 赋值给模板
        $smarty->assign('catalog', $dou->get_catalog());
        $smarty->assign('nav_list', $dou->get_nav('mobile'));
        
        $smarty->display('mobile.htm');
    }    

    // 导航添加处理
    elseif ($act == 'insert') {
        $nav_menu = explode(",", $_POST['nav_menu']);
        $module = $nav_menu[0];
        $guide = $module == 'nav' ? trim($_POST['guide']) : $nav_menu[1];
        
        if (empty($_POST['nav_name']))
            $dou->dou_msg($_LANG['nav_name'] . $_LANG['is_empty']);
            
        // CSRF防御令牌验证
        $firewall->check_token($_POST['token']);
        
        $sql = "INSERT INTO " . $dou->table('nav') . " (id, module, nav_name, guide, parent_id, type, sort)" . " VALUES (NULL, '$module', '$_POST[nav_name]', '$guide', 0, 'mobile', '$_POST[sort]')";
        $dou->query($sql);
        
        $dou->create_admin_log($_LANG['mobile'] . ' - ' . $_LANG['nav_add'] . ': ' . $_POST['nav_name']);
        $dou->dou_msg($_LANG['nav_add_succes'], 'mobile.php?rec=nav');
    }    

    // 导航编辑
    elseif ($act == 'edit') {
        $smarty->assign('ur_here', $_LANG['mobile_nav']);
        $smarty->assign('action_link', array (
                'text' => $_LANG['nav_list'],
                'href' => 'mobile.php?rec=nav' 
        ));
        
        // 验证并获取合法的ID
        $id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : '';
        
        $query = $dou->select($dou->table('nav'), '*', '`id` = \'' . $id . '\'');
        $nav_info = $dou->fetch_array($query);
        
        // CSRF防御令牌生成
        $smarty->assign('token', $firewall->get_token());
        
        // 格式化数据
        $nav_info['url'] = $nav_info['module'] == 'nav' ? $nav_info['guide'] : $dou->rewrite_url($nav_info['module'], $nav_info['guide']);
        
        // 赋值给模板
        $smarty->assign('catalog', $dou->get_catalog($nav_info['module'], $nav_info['guide']));
        $smarty->assign('nav_list', $dou->get_nav('mobile'));
        $smarty->assign('nav_info', $nav_info);
        
        $smarty->display('mobile.htm');
    }    

    // 导航编辑处理
    elseif ($act == 'update') {
        if (empty($_POST['nav_name']))
            $dou->dou_msg($_LANG['nav_name'] . $_LANG['is_empty']);
            
        // CSRF防御令牌验证
        $firewall->check_token($_POST['token']);
        
        /* 判断是站内还是站外导航 */
        if ($_POST['nav_menu']) {
            $nav_menu = explode(',', $_POST['nav_menu']);
            $update = ", module='$nav_menu[0]', guide='$nav_menu[1]'";
        } else {
            $update = ", guide='$_POST[guide]'";
        }
        
        $sql = "update " . $dou->table('nav') . " SET nav_name = '$_POST[nav_name]'" . $update . ", sort = '$_POST[sort]' WHERE id = '$_POST[id]'";
        $dou->query($sql);
        
        $dou->create_admin_log($_LANG['mobile'] . ' - ' . $_LANG['nav_edit'] . ': ' . $_POST['nav_name']);
        
        $dou->dou_msg($_LANG['nav_edit_succes'], 'mobile.php?rec=nav');
    }    

    // 导航删除
    elseif ($act == 'del') {
        // 验证并获取合法的ID
        $id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : $dou->dou_msg($_LANG['illegal'], 'mobile.php?rec=nav');
        
        $nav_name = $dou->get_one("SELECT nav_name FROM " . $dou->table('nav') . " WHERE id = '$id'");
        
        if (isset($_POST['confirm']) ? $_POST['confirm'] : '') {
            $dou->create_admin_log($_LANG['mobile'] . ' - ' . $_LANG['nav_del'] . ': ' . $nav_name);
            $dou->delete($dou->table('nav'), "id = $id", 'mobile.php?rec=nav');
        } else {
            $_LANG['del_check'] = preg_replace('/d%/Ums', $nav_name, $_LANG['del_check']);
            $dou->dou_msg($_LANG['del_check'], 'mobile.php?rec=nav', '', '30', "mobile.php?rec=nav&act=del&id=$id");
        }
    }
} 

/**
 * +----------------------------------------------------------
 * 首页幻灯图片
 * +----------------------------------------------------------
 */
elseif ($rec == 'show') {
    $smarty->assign('ur_here', $_LANG['mobile_show']);
    
    // act操作项的初始化
    $act = $check->is_rec($_REQUEST['act']) ? $_REQUEST['act'] : 'default';
    
    // 图片上传
    $images_dir = 'data/slide/' . M_PATH . '/'; // 文件上传路径 结尾加斜杠
    $thumb_dir = 'thumb/'; // 缩略图路径（相对于$images_dir） 结尾加斜杠，留空则跟$images_dir相同
    $img = new Upload(ROOT_PATH . $images_dir, $thumb_dir); // 实例化类文件
                                                            
    // 如果突破上次路径不存在则创建
    if (!file_exists(ROOT_PATH . $images_dir))
        mkdir(ROOT_PATH . $images_dir, 0777);
    if (!file_exists(ROOT_PATH . $images_dir . $thumb_dir))
        mkdir(ROOT_PATH . $images_dir . $thumb_dir, 0777);
        
    // 赋值给模板
    $smarty->assign('act', $act);
    $smarty->assign('show_list', $dou->get_show_list('mobile'));
    
    // 幻灯列表
    if ($act == 'default') {
        // CSRF防御令牌生成
        $smarty->assign('token', $firewall->get_token());
        
        $smarty->display('mobile.htm');
    }    

    // 幻灯添加处理
    elseif ($act == 'insert') {
        if (empty($_POST['show_name']))
            $dou->dou_msg($_LANG['show_name'] . $_LANG['is_empty']);
            
        // 上传图片生成
        $name = date('Ymd');
        for($i = 0; $i < 6; $i++) {
            $name .= chr(mt_rand(97, 122));
        }
        
        $upfile = $img->upload_image('show_img', $name); // 上传的文件域
        $file = $images_dir . $upfile;
        $img->to_file = true;
        $img->make_thumb($upfile, 100, 100);
        
        // CSRF防御令牌验证
        $firewall->check_token($_POST['token']);
        
        $sql = "INSERT INTO " . $dou->table('show') . " (id, show_name, show_link, show_img, type, sort)" .
                 " VALUES (NULL, '$_POST[show_name]', '$_POST[show_link]', '$file', 'mobile', '$_POST[sort]')";
        $dou->query($sql);
        
        $dou->create_admin_log($_LANG['mobile'] . ' - ' . $_LANG['show_add'] . ': ' . $_POST[show_name]);
        $dou->dou_msg($_LANG['show_add_succes'], 'mobile.php?rec=show');
    }    

    // 幻灯编辑
    elseif ($act == 'edit') {
        // 验证并获取合法的ID
        $id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : '';
        
        $query = $dou->select($dou->table('show'), '*', '`id` = \'' . $id . '\'');
        $show = $dou->fetch_array($query);
        
        // CSRF防御令牌生成
        $smarty->assign('token', $firewall->get_token());
        
        // 赋值给模板
        $smarty->assign('id', $id);
        $smarty->assign('show', $show);
        
        $smarty->display('mobile.htm');
    } 

    elseif ($act == 'update') {
        if (empty($_POST['show_name']))
            $dou->dou_msg($_LANG['show_name'] . $_LANG['is_empty']);
            
        // 上传图片生成
        if ($_FILES['show_img']['name'] != "") {
            // 分析广告图片名称
            $basename = addslashes(basename($_POST['show_img']));
            $file_name = substr($basename, 0, strrpos($basename, '.'));
            
            $upfile = $img->upload_image('show_img', "$file_name"); // 上传的文件域
            $file = $images_dir . $upfile;
            $img->to_file = true;
            $img->make_thumb($upfile, 100, 100);
            
            $up_file = ", image='$file'";
        }
        
        // CSRF防御令牌验证
        $firewall->check_token($_POST['token']);
        
        $sql = "update " . $dou->table('show') . " SET show_name='$_POST[show_name]'" . $up_file .
                 " ,show_link = '$_POST[show_link]', sort = '$_POST[sort]' WHERE id = '$_POST[id]'";
        $dou->query($sql);
        
        $dou->create_admin_log($_LANG['mobile'] . ' - ' . $_LANG['show_edit'] . ': ' . $_POST[show_name]);
        
        $dou->dou_msg($_LANG['show_edit_succes'], 'mobile.php?rec=show');
    }    

    // 幻灯删除
    elseif ($act == 'del') {
        // 验证并获取合法的ID
        $id = $check->is_number($_REQUEST['id']) ? $_REQUEST['id'] : $dou->dou_msg($_LANG['illegal'], 'mobile.php?rec=show');
        
        $show_name = $dou->get_one("SELECT show_name FROM " . $dou->table('show') . " WHERE id = '$id'");
        
        if (isset($_POST['confirm']) ? $_POST['confirm'] : '') {
            // 删除相应商品图片
            $show_img = $dou->get_one("SELECT show_img FROM " . $dou->table('show') . " WHERE id = '$id'");
            $file_name = basename($show_img);
            $image = explode(".", $file_name);
            $show_img_thumb = $images_dir . $thumb_dir . $image['0'] . "_thumb." . $image['1'];
            @ unlink(ROOT_PATH . $show_img);
            @ unlink(ROOT_PATH . $show_img_thumb);
            
            $dou->create_admin_log($_LANG['mobile'] . ' - ' . $_LANG['show_del'] . ': ' . $show_name);
            $dou->delete($dou->table('show'), "id = $id", 'mobile.php?rec=show');
        } else {
            $_LANG['del_check'] = preg_replace('/d%/Ums', $show_name, $_LANG['del_check']);
            $dou->dou_msg($_LANG['del_check'], 'mobile.php?rec=show', '', '30', "mobile.php?rec=show&act=del&id=$id");
        }
    }
}

/**
 * +----------------------------------------------------------
 * 设置模板
 * +----------------------------------------------------------
 */
elseif ($rec == 'theme') {
    $smarty->assign('ur_here', $_LANG['mobile_theme']);

    // act操作项的初始化
    $act = $check->is_rec($_REQUEST['act']) ? $_REQUEST['act'] : 'default';

    // 赋值给模板
    $smarty->assign('act', $act);
        
    // 幻灯列表
    if ($act == 'default') {
        $theme_enable = $dou->get_theme_info($_CFG['mobile_theme'], true);
        $theme_array = $dou->get_subdirs(ROOT_PATH . M_PATH . '/theme/');
        foreach ($theme_array as $unique_id) {
            if ($unique_id == $_CFG['mobile_theme']) continue;
            $theme_list[] = $dou->get_theme_info($unique_id, true);
        }
        
        // 赋值给模板
        $smarty->assign('theme_enable', $theme_enable);
        $smarty->assign('theme_list', $theme_list);
        
        $smarty->display('mobile.htm');
    }    

    // 在线安装模板
    elseif ($act == 'install') {
        $smarty->assign('get', urlencode(serialize($_GET)));
        $smarty->assign('localsite', $dou->dou_localsite('mobile'));
        
        $smarty->display('mobile.htm');
    }    

    // 模板启用
    elseif ($act == 'enable') {
        if ($check->is_extend_id($unique_id = $_REQUEST['unique_id'])) {
            $theme_array = $dou->get_subdirs(ROOT_PATH . M_PATH . '/theme/');
            if (in_array($unique_id, $theme_array)) { // 判断删除操作的模板是否真实存在
                // 替换系统设置中模板值
                $dou->query("UPDATE " . $dou->table('config') . " SET value = '$unique_id' WHERE name = 'mobile_theme'");
                $dou->dou_clear_cache(ROOT_PATH . 'cache/m'); // 更新缓存
            }
        }
        
        $dou->dou_header('mobile.php?rec=theme');
    } 

    // 删除模板
    elseif ($act == 'del') {
        // 载入扩展功能
        include_once (ROOT_PATH . ADMIN_PATH . '/include/cloud.class.php');
        $dou_cloud = new Cloud('cache');
    
        if ($check->is_extend_id($unique_id = $_REQUEST['unique_id'])) {
            $theme_array = $dou->get_subdirs(ROOT_PATH . M_PATH . '/theme/');
            if (in_array($unique_id, $theme_array)) { // 判断删除操作的模板是否真实存在
                $dou->del_dir(ROOT_PATH . M_PATH . '/theme/' . $unique_id);
                $dou_cloud->change_updatedate('mobile', $unique_id, true); // 删除更新时间记录
                $dou->create_admin_log($_LANG['mobile_theme_del'] . ': ' . $unique_id);
            }
        }
        
        $dou->dou_header('mobile.php?rec=theme');
    }
}

/**
 * +----------------------------------------------------------
 * 获取系统设置列表
 * +----------------------------------------------------------
 */
function get_cfg_list($tab = 'main') {
    $sql = "SELECT * FROM " . $GLOBALS['dou']->table('config') . " WHERE tab = 'mobile' ORDER BY sort ASC";
    $query = $GLOBALS['dou']->query($sql);
    while ($row = $GLOBALS['dou']->fetch_array($query)) {
        if ($row['box']) {
            $box = explode(",", $row['box']);
        }
        if ($row['name'] == 'mobile_logo') {
            $row['value'] = $row['value'] ? M_PATH . '/theme/' . $GLOBALS['_CFG']['mobile_theme'] . '/images/' . $row['value'] : '';
        }
        
        $cue = $GLOBALS['_LANG'][$row['name'] . '_cue'];
        
        // 数组类型的设置选项
        if ($row['type'] == 'array') {
            $arr = unserialize($row['value']);
            foreach ((array)$arr as $key=>$v) {
                $value_array[] = array (
                        "value" => $v,
                        "name" => $row['name'] . '[' . $key . ']',
                        "lang" => $GLOBALS['_LANG'][$row['name'] . '_' . $key],
                        "cue" => $GLOBALS['_LANG'][$row['name'] . '_' . $key . '_cue']
                );
            }
        }

        $cfg_list[] = array (
                "value" => $value_array ? $value_array : $row['value'],
                "name" => $row['name'],
                "type" => $row['type'],
                "box" => $box,
                "lang" => $GLOBALS['_LANG'][$row['name']],
                "cue" => $cue 
        );
    }
    
    return $cfg_list;
}
?>