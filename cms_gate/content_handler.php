<?php
/**
 *本文件用于将用户点赞的记录插入数据库
 *@date:2018年12月1日 下午4:14:48
 *@author:JackieHan<gogoend@qq.com>
 */
header("Content-type:application/json");
require dirname(__FILE__) . '/../cms_core/config/mydb.config.php';
require dirname(__FILE__) . '/../cms_core/include/public_function.inc.php';
if(empty($_COOKIE['PHPSESSID'])){
    die('{
        "error":"9000",
        "msg":"用户未登录"
    }');
}
$sid = $_COOKIE['PHPSESSID'];
session_start([
    $sid
]);
// var_dump($_SESSION);
$current_user = $_SESSION['id'];
$action = $_POST['action'];
$target_obj = $_POST['obj_id'];
switch ($action) {
    case 'zan':
        {
            try {
                $pdo = new PDO($dsn, $mysql_username, $mysql_password);
                $zan_get_sql = 'SELECT u_id,o_id FROM hp_zan WHERE (u_id=? AND o_id=?)';
                $zan_get_stmt = $pdo->prepare($zan_get_sql);
                $zan_get_array = [
                    $current_user,
                    $target_obj
                ];
                $zan_get_stmt->execute($zan_get_array);
                if ($zan_get_stmt->rowCount() == 0) {
                    
                    $zan_sql = 'INSERT INTO hp_zan (u_id,o_id,zan_time) VALUES (?,?,?)';
                    $zan_stmt = $pdo->prepare($zan_sql);
                    $zan_array = [
                        $current_user,
                        $target_obj,
                        now()
                    ];
                    $zan_stmt->execute($zan_array);
                    echo '{
                "error":"0",
                "msg":"赞"
            }';
                } else {
                    $un_zan_sql = 'DELETE FROM hp_zan WHERE (u_id=? AND o_id=?)';
                    $un_zan_stmt = $pdo->prepare($un_zan_sql);
                    $un_zan_stmt->execute($zan_get_array);
                    echo '{
                    "error":"0",
                    "msg":"取消赞"
                    }';
                }
                $pdo = null;
            } catch (PDOException $e) {
                $error_msg = $e->getMessage();
                die('{
                    "error":"5000",
                    "msg":"' . $error_msg . '"
                }');
            }
        }
}