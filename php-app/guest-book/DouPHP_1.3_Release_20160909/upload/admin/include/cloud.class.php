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
if (!defined('IN_DOUCO')) {
    die('Hacking attempt');
}
class Cloud {
    private $cache_dir; // 模块包所在目录
    private $root_dir; // 站点根目录目录
    
    /**
     * +----------------------------------------------------------
     * 构造函数
     * +----------------------------------------------------------
     */
    function Cloud($cache_dir) {
        $this->cache_dir = ROOT_PATH . $cache_dir . '/';
        $this->root_dir = ROOT_PATH;
    }

    /**
     * +----------------------------------------------------------
     * 安装模块
     * +----------------------------------------------------------
     * $type 类型
     * $cloud_id 模块ID
     * $mode 是否是本地上传模式
     * +----------------------------------------------------------
     */
    function handle($type, $cloud_id, $mode = '') {
        // 基础数据
        $item_zip = $this->cache_dir . $cloud_id . '.zip'; // 扩展压缩包
        $item_dir = $this->cache_dir . $cloud_id; // 扩展目录

        ob_end_flush(); // init.php打开输出缓冲区，输出信息不直接发送到浏览器，要先关闭以便输出缓冲区的内容

        // STEP1 安装条件验证
        if ($mode != 'update') { // 升级时无需安装条件验证
            if ($wrong = $this->install_check($type, $cloud_id)) {
                @unlink($item_zip);
                $this->dou_flush($wrong);
                exit;
            }
        }

        // STEP2 下载压缩包，如果是本地安装则直接显示正在解压缩
        if ($mode == 'local') {
            $this->dou_flush($GLOBALS['_LANG']['cloud_unzip_ing']);
        } else {
            if ($type == 'system') {
                $down_url = 'http://down.douco.com/' . $mode . '/' . $cloud_id . '.zip';
            } elseif ($type == 'onedou') {
                $down_url = 'http://cloud.douco.com/onedou/' . $cloud_id . '.html';
            } else {
                $down_url = 'http://cloud.douco.com/extend/down/' . $cloud_id . '.html';
            }
            
            $this->dou_flush($GLOBALS['_LANG']['cloud_down_ing_0'] . $down_url . $GLOBALS['_LANG']['cloud_down_ing_1']);
            if ($this->file_download($down_url, $this->cache_dir)) {
               $this->dou_flush($GLOBALS['_LANG']['cloud_unzip_ing']);
            } else {
               $this->dou_flush($GLOBALS['_LANG']['cloud_down_wrong']);
               exit;
            }
        }
        
        // STEP3 解压缩
        if ($this->file_unzip($item_zip, $item_dir)) {
            if ($type == 'module') $this->modify_theme_dir($item_dir); // 将解压得到的扩展目录中的模板文件夹名改成当前启用的模板文件夹名
            $this->dou_flush($GLOBALS['_LANG']['cloud_install_ing'] . $GLOBALS['_LANG']['cloud_' . $type] . '…');
        } else {
            @unlink($item_zip);
            $GLOBALS['dou']->del_dir($item_dir);
            $this->dou_flush($GLOBALS['_LANG']['cloud_unzip_wrong']);
            exit;
        }
        
        // STEP4 安装模块
        if ($wrong = $this->install($type, $cloud_id, $mode)) {
            $this->dou_flush($wrong);
            exit;
        } else {
            $text = $mode == 'update' ? $GLOBALS['_LANG']['cloud_update_0'] : $GLOBALS['_LANG']['cloud_install_0'];
            $success[] = $text . $cloud_id . $GLOBALS['_LANG']['cloud_install_1'];
            $success[] = $this->msg_success($type, $cloud_id);
            
            $this->dou_flush($success);
        }
        
        $GLOBALS['dou']->create_admin_log($GLOBALS['_LANG']['cloud_handle_success'] . $GLOBALS['_LANG']['cloud_' . $type] . ': ' . $cloud_id);
        
        // 操作完成补足页面HTML代码
        echo '</div></div></div><div class="clear"></div><div id="dcFooter"><div id="footer"><div class="line"></div><ul>' . $GLOBALS['_LANG']['footer_copyright'] . '</ul></div></div><div class="clear"></div></div></body></html>';
    }

