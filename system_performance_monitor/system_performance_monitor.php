<?php

require 'config.inc.php';
$time = time();
$mem = getmemfree();
$cpu = getcpustat();
$disk = getdiskusage();
$psinfo = getps();

$sysinfo = array(
  'TIME' => $time,
  'CPUSTAT' => $cpu,
  'MEMFREE' => $mem,
  'DISKINFO' => $disk,
  'PSINFO' => $psinfo,
);

$sysinfo_json = json_encode($sysinfo);

if (file_exists($filepath)) {
    jsonlogrotate($filepath);
}

sysinfosave($filepath, $sysinfo_json);

/*ECHO FROM FILE*/
function sysinfoecho($filepath)
{
    if (file_get_contents($filepath)) {
        echo file_get_contents($filepath);
    }
}
/*SAVE TO FILE*/
function sysinfosave($filepath, $sysinfo_json)
{
    $fopen = fopen($filepath, 'a') or die('File error !');
    fwrite($fopen, $sysinfo_json);
    fwrite($fopen, "\n");
    fclose($fopen);
}

/* logrotate */
function jsonlogrotate($filepath)
{
    $filepath_new = $filepath.'_new';
    exec("wc -l $filepath", $wc);
    preg_match('/(\d+)\s+(\S+)/', $wc[0], $match);
    $filelinecount = $match[1];
    if ($filelinecount > 480) {
        exec("tail -n 5 $filepath", $system_performance_monitor);

        $fopen = fopen($filepath_new, 'w') or die('File error !');
        foreach ($system_performance_monitor as $key => $value) {
            fwrite($fopen, $value);
            fwrite($fopen, "\n");
        }
        fclose($fopen);
        copy($filepath, $filepath.'_old');
        copy($filepath_new, $filepath);
    }
}

/* DISK STAT*/
function getdiskusage()
{
    exec('df', $dfoutput);
    foreach ($dfoutput as $key => $value) {
        if (preg_match('/(\S+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)%\s+(\S+)/', $value, $matches)) {
            if ($matches[1] == 'Filesystem') {
                /* Pass the head line . This will never run while there was a if_preg_match*/
            continue;
            }
            $tmp = array(
              '1K-blocks' => (int) $matches[2],
              'Used' => (int) $matches[3],
              'Avaliable' => (int) $matches[4],
              'Used_percent' => (int) $matches[5],
              'Mounted_on' => $matches[6],
              );

            $diskfilesystem[$matches[1]] = $tmp;
        }
    }

    return $diskfilesystem;
}

/* MEMORY STAT*/
function getmemfree()
{
    exec('free', $memfreeoutput);
    $tmp = null;
    foreach ($memfreeoutput as $key => $value) {
        if (preg_match('/\S+:\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/', $value, $tmp)) {
            $mem_matches = $tmp;
        } elseif (preg_match('/\-\/\+\sbuffers\/cache:\s+(\d+)\s+(\d+)/', $value, $tmp)) {
            $buffers_matches = $tmp;
        } elseif (preg_match('/Swap:\s+(\d+)\s+(\d+)\s+(\d+)/', $value, $tmp)) {
            $swap_matches = $tmp;
        }
    }

    if (!isset($buffers_matches)) {
        $tmp = array(
      'Mem_total' => $mem_matches[1],
      'Mem_used' => $mem_matches[2],
      'Mem_free' => $mem_matches[3],
      'Mem_shared' => $mem_matches[4],
      'Mem_buffers' => $mem_matches[5],
      'Mem_cached' => $mem_matches[6],
      'Swap_total' => $swap_matches[1],
      'Swap_used' => $swap_matches[2],
      'Swap_free' => $swap_matches[3],
    );
    } else {
        $tmp = array(
      'Mem_total' => $mem_matches[1],
      'Mem_used' => $mem_matches[2],
      'Mem_free' => $mem_matches[3],
      'Mem_shared' => $mem_matches[4],
      'Mem_buffers' => $mem_matches[5],
      'Mem_cached' => $mem_matches[6],
      'Buffers_used' => $buffers_matches[1],
      'Buffers_free' => $buffers_matches[2],
      'Swap_total' => $swap_matches[1],
      'Swap_used' => $swap_matches[2],
      'Swap_free' => $swap_matches[3],
    );
    }

    $memfree = $tmp;

    return $memfree;
}

/* CPU STAT*/
function getcpustat()
{
    exec('nproc', $nproc);
    $nproc = $nproc[0];

    for ($i = 0; $i < $nproc; ++$i) {
        unset($sarcpuoutput);
        exec('sar -P '.$i.' |tail -n 2', $sarcpuoutput);
        preg_match('/\d\d:\d\d:\d\d\s+\S+\s+\d+\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)/', $sarcpuoutput[0], $sarcpuarray);
        $tmp = array('user' => (int) $sarcpuarray[1],
                'nice' => (int) $sarcpuarray[2],
                'sys' => (int) $sarcpuarray[3],
                'io' => (int) $sarcpuarray[4],
                'steal' => (int) $sarcpuarray[5],
                'idle' => (int) $sarcpuarray[6], );
        $cpustat['cpu'.$i] = $tmp;
    }

    return $cpustat;
}

function getps()
{
    exec('ps auxf', $psinfo);

    return $psinfo;
}
