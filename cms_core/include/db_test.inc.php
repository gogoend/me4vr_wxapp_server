<?php
/**
*本文件用于处理连接数据库时发生的错误，当连接不上数据库就会跳转到初始化页面
*@date:2018年1月5日 下午4:26:01
*@author:JackieHan<gogoend@qq.com>
*/

function db_test(){
     $db_config= dirname(__FILE__).'/../config/mydb.config.php';
     if (file_exists($db_config)) {
     try{
         require $db_config;
         $pdo=new PDO($dsn,$mysql_username,$mysql_password);
         $pdo=null;
     }catch (Exception $e){
         //echo $e;
         die('{
            "error":"5101",
            "msg":"数据库无法连接，请检查登录凭据，以及数据库安装是否正确。"
         }');
     }
 } else {
         die('{
            "error":"5100",
            "msg":"数据库配置文件不存在，请重新配置。"
         }');
     }
 }