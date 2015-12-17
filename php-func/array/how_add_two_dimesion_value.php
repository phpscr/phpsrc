<?php

//我有一个学生名字和成绩的数组
$etudiants=array(
	"AAA"=>array(
		"Maths"=>"0",
		"Francais"=>"0",
		"Anglais"=>"0",
		"Histoire-Geographie"=>"0",
		"Sport"=>"0")

);

//现在我想在后面追加一个赋值
//"BBB"=>array("Maths"=>"1","Francais"=>"0","Anglais"=>"0","Histoire-Geographie"=>"0","Sport"=>"0")
/*
$etudiants=array(
	"AAA"=>array("Maths"=>"0","Francais"=>"0","Anglais"=>"0","Histoire-Geographie"=>"0","Sport"=>"0"),

	"BBB"=>array("Maths"=>"1","Francais"=>"0","Anglais"=>"0","Histoire-Geographie"=>"0","Sport"=>"0")
);
*/
//method-1
/*
$etudiants["BBB"] = array(
	"Maths"=>"1",
	"Francais"=>"0",
	"Anglais"=>"0",
	"Histoire-Geographie"=>"0",
	"Sport"=>"0"
);

*/
//method -2
//array 1
/*$etudiants = array(
	"AAA"=>array("Maths"=>"0","Francais"=>"0","Anglais"=>"0","Histoire-Geographie"=>"0","Sport"=>"0"));
*/
print_r($etudiants);
//array 2
$additional = array(
	"BBB"=>array(
		"Maths"=>"1",
		"Francais"=>"0",
		"Anglais"=>"0",
		"Histoire-Geographie"=>"0",
		"Sport"=>"0"
	)
);
print_r($additional);
$cards = array_merge($etudiants, $additional);  
print_r($cards); 
 