    /**
     * +----------------------------------------------------------
     * 安装模块
     * +----------------------------------------------------------
     * $type 类型
     * $cloud_id 模块ID
     * $mode 操作模式
     * +----------------------------------------------------------
     */
    function install($type, $cloud_id, $mode) {
        global $prefix;

        // 基础数据
        $item_zip = $this->cache_dir . $cloud_id . '.zip'; // 模块压缩包
        $item_dir = $this->cache_dir . $cloud_id; // 模块目录
        $sql_install = $this->root_dir . "data/backup/$cloud_id.sql"; // 安装用的SQL文件
        $sql_update = $this->root_dir . "data/backup/$cloud_id" . "_update.sql"; // 升级用的SQL文件
        
        // STEP1 拷贝模块文件
        if ($type == 'theme') {
            $this->dir_action($item_dir, $this->root_dir . 'theme/' . $cloud_id);
        } elseif ($type == 'mobile') {
            $this->dir_action($item_dir, $this->root_dir . M_PATH . '/theme/' . $cloud_id);
        } elseif ($type == 'plugin') {
            $this->dir_action($item_dir, $this->root_dir . 'include/plugin/' . $cloud_id);
        } else {
            $this->dir_action($item_dir, $this->root_dir);
        }
        
        // STEP2 数据库操作
        if ($type == 'module') {
            $sql_file = $mode == 'update' ? $sql_update : $sql_install; // 判断是安装还是升级
            if (file_exists($sql_file)) { // 模块允许不存在数据表
                $sql = file_get_contents($sql_file);
                $sql = preg_replace('/dou_/Ums', "$prefix", $sql); // 数据表前缀替换
                if ($GLOBALS['dou']->fn_execute($sql)) {
                    if ($mode != 'update') { // 升级时不判断模块类型
                        if (strpos($sql, $cloud_id . '_category') === false) {
                            $module_type = 'single_module';
                            
                            // 简单模块根据配置参数加入显示设置项和自定义设置项
                            if (strpos($sql, 'CREATE-SYSTEM-CONFIG') !== false) $this->change_system($cloud_id);
                        } else {
                            $module_type = 'column_module';
                            
                            // 栏目模块加入显示设置项和自定义设置项
                            $this->change_system($cloud_id);
                        }
                    }
                } else {
                    $wrong[] = $GLOBALS['_LANG']['cloud_sql_wrong'];
                }
            } elseif ($cloud_id == 'mobile') {
                $module_type = 'single_module';
            }
            
            // STEP3 将模块写入系统文件
            if ($module_type) { // 如果不存在模块类型，则不写入系统文件
                if (!$this->change_systemfile($cloud_id, $module_type))
                    $wrong[] = $GLOBALS['_LANG']['cloud_systemfile_wrong'];
            }
        } elseif ($type == 'system') {
            if (file_exists($action_dir = $this->root_dir . '_update/')) {
                include_once ($action_dir . 'action.php'); // 执行系统升级操作
                $GLOBALS['dou']->del_dir($action_dir);
            }
        }
        
        // STEP4 无论安装成功与否都将删除安装文件
        @unlink($item_zip);
        $GLOBALS['dou']->del_dir($item_dir);
        @unlink($sql_install);
        @unlink($sql_update);
            
        if ($wrong) {
            $this->clear_module($cloud_id); // 如果安装过程出错，则回滚安装步骤
            return $wrong;
        } else {
           if ($type != 'system') // 类型为system时不再重复以下操作
                $this->change_updatedate($type, $cloud_id, false, $mode); // 写入更新日期
        }
    }
    
