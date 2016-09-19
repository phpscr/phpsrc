<?php
/////////////////////////////////////////////////////////////////
// SpeedPHP中文PHP框架, Copyright (C)  2008 - 2010 SpeedPHP.com //
/////////////////////////////////////////////////////////////////

/**
 * spUrlRewrite 类，以扩展形式支持SpeedPHP框架URL_REWRITE的扩展。
 */
class spUrlRewrite
{
	var $params = array(
		// 'hide_default' => true, // 隐藏默认的main/index名称，已无效
		// 'args_path_info' => false, // 地址参数是否使用path_info模式，已无效。全为非path_info的模式
		'suffix' => '.html',
		'sep' => '-',
		'map' => array(
		),
		'args' => array(
		),
	);
	/**
	 * 构造函数，处理配置
	 */
	public function __construct()
	{
		$params = spExt('spUrlRewrite');
		if(is_array($params))$this->params = array_merge($this->params, $params);
	}	
	/**
	 * 在控制器/动作执行前，对路由进行改装，使其可以解析URL_WRITE的地址
	 */
	public function setReWrite()
	{
		GLOBAL $__controller, $__action;
		if(isset($_SERVER['HTTP_X_REWRITE_URL']))$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
		$request = ltrim(strtolower(substr($_SERVER["REQUEST_URI"], strlen(dirname($GLOBALS['G_SP']['url']['url_path_base'])))),"\/\\");
		if( '?' == substr($request, 0, 1) or 'index.php?' == substr($request, 0, 10) )return ;
		if( empty($request) or 'index.php' == $request ){
			$__controller = $GLOBALS['G_SP']['default_controller'];
			$__action = $GLOBALS['G_SP']['default_action'];
			return ;
		}
		$this->params['suffix'] = ( '' == $this->params['suffix'] )?'?':$this->params['suffix'];
		$request = explode($this->params['suffix'], $request, 2);
		$uri = array('first' => array_shift($request),'last' => ltrim(implode($request),'?'));
		$request = explode($this->params['sep'], $uri['first']);
		$uri['first'] = array('pattern' => array_shift($request),'args'  => $request);
		
		if( array_key_exists($uri['first']['pattern'], $this->params['map']) ){
			@list($__controller, $__action) = explode('@',$this->params['map'][$uri['first']['pattern']]);
			if( !empty($this->params['args'][$uri['first']['pattern']]) )foreach( $this->params['args'][$uri['first']['pattern']] as $v )spClass("spArgs")->set($v, array_shift($uri['first']['args']));
		}elseif( isset($this->params['map']['@']) && !in_array($uri['first']['pattern'].'.php', array_map('strtolower',scandir($GLOBALS['G_SP']['controller_path']))) ){
			@list($__controller, $__action) = explode('@',$this->params['map']['@']);
			if( !empty($this->params['args']['@']) ){
				$uri['first']['args'] = array_merge(array($uri['first']['pattern']), $uri['first']['args']);
				foreach( $this->params['args']['@'] as $v )spClass("spArgs")->set($v, array_shift($uri['first']['args']));
			}
		}else{
			$__controller = $uri['first']['pattern'];$__action = array_shift($uri['first']['args']);
		}
		if(!empty($uri['first']['args']))for($u = 0; $u < count($uri['first']['args']); $u++){
			spClass("spArgs")->set($uri['first']['args'][$u], isset($uri['first']['args'][$u+1])?$uri['first']['args'][$u+1]:"");
			$u+=1;}
		if(!empty($uri['last'])){
			$uri['last'] = explode('&',$uri['last']);
			foreach( $uri['last'] as $val ){
				@list($k, $v) = explode('=',$val);if(!empty($k))spClass("spArgs")->set($k,isset($v)?$v:"");}}
	}


	/**
	 * 在构造spUrl地址时，对地址进行URL_WRITE的改写
	 *
	 * @param urlargs    spUrl的参数
	 */
	public function getReWrite($urlargs = array())
	{
		$uri = trim(dirname($GLOBALS['G_SP']['url']["url_path_base"]),"\/\\");
		if( empty($uri) ){$uri = '/';}else{$uri = '/'.$uri.'/';}
		if( $GLOBALS['G_SP']["default_controller"] == $urlargs['controller'] && $GLOBALS['G_SP']["default_action"] == $urlargs['action'] && empty($urlargs['args']) ){
			return $uri.((null != $urlargs['anchor']) ? "#{$anchor}" : '');
		}elseif( $k = array_search(strtolower($urlargs['controller'].'@'.$urlargs['action']), array_map('strtolower',$this->params['map']))){
			$uri .= ('@'==$k)?'':$k;$isfirstmark = ('@'==$k);
			if( !empty( $this->params['args'][$k] ) && !empty($urlargs['args']) ){
				foreach( $this->params['args'][$k] as $defarg ){
					if( $isfirstmark ){
						$uri .= isset($urlargs['args'][$defarg]) ? $urlargs['args'][$defarg] : '';$isfirstmark = 0;
					}else{
						$uri .= isset($urlargs['args'][$defarg]) ? $this->params['sep'].$urlargs['args'][$defarg] : $this->params['sep'];
					}
					unset($urlargs['args'][$defarg]);
				}
			}
		}else{
			$uri .= $urlargs['controller'].$this->params['sep'].$urlargs['action'];
		}
		if( !empty($urlargs['args']) ){
			foreach($urlargs['args'] as $k => $v)$uri.= $this->params['sep'].$k.$this->params['sep'].$v;
		}else{
			$uri = rtrim($uri, $this->params['sep']);
		}
		return $uri.$this->params['suffix'] .((null != $urlargs['anchor']) ? "#{$anchor}" : '');
	}
}