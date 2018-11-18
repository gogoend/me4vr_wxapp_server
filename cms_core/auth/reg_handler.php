<?php
/**
*本文件用于将用户提交的数据插入数据库
*@date:2017年12月8日 上午10:49:48
*@author:JackieHan<gogoend@qq.com>
*/
require dirname(__FILE__).'/../include/user.class.php';
header("Content-type:text/html;charset=utf-8");
$sign_up=new user();
$sign_up->user_reg();
