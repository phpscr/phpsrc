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
class Common extends DbMysql {
    /**
     * +----------------------------------------------------------
     * 获取网站信息
     * +----------------------------------------------------------
     */
    function get_config() {
        $query = $this->select_all($this->table('config'));
        while ($row = $this->fetch_array($query)) {
            $config[$row['name']] = $row['value'];
        }
        if ($config['qq'] && !defined('IS_ADMIN')) {
            $config['qq'] = $this->dou_qq($config['qq']);
        }
        $config['root_url'] = ROOT_URL;
        $config['m_url'] = M_URL;
        
        return $config;
    }
    
    /**
     * +----------------------------------------------------------
     * 重写 URL 地址
     * +----------------------------------------------------------
     * $module 模块
     * $value 根据是数字或字符来判断传递的是ID还是参数
     * +----------------------------------------------------------
     */
    function rewrite_url($module, $value = '') {
        // 根据是数字或字符来判断$value传递的是ID还是参数
        if (is_numeric($value)) {
            $id = $value;
        } else {
            $rec = $value;
        }
        
        if ($GLOBALS['_CFG']['rewrite']) {
            $filename = $module != 'page' ? '/' . $id : '';
            $item = (!strpos($module, 'category') && $id) ? $filename . '.html' : '';
            $url = $this->get_unique($module, $id) . $item . ($rec ? '/' . $rec : '');
        } else {
            $req = $rec ? '?rec=' . $rec : ($id ? '?id=' . $id : '');
            $url = $module . '.php' . $req;
        }
        
        if ($module == 'mobile') {
            // PC版中生成手机版URL
            return ROOT_URL . M_PATH;
        } else {
            // 移动版和PC版的根网址不同
            return (defined('IS_MOBILE') ? M_URL : ROOT_URL) . $url;
        }
    }
    
    /**
     * +----------------------------------------------------------
     * 系统模块
     * +----------------------------------------------------------
     */
    function dou_module() {
        // 读取系统文件
        $setting = $this->read_system();
        $module['column'] = array_filter($setting['column_module']);
        $module['single'] = array_filter($setting['single_module']);
        $module['all'] = array_merge($module['column'], $module['single']); 
        
        // 读取模块语言文件
        $lang_path = ROOT_PATH . 'languages/' . (defined('IS_ADMIN') ? 'zh_cn/admin/' : $GLOBALS['_CFG']['language'] . '/');
        $lang_list = glob($lang_path . '*.lang.php');
        if (is_array($lang_list)) {
            foreach ($lang_list as $lang) {
                $module['lang'][] = $lang;
            }
        }

        // 读取模块初始化文件
        $init_list = glob(ROOT_PATH . 'include/' . '*.init.php');
        if (is_array($init_list)) {
            foreach ($init_list as $init) {
                $module['init'][] = $init;
            }
        }
        
        // 模块开启状态
        foreach ((array) $module['all'] as $module_id) {
            $_OPEN[$module_id] = true;
        }
        $module['open'] = $_OPEN;
        
        return $module;
    }
     
    /**
     * +----------------------------------------------------------
     * 将系统文件转换为数组
     * +----------------------------------------------------------
     */
    function read_system() {
        $content = file(ROOT_PATH . 'data/system.dou');
        foreach ((array) $content as $line) {
            $line = trim($line);
            if (strpos($line, '//') !== 0) {
                $arr = explode(':', $line);
                $setting[$arr[0]] = explode(',', $arr[1]);
            }
        }
        
        return $setting;
    }
    
    /**
     * +----------------------------------------------------------
     * 所有模块URL和当前模块URL生成
     * +----------------------------------------------------------
     */
    function dou_url() {
        // 所有模块URL
        $module = $this->dou_module();
        foreach ((array) $module['column'] as $module_id) {
            $url[$module_id] = $this->rewrite_url($module_id . '_category');
        }
        foreach ((array) $module['single'] as $module_id) {
            $url[$module_id] = $this->rewrite_url($module_id);
        }

        // 购物车URL
        $url['cart'] = $this->rewrite_url('order', 'cart');

        // 会员模块常用URL
        foreach (explode('|', 'login|register|logout|order|order_list') as $value)
            $url[$value] = $this->rewrite_url('user', $value);

        // 当前模块子栏目URL
        if ($GLOBALS['subbox']['sub']) { // 判断模块页面的column值
            foreach (explode('|', $GLOBALS['subbox']['sub']) as $value) {
                $url[$value] = $this->rewrite_url($GLOBALS['subbox']['module'], $value);
            }
        }
        
        return $url;
    }
    
