<?php
/**
* ���±�����������ķ�����˵�����޸�
*/
$dbhost = 'localhost';			// ���ݿ������
$dbuser = 'root';			// ���ݿ��û���
$dbpw = '';				// ���ݿ�����
$dbname = 'phpwind';			// ���ݿ���
$database = 'mysql';			// ���ݿ�����
$PW = 'pw_';					// �����ַ�
$pconnect = '0';		// �Ƿ�־�����

/*
MYSQL��������
���������̳��������������Ҫ���ô������޸�
�벻Ҫ������Ĵ�����򽫿��ܵ�����̳������������
*/
$charset='';

/**
* ��̳��ʼ��,ӵ����̳����Ȩ��
*/
$manager='admin';			//����Ա�û���
$manager_pwd='';	//����Ա����

/**
* ����վ������
*/
$db_hostweb='1';		//�Ƿ�Ϊ��վ��

/*
* ����url��ַ����http:// ��ͷ�ľ��Ե�ַ  Ϊ��ʹ��Ĭ��
*/
$attach_url=array();

/*
* ͼƬ����Ŀ¼����
*/
$picpath='image';
$attachname='attachment';


/**
* �������
*/
$db_hackdb=array(

		'bank'=>array('����','bank','1'),
		'colony'=>array('����Ȧ','colony','1'),
		'advert'=>array('������','advert','0'),
		'new'=>array('��ҳ���ù���','new','0'),
		'medal'=>array('ѫ������','medal','1'),
		'toolcenter'=>array('��������','toolcenter','1'),
		'blog'=>array('����','blog','1'),
		'invite'=>array('����ע��','invite','1'),
		'passport'=>array('ͨ��֤','passport','0'),
		'team'=>array('�Ŷӹ���������','team','1'),
		'nav'=>array('�Զ��嵼��','nav','0'),
		
		);
?>