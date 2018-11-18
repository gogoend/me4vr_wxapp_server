<?php
/**
*本文件用于存放常用的函数
*@date:2017年12月9日 下午4:33:26
*@author:JackieHan<gogoend@qq.com>
*/
date_default_timezone_set('Asia/Shanghai');
$random_char=[
    "salt"=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890-=_+~@#&<>',
    "captcha"=>'ABCEFGHJKLMNPQRTXYbcdefghjkmnpqrstwxy346789',
    "normal"=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890',
    "rand_name"=>'bcdefghjkmnpqrstwxy346789'
];
function get_random_char($length,$source_char){
    $char=str_shuffle($source_char);//把字符串打乱
    $rand_char='';
    for($i=1;$i<=$length;$i++){
        $rand_char.=substr($char, rand(0,strlen($char)-1),1);//从已经打乱的字符串中抽取一个字符，重复$length次
    }
    return $rand_char;
}
function now(){
    return date('Y-m-d H:i:s');
}
function get_uuid()
{
    if (strpos(strtolower(PHP_OS), 'win') === false) {
        // Linux系统直接由随机设备得到UUID
        exec('cat /proc/sys/kernel/random/uuid', $result, $return_code);
        return $uuid = $result[0];
    } else {
        // Windows系统由PHP生成随机字符串
        $source_char = md5(uniqid(mt_rand(), true));
        $uuid = substr($source_char, 0, 8) . '-';
        $uuid .= substr($source_char, 8, 4) . '-';
        $uuid .= substr($source_char, 12, 4) . '-';
        $uuid .= substr($source_char, 16, 4) . '-';
        $uuid .= substr($source_char, 20, 12);
        return $uuid;
    }
}