<?php
/**
 * Useforum  Copyright (C) 2010-2014 游戏控制器
 * 添加日期 2014-7-22 GW
 */
class game extends useforum
{
	//显示游戏
	function dynasty2048(){

	}

    function dynasty2048_score(){
        $conditions = array('uid'=>$_SESSION["userinfo"]['uid']);
        $find = spClass("lib_game")->find($conditions);
        if($find['score'] < $this->spArgs("score") && $find['score'] ){
            spClass("lib_game")->updateField($conditions,'score',$this->spArgs("score"));
        }elseif(!$find['score'] ){
            spClass("lib_game")->create(array(
                'uid'=>$_SESSION["userinfo"]['uid'],
                'ctime' => $_SERVER['REQUEST_TIME'],
                'score' => $this->spArgs("score"),
                'gname' => "dynasty2048"
                ));
        }
        exit;

    }


}	