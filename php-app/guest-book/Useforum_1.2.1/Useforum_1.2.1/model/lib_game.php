<?php
/**
 * Useforum  Copyright (C) 2010-2013 评论模型
 * 添加日期 2011 GW
 */
class lib_game extends spModel
{
	var $pk = "grid";
	var $table = "game";

	var $linker = array(
		array(//关联user表，获得个人信息
			'type' => 'hasone',
			'map' => 'info',
			'mapkey' => 'uid',
			'fclass' => 'lib_user',
			'fkey' => 'uid',
			'field' => 'avatar,uname,male',
			'enabled' => true
		),
	);
}