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

/* error_reporting */
error_reporting(E_ALL ^ E_NOTICE);
class upgrade {
    var $sqlcharset;
    function Upgrade($sqlcharset) {
        $this->sqlcharset = $sqlcharset;
    }
    
    /**
     * 模板函数
     */
    function tpl($file) {
        $file = ROOT_PATH . 'template/' . $file . '.htm';
        return $file;
    }
    
    /**
     * 判断 文件/目录 是否可写
     */
    function check_writeable($file) {
        if (file_exists($file)) {
            if (is_dir($file)) {
                $dir = $file;
                if ($fp = @fopen("$dir/test.txt", 'w')) {
                    @fclose($fp);
                    @unlink("$dir/test.txt");
                    $writeable = 1;
                } else {
                    $writeable = 0;
                }
            } else {
                if ($fp = @fopen($file, 'a+')) {
                    @fclose($fp);
                    $writeable = 1;
                } else {
                    $writeable = 0;
                }
            }
        } else {
            $writeable = 2;
        }
        
        return $writeable;
    }
    
    /**
     * +----------------------------------------------------------
     * 清除缓存及已编译模板
     * +----------------------------------------------------------
     */
    function dou_clear_cache($dir) {
        $dir = realpath($dir);
        if (!$dir || !@ is_dir($dir))
            return 0;
        $handle = @ opendir($dir);
        if ($dir[strlen($dir) - 1] != DIRECTORY_SEPARATOR)
            $dir .= DIRECTORY_SEPARATOR;
        while ($file = @ readdir($handle)) {
            if ($file != '.' && $file != '..') {
                if (@ is_dir($dir . $file) && !is_link($dir . $file))
                    $this->dou_clear_cache($dir . $file);
                else
                    @ unlink($dir . $file);
            }
        }
        closedir($handle);
    }

    /**
     * 数据库导入
     */
    function sql_execute($sql) {
        global $link;
        
        $sqls = $this->sql_split($sql);
        if (is_array($sqls)) {
            foreach ($sqls as $sql) {
                if (trim($sql) != '') {
                    mysql_query($sql, $link);
                }
            }
        } else {
            mysql_query($sqls, $link);
        }
        return true;
    }
    
    /**
     * 数据分离
     */
    function sql_split($sql) {
        global $prefix;
        if ($this->version() > '4.1' && $this->sqlcharset) {
            $sql = preg_replace("/TYPE=(InnoDB|MyISAM)( DEFAULT CHARSET=[^; ]+)?/", "TYPE=\\1 DEFAULT CHARSET=" . $this->sqlcharset, $sql);
        }
        
        $sql = str_replace("\r", "\n", $sql);
        $ret = array ();
        $num = 0;
        $queriesarray = explode(";\n", trim($sql));
        unset($sql);
        foreach ($queriesarray as $query) {
            $ret[$num] = '';
            $queries = explode("\n", trim($query));
            $queries = array_filter($queries);
            foreach ($queries as $query) {
                $str1 = substr($query, 0, 1);
                if ($str1 != '#' && $str1 != '-')
                    $ret[$num] .= $query;
            }
            $num++;
        }
        return ($ret);
    }
    
    /**
     * 返回 MySQL 服务器的信息
     */
    function version() {
        global $link;
        if (empty($this->version)) {
            $this->version = mysql_get_server_info($link);
        }
        return $this->version;
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
            if (!is_dir($action_dir)) @mkdir($action_dir);
            while (($file = @readdir($dir)) !== false) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($source_dir . '/' . $file)) {
                        $this->dir_action($source_dir . '/' . $file, $action_dir . '/' . $file, $del);
                    } else {
                        if ($del) {
                            @unlink($action_dir . '/' . $file);
                        } else {
                            @copy($source_dir . '/' . $file, $action_dir . '/' . $file);
                        }
                    }
                }
            }
            closedir($dir);
        }
    }

    /**
     * +----------------------------------------------------------
     * 删除目录及目录下所有子目录和文件
     * +----------------------------------------------------------
     * $dir 要删除的目录
     * +----------------------------------------------------------
     */
    function del_dir($dir) {
        if ($handle = @opendir($dir)) {
           // 删除目录下子目录和文件
           while (false !== ($item = @readdir($handle))) {  
               if ($item != '.' && $item != '..') {  
                   if (is_dir( "$dir/$item")) {  
                       $this->del_dir("$dir/$item");  
                   } else {  
                       @unlink("$dir/$item");  
                   }  
               }  
           }  
           closedir($handle);
           
           // 删除目录本身
           @rmdir($dir);  
        } 
    }
}
?>