    /**
     * +----------------------------------------------------------
     * 模块安装验证
     * +----------------------------------------------------------
     * $type 类型
     * $cloud_id 模块ID
     * +----------------------------------------------------------
     */
    function install_check($type, $cloud_id) {
        if ($type == 'module') {
            if (in_array($cloud_id, $GLOBALS['_MODULE']['all'])) {
                $wrong[] = $GLOBALS['_LANG']['cloud_'.$type] . $GLOBALS['_LANG']['cloud_install_repeat'];
            } elseif ($GLOBALS['dou']->table_exist($cloud_id)) {
                $wrong[] = $GLOBALS['_LANG']['cloud_'.$type] . $GLOBALS['_LANG']['cloud_sql_repeat'];
            }
        } elseif ($type == 'plugin') {
            if (file_exists($this->root_dir . 'include/plugin/' . $cloud_id))
                $wrong[] = $GLOBALS['_LANG']['cloud_'.$type] . $GLOBALS['_LANG']['cloud_install_repeat'];
        } elseif ($type == 'theme') {
            if (file_exists($this->root_dir . 'theme/' . $cloud_id))
                $wrong[] = $GLOBALS['_LANG']['cloud_'.$type] . $GLOBALS['_LANG']['cloud_install_repeat'];
        } elseif ($type == 'mobile') {
            if (file_exists($this->root_dir . M_PATH . '/theme/' . $cloud_id))
                $wrong[] = $GLOBALS['_LANG']['cloud_'.$type] . $GLOBALS['_LANG']['cloud_install_repeat'];
        }
        
        return $wrong;
    }
    
    /**
     * +----------------------------------------------------------
     * 修改系统文件
     * +----------------------------------------------------------
     * $cloud_id 扩展ID
     * $type 模块类型
     * $del 删除模式
     * +----------------------------------------------------------
     */
    function change_systemfile($cloud_id, $type = '', $del = false) {
        // 读取模块列表
        $module = $GLOBALS['_MODULE'];
        
        if ($del) { // 删除模块
            // 栏目模块
            foreach ((array)$module['column'] as $key=>$value) {
                if ($value == $cloud_id) unset($module['column'][$key]);
            }
            
            // 简单模块
            foreach ((array)$module['single'] as $key=>$value) {
                if ($value == $cloud_id) unset($module['single'][$key]);
            }
        } else { // 添加模块
            if ($type == 'column_module') {
                $module['column'][] = $cloud_id;
            } else {
                $module['single'][] = $cloud_id;
            }
        }
        
        // 删减后的新模块配置信息
        $new_column = "column_module:" . implode(',',$module['column']);
        $new_single = "single_module:" . implode(',',$module['single']);
        
        // 修改系统文件
        $system_file = $this->root_dir . 'data/system.dou';
        foreach (@file($system_file) as $line) {
            $line = trim($line);
            if (strpos($line, 'column_module') === 0) {
                $new_content .= $new_column . "\r\n";
            } elseif (strpos($line, 'single_module') === 0) {
                $new_content .= $new_single . "\r\n";
            } else {
                $new_content .= $line . "\r\n";
            }
        }
        
        // 将系统文件内容写入
        if (file_put_contents($system_file, $new_content))
            return true;
    }
    
    /**
     * +----------------------------------------------------------
     * 写入更新日期
     * +----------------------------------------------------------
     * $type 类型
     * $cloud_id 模块ID
     * $del 删除模式
     * $mode 操作模式
     * +----------------------------------------------------------
     */
    function change_updatedate($type, $cloud_id, $del = false, $mode = '') {
        // 读取更新时间（不使用$_CFG是因为$_CFG后如果操作了数据库不能获取操作数据库后的最新数据）
        $update_date = $GLOBALS['dou']->get_one("SELECT value FROM " . $GLOBALS['dou']->table('config') . " WHERE name = 'update_date'");
        $update_date = unserialize($update_date);
        
        // 删减操作
        if ($del) {
            unset($update_date[$type][$cloud_id]);
        } else {
            if ($type == 'system') {
                $date = substr(trim($cloud_id), -8);
                $update_date['system'][$mode] = $date;
            } else {
                $update_date[$type][$cloud_id] = date("Ymd", time());
            }
        }
        
        // 重新写入更新时间
        $new_update_date = serialize($update_date);
        $GLOBALS['dou']->query("UPDATE " . $GLOBALS['dou']->table('config') . " SET value = '$new_update_date' WHERE name = 'update_date'");
    }
    