    /**
     * +----------------------------------------------------------
     * 获取别名
     * +----------------------------------------------------------
     * $module 模块
     * $id 项目ID
     * +----------------------------------------------------------
     */
    function get_unique($module, $id) {
        $filed = $module == 'page' ? id : cat_id;
        $table_module = $module;
        
        // 非单页面和分类模型下获取分类ID
        if (!strpos($module, 'category') && $module != 'page') {
            $id = $this->get_one("SELECT cat_id FROM " . $this->table($module) . " WHERE id = " . $id);
            $table_module = $module . '_category';
        }
        
        $unique_id = $this->get_one("SELECT unique_id FROM " . $this->table($table_module) . " WHERE " . $filed . " = " . $id);
        
        // 把分类页和列表的别名统一
        $module = preg_replace("/\_category/", '', $module);
        
        // 伪静态时使用的完整别名
        if ($module == 'page') {
            $unique = $unique_id;
        } elseif ($module == 'article') {
            $unique = $unique_id ? '/' . $unique_id : $unique_id;
            $unique = 'news' . $unique;
        } else {
            $unique = $unique_id ? '/' . $unique_id : $unique_id;
            $unique = $module . $unique;
        }
        
        return $unique;
    }
    
    /**
     * +----------------------------------------------------------
     * 格式化商品价格
     * +----------------------------------------------------------
     * $price 需要格式化的价格
     * +----------------------------------------------------------
     */
    function price_format($price = '') {
        $price = number_format($price, $GLOBALS['_CFG']['price_decimal']);
        $price_format = preg_replace('/d%/Ums', $price, $GLOBALS['_LANG']['price_format']);
        
        return $price_format;
    }
    
    /**
     * +----------------------------------------------------------
     * 获取当前分类下所有子分类
     * +----------------------------------------------------------
     * $table 数据表名
     * $parent_id 父类ID
     * $child 子类ID零时存储器
     * +----------------------------------------------------------
     */
    function dou_child_id($table, $parent_id = '0', &$child_id = '') {
        $data = $this->fetch_array_all($this->table($table), 'sort ASC, cat_id ASC');
        foreach ((array) $data as $value) {
            if ($value['parent_id'] == $parent_id) {
                $child_id .= ',' . $value['cat_id'];
                $this->dou_child_id($table, $value['cat_id'], $child_id);
            }
        }

        return $child_id;
    }
    
    /**
     * +----------------------------------------------------------
     * 向客户端发送原始的 HTTP 报头
     * +----------------------------------------------------------
     * $url 跳转网址
     * +----------------------------------------------------------
     */
    function dou_header($url) {
        header("Location: " . $url);
        exit;
    }
    
    /**
     * +----------------------------------------------------------
     * 获取无层次商品分类，将所有分类存至同一级数组，用$mark作为标记区分
     * +----------------------------------------------------------
     * $table 数据表名
     * $parent_id 默认获取一级导航
     * $level 无限极分类层次
     * $current_id 当前页面栏目ID
     * $category_nolevel 储存分类信息的数组
     * $mark 无限极分类标记
     * +----------------------------------------------------------
     */
    function get_category_nolevel($table, $parent_id = 0, $level = 0, $current_id = '', &$category_nolevel = array(), $mark = '-') {
        $data = $this->fetch_array_all($this->table($table), 'sort ASC, cat_id ASC');
        foreach ((array) $data as $value) {
            if ($value['parent_id'] == $parent_id && $value['cat_id'] != $current_id) {
                $value['url'] = $this->rewrite_url($table, $value['cat_id']);
                $value['mark'] = str_repeat($mark, $level);
                $category_nolevel[] = $value;
                $this->get_category_nolevel($table, $value['cat_id'], $level + 1, $current_id, $category_nolevel);
            }
        }
        
        return $category_nolevel;
    }
    
