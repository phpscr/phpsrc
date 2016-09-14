<?php
/**
 * Useforum  Copyright (C) 2010-2013 话题模型
 * 添加日期 2011 GW
 */
class lib_topic extends spModel
{
	var $pk = "gid"; // 每个留言唯一的标志，可以称为主键
	var $table = "topic"; // 数据表的名称
		
	var $verifier = array( // 留言内容验证规则
		"rules" => array(
			'contents' => array(
				'notnull' => TRUE,
				'minlength' => 5,
				'maxlength' => 10000 
			),
		),
		"messages" => array(
			'contents' => array(
				'notnull' => "内容不能为空",
				'minlength' => "内容必须大于5个字符",
				'maxlength' => "内容必须小于10000个字符", 
			),
		)
	);	
	// 请注意，这里我们覆盖了spModel的create函数，以方便我们对新增的记录加入时间与用户名
	public function create($row){
		// 使用array_merge构造新的$row
		$row = array_merge($row, array(
			'ip' => getIP('ip'),
			'ctime' => $_SERVER['REQUEST_TIME'],
			'rtime' => $_SERVER['REQUEST_TIME'],
			'uname' => $_SESSION["userinfo"]["uname"]
		));
		// 调用父类（spModel）的create方法
		$gid = parent::create($row);
		return $gid;
	}

	//@某人发送提醒
	public function sendAt ($content,$gid){
		if (atSomeone($content)){
			$list =  atSomeone($content);
			$title = spClass("lib_topic") ->find(array("gid"=>$gid),"title,uname");
			$url = spUrl("main","view",array('gid'=>$gid));
			$author = spUrl("user","profile",array('uname'=>$_SESSION["userinfo"]["uname"]));
			$newrow = "<a href={$author} target=_blank>{$_SESSION["userinfo"]["uname"]}</a>在话题“<a href={$url} target=_blank>{$title['title']}</a>”中提到了你，快去看看吧！";
			foreach ($list as $k => $v){
				if ( parent::find(array("uname"=>$v))){
					$notice = spAccess('r',$v);
					$notice['notice'][] = $newrow;
					$notice['update']=1;
					spAccess('w' , $v, $notice);
				}
			}
		}
	}

	var $linker = array(
		//关联获取评分列表
		array(
			'type' => 'hasmany',
			'map' => 'score',
			'mapkey' => 'gid',
			'fclass' => 'lib_score',
			'fkey' => 'gid',
			'sort' => 'ctime DESC',
			'enabled' => true,
		),
		//关联获取版块名称
		array(
			'type' => 'hasone',
			'map' => 'f',
			'mapkey' => 'forum',
			'fclass' => 'lib_forum',
			'fkey' => 'id',
			'field' => 'name',
			'enabled' => true,
		),
		//关联获取评论数
		array(
			'type' => 'hasone',
			'map' => 'replynum',
			'mapkey' => 'gid',
			'fclass' => 'lib_reply',
			'fkey' => 'gid',
			'countonly' => true,
			'enabled' => true,
		),
		//关联获取最后回复者
		array(
			'type' => 'hasone',   // 关联类型，这里是一对一关联
			'map' => 'lastreply',    // 关联的标识
			'mapkey' => 'gid', // 本表与对应表关联的字段名
			'fclass' => 'lib_reply', // 对应表的类名
			'fkey' => 'gid',    // 对应表中关联的字段名
			'enabled' => true ,    // 启用关联
			'sort' => 'ctime DESC',
			'field' => 'uname,rid',
		)
);

}