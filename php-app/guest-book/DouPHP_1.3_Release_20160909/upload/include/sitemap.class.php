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
class SiteMap {
    var $header = "<\x3Fxml version=\"1.0\" encoding=\"UTF-8\"\x3F>\n\t<urlset xmlns=\"http://www.google.com/schemas/sitemap/0.84\">";
    var $footer = "\t</urlset>";
    var $output;
    
    /**
     * +----------------------------------------------------------
     * 构造函数
     * +----------------------------------------------------------
     */
    function SiteMap($domain, $today = '') {
        $this->domain = $domain;
        $this->today = $today;
    }
    
    /**
     * +----------------------------------------------------------
     * 构造站点地图
     * +----------------------------------------------------------
     */
    function build_sitemap() {
        $output = $this->header . "\n\n";
        $output .= $this->read_item();
        $output .= $this->footer;
        return $output;
    }
    
    /**
     * +----------------------------------------------------------
     * 遍历目录将格式转换为sitemap格式
     * +----------------------------------------------------------
     */
    function read_item() {
        $item = $this->array_item();
        
        $arr = "\t\t<url>\n";
        $arr .= "\t\t\t<loc>$this->domain</loc>\n";
        $arr .= "\t\t\t<lastmod>$this->today</lastmod>\n";
        $arr .= "\t\t\t<changefreq>hourly</changefreq>\n";
        $arr .= "\t\t\t<priority>0.9</priority>\n";
        $arr .= "\t\t</url>\n\n";
        
        foreach ($item as $row) {
            $arr .= "\t\t<url>\n";
            $arr .= "\t\t\t<loc>$row[url]</loc>\n";
            $arr .= "\t\t\t<lastmod>$row[date]</lastmod>\n";
            $arr .= "\t\t\t<changefreq>$row[changefreq]</changefreq>\n";
            $arr .= "\t\t\t<priority>0.9</priority>\n";
            $arr .= "\t\t</url>\n\n";
        }
        
        return $arr;
    }
    
    /**
     * +----------------------------------------------------------
     * 获取整站目录数据
     * +----------------------------------------------------------
     */
    function array_item() {
        // 单页面列表
        foreach ($GLOBALS['dou']->get_page_nolevel() as $row) {
            $item_array[] = array (
                    "date" => $this->today,
                    "changefreq" => 'weekly',
                    "url" => $row['url'] 
            );
        }
        
        // 栏目模块
        foreach ($GLOBALS['_MODULE']['column'] as $module_id) {
            // 栏目
            $item_array[] = array (
                    "date" => $this->today,
                    "changefreq" => 'hourly',
                    "url" => $GLOBALS['dou']->rewrite_url($module_id . '_category') 
            );
            foreach ($GLOBALS['dou']->get_category_nolevel($module_id . '_category') as $row) {
                $item_array[] = array (
                        "date" => $this->today,
                        "changefreq" => 'hourly',
                        "url" => $row['url'] 
                );
            }
            
            // 内容列表
            foreach ($GLOBALS['dou']->get_list($module_id, 'ALL') as $row) {
                $item_array[] = array (
                        "date" => $row['add_time'],
                        "changefreq" => 'weekly',
                        "url" => $row['url'] 
                );
            }
        }

        // 简单模块
        foreach ($GLOBALS['_MODULE']['single'] as $module_id) {
            // 不显示的模块
           $no_show = 'plugin';
           if (!in_array($module_id, explode('|', $no_show))) {
               $item_array[] = array (
                        "date" => $this->today,
                        "changefreq" => 'weekly',
                        "url" => $GLOBALS['dou']->rewrite_url($module_id) 
                );
           }
        }
        
        return $item_array;
    }
}
?>