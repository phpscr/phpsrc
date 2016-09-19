<?php
/////////////////////////////////////////////////////////////////
// SpeedPHP中文PHP框架, Copyright (C) 2010-2013  2008 - 2010 SpeedPHP.com //
/////////////////////////////////////////////////////////////////

/**
 * spAccessCache 类，以扩展形式支持spAccess函数拥有更多的缓存方式的扩展。
 *
 * 应用程序配置中需要使用到路由挂靠点以及spAccess挂靠点
 * 'launch' => array( 
 *  	'function_access' => array(
 *			array("spAccessCache", "xcache"), // 第二个参数为缓存驱动类型的名称
 * 	    ),
 *),
 * 
 * 本扩展要求SpeedPHP框架2.5版本以上，以支持对spAccess函数的挂靠程序。
 */
if( SP_VERSION < 2.5 )spError('spAccessCache扩展要求SpeedPHP框架版本2.5以上。');
class spAccessCache{
	/**
	 * 魔术函数  通过函数名来调用不同的缓存驱动类
	 */
	public function __call($name, $args){
		$driverClass = 'access_driver_'.$name;
		if(!class_exists($driverClass))spError('spAccess无法找到名为{$name}缓存驱动程序，请检查!');
		@list($method, $name, $value, $life_time) = $args;
		if('w' == $method){ // 写数据
			$life_time = ( -1 == $life_time ) ? '300000000' : $life_time;
			return spClass($driverClass)->set($name, serialize($value), $life_time);
		}elseif('c' == $method){ // 清除数据
			return spClass($driverClass)->del($name);
		}else{ // 读数据
			return unserialize(spClass($driverClass)->get($name));
		}
	}
}

/**
 * access_driver_db  数据库缓存驱动类
 * access_driver_db可以让开发者使用数据库本身作为缓存驱动。
 */
class access_driver_db extends spModel{
	public $pk = 'cacheid';
	public $table = 'access_cache';
	public function get($name){
		if(! $result = $this->find(array('cachename'=>$name),'cacheid DESC','cachevalue') )return FALSE;
		if( substr($result, 0, 10) < time() ){$this->del($name);return FALSE;}
		return unserialize(substr($result, 10));
	}
	public function set($name, $value, $life_time){
		$value = ( time() + $life_time ).serialize($value);
		if( FALSE !== $this->find(array('cachename'=>$name),'cacheid DESC','cachevalue') ){
			return $this->updateField(array('cachename'=>$name), 'cachevalue', $value);
		}else{
			return $this->create(array('cachename'=>$name, 'cachevalue'=>$value));
		}
	}
	public function del($name){return $this->delete(array('cachename'=>$name));}
}