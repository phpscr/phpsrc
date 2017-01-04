<?php
/**
 * Useforum  Copyright (C) 2010-2013 用户模型
 * 添加日期 2011 GW
 */
class lib_user extends spModel
{
	var $pk = "uid"; // 每个留言唯一的标志，可以称为主键
	var $table = "user"; // 数据表的名称
	var $addrules = array(
		'hadname' => array('lib_user', 'checkname'),
		'hademail' => array('lib_user', 'checkemail'),
	);
	var $reg_verifier = array(
		"rules" => array( // 规则
			'uname' => array(  // 这里是对uname的验证规则
				'hadname' => TRUE,
				'notnull' => TRUE, // uname不能为空
				'minlength' => 2,  // uname长度不能小于2
				'maxlength' => 15,  // uname长度不能大于15
			),
			'email' => array(  // 这里是对email的验证规则
				'hademail' => TRUE,
				'notnull' => TRUE, // uname不能为空
				'email' => TRUE,
			),
			'upass' => array(  // 这里是对密码的验证规则
				'notnull' => TRUE, // 密码不能为空
				'minlength' => 5,  // 密码长度不能小于5
				'maxlength' => 20, // 密码长度不能大于20
			),
			'comfirm_upass' => array(  // 这里是对第二次输入的密码的验证规则
				'equalto' => 'upass', // 要等于'password'，也就是要与上面的密码相等
			),

		),
		"messages" => array( // 提示信息
			'uname' => array(
				'hadname' => "用户名已存在",
				'notnull' => "用户名不能为空",
				'minlength' => "用户名不能少于2个字符",
				'maxlength' => "用户名不能大于15个字符",
			),
			'email' => array(
				'hademail' => "电子邮箱已被注册",
				'notnull' => "email不能为空",
				'email' => "email格式不对",
			),
			'upass' => array(
				'notnull' => "密码不能为空",
				'minlength' => "密码不能少于5个字符",
				'maxlength' => "密码不能大于20个字符",
			),
			'comfirm_upass' => array(
				'equalto' => "两次输入的密码不一致",
			),
		)
	);
	var $pwdverifier = array(
		"rules" => array(
			'password' => array(  // 这里是对uname的验证规则
				'notnull' => TRUE, 
				'minlength' => 5, 
				'maxlength' => 20  
			),
            'comfirm_upass' => array(  // 这里是对第二次输入的密码的验证规则  
                'equalto' => 'password', // 要等于'password'，也就是要与上面的密码相等  
            ), 
		),
		"messages" => array(
			'password' => array(
				'notnull' => "密码不能为空",
				'minlength' => "密码不能少于5个字符",
				'maxlength' => "密码不能大于20个字符"
			),
            'comfirm_upass' => array(  // 这里是对第二次输入的密码的验证规则  
                'equalto' => '两次输入的密码不一致', // 要等于'password'，也就是要与上面的密码相等  
            ), 
		)
	);
	/***
	 * @param uname    用户名
	 * @param upass    密码，请注意，本例中使用了加密输入框，所以这里的$upass是经过MD5加密的字符串。
	 */
	public function userlogin($uname, $upass,$autologin=''){
		if ( preg_match('/^[A-Za-z0-9]+([._\-\+]*[A-Za-z0-9]+)*@([A-Za-z0-9-]+\.)+[A-Za-z]{2,4}+$/', $uname) != 0 ){
			$conditions = array(
				'email' => $uname,
				'upass' => $upass,  //
			);
		}else{
			$conditions = array(
				'uname' => $uname,
				'upass' => $upass,  //
			);
		}

		// 检查用户名/密码，由于$conditions是数组，所以SP会自动过滤SQL攻击字符以保证数据库安全。
		if( $result = $this->find($conditions) ){ 
			// 成功通过验证，下面开始对用户的权限进行会话设置，最后返回用户ID
			// 用户的角色存储在用户表的acl字段中
			spClass('spAcl')->set($result['acl']); // 通过spAcl类的set方法，将当前会话角色设置成该用户的角色
			$_SESSION["userinfo"] = $result; // 在SESSION中记录当前用户的信息
			if($autologin){
                setcookie('autologin',$result['uname'],time()+3600*24*10);//保持7天自动登录
                $pass = rand() . $_SERVER['REQUEST_TIME'] . 'Useforum' . $result['uname'];
                setcookie('temppassword',md5($pass),time()+3600*24*10);//保持7天自动登录
                $notice = spAccess('r',$result['uname']);
                $notice['temppassword'] = md5($pass);
                spAccess('w' , $result['uname'], $notice,3600*24*10);
			}
			return true;
		}else{
			// 找不到匹配记录，用户名或密码错误，返回false
			return false;
		}
	}

	/*写入登录时间*/
	public function last($uname){
		$notice = spAccess('r',$uname);
		$notice['last'] = $_SERVER['REQUEST_TIME'];
		spAccess('w' , $uname, $notice);
	}

	/*写入登录IP*/
	public function setIP ($uname){
		$notice = spAccess('r',$uname);
		$lastip = $notice['ip'];
		$notice['ip'] = getIP();
		spAccess('w' , $uname, $notice);
		return $lastip;
	}

	function checkname($val,$right){
		return ($this->findBy('uname' , $val) ? false : true);
	}

	function checkemail($val,$right){
		return ($this->findBy('email' , $val) ? false : true);
	}

	public function creatuser($uname,$email,$upass,$ip){
		$conditions = array(
			'ctime' => $_SERVER['REQUEST_TIME'],
			'uname' => $uname,
			'upass' => $upass,
			'email' => $email,
			'ip' => getIP(),
		);
		$this->create($conditions);
		$newuser = array(
			'uname' => $uname,
		);
		if( $result = $this->find($newuser) ){
			// 成功通过验证，下面开始对用户的权限进行会话设置，最后返回用户ID
			// 用户的角色存储在用户表的acl字段中
			spClass('spAcl')->set($result['acl']); // 通过spAcl类的set方法，将当前会话角色设置成该用户的角色
			$_SESSION["userinfo"] = $result; // 在SESSION中记录当前用户的信息
		}
	}

	var $linker = array(
		/*关联获取听众数*/
		array(
			'type' => 'hasmany',
			'map' => 'fnum',
			'mapkey' => 'uid',
			'fclass' => 'lib_follow',
			'fkey' => 'following',
			'countonly' => true,
			'enabled' => true,
		),
);

	
}