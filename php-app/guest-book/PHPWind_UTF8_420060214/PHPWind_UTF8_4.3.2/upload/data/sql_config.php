<?php
/**
* ���±�����������ķ�����˵�����޸�
*/
$dbhost = 'localhost';			// ���ݿ������
$dbuser = '';			// ���ݿ��û���
$dbpw = '';				// ���ݿ�����
$dbname = '';			// ���ݿ���
$database = 'mysql';			// ���ݿ�����
$PW = 'pw_';					// �����ַ�
$pconnect = 0;			// �Ƿ�־�����

/*
MYSQL��������
���������̳��������������Ҫ���ô������޸�
�벻Ҫ������Ĵ�����򽫿��ܵ�����̳������������
*/
$charset='gbk';

/**
* ��̳��ʼ��,ӵ����̳����Ȩ��
*/
$manager='';			// ����Ա�û���
$manager_pwd='';	// ����Ա����
$adminemail = '';	// ��̳ϵͳ Email

/**
* ����վ������
*/
$db_hostweb=1;		// �Ƿ�Ϊ��վ��

/*
* ����url��ַ����http:// ��ͷ�ľ��Ե�ַ  Ϊ��ʹ��Ĭ��
*/
$attach_url='';



/**
* �������
*/
$db_hackdb=array(

		'bank'=>array('����','bank','bank.php','bankset.php','1','hack/bank.php,hack/bankset.php,template/wind/bank.htm,template/admin/bankset.htm'),
		'colony'=>array('PWȺ','colony','colony.php','colonyset.php','1','colony.php,colonyset.php,template/admin/colonyset.htm,template/wind/colony.htm,data/bbscache/cn_cache.php'),
		
		);
?>