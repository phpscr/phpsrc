<?php
/**
 * Useforum  Copyright (C) 2010-2013 评分模型
 * 添加日期 13-7-8 GW
 */
class lib_score extends spModel
{
	var $pk = "sid"; // 每个留言唯一的标志，可以称为主键
	var $table = "score"; // 数据表的名称
	var $addrules = array(  
		'lessthan5' => array('lib_score', 'lessthan5'),
	);  
	var $verifier = array( // 留言内容验证规则
		"rules" => array( 
			'reason' => array(
				'maxlength' => 100
			),
			'score' => array(
				'lessthan5' => true
			),
		),
		"messages" => array( 
			'reason' => array(
				'minlength' => "理由有点长了,请保持在100字符以内……"
			),
			'score' => array(
				'lessthan5' => "请选择一个在1到5之间的合适的整数最为分值"
			),
		)
	);
	function lessthan5($val,$right){ 
		return ($val>=1 and $val<=5) ? true : false ;
	}
	public function sendnotice($gid,$score){
		$title = spClass("lib_topic") ->find(array("gid"=>$gid),"title,uname");
		$url = spUrl("main","view",array('gid'=>$gid));
		$author = spUrl("user","profile",array('uname'=>$_SESSION["userinfo"]["uname"]));
		$notice = spAccess('r',$title['uname']); 
		$notice['update']=1;
		$newrow = "<a href={$author} target=_blank>{$_SESSION["userinfo"]["uname"]}</a>给您的话题“<a href={$url} target=_blank>{$title['title']}</a>”评分{$score}，快去看看吧！";
		$notice['notice'][] = $newrow;
		spAccess('w' , $title['uname'], $notice); 
	}
	
}