    /**
     * +----------------------------------------------------------
     * 获取无层次单页面列表
     * +----------------------------------------------------------
     * $parent_id 调用该ID下的所有单页面，为空时则调用所有
     * $level 无限极分类层次
     * $current_id 当前页面栏目ID
     * $mark 无限极分类标记
     * +----------------------------------------------------------
     */
    function get_page_nolevel($parent_id = 0, $level = 0, $current_id = '', &$page_nolevel = array(), $mark = '-') {
        $data = $this->fetch_array_all($this->table('page'));
        foreach ((array) $data as $value) {
            if ($value['parent_id'] == $parent_id && $value['id'] != $current_id) {
                $value['url'] = $this->rewrite_url('page', $value['id']);
                $value['mark'] = str_repeat($mark, $level);
                $value['level'] = $level;
                $page_nolevel[] = $value;
                $this->get_page_nolevel($value['id'], $level + 1, $current_id, $page_nolevel);
            }
        }
        return $page_nolevel;
    }
    
    /**
     * +----------------------------------------------------------
     * 获取幻灯图片列表
     * +----------------------------------------------------------
     */
    function get_show_list($type = 'pc') {
        if ($type) {
            $where = " WHERE type = '$type'";
        }
        
        $sql = "SELECT * FROM " . $this->table('show') . $where . " ORDER BY sort ASC, id ASC";
        $query = $this->query($sql);
        while ($row = $this->fetch_array($query)) {
            $image = explode('.', basename($row['show_img']));
            $thumb = $GLOBALS['images_dir'] . $GLOBALS['thumb_dir'] . $image['0'] . "_thumb." . $image['1'];
            
            $show_list[] = array (
                    "id" => $row['id'],
                    "show_name" => $row['show_name'],
                    "show_link" => $row['show_link'],
                    "show_img" => ROOT_URL . $row['show_img'],
                    "thumb" => ROOT_URL . $thumb,
                    "sort" => $row['sort'] 
            );
        }
        
        return $show_list;
    }
    
    /**
     * +----------------------------------------------------------
     * 获取列表
     * +----------------------------------------------------------
     * $module 模块
     * $cat_id 分类ID
     * $num 调用数量
     * $sort 排序
     * +----------------------------------------------------------
     */
    function get_list($module, $cat_id = '', $num = '', $sort = '') {
        $where = $cat_id == 'ALL' ? '' : " WHERE cat_id IN (" . $cat_id . $this->dou_child_id($module . '_category', $cat_id) . ")";
        $sort = $sort ? $sort . ',' : '';
        $limit = $num ? ' LIMIT ' . $num : '';
        
        $sql = "SELECT * FROM " . $this->table($module) . $where . " ORDER BY " . $sort . "id DESC" . $limit;
        $query = $this->query($sql);
        while ($row = $this->fetch_array($query)) {
            $item['id'] = $row['id'];
            if ($row['title']) $item['title'] = $row['title'];
            if ($row['name']) $item['name'] = $row['name'];
            if (!empty($row['price'])) $item['price'] = $row['price'] > 0 ? $this->price_format($row['price']) : $GLOBALS['_LANG']['price_discuss'];
            if ($row['click']) $item['click'] = $row['click'];

            $item['add_time'] = date("Y-m-d", $row['add_time']);
            $item['add_time_short'] = date("m-d", $row['add_time']);
            $item['description'] = $row['description'] ? $row['description'] : $this->dou_substr($row['content'], 320);
            $item['image'] = $row['image'] ? ROOT_URL . $row['image'] : '';
            $image = explode(".", $row['image']);
            $item['thumb'] = ROOT_URL . $image[0] . "_thumb." . $image[1];
            $item['url'] = $this->rewrite_url($module, $row['id']);
            
            $list[] = $item;
        }
        
        return $list;
    }
    
