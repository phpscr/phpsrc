+-------------------------------------------------------------------
| DouPHP 使用说明
+-------------------------------------------------------------------

一、平台需求
1.Windows 平台：
IIS/Apache/Nginx + PHP 5.0及以上版本 + MySQL5.0及以上

2.Linux/Unix 平台
Apache + PHP 5.0及以上版本 + MySQL5.0及以上

建议使用平台：Linux + Apache2.2 + PHP5.2/PHP5.3 + MySQL5.0

3.PHP必须环境或启用的系统函数：
GD扩展库
MySQL扩展库
系统函数 —— phpinfo、dir
伪静态 —— mod_rewrite（apache环境）、ISAPI_Rewrite（IIS环境）

4.基本目录结构
/
..../install     安装程序目录，安装完后可删除[安装时必须有可写入权限]
..../admin       默认后台管理目录
..../cache       Smarty缓存目录[必须可写入]
..../data        Config文件或其它可写入数据存放目录[必须可写入]
..../images      产品图片或其它附件存放目录[必须可写入]
..../include     类库文件目录
..../languages   语言文件目录
..../m           手机版目录
..../theme       系统默认内核模板目录


二、程序安装使用
1.下载程序并解压
2.将upload里的文件上传至站点根目录（不包括upload目录本身）
3.运行http://www.domain.com/install(domain表示你的域名),按照安装说明进行程序安装
 
三、相关资源
DouPHP官网   http://www.douco.com
开发者社区   http://bbs.douco.cn