<?php
!function_exists('db_cv') && exit('Forbidden');
$db->update("DELETE FROM pw_msg WHERE mdate<$timestamp-2592000 AND type='public'");//删除群发消息(一个月)
$db->update("DELETE FROM pw_cknum WHERE $timestamp-time>'1800'");//删除无效验证码
$db_plist && $db->update("DELETE FROM pw_pidtmp");
?>