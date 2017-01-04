<?php
$logtype=array(
	'bk_save'   => '存款',
	'bk_draw'   => '取款',
	'bk_vire'   => '转帐',
	'bk_credit' => '转换',

	'topped'    => '置顶',
	'digest'    => '精华',
	'highlight'	=> '加亮',
	'push'		=> '提前',
	'locked'	=> '锁定',
	'delrp'		=> '删回复',
	'deltpc'	=> '删主题',
	'delete'	=> '删除',
	'move'		=> '移动',
	'copy'		=> '复制',
	'edit'		=> '编辑',
	'shield'    => '屏蔽',
	'unite'     => '合并',
	'remind'    => '提示',

	'credit'	=> '评分',
	'deluser'	=> '用户',

	'cy_donate'	=> '捐献',
	'cy_join'	=> '加群',
	'cy_vire'	=> '转帐',
);

$lang = array (
	'bk_save_descrip_1'		=>	"[b]{$log[username1]}[/b]使用活期存款功能，存入金额：{$log[field1]}{$db_moneyname}",
	'bk_save_descrip_2'		=>	"[b]{$log[username1]}[/b]使用定期存款功能，存入金额：{$log[field1]}{$db_moneyname}",
	'bk_draw_descrip_1'		=>	"[b]{$log[username1]}[/b]使用活期取款功能，取出金额：{$log[field1]}{$db_moneyname}",
	'bk_draw_descrip_2'		=>	"[b]{$log[username1]}[/b]使用定期取款功能，取出金额：{$log[field1]}{$db_moneyname}",
	'bk_vire_descrip'		=>	"[b]{$log[username1]}[/b]使用转帐功能，转帐给[b]{$log[username2]}[/b]金额：{$log[field1]}{$db_moneyname}",
	'bk_credit_descrip'		=>	"[b]{$log[username1]}[/b]使用积分转换功能，将{$log[sellname]}转换为{$log[buyname]},总共花费 {$log[sellname]}:{$log[field1]}，获得 {$log[buyname]}:{$log[field2]}",

	'topped_descrip'		=>	"文章：[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n操作：将文章设为置顶{$log[topped]}\n原因：{$log[reason]}",
	'untopped_descrip'		=>	"文章：[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n操作：解除文章置顶\n原因：{$log[reason]}",
	'digest_descrip'		=> "文章：[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n操作：将文章设为精华{$log[digest]}\n原因：{$log[reason]}\n影响：{$log[affect]}",
	'undigest_descrip'		=>	"文章：[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n操作：取消文章精华\n原因：{$log[reason]}\n影响：{$log[affect]}",
	'highlight_descrip'		=>	"文章：[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n操作：将文章标题加亮\n原因：{$log[reason]}",
	'unhighlight_descrip'		=>	"文章：[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n操作：将文章标题取消加亮\n原因：{$log[reason]}",
	'push_descrip'			=>	"文章：[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n操作：将文章提前\n原因：{$log[reason]}",
	'lock_descrip'			=>	"文章：[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n操作：将文章锁定\n原因：{$log[reason]}",
	'unlock_descrip'		=>	"文章：[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n操作：将文章解除锁定\n原因：{$log[reason]}",
	'delrp_descrip'			=> "文章：[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n操作：删除回复\n原因：{$log[reason]}\n影响：{$log[affect]}",
	'deltpc_descrip'		=> "文章：[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n操作：删除主题\n原因：{$log[reason]}\n影响：{$log[affect]}",
	'del_descrip'		=>  "文章：[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n操作：将文章删除\n原因：{$log[reason]}\n影响：{$log[affect]}",
	'move_descrip'			=>  "文章：[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n操作：将文章移动到新版块([url=$db_bbsurl/thread.php?fid={$log[tofid]}][b]$log[toforum][/b][/url])\n原因：{$log[reason]}",
	'copy_descrip'			=>	"文章：[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n操作：将文章复制到新版块([url=$db_bbsurl/thread.php?fid={$log[tofid]}][b]$log[toforum][/b][/url])\n原因：{$log[reason]}",
	'edit_descrip'			=>	"文章：[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n操作：编辑文章",

	'credit_descrip'		=>	"文章：[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n操作：文章被评分\n原因：{$log[reason]}\n影响：{$log[affect]}",
	'creditdel_descrip'		=>	"文章：[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n操作：文章评分被取消\n原因：{$log[reason]}\n影响：{$log[affect]}",
	'deluser_descrip'		=>	"用户 [b]{$log[username1]}[/b] 被删除\n操作：批量删除用户",

	'join_descrip'		=> "[b]{$log[username1]}[/b] 加入群[b]{$log[cname]}[/b]，花费交易币：{$log[field1]}。",
	'donate_descrip'	=> "[b]{$log[username1]}[/b] 使用捐献给所在群($log[cname])捐献交易币：{$log[field1]}。",
	'cy_vire_descrip'	=> "[b]{$log[username2]}[/b] 使用群交易币管理功能，给用户[b]{$log[username1]}[/b]转帐 {$log[field1]}交易币，系统收取手续费：{$log[tax]}。",
	'shield_descrip'		=> "文章：[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n操作：屏蔽主题\n原因：{$log[reason]}",
	'unite_descrip'     =>
	"文章：[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n操作：主题合并\n原因：{$log[reason]}",
	'remind_descrip'		=> "文章：[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n操作：管理提示\n原因：{$log[reason]}",
);
?>