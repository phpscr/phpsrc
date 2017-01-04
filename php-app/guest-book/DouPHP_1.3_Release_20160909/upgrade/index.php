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
define('IN_DOUCO', true);

require (dirname(__FILE__) . '/include/init.php');

// rec操作项的初始化
$step = $_REQUEST['step'] ? trim($_REQUEST['step']) : 'upgrade';

// 更新后的版本
$new_version = 'v1.3 Release 20160909';

/**
 * +----------------------------------------------------------
 * 升级提示
 * +----------------------------------------------------------
 */
if ($step == 'maual') {
    $title = $_LANG['douphp'] . " &rsaquo; " . $_LANG['maual'];
    
    $smarty->assign('title', $title);
    $smarty->display('maual.htm');
}

/**
 * +----------------------------------------------------------
 * 升级检测
 * +----------------------------------------------------------
 */
elseif ($step == 'upgrade') {
    $title = $_LANG['douphp'] . " &rsaquo; " . $_LANG['upgrade'];
    
    // 版本号
    $ver_cur = number_format(substr($_CFG['douphp_version'], 1, 4), 1);
    
    // 历史版本
    $ver_list = array (1.0, 1.1, 1.2, 1.3);
    
    // 根据版本显示升级提示
    foreach ($ver_list as $ver) {
        $ver = number_format($ver, 1);
        $file_txt = U_PATH . 'data/txt/upgrade_' . $ver . '.txt';
        if ($ver >= $ver_cur && file_exists($file_txt))
            $up_txt .= file_get_contents($file_txt) . "\n";
    }
    
    $up_array = explode("\n", $up_txt);
    
    foreach ($up_array as $replace) {
        if (strstr($replace, '修正')) {
            $replace = '<i class="x">问题修复</i><em>' . $replace . '</em>';
        }
        if (strstr($replace, '增加') || strstr($replace, '新增')) {
            $replace = '<i class="z">新功能</i><em>' . $replace . '</em>';
        }
        if (strstr($replace, '优化')) {
            $replace = '<i class="y">优化</i><em>' . $replace . '</em>';
        }
        if (strstr($replace, '安全')) {
            $replace = '<i class="a">安全升级</i><em>' . $replace . '</em>';
        }
        
								if ($replace)
            $up_list[] = $replace;
    }
    
    // 检查目录
    $check_dirs = array (
            'cache',
            'cache/admin',
            'cache/m',
            'data',
            'data/slide',
            'data/backup',
            'images/article',
            'images/product',
            'images/upload' 
    );
    
    // 如果目录不存在则建立
    foreach ($check_dirs as $dir) {
        $full_dir = ROOT_PATH . $dir;
        
        if (!file_exists($full_dir))
            mkdir($full_dir, 0777);
    }
    
    // 检查数据库连接
    if (!$link = @mysql_connect($dbhost, $dbuser, $dbpass))
        $up_info['wrong'] = $_LANG['wrong_connect'];
        
        // 如果提交表单执行以下
    if ($_POST['upgrade']) {
        // 嵌入config配置文件
        $file_config = ROOT_PATH . "data/config.php";
        include_once ($file_config);
        
        // 修改config文件内容
        $config_str = "<?php\n";
        $config_str .= "/**\n";
        $config_str .= " * DouPHP\n";
        $config_str .= " * --------------------------------------------------------------------------------------------------\n";
        $config_str .= " * 版权所有 2013-2014 漳州豆壳网络科技有限公司，并保留所有权利。\n";
        $config_str .= " * 网站地址: http://www.douco.com\n";
        $config_str .= " * --------------------------------------------------------------------------------------------------\n";
        $config_str .= " * 这不是一个自由软件！您只能在遵守授权协议前提下对程序代码进行修改和使用；不允许对程序代码以任何形式任何目的的再发布。\n";
        $config_str .= " * 授权协议：http://www.douco.com/license.html\n";
        $config_str .= " * --------------------------------------------------------------------------------------------------\n";
        $config_str .= " * Author: DouCo\n";
        $config_str .= " * Release Date: 2014-06-05\n";
        $config_str .= " */\n\n";
        
        $config_str .= "// database host\n";
        $config_str .= '$dbhost   = "' . $dbhost . '";' . "\n\n";
        
        $config_str .= "// database name\n";
        $config_str .= '$dbname   = "' . $dbname . '";' . "\n\n";
        
        $config_str .= "// database username\n";
        $config_str .= '$dbuser   = "' . $dbuser . '";' . "\n\n";
        
        $config_str .= "// database password\n";
        $config_str .= '$dbpass   = "' . $dbpass . '";' . "\n\n";
        
        $config_str .= "// table prefix\n";
        $config_str .= '$prefix   = "' . $prefix . '";' . "\n\n";
        
        $config_str .= "// charset\n";
        $config_str .= "define('DOU_CHARSET','utf-8');" . "\n\n";
        
        $config_str .= "// administrator path\n";
        $config_str .= "define('ADMIN_PATH','admin');\n\n";
        
        $config_str .= "// mobile path\n";
        $config_str .= "define('M_PATH','m');\n\n\n";
        
        $config_str .= "?>";
        
        file_put_contents($file_config, $config_str);
        
        // 根据版本执行数据库升级
        foreach ($ver_list as $ver) {
            $ver = number_format($ver, 1);
            $file_sql = U_PATH . 'data/sql/upgrade_' . $ver . '.sql';
            if ($ver >= $ver_cur && file_exists($file_sql))
                $sql .= file_get_contents($file_sql) . "\n\n\n";
        }
        
        // 数据表前缀替换
        $sql = preg_replace('/dou_/Ums', "$prefix", $sql);
        
        // 进行安装的常规替换
        $sql_head = "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n";
        $sql_head .= "SET time_zone = '+00:00';\n\n\n";
        
        $sql_head .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
        $sql_head .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
        $sql_head .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
        $sql_head .= "/*!40101 SET NAMES utf8 */;\n\n";
        
        $sql = $sql_head . $sql;
        
        // 导入数据
        $upgrade->sql_execute($sql);
        
        /* 写入 hash_code，做为网站唯一性密钥 */
        $hash_code = md5(md5(time()) . md5(md5(ROOT_URL . $dbhost . $dbname . $dbuser . $dbpass)));
        
        // 生成初始化数据查询语句
        foreach ($_CFG as $key => $value) {
            $up_sql .= "UPDATE " . $dou->table('config') . " SET value = '$value' WHERE name = '$key';\n";
        }
        $up_sql .= "UPDATE " . $dou->table('config') . " SET value = '" . $new_version . "' WHERE name = 'douphp_version';\n";
        $up_sql .= "UPDATE " . $dou->table('config') . " SET value = '$hash_code' WHERE name = 'hash_code';\n";
        $up_sql .= "UPDATE " . $dou->table('config') . " SET box = '' WHERE name = 'language';\n";
        
        // 写入更新日期
        $need_update = array('update', 'patch', 'article', 'product'); // 系统升级时需要同步写入更新日期的
        $date = substr(trim($new_version), -8);;
        if ($_CFG['update_date']) {
            $update_date = unserialize($_CFG['update_date']);
            foreach ($update_date as $key_class=>$class) {
                foreach ($class as $key_item=>$item) {
                    if (in_array($key_item, $need_update)) {
                        $update_date[$key_class][$key_item] = $date;
                    } else {
                        $update_date[$key_class][$key_item] = $item;
                    }
                }
            }
            $update_date = serialize($update_date);
        } else {
            $update_date = 'a:2:{s:6:"system";a:2:{s:6:"update";i:' . $date . ';s:5:"patch";i:' . $date . ';}s:6:"module";a:2:{s:7:"article";i:' . $date . ';s:7:"product";i:' . $date . ';}}';
        }
        $up_sql .= "UPDATE " . $dou->table('config') . " SET value = '$update_date' WHERE name = 'update_date'";
        
        // 根据版本进行功能升级
        foreach ($ver_list as $ver) {
            $ver = number_format($ver, 1);
            $file_php = U_PATH . 'data/php/upgrade_' . $ver . '.php';
            if ($ver >= $ver_cur && file_exists($file_php))
                include($file_php);
        }
        
        // 还原站点原始数据
        $upgrade->sql_execute($up_sql);
								
        header("Location:index.php?step=finish");
    }
    
    $smarty->assign('title', $title);
    $smarty->assign('up_list', $up_list);
    $smarty->assign('up_info', $up_info);
    $smarty->display('upgrade.htm');
} 

/**
 * +----------------------------------------------------------
 * 升级完成
 * +----------------------------------------------------------
 */
elseif ($step == 'finish') {
    // 生成system.dou文件
    if (!file_exists($system_file)) {
        $content .= "// DOUPHP INSTALLED\r\n";
        $content .= "column_module:product,article\r\n";
        $content .= "single_module:guestbook,link\r\n";
        file_put_contents($system_file, $content);
        
        // 删除旧的系统安装标志
        @unlink(ROOT_PATH . 'data/install.lock');
    }

    // 清除缓存
    $upgrade->dou_clear_cache(ROOT_PATH . "cache");
    
    $title = $_LANG['douphp'] . " &rsaquo; " . $_LANG['finish'];
    
    $smarty->assign('title', $title);
    $smarty->display('finish.htm');
}

?>