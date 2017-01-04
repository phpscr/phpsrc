此功能可以将部分常用页面(如 forumdisplay.php、showthread.php 等)进行 URL 静态化转换，形成类似 http://localhost/forum-1.html 形式的超级链接，从而使论坛内容更容易被搜索引擎挖掘，提高被收录的机率。
使用URL静态化功能前请保证论坛的cookie设置正确，否则可能会发生无法正常登陆等问题。

名称:静态化url rewrite 1.0 
作者：guyefeng 
版本：MolyX2.6.1
平台：apache iis
技术支持：http://www.molyx.com 

==================================================
插件安装说明： 

apache用户
1.Apache Web Server（独立主机用户）

首先确认您的服务器是apache架设的web服务器
确定您使用的 Apache 版本，及是否加载了 mod_rewrite 模块。

Apache 1.x 的用户请检查 conf/httpd.conf 中是否存在如下两段代码：
LoadModule rewrite_module libexec/mod_rewrite.so
AddModule mod_rewrite.c

Apache 2.x 的用户请检查 conf/httpd.conf 中是否存在如下一段代码：
LoadModule rewrite_module modules/mod_rewrite.so

如果存在，那么在配置文件（通常就是 conf/httpd.conf）中加入如下代码。此时请务必注意，如果网站使用通过虚拟主机来定义，请务必加到虚拟主机配置，即 <VirtualHost> 中去，如果加在虚拟主机配置外部将可能无法使用。改好后然后将 Apache 重启。

<IfModule mod_rewrite.c>
RewriteEngine On

RewriteRule ^(.*)/forum-([0-9]+)-([0-9]+)-([0-9]+)\.html(.*)$ $1/forumdisplay.php?f=$2&st=$4&pp=$3&$5
RewriteRule ^(.*)/forum-([0-9]+)-([0-9]+)\.html(.*)$ $1/forumdisplay.php?f=$2&pp=$3&$4
RewriteRule ^(.*)/forum-([0-9]+)-q-([0-9]+)\.html(.*)$ $1/forumdisplay.php?f=$2&filter=quintessence&pp=$3&$4
RewriteRule ^(.*)/forum-([0-9]+)-q\.html(.*)$ $1/forumdisplay.php?f=$2&filter=quintessence&$3
RewriteRule ^(.*)/forum-([0-9]+)\.html(.*)$ $1/forumdisplay.php?f=$2&$3
RewriteRule ^(.*)/thread-([0-9]+)-([0-9]+)\.html(.*)$ $1/showthread.php?t=$2&pp=$3&$4
RewriteRule ^(.*)/thread-([0-9]+)\.html(.*)$ $1/showthread.php?t=$2&$3
RewriteRule ^(.*)/user-([0-9]+)\.html $1/profile.php?u=$2[L]
RewriteRule ^(.*)/archive/f-([0-9]+)-([0-9]+)\.html $1/index.php?f$2-$3.html[L]
RewriteRule ^(.*)/archive/t-([0-9]+)-([0-9]+)\.html $1/index.php?t$2-$3.html[L]
</IfModule>

如果没有安装 mod_rewrite，您可以重新编译 Apache，并在原有 configure 的内容中加入 --enable-rewrite=shared，然后再在 Apache 配置文件中加入上述代码即可。 

2.Apache Web Server（虚拟主机用户）

在开始以下设置之前，请首先咨询您的空间服务商，空间是否支持 Rewrite 以及是否支持对站点目录中 .htaccess 的文件解析，否则即便按照下面的方法设置好了，也无法使用。 

检查论坛所在目录中是否存在 .htaccess 文件，如果不存在，请手工建立此文件。编辑并修改本目录下的 .htaccess 文件，其内容如下

# 将 RewriteEngine 模式打开
RewriteEngine On

# 修改以下语句中的 /molyx_board 为你的论坛目录地址，如果程序放在根目录中，请将 /molyx_board 修改为 /
RewriteBase /molyx_board 

# Rewrite 系统规则请勿修改
RewriteRule forum-([0-9]+)-([0-9]+)-([0-9]+)\.html forumdisplay.php?f=$1&st=$3&pp=$2&$4
RewriteRule forum-([0-9]+)-([0-9]+)\.html forumdisplay.php?f=$1&pp=$2&$3
RewriteRule forum-([0-9]+)-q-([0-9]+)\.html forumdisplay.php?f=$1&filter=quintessence&pp=$2
RewriteRule forum-([0-9]+)-q\.html forumdisplay.php?f=$1&filter=quintessence
RewriteRule forum-([0-9]+)\.html forumdisplay.php?f=$1
RewriteRule thread-([0-9]+)-([0-9]+)\.html showthread.php?t=$1&pp=$2&$3
RewriteRule thread-([0-9]+)\.html showthread.php?t=$1&$2
RewriteRule user-([0-9]+)\.html profile.php?u=$1
RewriteRule archive/f-([0-9]+)-([0-9]+)\.html archive/index.php?f$1-$2.html
RewriteRule archive/t-([0-9]+)-([0-9]+)\.html archive/index.php?t$1-$2.html

请遵照上面的提示，修改论坛所在的路径，然后保存。将 .htaccess 文件上传到论坛所在的目录中。 

3.针对IIS用户

首先,下载ISAPI_Rewrite。ISAPI_Rewrite分精简(Lite)和完全(Full)版。精简版不支持对每个虚拟主
机站点进行重写，只能进行全局处理。不过对于有服务器的朋友，精简版也就够了。

MolyX规则精简版下载地址：http://www.molyx.com/RewriteIIS.zip 解压缩到本地服务器上，路径随意

打开Internet 信息服务，右键，web站点属性，点ISAPI筛选器选项卡。添加筛选器,名称自己填（如：rewrite），路径指定到上一步解压出来的Rewrite.dll文件，点击确定

4.进入论坛系统设置，根据需要开启 URL 静态化 功能。