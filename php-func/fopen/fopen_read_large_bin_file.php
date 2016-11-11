<?php
/**
 * 读写大二进制文件，不必申请很大内存
 * 只有读取到内容才创建文件
 * 保证目录可写.
 *
 * @param string $srcPath 源文件路径
 * @param string $dstPath 目标文件路径
*
 * @return bool
 */
function fetch_big_file($srcPath, $dstPath)
{
        set_time_limit(0); // 设置脚本执行时间无限长
        if (!$fpSrc = fopen($srcPath, 'rb')) {
            return false;
          }

          $isWriteFileOpen = false; // 写文件 是否已打开？

          do {
                    $data = fread($fpSrc, 8192); // 每次读取 8*1024个字节
                    if (!$data) {
                                    break;
                    } elseif (!$isWriteFileOpen) {
                      // 第一次读取文件，并且有内容，才创建文件
                      $fpDst = fopen($dstPath, 'wb');
                      $isWriteFileOpen = true;
                      fwrite($fpDst, $data);
                    } else {
                      // 写入
                      fwrite($fpDst, $data);
                    }
          }while (true);

          fclose($fpSrc);
          fclose($fpDst);
          return true;
}
$srcPath = 'http://sina.com.cn';
$dstPath = 'a.html';
fetch_big_file($srcPath, $dstPath);
echo 'success';
