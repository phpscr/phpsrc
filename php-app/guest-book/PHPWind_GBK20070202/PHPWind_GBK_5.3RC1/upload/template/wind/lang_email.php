<?php

$lang = array (

'email_check_subject'			=>"激活您在 {$db_bbsname} 会员帐号的必要步骤!",
'email_check_content'			=>"{$rg_name},您好！\n\n{$db_bbsname}欢迎您的到来！\n首先您得激活您的用户名(点击下行网址激活,如果用户名是中文请点击下行网址激活)\n{$db_bbsurl}/register.php?vip=activating&r_uid={$winduid}&pwd={$timestamp}\n您的注册名为:{$rg_name}\n您的密码为:{$regpwd}\n请尽快删除此邮件，以免别人偷看到您的密码\n\n如果忘了密码，可以到社区写信请管理员重新设定\n请查看社区各版的发贴规则，以免帖子被删除\n社区地址：{$db_bbsurl}\n\n本社区采用 PHPWind 架设,欢迎访问: http://www.phpwind.com",
'email_additional'				=>"From:{$fromemail}\r\nReply-To:{$fromemail}\r\nX-Mailer: PHPWind邮件快递",
'email_welcome_subject'			=>"{$rg_name},您好,感谢您注册{$db_bbsname}",
'email_welcome_content'			=>"{$rg_name},您好！\n\n{$db_bbsname}欢迎您的到来！\n您的注册名为:{$rg_name}\n您的密码为:{$regpwd}\n请尽快删除此邮件，以免别人偷看到您的密码\n\n如果忘了密码，可以到社区写信请管理员重新设定\n请查看社区各版的发贴规则，以免帖子被删除\n社区地址：{$db_bbsurl}\n\n本社区采用 PHPWind 架设,欢迎访问: http://www.phpwind.com",
'email_sendpwd_subject'			=>"{$db_bbsname} 密码重发",
'email_sendpwd_content'			=>"请到下面的网址修改密码： \n {$db_bbsurl}/sendpwd.php?action=getback&pwuser={$pwuser}&submit={$submit}\n修改后请牢记您的密码\n欢迎来到 {$db_bbsname}我们的网址是:{$db_bbsurl}\n",
'email_reply_subject'			=>"{$receiver}您在{$db_bbsname}的帖子有回复",
'email_reply_content'			=>"Hi, {$receiver} ,\n    我是{$db_bbsname}邮件大使，\n    您在{$db_bbsname}发表的文章: {$old_title}\n    现在有人回复.快来关注一下吧\n    {$db_bbsurl}/read.php?fid={$fid}&tid={$tid}\n    下次再有人参与主题时,我将不来打扰了\n\n___________________________________\n欢迎访问 {$db_wwwname}\n本社区采用PHPWind 架设,欢迎访问: http://www.phpwind.com",

);
?>