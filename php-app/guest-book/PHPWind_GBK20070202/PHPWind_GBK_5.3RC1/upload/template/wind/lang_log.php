<?php
$logtype=array(
	'bk_save'   => '���',
	'bk_draw'   => 'ȡ��',
	'bk_vire'   => 'ת��',
	'bk_credit' => 'ת��',

	'topped'    => '�ö�',
	'digest'    => '����',
	'highlight'	=> '����',
	'push'		=> '��ǰ',
	'locked'	=> '����',
	'delrp'		=> 'ɾ�ظ�',
	'deltpc'	=> 'ɾ����',
	'delete'	=> 'ɾ��',
	'move'		=> '�ƶ�',
	'copy'		=> '����',
	'edit'		=> '�༭',
	'shield'    => '����',
	'unite'     => '�ϲ�',
	'remind'    => '��ʾ',

	'credit'	=> '����',
	'deluser'	=> '�û�',

	'cy_donate'	=> '����',
	'cy_join'	=> '��Ⱥ',
	'cy_vire'	=> 'ת��',
);

$lang = array (
	'bk_save_descrip_1'		=>	"[b]{$log[username1]}[/b]ʹ�û��ڴ��ܣ������{$log[field1]}{$db_moneyname}",
	'bk_save_descrip_2'		=>	"[b]{$log[username1]}[/b]ʹ�ö��ڴ��ܣ������{$log[field1]}{$db_moneyname}",
	'bk_draw_descrip_1'		=>	"[b]{$log[username1]}[/b]ʹ�û���ȡ��ܣ�ȡ����{$log[field1]}{$db_moneyname}",
	'bk_draw_descrip_2'		=>	"[b]{$log[username1]}[/b]ʹ�ö���ȡ��ܣ�ȡ����{$log[field1]}{$db_moneyname}",
	'bk_vire_descrip'		=>	"[b]{$log[username1]}[/b]ʹ��ת�ʹ��ܣ�ת�ʸ�[b]{$log[username2]}[/b]��{$log[field1]}{$db_moneyname}",
	'bk_credit_descrip'		=>	"[b]{$log[username1]}[/b]ʹ�û���ת�����ܣ���{$log[sellname]}ת��Ϊ{$log[buyname]},�ܹ����� {$log[sellname]}:{$log[field1]}����� {$log[buyname]}:{$log[field2]}",

	'topped_descrip'		=>	"���£�[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n��������������Ϊ�ö�{$log[topped]}\nԭ��{$log[reason]}",
	'untopped_descrip'		=>	"���£�[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n��������������ö�\nԭ��{$log[reason]}",
	'digest_descrip'		=> "���£�[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n��������������Ϊ����{$log[digest]}\nԭ��{$log[reason]}\nӰ�죺{$log[affect]}",
	'undigest_descrip'		=>	"���£�[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n������ȡ�����¾���\nԭ��{$log[reason]}\nӰ�죺{$log[affect]}",
	'highlight_descrip'		=>	"���£�[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n�����������±������\nԭ��{$log[reason]}",
	'unhighlight_descrip'		=>	"���£�[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n�����������±���ȡ������\nԭ��{$log[reason]}",
	'push_descrip'			=>	"���£�[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n��������������ǰ\nԭ��{$log[reason]}",
	'lock_descrip'			=>	"���£�[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n����������������\nԭ��{$log[reason]}",
	'unlock_descrip'		=>	"���£�[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n�����������½������\nԭ��{$log[reason]}",
	'delrp_descrip'			=> "���£�[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n������ɾ���ظ�\nԭ��{$log[reason]}\nӰ�죺{$log[affect]}",
	'deltpc_descrip'		=> "���£�[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n������ɾ������\nԭ��{$log[reason]}\nӰ�죺{$log[affect]}",
	'del_descrip'		=>  "���£�[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n������������ɾ��\nԭ��{$log[reason]}\nӰ�죺{$log[affect]}",
	'move_descrip'			=>  "���£�[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n�������������ƶ����°��([url=$db_bbsurl/thread.php?fid={$log[tofid]}][b]$log[toforum][/b][/url])\nԭ��{$log[reason]}",
	'copy_descrip'			=>	"���£�[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n�����������¸��Ƶ��°��([url=$db_bbsurl/thread.php?fid={$log[tofid]}][b]$log[toforum][/b][/url])\nԭ��{$log[reason]}",
	'edit_descrip'			=>	"���£�[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n�������༭����",

	'credit_descrip'		=>	"���£�[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n���������±�����\nԭ��{$log[reason]}\nӰ�죺{$log[affect]}",
	'creditdel_descrip'		=>	"���£�[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n�������������ֱ�ȡ��\nԭ��{$log[reason]}\nӰ�죺{$log[affect]}",
	'deluser_descrip'		=>	"�û� [b]{$log[username1]}[/b] ��ɾ��\n����������ɾ���û�",

	'join_descrip'		=> "[b]{$log[username1]}[/b] ����Ⱥ[b]{$log[cname]}[/b]�����ѽ��ױң�{$log[field1]}��",
	'donate_descrip'	=> "[b]{$log[username1]}[/b] ʹ�þ��׸�����Ⱥ($log[cname])���׽��ױң�{$log[field1]}��",
	'cy_vire_descrip'	=> "[b]{$log[username2]}[/b] ʹ��Ⱥ���ױҹ����ܣ����û�[b]{$log[username1]}[/b]ת�� {$log[field1]}���ױң�ϵͳ��ȡ�����ѣ�{$log[tax]}��",
	'shield_descrip'		=> "���£�[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n��������������\nԭ��{$log[reason]}",
	'unite_descrip'     =>
	"���£�[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n����������ϲ�\nԭ��{$log[reason]}",
	'remind_descrip'		=> "���£�[url=$db_bbsurl/read.php?tid=$log[tid]]$log[subject][/url]\n������������ʾ\nԭ��{$log[reason]}",
);
?>