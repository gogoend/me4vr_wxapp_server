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
         require dirname(__FILE__).'/../config/mydb.config.php';
         $pdo=new PDO($dsn,$mysql_username,$mysql_password);
         $pdo=null;
     }catch (Exception $e){
         header("Location:/oobe");
     }
 } else {
         header("Location:/oobe");
     }
 }