    /**
     * +----------------------------------------------------------
     * 获取有层次的栏目分类，有几层分类就创建几维数组
     * +----------------------------------------------------------
     * $table 数据表名
     * $parent_id 默认获取一级导航
     * $current_id 当前页面栏目ID
     * +----------------------------------------------------------
     */
    function get_category($table, $parent_id = 0, $current_id = '') {
        $category = array ();
        $data = $this->fetch_array_all($this->table($table), 'sort ASC, cat_id ASC');
        foreach ((array) $data as $value) {
            // $parent_id将在嵌套函数中随之变化
            if ($value['parent_id'] == $parent_id) {
                $value['url'] = $this->rewrite_url($table, $value['cat_id']);
                $value['cur'] = $value['cat_id'] == $current_id ? true : false;
                
                foreach ($data as $child) {
                    // 筛选下级导航
                    if ($child['parent_id'] == $value['cat_id']) {
                        // 嵌套函数获取子分类
                        $value['child'] = $this->get_category($table, $value['cat_id'], $current_id);
                        break;
                    }
                }
                $category[] = $value;
            }
        }
        
        return $category;
    }
    
    /**
     * +----------------------------------------------------------
     * 获取指定分类单页面列表
     * +----------------------------------------------------------
     * $parent_id 调用该ID下的所有单页面，为空时则调用所有
     * $current_id 当前打开的单页面ID，高亮菜单使用
     * +----------------------------------------------------------
     */
    function get_page_list($parent_id = 0, $current_id = '') {
        $page_list = array ();
        $data = $this->fetch_array_all($this->table('page'), 'id ASC');
        foreach ((array) $data as $value) {
            // $parent_id将在嵌套函数中随之变化
            if ($value['parent_id'] == $parent_id) {
                $value['url'] = $this->rewrite_url('page', $value['id']);
                $value['cur'] = $value['id'] == $current_id ? true : false;
                
                foreach ($data as $child) {
                    // 筛选下级单页面
                    if ($child['parent_id'] == $value['id']) {
                        // 嵌套函数获取子分类
                        $value['child'] = $this->get_page_list($value['id'], $current_id);
                        break;
                    }
                }
                $page_list[] = $value;
            }
        }
        
        return $page_list;
    }
    
    /**
     * +----------------------------------------------------------
     * 分页
     * +----------------------------------------------------------
     * $table 数据表名
     * $page_size 每页显示数量
     * $page 当前页码
     * $page_url 地址栏中参数传递
     * $where SQL查询条件
     * $get 地址栏中参数传递
     * $close_rewrite 强制关闭伪静态
     * +----------------------------------------------------------
     */
    function pager($table, $page_size = 10, $page, $page_url = '', $where = '', $get = '', $close_rewrite = false) {
        $sql = "SELECT * FROM " . $this->table($table) . $where;
        $record_count = mysql_num_rows($this->query($sql));
        
        // 调整分页链接样式
        if (!defined('IS_ADMIN') && $GLOBALS['_CFG']['rewrite'] && !$close_rewrite) {
            $get_page = '/o';
            $get = preg_replace('/&/', '?', $get, 1); // 将起始参数标记改为'?'
            $get = '/' . $get; // 起始参数前加'/'
        } else {
            $get_page = strpos($page_url, '?') !== false ? '&page=' : '?page=';
        }
        
        $page_count = ceil($record_count / $page_size);
        $first = $page_url . $get_page . '1' . $get;
        $previous = $page_url . $get_page . ($page > 1 ? $page - 1 : 0) . $get;
        $next = $page_url . $get_page . ($page_count > $page ? $page + 1 : 0) . $get;
        $last = $page_url . $get_page . $page_count . $get;
        
        $pager = array (
                "record_count" => $record_count,
                "page_size" => $page_size,
                "page" => $page,
                "page_count" => $page_count,
                "previous" => $previous,
                "next" => $next,
                "first" => $first,
                "last" => $last 
        );
        
        $start = ($page - 1) * $page_size;
        $limit = " LIMIT $start, $page_size";
        
        $GLOBALS['smarty']->assign('pager', $pager);
        
        return $limit;
    }
    
