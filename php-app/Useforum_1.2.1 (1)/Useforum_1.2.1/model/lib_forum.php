<?php
/**
 * Useforum  Copyright (C) 2010-2013 版块模型
 * 添加日期 2012 GW
 */
class lib_forum extends spModel
{
	var $pk = "id"; // 每个留言唯一的标志，可以称为主键
	var $table = "forum"; // 数据表的名称
	//关联话题表，获得话题数
	var $linker = array(
		array(
			'type' => 'hasmany',
			'map' => 'topicnum',
			'mapkey' => 'id',
			'fclass' => 'lib_topic',
			'fkey' => 'forum',
			'enabled' => true ,
			'countonly' => true,
		),
		array(
			'type' => 'hasmany', //一对多关联
			'map' => 'bm',
			'mapkey' => 'id',
			'fclass' => 'lib_user',
			'fkey' => 'forum',
			'field' => 'uname',
			'enabled' => true,
		),
		array(
			'type' => 'hasone',   // 关联类型，这里是一对一关联
			'map' => 'newpost',    // 关联的标识
			'mapkey' => 'id', // 本表与对应表关联的字段名
			'fclass' => 'lib_topic', // 对应表的类名
			'fkey' => 'forum',    // 对应表中关联的字段名
			'enabled' => true ,    // 启用关联
			'sort' => 'rtime DESC',
			'field' => 'title,gid,rtime,uname',
		)
	);
}