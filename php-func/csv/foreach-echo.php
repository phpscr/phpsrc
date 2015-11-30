<?php 
//声明一个三维数组 
$info=array( 
	"user"=>array( 
		array(1,"zhangsan",20,"nan"), 
		array(2,"lisi",20,"nan"), 
		array(3,"wangwu",25,"nv") 
	), 
	"score"=>array( 
		array(1,100,98,95,96), 
		array(2,56,98,87,84), 
		array(3,68,75,84,79) 
		), 
	"connect"=>array( 
		array(1,'2468246',"salkh@bbs.com"), 
		array(2,'343681643',"aikdki@sina.com"), 
		array(3,'3618468',"42816@qq.com") 
	) 
); 
//循环遍历，输出一个表格 
foreach($info as $tableName=>$table){ 
	echo "<table align='center' border='1' width=300>"; 
	echo "<caption><h1>".$tableName."</h1></caption>";//以每个数组的键值作为表名 
		foreach($table as $row){ 
			echo "<tr>"; 
			foreach($row as $col){ 
			echo "<td>".$col."</td>"; 
			} 
		echo "</tr>"; 
	} 
	echo "</table>"; 
} 
?> 