    /**
     * +----------------------------------------------------------
     * 为栏目模块加入显示设置项和自定义设置项
     * +----------------------------------------------------------
     * $cloud_id 模块ID
     * $del 删除操作
     * $display_option 是否写入显示项
     * +----------------------------------------------------------
     */
    function change_system($cloud_id, $del = false, $display_option = true) {
        // 序列化恢复
        $display = unserialize($GLOBALS['_CFG']['display']);
        $defined = unserialize($GLOBALS['_CFG']['defined']);
        $mobile_display = unserialize($GLOBALS['_CFG']['mobile_display']);
        
        // 删减操作
        if ($del) {
            unset($display[$cloud_id]);
            unset($display['home_' . $cloud_id]);
            unset($defined[$cloud_id]);
            unset($mobile_display[$cloud_id]);
            unset($mobile_display['home_' . $cloud_id]);
        } else {
            $display[$cloud_id] = 10;
            $display['home_' . $cloud_id] = 5;
            $defined[$cloud_id] = '';
            $mobile_display[$cloud_id] = 10;
            $mobile_display['home_' . $cloud_id] = 5;
        }
        
        // 重新写入系统设置项
        $GLOBALS['dou']->query("UPDATE " . $GLOBALS['dou']->table('config') . " SET value = '" . serialize($defined) . "' WHERE name = 'defined'");
        if ($display_option) {
            $GLOBALS['dou']->query("UPDATE " . $GLOBALS['dou']->table('config') . " SET value = '" . serialize($display) . "' WHERE name = 'display'");
            $GLOBALS['dou']->query("UPDATE " . $GLOBALS['dou']->table('config') . " SET value = '" . serialize($mobile_display) . "' WHERE name = 'mobile_display'");
        }
    }
    
    /**
     * +----------------------------------------------------------
     * 清理模块至未安装的状态
     * +----------------------------------------------------------
     * $cloud_id 模块ID
     * +----------------------------------------------------------
     */
    function clear_module($cloud_id) {
        // STEP1 删除模块数据表
        $this->del_module_table($cloud_id);
        $this->change_system($cloud_id, true);
        
        // STEP2 删除模块文件
        $this->dir_action($this->cache_dir . $cloud_id, $this->root_dir, true); // true代表删除模式

        // STEP3 修改系统文件-删除操作
        $this->change_systemfile($cloud_id, '', true);
    }
    
    /**
     * +----------------------------------------------------------
     * 删除模块数据库
     * +----------------------------------------------------------
     * $cloud_id 模块ID
     * +----------------------------------------------------------
     */
    function del_module_table($cloud_id) {
        global $prefix;
        
        // 读取数据库文件
        $sql_file = $this->cache_dir . "$cloud_id/data/backup/$cloud_id.sql";
        if (file_exists($sql_file)) {
            $content = file($sql_file);
            foreach ((array)$content as $line) {
                if (strpos($line, 'DROP TABLE IF EXISTS') !== false) {
                    $line = preg_replace('/dou_/Ums', "$prefix", trim($line)); // 数据表删除语句
                    if (!$GLOBALS['dou']->query($line)) return false;
                }
            }
            
            return true;
        }
    }
    
    /**
     * +----------------------------------------------------------
     * 将解压得到的扩展目录中的模板文件夹名改成当前启用的模板文件夹名
     * +----------------------------------------------------------
     * $item_dir 扩展目录
     * +----------------------------------------------------------
     */
    function modify_theme_dir($item_dir) {
        if (file_exists($item_dir . '/theme/default'))
            @rename($item_dir . '/theme/default', $item_dir . '/theme/' . $GLOBALS['_CFG']['site_theme']);
        if (file_exists($item_dir . '/m/theme/default'))
            @rename($item_dir . '/m/theme/default', $item_dir . '/m/theme/' . $GLOBALS['_CFG']['mobile_theme']);
        if (M_PATH != 'm')
            @rename($item_dir . '/m', $item_dir . '/' . M_PATH);
        if (ADMIN_PATH != 'admin')
            @rename($item_dir . '/admin', $item_dir . '/' . ADMIN_PATH);
    }
    
