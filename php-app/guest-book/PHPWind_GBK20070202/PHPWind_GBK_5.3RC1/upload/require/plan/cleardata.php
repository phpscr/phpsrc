<?php
!function_exists('db_cv') && exit('Forbidden');
$db->update("DELETE FROM pw_msg WHERE mdate<$timestamp-2592000 AND type='public'");//ɾ��Ⱥ����Ϣ(һ����)
$db->update("DELETE FROM pw_cknum WHERE $timestamp-time>'1800'");//ɾ����Ч��֤��
$db_plist && $db->update("DELETE FROM pw_pidtmp");
?>