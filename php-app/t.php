<?php
 $url = "http://www.weather.com.cn/weather/101050101.shtml";
  $page_content = file_get_contents($url);
  var_dump($page_content);
?>
