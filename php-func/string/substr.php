substr()函数介绍

substr() 函数返回字符串的一部分。
语法：substr(string,start,length)。
•string：必需。规定要返回其中一部分的字符串。
•start：必需。规定在字符串的何处开始。正数 - 在字符串的指定位置开始；负数 - 在从字符串结尾的指定位置开始；0 - 在字符串中的第一个字符处开始。
•charlist：可选。规定要返回的字符串长度。默认是直到字符串的结尾。正数 - 从 start 参数所在的位置返回；负数 - 从字符串末端返回。
注释：如果 start 是负数且 length 小于等于 start，则 length 为 0。
Program List：负值的start参数
<?php
    $rest = substr("abcdef", -1);    // returns "f"
    echo $rest.'<br />';
    $rest = substr("abcdef", -2);    // returns "ef"
    echo $rest.'<br />';
    $rest = substr("abcdef", -3, 1); // returns "d"
    echo $rest.'<br />';
?>

//程序运行结果：
1 f
2 ef
3 d

//Program List：负值的length参数
//就是从start位置开始，若length为负值的话，就从字符串的末尾开始数。substr("abcdef", 2, -1)的话，就是从c开始，然后-1说明截取到e，就是要截取cde。
<?php
    $rest = substr("abcdef", 0, -1);  // returns "abcde"
    echo $rest.'<br />';
    $rest = substr("abcdef", 2, -1);  // returns "cde"
    echo $rest.'<br />';
    $rest = substr("abcdef", 4, -4);  // returns ""
    echo $rest.'<br />';
    $rest = substr("abcdef", -3, -1); // returns "de"
    echo $rest.'<br />';
?>

//程序运行结果：
1 abcde
2 cde
3 de

//Program List：基本的substr()函数用法
<?php
echo substr('abcdef', 1);     // bcdef
echo '<br />';
echo substr('abcdef', 1, 3);  // bcd
echo '<br />';
echo substr('abcdef', 0, 4);  // abcd
echo '<br />';
echo substr('abcdef', 0, 8);  // abcdef
echo '<br />';
echo substr('abcdef', -1, 1); // f
echo '<br />';
// Accessing single characters in a string
// can also be achieved using "square brackets"
$string = 'abcdef';
echo $string[0];                 // a
echo '<br />';
echo $string[3];                 // d
echo '<br />';
echo $string[strlen($string)-1]; // f
echo '<br />';
?>

//程序运行结果：
1 bcdef
2 bcd
3 abcd
4 abcdef
5 f
6 a
7 d
8 f

//Program List：移除后缀
<?php
//removes string from the end of other
function removeFromEnd($string, $stringToRemove)
{
    // 获得需要移除的字符串的长度
    $stringToRemoveLen = strlen($stringToRemove);
    // 获得原始字符串的长度
    $stringLen = strlen($string);

    // 计算出需要保留字符串的长度
    $pos = $stringLen - $stringToRemoveLen;

    $out = substr($string, 0, $pos);
    return $out;
}


$string = 'nowamagic.jpg.jpg';
$result = removeFromEnd($string, '.jpg');
echo $result;
?>

程序运行结果：
1 nowamagic.jpg
Program List：太长的字符串只显示首尾，中间用省略号代替

01
<?php
02
$file = "Hellothisfilehasmorethan30charactersandthisfayl.exe";
03
function funclongwords($file)


04
{



05
    if (strlen($file) > 30)



06
    {



07
        $vartypesf = strrchr($file,".");



08
        // 获取字符创总长度



09
        $vartypesf_len = strlen($vartypesf);



10
        // 截取左边15个字符



11
        $word_l_w = substr($file,0,15);



12
        // 截取右边15个字符



13
        $word_r_w = substr($file,-15);



14
        $word_r_a = substr($word_r_w,0,-$vartypesf_len);

15
        return $word_l_w."...".$word_r_a.$vartypesf;



16
    }



17
    else



18
        return $file;



19
}



20
// RETURN: Hellothisfileha...andthisfayl.exe



21
$result = funclongwords($file);



22
echo $result;



23
?>

程序运行结果：















1
Hellothisfileha...andthisfayl.exe

Program List：将多出的文字显示为省略号

很多时候我们需要显示固定的字数，多出的字数用省略号代替。















01
<?php



02
$text = 'welcome to nowamagic, I hope you can find something you wanted.';



03
$result = textLimit($text, 30);



04
echo $result;



05
function textLimit($string, $length, $replacer = '...')



06
{



07
    if(strlen($string) > $length)



08
    return (preg_match('/^(.*)\W.*$/', substr($string, 0, $length+1), $matches) ? $matches[1] : substr($string, 0, $length)) . $replacer;



09




10
    return $string;



11
}



12
?>

程序运行结果：















1
welcome to nowamagic, I hope...

Program List：格式化字符串

有时候我们需要格式化字符串，比如电话号码。















01
<?php



02
function str_format_number($String, $Format)



03
{



04
    if ($Format == '') return $String;



05
    if ($String == '') return $String;



06
    $Result = '';



07
    $FormatPos = 0;



08
    $StringPos = 0;



09
    while ((strlen($Format) - 1) >= $FormatPos)



10
    {



11
        //If its a number => stores it



12
        if (is_numeric(substr($Format, $FormatPos, 1)))



13
        {



14
            $Result .= substr($String, $StringPos, 1);



15
            $StringPos++;



16
        //If it is not a number => stores the caracter



17
        }



18
        else



19
        {



20
            $Result .= substr($Format, $FormatPos, 1);



21
        }



22
        //Next caracter at the mask.



23
        $FormatPos++;



24
    }



25
    return $Result;



26
}



27
// For phone numbers at Buenos Aires, Argentina



28
// Example 1:



29
    $String = "8607562337788";



30
    $Format = "+00 0000 0000000";



31
    echo str_format_number($String, $Format);



32
    echo '<br />';



33
// Example 2:



34
    $String = "8607562337788";



35
    $Format = "+00 0000 00.0000000";



36
    echo str_format_number($String, $Format);



37
    echo '<br />';



38
// Example 3:



39
    $String = "8607562337788";



40
    $Format = "+00 0000 00.000 a";



41
    echo str_format_number($String, $Format);



42
    echo '<br />';



43
?>

程序运行结果：















1
+86 0756 2337788



2
+86 0756 23.37788



3
+86 0756 23.377 a
