<?php

$lang = array (

'email_check_subject'			=>"�������� {$db_bbsname} ��Ա�ʺŵı�Ҫ����!",
'email_check_content'			=>"{$rg_name},���ã�\n\n{$db_bbsname}��ӭ���ĵ�����\n�������ü��������û���(���������ַ����,����û�������������������ַ����)\n{$db_bbsurl}/register.php?vip=activating&r_uid={$winduid}&pwd={$timestamp}\n����ע����Ϊ:{$rg_name}\n��������Ϊ:{$regpwd}\n�뾡��ɾ�����ʼ����������͵������������\n\n����������룬���Ե�����д�������Ա�����趨\n��鿴��������ķ��������������ӱ�ɾ��\n������ַ��{$db_bbsurl}\n\n���������� PHPWind ����,��ӭ����: http://www.phpwind.com",
'email_additional'				=>"From:{$fromemail}\r\nReply-To:{$fromemail}\r\nX-Mailer: PHPWind�ʼ����",
'email_welcome_subject'			=>"{$rg_name},����,��л��ע��{$db_bbsname}",
'email_welcome_content'			=>"{$rg_name},���ã�\n\n{$db_bbsname}��ӭ���ĵ�����\n����ע����Ϊ:{$rg_name}\n��������Ϊ:{$regpwd}\n�뾡��ɾ�����ʼ����������͵������������\n\n����������룬���Ե�����д�������Ա�����趨\n��鿴��������ķ��������������ӱ�ɾ��\n������ַ��{$db_bbsurl}\n\n���������� PHPWind ����,��ӭ����: http://www.phpwind.com",
'email_sendpwd_subject'			=>"{$db_bbsname} �����ط�",
'email_sendpwd_content'			=>"�뵽�������ַ�޸����룺 \n {$db_bbsurl}/sendpwd.php?action=getback&pwuser={$pwuser}&submit={$submit}\n�޸ĺ����μ���������\n��ӭ���� {$db_bbsname}���ǵ���ַ��:{$db_bbsurl}\n",
'email_reply_subject'			=>"{$receiver}����{$db_bbsname}�������лظ�",
'email_reply_content'			=>"Hi, {$receiver} ,\n    ����{$db_bbsname}�ʼ���ʹ��\n    ����{$db_bbsname}���������: {$old_title}\n    �������˻ظ�.������עһ�°�\n    {$db_bbsurl}/read.php?fid={$fid}&tid={$tid}\n    �´������˲�������ʱ,�ҽ�����������\n\n___________________________________\n��ӭ���� {$db_wwwname}\n����������PHPWind ����,��ӭ����: http://www.phpwind.com",

);
?>