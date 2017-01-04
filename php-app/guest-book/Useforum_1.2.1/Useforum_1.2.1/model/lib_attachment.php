<?php
/**
 * Useforum  Copyright (C) 2010-2013 附件模型
 * 添加日期 2013-8-28 GW
 */
class lib_attachment extends spModel
{
	var $pk = "aid"; // 每个留言唯一的标志，可以称为主键
     var $table = "attachment"; // 数据表的名称

	//关联user表，获得评论者个人信息
	var $linker = array(
		array(
			'type' => 'hasone',
			'map' => 'uname',
			'mapkey' => 'uid',
			'fclass' => 'lib_user',
			'fkey' => 'uid',
			'field' =>'uname',
			'enabled' => true
		)
	);
}