<?php
/**
*本文件用于注销用户登录
*@date:2017年12月27日 上午9:11:06
*@author:JackieHan<gogoend@qq.com>
*/

require dirname(__FILE__).'/../../cms_core/include/sign_up.class.php';
header("Content-type:text/html;charset=utf-8");
$sign_out=new admin_sign_in();
$sign_out->sign_out();
header('Location:/index.php');