    /**
     * +----------------------------------------------------------
     * 获取上一项下一项
     * +----------------------------------------------------------
     */
    function lift($module, $id, $cat_id) {
        $field = $this->field_exist($module, 'title') ? 'title' : 'name'; // 判断包含title字段还是name字段
        $screen = $cat_id ? " AND cat_id = $cat_id" : ''; // 判断是否有分类筛选
        
        // 上一项
        $lift['previous'] = $this->fetch_assoc($this->query("SELECT id, " . $field . " FROM " . $this->table($module) . " WHERE id > $id" . $screen . " ORDER BY id ASC"));
        if ($lift['previous']) $lift['previous']['url'] = $this->rewrite_url($module, $lift['previous']['id']);
        // 下一项
        $lift['next'] = $this->fetch_assoc($this->query("SELECT id, " . $field . " FROM " . $this->table($module) . " WHERE id < $id" . $screen . " ORDER BY id DESC"));
        if ($lift['next']) $lift['next']['url'] = $this->rewrite_url($module, $lift['next']['id']);
        
        return $lift;
    }
    
    /**
     * +----------------------------------------------------------
     * 获取真实IP地址
     * +----------------------------------------------------------
     */
    function get_ip() {
        static $ip;
        if (isset($_SERVER)) {
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $ip = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $ip = $_SERVER["REMOTE_ADDR"];
            }
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR")) {
                $ip = getenv("HTTP_X_FORWARDED_FOR");
            } else if (getenv("HTTP_CLIENT_IP")) {
                $ip = getenv("HTTP_CLIENT_IP");
            } else {
                $ip = getenv("REMOTE_ADDR");
            }
        }
        
        if (preg_match('/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/', $ip)) {
            return $ip;
        } else {
            return '127.0.0.1';
        }
    }

    /**
     * +----------------------------------------------------------
     * 获取第一条记录
     * +----------------------------------------------------------
     * $log 日志内容
     * $desc 是否倒序
     * +----------------------------------------------------------
     */
    function get_first_log($log, $desc = false) {
        $log_array = explode(',', $log);
        $log = $desc ? ($log_array[1] ? $log_array[1] : $log_array[0]) : $log_array[0];
        return $log;
    }

    /**
     * +----------------------------------------------------------
     * 获取插件配置信息
     * +----------------------------------------------------------
     * $unique_id 插件唯一ID
     * +----------------------------------------------------------
     */
    function get_plugin($unique_id) {
        $plugin = $this->fetch_array($this->select($this->table('plugin'), '*', '`unique_id` = \'' . $unique_id . '\''));
        if ($plugin['config'])
            $plugin['config'] = unserialize($plugin['config']);
        
        return $plugin;
    }
    
    /**
     * +----------------------------------------------------------
     * 判断目录状态
     * +----------------------------------------------------------
     */
    function dir_status($dir) {
        // 判断目录是否存在
        if (file_exists($dir)) {
            // 判断目录是否可写
            if ($fp = @fopen("$dir/test.txt", 'w')) {
                @fclose($fp);
                @unlink("$dir/test.txt");
                $status = 'write';
            } else {
                $status = 'exist';
            }
        } else {
            $status = 'no_exist';
        }
        
        return $status;
    }

    /**
     * +----------------------------------------------------------
     * 邮件发送
     * +----------------------------------------------------------
     * $mailto 收件人地址
     * $title 邮件标题
     * $body 邮件正文
     * +----------------------------------------------------------
     */
    function send_mail($mailto, $subject = '', $body = '') {
        if ($GLOBALS['_CFG']['mail_service'] && file_exists(ROOT_PATH . 'include/mail.class.php')) {
            include_once (ROOT_PATH . 'include/mail.class.php');
            include_once (ROOT_PATH . 'include/smtp.class.php');

            $mail = new PHPMailer;                                // 实例化
            
            //$mail->SMTPDebug = 3;                               // 启用SMTP调试功能
             
            $mail->CharSet ="UTF-8";                              // 设定邮件编码
            $mail->isSMTP();                                      // 设定使用SMTP服务
            $mail->Host = $GLOBALS['_CFG']['mail_host'];          // SMTP服务器
            $mail->SMTPAuth = true;                               // 启用SMTP验证功能
            $mail->Username = $GLOBALS['_CFG']['mail_username'];  // SMTP服务器用户名
            $mail->Password = $GLOBALS['_CFG']['mail_password'];  // SMTP服务器密码
            if ($GLOBALS['_CFG']['mail_ssl'])
                $mail->SMTPSecure = 'ssl';                        // 安全协议，可以注释掉
            $mail->Port = $GLOBALS['_CFG']['mail_port'];          // SMTP服务器的端口号
            
            $mail->From = $GLOBALS['_CFG']['mail_username'];      // 发件人地址
            $mail->FromName = $GLOBALS['_CFG']['site_name'];      // 发件人姓名
            $mail->addAddress($mailto, '');                       // 收件地址，可选指定收件人姓名
            
            $mail->isHTML(true);                                  // 是否HTML格式邮件
            
            $mail->Subject = $subject;                            // 邮件标题
            $mail->Body    = $body;                               // 邮件内容
            
            // 邮件正文不支持HTML的备用显示
            $mail->AltBody = $GLOBALS['_LANG']['mail_altbody']; 
            
            if($mail->send()) {
                return true;
            }
        } else {
            $subject = "=?UTF-8?B?".base64_encode($subject)."?=";          // 解决邮件主题乱码问题，UTF8编码格式
            $header  = "From: ".$GLOBALS['_CFG']['site_name']." <".$GLOBALS['_CFG']['email'].">\n";
            $header .= "Return-Path: <".$GLOBALS['_CFG']['email'].">\n";   // 防止被当做垃圾邮件
            $header .= "MIME-Version: 1.0\n";
            $header .= "Content-type: text/html; charset=utf-8\n";         // 邮件内容为utf-8编码
            $header .= "Content-Transfer-Encoding: 8bit\r\n";              // 注意header的结尾，只有这个后面有\r
            ini_set('sendmail_from', $GLOBALS['_CFG']['email']);           // 解决mail的一个bug
            $body = wordwrap($body, 70);                                   // 每行最多70个字符,这个是mail方法的限制
            if (mail($mailto, $subject, $body, $header))
                return ture;
        }
    }
    
    /**
     * +----------------------------------------------------------
     * 生成在线客服QQ数组
     * +----------------------------------------------------------
     */
    function dou_qq($im) {
        $im_explode = explode(',', $im);
        foreach ((array) $im_explode as $value) {
            if (strpos($value, '/') !== false) {
                $arr = explode('/', $value);
                $list['number'] = $arr['0'];
                $list['nickname'] = $arr['1'];
                $im_list[] = $list;
            } else {
                $im_list[] = $value;
            }
        }
        
        return $im_list;
    }
    
    /**
     * +----------------------------------------------------------
     * 清除html,换行，空格类并且可以截取内容长度
     * +----------------------------------------------------------
     * $str 要处理的内容
     * $length 要保留的长度
     * $charset 要处理的内容的编码，一般情况无需设置
     * +----------------------------------------------------------
     */
    function dou_substr($str, $length, $clear_space = true, $charset = DOU_CHARSET) {
        $str = trim($str); // 清除字符串两边的空格
        $str = strip_tags($str, ""); // 利用php自带的函数清除html格式
        $str = preg_replace("/\r\n/", "", $str);
        $str = preg_replace("/\r/", "", $str);
        $str = preg_replace("/\n/", "", $str);
        // 判断是否清除空格
        if ($clear_space) {
            $str = preg_replace("/\t/", "", $str);
            $str = preg_replace("/ /", "", $str);
            $str = preg_replace("/&nbsp;/", "", $str); // 匹配html中的空格
        }
        $str = trim($str); // 清除字符串两边的空格
        
        if (function_exists("mb_substr")) {
            $substr = mb_substr($str, 0, $length, $charset);
        } else {
            $c['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $c['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            preg_match_all($c[$charset], $str, $match);
            $substr = join("", array_slice($match[0], 0, $length));
        }
        
        return $substr;
    }

}
?>