<?php

/**
 * 用于处理微信用户登录请求
 * @date: 2018年5月26日 下午1:30:18
 * @author: JackieHan
 */
header("Content-type:text/html;charset=utf-8");
header("Content-type:application/json");
require dirname(__FILE__).'/../../cms_core/include/wx_user.class.php';
$wxlogin=new wx_user();
if(isset($_GET['code'])){
$wxlogin->wx_user_handler();
}else if(isset($_GET['encrypted_data'])&&isset($_GET['iv'])){
    $wxlogin->decrypted_wxdata($_COOKIE['PHPSESSID'],$_GET['encrypted_data'],$_GET['iv']);
}




