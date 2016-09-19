<?php
/**
 * Useforum  Copyright (C) 2010-2014 收听模型
 * 添加日期 14-7-5 GW
 */
class lib_follow extends spModel
{
	var $pk = "fid"; // 每个留言唯一的标志，可以称为主键
	var $table = "follow"; // 数据表的名称
	//关联user表，获得收听者个人信息
	var $linker = array(
		array(
			'type' => 'hasone',
			'map' => 'info',
			'mapkey' => 'follower',
			'fclass' => 'lib_user',
			'fkey' => 'uid',
			'field' => 'uname,credits,avatar',
			'enabled' => true
		)
	);
}