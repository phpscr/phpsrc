
<?php
$cars = array
   (
   array("Volvo",33,20),
   array("BMW",17,15),
   array("Saab",5,2),
   array("Land Rover",15,11)
   );
//var_dump($cars);
   
for ($row = 0; $row <  4; $row++) {
echo "行数$row";
echo "\n";
//echo "\n";
//	var_dump($row);
   for ($col = 0; $col <  3; $col++) {
//     echo ".$cars[$row][$col].";
//	var_dump($col);
	echo $cars[$row][$col];
	echo "\n";
   }
}
?>

