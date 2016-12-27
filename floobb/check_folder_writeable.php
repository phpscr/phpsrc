<?php
/**
 * 判断 文件/目录 是否可写（取代系统自带的 is_writeable 函数）.
 *
 * @param string $file 文件/目录
*
 * @return bool
 */
function new_is_writeable($file)
{
    if (is_dir($file)) {
        $dir = $file;
        if ($fp = @fopen("$dir/test.txt", 'w')) {
            @fclose($fp);
            @unlink("$dir/test.txt");
            $writeable = 1;
        } else {
            $writeable = 0;
        }
    } else {
        if ($fp = @fopen($file, 'a+')) {
            @fclose($fp);
            $writeable = 1;
        } else {
            $writeable = 0;
        }
    }

    return $writeable;
}
$path= dirname(__FILE__)."\db";
echo $path;


if(new_is_writeable($path)){
     echo"\t";
     echo "ok";
}
?>
