<?php
namespace Think5Cli\traits;
use think\console\Output;

trait Cli {
    public function log($msg, $type = 'info', $tans = 'utf-8')
    {
        $out = new Output();
//        $msg = mb_convert_encoding($msg, 'gb2312', $tans);
        $s = "[{$type}]".date("Y-m-d H:i") . ' ' . $msg;
        $out->writeln($s);
    }
}