    /**
     * +----------------------------------------------------------
     * 目录下文件删除或复制
     * +----------------------------------------------------------
     * $source_dir 目录来源
     * $action_dir 目标目录
     * $del 删除模式
     * +----------------------------------------------------------
     */
    function dir_action($source_dir, $action_dir, $del = false) {
        if (is_dir($source_dir)) {
            $dir = opendir($source_dir);
            if (!is_dir($action_dir)) mkdir($action_dir);
            while (($file = readdir($dir)) !== false) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($source_dir . '/' . $file)) {
                        $this->dir_action($source_dir . '/' . $file, $action_dir . '/' . $file, $del);
                    } else {
                        if ($del) {
                            unlink($action_dir . '/' . $file);
                        } else {
                            copy($source_dir . '/' . $file, $action_dir . '/' . $file);
                        }
                    }
                }
            }
            closedir($dir);
        }
    }
    
    /**
     * +----------------------------------------------------------
     * 解压缩文件
     * +----------------------------------------------------------
     * $zipfile 压缩包
     * $savepath 解压后的路径
     * +----------------------------------------------------------
     */
    function file_unzip($zipfile, $savepath) {
        include_once (ROOT_PATH . ADMIN_PATH . '/include/pclzip.class.php');
        $archive = new PclZip($zipfile);
        if ($archive->extract(PCLZIP_OPT_PATH, $savepath)) {
            return true;
        }
    }
    
    /**
     * +----------------------------------------------------------
     * 下载文件
     * +----------------------------------------------------------
     * $file_url 文件地址
     * $save_path 保存路径
     * +----------------------------------------------------------
     */
    function file_download($file_url, $save_path) {
        $basename = basename($file_url);
        $file_name = strpos($basename, '.html') ? str_replace('.html', '.zip' ,$basename) : $basename;
        $save_file = $save_path . $file_name;
        $file_url = str_replace(' ', '%20', $file_url);
        
        $cloud_account = unserialize($GLOBALS['_CFG']['cloud_account']);
    
        if(function_exists('curl_init')) {
            $ch = curl_init();
            $data = array('user' => $cloud_account['user'], 'password' => $cloud_account['password'], 'url' => ROOT_URL);  
            curl_setopt($ch, CURLOPT_URL, $file_url);  
            curl_setopt($ch, CURLOPT_POST, 1);  
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $temp = curl_exec($ch);
            if(@file_put_contents($save_file, $temp) && !curl_error($ch) && !preg_match("/404 Not Found/", $temp)) {
                return $save_file;
            } else {
                return false;
            }
            curl_close($ch);
        }
    }

    /**
     * +----------------------------------------------------------
     * 输出缓冲区内容
     * +----------------------------------------------------------
     * $text 内容
     * +----------------------------------------------------------
     */
    function dou_flush($text) {
        if (is_array($text)) {
            foreach ($text as $value) {
                $flush .= '<p>' . $value . '</p>';
            }
        } else {
            $flush = '<p>' . $text . '</p>';
        }
        echo $flush;
        sleep(1);
        ob_flush();
        flush();
    }
    
    /**
     * +----------------------------------------------------------
     * 生成安装成功后提示连接
     * +----------------------------------------------------------
     * $type 类型
     * +----------------------------------------------------------
     */
    function msg_success($type, $cloud_id) {
        switch ($type) {
            case 'plugin':
              $msg = '<a href="plugin.php?rec=enable&unique_id=' . $cloud_id . '" class="btnGray">' . $GLOBALS['_LANG']['cloud_plugin_enable'] . '</a> <a href="plugin.php" class="btnGray">' . $GLOBALS['_LANG']['cloud_plugin_home'] . '</a>';
              break;  
            case 'theme':
              $msg = '<a href="theme.php?rec=enable&unique_id=' . $cloud_id . '" class="btnGray">' . $GLOBALS['_LANG']['cloud_theme_enable'] . '</a> <a href="theme.php" class="btnGray">' . $GLOBALS['_LANG']['cloud_theme_home'] . '</a>';
              break;
            case 'mobile':
              $msg = '<a href="mobile.php?rec=theme&act=enable&unique_id=' . $cloud_id . '" class="btnGray">' . $GLOBALS['_LANG']['cloud_theme_enable'] . '</a> <a href="mobile.php?rec=theme" class="btnGray">' . $GLOBALS['_LANG']['cloud_mobile_theme_home'] . '</a>';
              break;
            case 'module':
              $msg = '<a href="module.php" class="btnGray">' . $GLOBALS['_LANG']['cloud_module_home'] . '</a>';
              break;
            default:
              $msg = '<a href="index.php" class="btnGray">' . $GLOBALS['_LANG']['cloud_admin_home'] . '</a>';
        }
        return $msg;
    }

}
?>