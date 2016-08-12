<?php
$dir = new DirectoryIterator(dirname(__FILE__));
foreach ($dir as $fileinfo) {
   if (!$fileinfo->isDot()) {
   //var_dump($fileinfo->getFilename());
      echo($fileinfo->getFilename()).PHP_EOL;
   }
}
?> 
