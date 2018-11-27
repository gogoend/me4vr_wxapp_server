<?php
/**
*本文件用于处理用户的登录请求
*@date:2017年12月9日 下午4:32:25
*@author:JackieHan<gogoend@qq.com>
*/
require dirname(__FILE__).'/../../cms_core/include/user.class.php';
header("Content-type:text/html;charset=utf-8");
$sign_in=new user();
$sign_in->user_login();
// if($sign_in->check_input()==true){
//     session_start();
//     $_SESSION['current_user']=$sign_in->input_username;
//     echo "666";
// }