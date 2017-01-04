<?php
/**
 * Useforum  Copyright (C) 2010-2014 接口
 * 添加日期 14-7-4 GW
 */
class api extends useforum
{

	function qq_login (){
		require_once("./include/Extensions/API/qqConnectAPI.php");
		$qc = new QC();
		$qc->qq_login();
	}

	function index (){
		require_once("./include/Extensions/API/qqConnectAPI.php");
		$qc = new QC();
		$access_token = $qc->qq_callback();
		$openid = $qc->get_openid();
		if(!$_SESSION['userinfo']['uid']):
			if($result = spClass("lib_user") ->find(array("openid"=>$openid)))://判断是否存在openid记录
				spClass('spAcl')->set($result['acl']);
				$_SESSION["userinfo"] = $result; // 在SESSION中记录当前用户的信息
					setcookie('autologin',$result['uname'],time()+3600*24*10);//保持7天自动登录
					$pass = rand() . $_SERVER['REQUEST_TIME'] . 'Useforum' . $result['uname'];
					setcookie('temppassword',md5($pass),time()+3600*24*10);//保持7天自动登录
					$notice = spAccess('r',$result['uname']);
					$notice['temppassword'] = md5($pass);
					spAccess('w' , $result['uname'], $notice,3600*24*10);
					$this->success("使用QQ互联登录成功，欢迎您!",spUrl());
			else:
				$this->qq_register($openid, $access_token );
			endif;
		else:
			spClass('lib_user')->updateField(array('uid'=>$_SESSION["userinfo"]["uid"]), 'openid',$openid);
			$i = spAccess('r',$_SESSION["userinfo"]["uname"]);
			$i['access_token'] = $access_token;
			spAccess('w' , $_SESSION["userinfo"]["uname"],$i);
			$this->success("已成功绑定QQ！", spUrl("api","qq_binding"));
		endif;
	}

	function qq_register ($openid,$access_token){
		$this->openid = $openid;
		$this->access_token = $access_token;
		if(array_key_exists("uname",$this->spArgs())){
			if($this->spArgs("type") ==1){ //登录已有账号
				$userObj = spClass("lib_user");
				$uname = $this->spArgs("uname");
				$upass = md5($this->spArgs("upass"));
				$autologin = $this->spArgs("autologin");
				if( $_COOKIE['lognum'] <= 5 ||($_SERVER['REQUEST_TIME'] - $_COOKIE['logtime']) > 900){
					if( false == $userObj->userlogin($uname, $upass,$autologin) ){
						setcookie("lognum",$_COOKIE['lognum'] + 1, $_SERVER['REQUEST_TIME']+900);
						$_COOKIE['lognum']++;
						if($_COOKIE['lognum'] > 5)setcookie("logtime",$_SERVER['REQUEST_TIME'], $_SERVER['REQUEST_TIME']+3600);
						$this->errmsg ="用户名或密码错误，请重新输入！";
					}else{
						$userObj->last($uname);
						$ip = $userObj->setIP($uname);
						$info = $userObj->find(array('uname'=>$uname),'acl');
						spClass('spAcl')->set( $info['acl']);
						$city = getCity(array( "ip" => $ip ));
						$useracl = spClass("spAcl")->get(); // 通过acl的get可以获取到当前用户的角色标识
						$acl = "GBADMIN" == $useracl ? "管理员":"用户";
						if ( $ip ) $lastip = "<br>上次登录IP {$ip} 地点 {$city}";
						$i = spAccess("r",$uname);
						$i['last'] = $_SERVER['REQUEST_TIME'];
						$i['access_token'] = $access_token;
						spAccess("w",$uname,$i);
						$userObj->updateField(array('uname'=>$uname), 'openid',  $this->spArgs("openid"));
						$this->success("使用QQ互联登录成功，欢迎您，尊敬的{$acl}！{$lastip}",spUrl(),2);
					}
				}else{
					$this->errmsg ="输入错误超过5次，请等待15分钟再试！";
				}
			}else{
				$userObj = spClass("lib_user");
				$userObj->verifier = $userObj->reg_verifier;
				$upass = md5($this->spArgs("upass"));
				$results = $userObj->spVerifier($this->spArgs());
				if( false == $results ){
					$userObj->creatuser($this->spArgs("uname"),$this->spArgs("email"),$upass);
					/*自动登录写入*/
					if($autologin = $this->spArgs("uname")){
						setcookie('autologin',$this->spArgs("uname"),time()+3600*24*10);//保持7天自动登录
						$pass = rand() . $_SERVER['REQUEST_TIME'] . 'Useforum' . $this->spArgs("uname");
						setcookie('temppassword',md5($pass),time()+3600*24*10);
					$notice = spAccess('r',$this->spArgs("uname"));
					$notice['temppassword'] = md5($pass);
					spAccess('w' , $this->spArgs("uname"), $notice,3600*24*10);
					}
					$i = spAccess('r',$this->spArgs("uname"));
					$i['access_token'] = $access_token;
					spAccess('w' , $this->spArgs("uname"), $i);
					$userObj->updateField(array('uname'=>$this->spArgs("uname")), 'openid',  $this->spArgs("openid"));
					spClass('spAcl')->set('GBUSER');
					$this->success("使用QQ互联登录成功！",spUrl());
				}else{
					foreach($results as $item){
						foreach($item as $msg){
							$this->errmsg = "{$msg}；{$this->errmsg}";
						}
					}
				}
			}
		}
		$this->display("api_index.html");
	}

	//qq绑定
	function qq_binding(){
		$result = spClass("lib_user") ->find(array("uid"=>$_SESSION["userinfo"]["uid"]),null,'openid');
		if($result['openid']){
			$this->is_binding = 1;
		}
		if($this->spArgs('remove_binding')){
			spClass('lib_user')->updateField(array('uid'=>$_SESSION["userinfo"]["uid"]), 'openid','');
			$this->success("已解除QQ绑定！", spUrl("api","qq_binding"));
		}
	}

}	