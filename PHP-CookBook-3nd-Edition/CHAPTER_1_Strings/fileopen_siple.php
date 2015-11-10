/*
PHP fopen 函数读写 txt 文件实现程序下面我们利用几个实例来总结了利用 php fopen函数来实现文件的读写操作，有需要学习的朋友可参考参考。简单的参考fopen函数
fopen() 函数打开文件或者URL。 如果打开失败，本函数返回FALSE。
语法
fopen(filename,mode,include_path,context)
*/
//实例1 创建一个文件的例子： 代码如下复制代码

<?php
if(!file_exists("test.txt")){
// 如果文件不存在（默认为当前目录下）
$fh = fopen("test.txt","w");
fclose($fh);
//关闭文件
}
?>

//实例2 利用php的读写文本文档的功能来实现修改和编辑robots文件
<?php
function get_txt($robots_file) //定义函数，内容用 {}括起来
{
  if(file_exists($robots_file)) //如果文件存在，读取其中的内容
    {
      $fp=@fopen($robots_file,"r"); //r是read的缩写，代表读取的意思，以只读方式打开文件
        if ($fp) {
          while (!feof($fp)) { //如果没有到文件尾部
          $robots_txt = fgets($fp, 4096); //逐行读取
          $robots_all = $robots_all.$robots_txt; //将数据保存到$robots_all里面
        }
        return($robots_all); //返回所有内容
        fclose($fp); //关闭文件
      }
    }
}
function put_txt($robots_txt)
{
$fp=fopen("robots.txt","w");
//w是write的缩写，886qcd.com 代表写入的意思，以写入的方式打开文件
fputs($fp,$robots_txt); //输出文本到文件
fclose($fp);
}
?>

<?php
$edit=$_GET["edit"];
$txt=$_POST["txt"];
$get_txt=get_txt("robots.txt");
//调用刚才定义的函数打开robots文件。
if($edit=="write")
{
put_txt($txt);
echo " 成功保存 <a href=robots-editer.php> 返回 </a>";
}
else
{
echo " 成功读取 <a href=robots.txt target=_blank>robots.txt</a> <a
href=writer.php> 返回 </a>";
}
?>

<?php
if($edit=="")
{
?>
<form name="form1" action="?edit=write" method="post">
<textarea name="txt" cols="160" rows="30"><?php echo $get_txt; ?></textarea>
<br />
<input name="submit" value="
保存
" type="submit" />
</form>
<?php
}
?>
/*
通过 PHP 读取文本文档 counter.txt 里的数据，并 +1 保存到文本文档中。
新建 counter.php 文档，  输入如下代码，跟 ASP 不同的是 PHP 里 的单行注释是用
// 或者 # ，多行注释用 /* */ 来实现
*/
<?php
function get_hit($counter_file)
// 定义函数，内容用 {} 括起来，学过编程的人应该看出来了，跟 C 语言有点相似
{
$count=0;
// 将计数器归零， Php 里的变量前面加上 $ 号
if(file_exists($counter_file))
// 如果计数器文件存在，读取其中的内容
{
$fp=fopen($counter_file,"r");
//r 是 read 的缩写，代表读取的意思，以只读方式打开文件
$count=0+fgets($fp,20);
/*
读取前 20 位数赋值给 count 变量， 由于 fgets() 函数读取的是字 符串，所以需要在前面 +0
来转换为整数， 这一点跟 ASP 就不同了， ASP 中字符串可以直接跟整型进行运算，而不用转换。
*/
fclose($fp); // 关闭文件
}
$count++;
// 增加计数，这一点跟 C 就非常相似了
$fp=fopen($counter_file,"w");
//w 是 write 的缩写，代表写入的意思，以写入的方式打开文件 fputs($fp,$count);
// 输出计数值到文件
fclose($fp);
return($count);
// 返回计数值
}
?>
<?php
$hit=get_hit("counter.txt");
// 调用刚才定义的函数处理 counter.txt 文档，并把结果赋值给 hit 变量。
echo " 您是第 <b>"."$hit"."</b> 位访客！ ";
// 输出结果。
