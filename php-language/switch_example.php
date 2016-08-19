<?php
/**
 * 返回字节数
 *
 * @param string $val 如 400M
 */
function return_bytes($val = '')
{
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch ($last)
    {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

$memorylimit = ini_get('memory_limit');
echo $memorylimit, '<br/>';
echo return_bytes($memorylimit);

//输出：
////400M
////419430400

?>
