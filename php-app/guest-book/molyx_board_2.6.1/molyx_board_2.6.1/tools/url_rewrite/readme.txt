�˹��ܿ��Խ����ֳ���ҳ��(�� forumdisplay.php��showthread.php ��)���� URL ��̬��ת�����γ����� http://localhost/forum-1.html ��ʽ�ĳ������ӣ��Ӷ�ʹ��̳���ݸ����ױ����������ھ���߱���¼�Ļ��ʡ�
ʹ��URL��̬������ǰ�뱣֤��̳��cookie������ȷ��������ܻᷢ���޷�������½�����⡣

����:��̬��url rewrite 1.0 
���ߣ�guyefeng 
�汾��MolyX2.6.1
ƽ̨��apache iis
����֧�֣�http://www.molyx.com 

==================================================
�����װ˵���� 

apache�û�
1.Apache Web Server�����������û���

����ȷ�����ķ�������apache�����web������
ȷ����ʹ�õ� Apache �汾�����Ƿ������ mod_rewrite ģ�顣

Apache 1.x ���û����� conf/httpd.conf ���Ƿ�����������δ��룺
LoadModule rewrite_module libexec/mod_rewrite.so
AddModule mod_rewrite.c

Apache 2.x ���û����� conf/httpd.conf ���Ƿ��������һ�δ��룺
LoadModule rewrite_module modules/mod_rewrite.so

������ڣ���ô�������ļ���ͨ������ conf/httpd.conf���м������´��롣��ʱ�����ע�⣬�����վʹ��ͨ���������������壬����ؼӵ������������ã��� <VirtualHost> ��ȥ����������������������ⲿ�������޷�ʹ�á��ĺú�Ȼ�� Apache ������

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

���û�а�װ mod_rewrite�����������±��� Apache������ԭ�� configure �������м��� --enable-rewrite=shared��Ȼ������ Apache �����ļ��м����������뼴�ɡ� 

2.Apache Web Server�����������û���

�ڿ�ʼ��������֮ǰ����������ѯ���Ŀռ�����̣��ռ��Ƿ�֧�� Rewrite �Լ��Ƿ�֧�ֶ�վ��Ŀ¼�� .htaccess ���ļ����������򼴱㰴������ķ������ú��ˣ�Ҳ�޷�ʹ�á� 

�����̳����Ŀ¼���Ƿ���� .htaccess �ļ�����������ڣ����ֹ��������ļ����༭���޸ı�Ŀ¼�µ� .htaccess �ļ�������������

# �� RewriteEngine ģʽ��
RewriteEngine On

# �޸���������е� /molyx_board Ϊ�����̳Ŀ¼��ַ�����������ڸ�Ŀ¼�У��뽫 /molyx_board �޸�Ϊ /
RewriteBase /molyx_board 

# Rewrite ϵͳ���������޸�
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

�������������ʾ���޸���̳���ڵ�·����Ȼ�󱣴档�� .htaccess �ļ��ϴ�����̳���ڵ�Ŀ¼�С� 

3.���IIS�û�

����,����ISAPI_Rewrite��ISAPI_Rewrite�־���(Lite)����ȫ(Full)�档����治֧�ֶ�ÿ��������
��վ�������д��ֻ�ܽ���ȫ�ִ������������з����������ѣ������Ҳ�͹��ˡ�

MolyX���򾫼�����ص�ַ��http://www.molyx.com/RewriteIIS.zip ��ѹ�������ط������ϣ�·������

��Internet ��Ϣ�����Ҽ���webվ�����ԣ���ISAPIɸѡ��ѡ������ɸѡ��,�����Լ���磺rewrite����·��ָ������һ����ѹ������Rewrite.dll�ļ������ȷ��

4.������̳ϵͳ���ã�������Ҫ���� URL ��̬�� ���ܡ