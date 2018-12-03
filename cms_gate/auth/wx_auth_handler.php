<?php

/**
 * 用于处理微信用户登录请求
 * @date: 2018年5月26日 下午1:30:18
 * @author: JackieHan
 */
//header("Content-type:application/json");

function show_img($url){
    //file_get_contents($url,true); 可以读取远程图片，也可以读取本地图片
    $img = file_get_contents($url,true);
    //使用图片头输出浏览器
    header("Content-Type: image/jpeg;text/html; charset=utf-8");
    return $img;
  }

require dirname(__FILE__).'/../../cms_core/include/wx_user.class.php';
$wxlogin=new wx_user();
if(isset($_GET['code'])){
$wxlogin->wx_user_handler();
}else if(isset($_GET['encrypted_data'])&&isset($_GET['iv'])){
    $wxlogin->decrypted_wxdata($_COOKIE['PHPSESSID'],$_GET['encrypted_data'],$_GET['iv']);
}else if(isset($_GET['continue'])&&$_GET['continue']=='yes'){
    session_start([$_COOKIE['PHPSESSID']]);
    $u_info=($wxlogin->get_user($_SESSION['id']));
    $u_info_need=['nickName'=>$u_info->nick_name,'uuid'=>$u_info->uuid,'lastLoginTime'=>$u_info->last_login_time];
    unset($u_info);
    echo json_encode($u_info_need);
}else if(isset($_GET['avatar'])){
    header('Content-type: image/jpeg');
    $img_path=dirname(__FILE__).'/../../cms_data/user_avatar/'.$_GET['avatar'].'.jpeg';
    echo show_img($img_path);
}




