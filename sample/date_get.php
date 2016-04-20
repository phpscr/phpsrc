<?php 
 /* 
 Plugin Name: 显示时间 
 Version: 1.0 
 Plugin URL:  
 Description: 在底部显示当前时间 
 Author: 无名智者 
 Author Email: azlu@qq.com 
 Author URL:  
 For: 不限 
 */ 
 /** 
  * 显示时间插件 
  * @author ldldldld5 
  * 一旦插件被激活，此文件将被include 
  */ 
 // if (!defined('SYSTEM_ROOT')) { die('Insufficient Permissions'); }  
 //设置时区以符合本地时间 
  
// function ld_time_system() { 
   date_default_timezone_set ("Asia/Shanghai"); 
   $date=getdate(date("U")); 
 	echo ("现在是北京时间 $date[year] 年 $date[mon] 月 $date[mday] 日 $date[hours] 时 $date[minutes] 分 $date[seconds] 秒"); 
// } 
  
// addAction('footer','ld_time_system'); 

