<?php
/**
 * Useforum  Copyright (C) 2010-2013 评论模型
 * 添加日期 2011 GW
 */
class lib_reply extends spModel
{
	var $pk = "rid"; 
	var $table = "reply";

	// 请注意，这里我们覆盖了spModel的create函数，以方便我们对新增的记录加入时间与用户名
	public function create($row){
		// 使用array_merge构造新的$row
		$row = array_merge($row, array(
			'ctime' => $_SERVER['REQUEST_TIME'],
			'ip' => getIP(),
			'uname' => $_SESSION["userinfo"]["uname"]
		));
		// 调用父类（spModel）的create方法
		$rid = parent::create($row);
		return $rid;
	}

	//评论某人发送提醒
	public function sendnotice($gid){
		// 使用array_merge构造新的$row
		$title = spClass("lib_topic") ->find(array("gid"=>$gid),"title,uname");
		if($_SESSION["userinfo"]["uname"] != $title['uname']){
			$url = spUrl("main","view",array('gid'=>$gid));
			$author = spUrl("user","profile",array('uname'=>$_SESSION["userinfo"]["uname"]));
			$notice = spAccess('r',$title['uname']); 
			$notice['update']=1;
			$newrow = "<a href={$author} target=_blank>{$_SESSION["userinfo"]["uname"]}</a>评论了您的话题“<a href={$url} target=_blank>{$title['title']}</a>”，快去看看吧！";
			$notice['notice'][] = $newrow;
			spAccess('w' , $title['uname'], $notice); 
		}
	}

	//@某人发送提醒
	public function sendAt ($content,$rid,$gid){
		if (atSomeone($content)){
			$list =  atSomeone($content);
			$topic = spClass("lib_topic") ->find(array("gid"=>$gid),"title");
			$url = spUrl("main","view",array('gid'=>$gid));
			$author = spUrl("user","profile",array('uname'=>$_SESSION["userinfo"]["uname"]));
			$newrow = "<a href={$author} target=_blank>{$_SESSION["userinfo"]["uname"]}</a>在对话题“<a href={$url} target=_blank>{$topic['title']}</a>”的评论中提到了你，快去看看吧！";
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
		array(//关联user表，获得评论者个人信息
			'type' => 'hasone',
			'map' => 'replyer',
			'mapkey' => 'uname',
			'fclass' => 'lib_user',
			'fkey' => 'uname',
			'field' => 'credits,avatar,uid,ctime,acl,digestpost',
			'enabled' => true
		),
		array(//关联获取话题标题
			'type' => 'hasone',
			'map' => 'topic',
			'mapkey' => 'gid',
			'fclass' => 'lib_topic',
			'fkey' => 'gid',
			'field' => 'title,uname',
			'enabled' => true
		),
	);
}