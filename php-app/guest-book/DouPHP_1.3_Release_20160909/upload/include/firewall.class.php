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
class Firewall {
    /**
     * +----------------------------------------------------------
     * 豆壳防火墙
     * +----------------------------------------------------------
     */
    function dou_firewall() {
        // 交互数据转义操作
        $this->dou_magic_quotes();
    }
    
    /**
     * +----------------------------------------------------------
     * 安全处理用户输入信息
     * +----------------------------------------------------------
     */
    function dou_foreground($value) {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = htmlspecialchars($v, ENT_QUOTES);
            }
        } else {
            $value = htmlspecialchars($value, ENT_QUOTES);
        }
        
        return $value;
    }
    
    /**
     * +----------------------------------------------------------
     * 交互数据转义操作
     * 使用addslashes前必须先判断magic_quotes_gpc是否开启，如果开启的情况下还使用addslashes会导致双层转义，即写入的数据会出现/。
     * 服务器默认开启magic_quotes_gpc时会对post、get、cookie过来的数据增加转义字符
     * +----------------------------------------------------------
     */
    function dou_magic_quotes() {
        if (!@ get_magic_quotes_gpc()) {
            $_GET = $_GET ? $this->addslashes_deep($_GET) : '';
            $_POST = $_POST ? $this->addslashes_deep($_POST) : '';
            $_COOKIE = $this->addslashes_deep($_COOKIE);
            $_REQUEST = $this->addslashes_deep($_REQUEST);
        }
    }
    
    /**
     * +----------------------------------------------------------
     * 递归方式的对变量中的特殊字符进行转义
     * 使用addslashes转义会为引号加上反斜杠，但写入数据库时MYSQL会自动将反斜杠去掉
     * +----------------------------------------------------------
     */
    function addslashes_deep($value) {
        if (empty($value)) {
            return $value;
        }
        
        if (is_array($value)) {
            foreach ((array) $value as $k => $v) {
                unset($value[$k]);
                $k = addslashes($k);
                if (is_array($v)) {
                    $value[$k] = $this->addslashes_deep($v);
                } else {
                    $value[$k] = addslashes($v);
                }
            }
        } else {
            $value = addslashes($value);
        }
        
        return $value;
    }
    
    /**
     * +----------------------------------------------------------
     * 递归方式的对变量中的特殊字符去除转义
     * +----------------------------------------------------------
     */
    function stripslashes_deep($value) {
        if (empty($value)) {
            return $value;
        }
        
        if (is_array($value)) {
            foreach ((array) $value as $k => $v) {
                unset($value[$k]);
                $k = stripslashes($k);
                if (is_array($v)) {
                    $value[$k] = $this->stripslashes_deep($v);
                } else {
                    $value[$k] = stripslashes($v);
                }
            }
        } else {
            $value = stripslashes($value);
        }
        return $value;
    }
    
    /**
     * +----------------------------------------------------------
     * 设置令牌
     * +----------------------------------------------------------
     * $id 令牌ID
     * $is_admin 是否是在后台调用
     * +----------------------------------------------------------
     */
    function set_token($id, $is_admin = false) {
        $token = md5(uniqid(rand(), true));
        $n = rand(1, 24);
        $_SESSION[DOU_ID]['token'][$id] = substr($token, $n, 8);
        
        // 如果不是在后台调用则返回值
        if (!$is_admin) return $_SESSION[DOU_ID]['token'][$id];
    }
    
    /**
     * +----------------------------------------------------------
     * 设置后台令牌
     * +----------------------------------------------------------
     */
    function set_admin_token() {
        $this->set_token('admin', true);
    }
    
    /**
     * +----------------------------------------------------------
     * 验证令牌（如果是在后台操作，将采用每次登录更新令牌）
     * +----------------------------------------------------------
     * $token 一次性令牌
     * $id 令牌ID
     * $boolean 是否直接返回布尔值
     * +----------------------------------------------------------
     */
    function check_token($token, $id = '', $boolean = false) {
        $id = $id ? $id : 'admin'; // 默认为后台统一使用的令牌标记'admin'
        if (isset($_SESSION[DOU_ID]['token'][$id]) && $token == $_SESSION[DOU_ID]['token'][$id]) {
            // 令牌正确
            if (strpos(DOU_ID, 'admin_') === false) {
                // 如果不是在后台将清空令牌，后台只有每次登录或者令牌错误时会更
                unset($_SESSION[DOU_ID]['token'][$id]);
            }
            return true; // 返回令牌验证成功标记
        } else {
            // 令牌错误，返回错误提示
            if ($boolean) {
                // 情况一：不做任何提示直接返回一个错误标记的布尔值
                return false;
            } elseif (strpos(DOU_ID, 'admin_') !== false) {
                // 情况二：后台非法操作时提示
                unset($_SESSION[DOU_ID]); // 清空后台所有SESSION
                header('Content-type: text/html; charset=' . DOU_CHARSET);
                echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">" . $GLOBALS['_LANG']['illegal'];
                exit;
            } elseif (strpos(DOU_ID, 'dou_') !== false) {
                // 情况三：网站前台非法操作时提示
                unset($_SESSION[DOU_ID]['token'][$id]);
                $GLOBALS['dou']->dou_msg($GLOBALS['_LANG']['illegal'], ROOT_URL);
            } elseif (strpos(DOU_ID, 'mobile_') !== false) {
                // 情况四：手机版非法操作时提示
                unset($_SESSION[DOU_ID]['token'][$id]);
                $GLOBALS['dou']->dou_msg($GLOBALS['_LANG']['illegal'], M_URL);
            }
        }
    }
    
    /**
     * +----------------------------------------------------------
     * 获取后台令牌
     * +----------------------------------------------------------
     * $id 令牌ID
     * +----------------------------------------------------------
     */
    function get_token($id = 'admin') {
        return $_SESSION[DOU_ID]['token'][$id];
    }
    
    /**
     * +----------------------------------------------------------
     * 获取合法的分类ID或者栏目ID
     * +----------------------------------------------------------
     * $module 模块名称及数据表名
     * $id 分类ID或者栏目ID
     * $unique_id 伪静态别名
     * +----------------------------------------------------------
     */
    function get_legal_id($module, $id = '', $unique_id = '') {
        // 如果有设置则验证合法性，验证通过的情况包括为空和赋值合法，分类页允许ID为空，详细页（包括单页面）不允许ID为空
        if ((isset($id) && !$GLOBALS['check']->is_number($id)) || (isset($unique_id) && !$GLOBALS['check']->is_unique_id($unique_id)))
            return -1;
        
        if (isset($unique_id)) {
            if ($module == 'page') {
                $get_id = $GLOBALS['dou']->get_one("SELECT id FROM " . $GLOBALS['dou']->table($module) . " WHERE unique_id = '$unique_id'");
            } else {
                if (isset($id)) {
                    if ($id === '0') return 0; // 分类页允许ID为0
                    $system_unique_id = $GLOBALS['dou']->get_one("SELECT c.unique_id FROM " . $GLOBALS['dou']->table($module . '_category') .  " AS c LEFT JOIN " . $GLOBALS['dou']->table($module) . " AS i ON id = '$id' WHERE c.cat_id = i.cat_id");
                    $get_id = $system_unique_id == $unique_id ? $id : '';
                } else {
                    $get_id = $GLOBALS['dou']->get_one("SELECT cat_id FROM " . $GLOBALS['dou']->table($module) . " WHERE unique_id = '$unique_id'");
                }
            }
        } else {
            if (isset($id)) {
                if (strpos($module, 'category')) {
                    if ($id === '0') return 0; // 分类页允许ID为0
                    $get_id = $GLOBALS['dou']->get_one("SELECT cat_id FROM " . $GLOBALS['dou']->table($module) . " WHERE cat_id = '$id'");
                } else {
                    $get_id = $GLOBALS['dou']->get_one("SELECT id FROM " . $GLOBALS['dou']->table($module) . " WHERE id = '$id'");
                }
            } else {
                // $unique_id和$id都没设置只可能为分类主页或者是详细页没有输入id
                return strpos($module, 'category') ? 0 : -1;
            }
        }
        
        $legal_id = $get_id ? $get_id : -1;
        
        return $legal_id;
    }
}
?>