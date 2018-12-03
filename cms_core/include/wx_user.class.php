<?php

/**
 * 用于处理微信用户登录请求
 * @date: 2018年5月26日 下午1:30:18
 * @author: JackieHan
 */
require_once 'public_function.inc.php';
require 'user.class.php';
require 'wx_biz_data_crypt.class.php';
require dirname(__FILE__).'/../config/wxapp.config.php';
class wx_user extends user
{

    public $open_id;

    public $session_key;
    
    public $avatar_url;

    public $wx_user_info;
    
    public function build_wx_request()
    {
        global $appid,$secret;
        if(!isset($appid)||!isset($secret)){
            header("Content-type:application/json");
            die('{"error":"5000","msg":"登录失败：服务器无凭据，请联系管理员重新配置相关凭据。"}');
        }
        $request_data = [
            "appid" => $appid,
            "secret" => $secret,
            "js_code" => $_GET['code'],
            "grant_type" => "authorization_code"
        ];
        $_GET['code']=null;
        unset($_GET['code']);

        $url = "https://api.weixin.qq.com/sns/jscode2session?" . http_build_query($request_data);
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        
        $response_json = curl_exec($ch);
        if ($response_json == false) {
            curl_close($ch);
            die( '{"error":"5001","msg":"登录失败：小程序服务器与无法接入微信服务器，请联系管理员检查网络连接。"}' );
        } else {
            $response = json_decode($response_json); // 返回数组
            if (! empty($response->errcode) || isset($response->errcode)) {
                curl_close($ch);
                die('{"error":"5002","msg":"登录失败：微信服务器认证失败。返回的错误代码：'.$response->errcode.'"}');
            } else {
                //处理登录成功
                curl_close($ch);
                return $response;
            }
        }
    }

    public function wx_user_handler()
    {
        require dirname(__FILE__) . '/../config/mydb.config.php';
        $wx_response = $this->build_wx_request();
        //var_dump($wx_response);
        if ($wx_response != false) {
            // $wx_response = json_decode($response_json); // 数组
            $this->open_id = $wx_response->openid;
            $this->session_key = $wx_response->session_key;
            $pdo = new PDO($dsn, $mysql_username, $mysql_password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            //查询用户之前有没有进行过授权、登录
            $if_u_exist_sql = "SELECT * FROM hp_3rd_relation WHERE open_id=?";
            $if_u_exist_stmt = $pdo->prepare($if_u_exist_sql);
            $if_u_exist_array = [
                $this->open_id
            ];
            $if_u_exist_stmt->execute($if_u_exist_array);
            // 处理第一次登录的用户
            if ($if_u_exist_stmt->rowCount() == 0) {
                
                //写入第三方关系表
                $new_wx_u_sql = 'INSERT INTO hp_3rd_relation (vendor_id,open_id) VALUES (?,?)';
                $new_wx_u_stmt = $pdo->prepare($new_wx_u_sql);
                $new_wx_u_array = [1,$this->open_id];
                $new_wx_u_stmt->execute($new_wx_u_array);
                
                //进行用户注册流程
                $wx_reg_sql = 'INSERT INTO hp_user (u_name,u_pwd,u_salt,ug_id,u_email,u_reg_time,uuid) VALUES (?,?,?,?,?,?,?)';
                $wx_reg_stmt = $pdo->prepare($wx_reg_sql);
                $this->name = 'wx_' . get_random_char(16, 'bcdefghjkmnpqrstwxy346789');
                $this->pwd = 'wx_user_null_password';
                $this->salt = 'wx_null';
                $this->group = 2;
                $this->email = $this->name . '@null.null';
                $this->reg_time = now();
                $this->uuid=get_uuid();
                $wx_reg_array = [$this->name,$this->pwd,$this->salt,$this->group,$this->email,$this->reg_time,$this->uuid];

                $wx_reg_stmt->execute($wx_reg_array);
                //获得刚刚注册的用户的id
                $get_id_sql = 'SELECT u_id FROM hp_user WHERE u_name=?';
                $get_id_stmt = $pdo->prepare($get_id_sql);
                $get_id_array = array($this->name);
                $get_id_stmt->execute($get_id_array);
                $get_id_result = $get_id_stmt->fetch(PDO::FETCH_ASSOC);
                $this->id = $get_id_result['u_id'];
                
                //将用户id插入到第三方关系表中，与open_id一一对应
                $wx_reg_id_sql = 'UPDATE hp_3rd_relation SET u_id=? WHERE open_id=?';
                $wx_reg_id_stmt = $pdo->prepare($wx_reg_id_sql);
                $wx_reg_id_array = [$this->id,$this->open_id];
                $wx_reg_id_stmt->execute($wx_reg_id_array);
                
                //将用户id写入用户登录状态表中
                $wx_user_1st_login_sql = 'INSERT INTO hp_user_login (u_id,u_status) VALUES (?,?)';
                $wx_user_1st_login_stmt = $pdo->prepare($wx_user_1st_login_sql);
                $wx_user_1st_login_array = [$this->id,1];
                $wx_user_1st_login_stmt->execute($wx_user_1st_login_array);
            }
            
            // 开始会话
            //获得当前时间
            $now = now();
            
            //用户首次登录流程完成后正式登录
            //再次执行这条语句来获得用户id
            $if_u_exist_stmt->execute($if_u_exist_array);
            $result = $if_u_exist_stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $result['u_id'];
            
            //更新最近一次登录时间
            $update_login_time_sql = "UPDATE hp_user_login SET last_login_time=? WHERE u_id=?";
            $update_login_time_stmt = $pdo->prepare($update_login_time_sql);
            $update_login_time_array = [$now,$this->id];
            $update_login_time_stmt->execute($update_login_time_array);
            
            // 添加一条登录记录
            $update_login_record_sql = 'INSERT INTO hp_user_login_record (u_id,login_time,login_ip,login_channel) VALUES (?,?,?,?)';
            $update_login_record_stmt = $pdo->prepare($update_login_record_sql);
            $update_login_record_array = [$this->id,$now,$this->get_ip(),'1'];
            $update_login_record_stmt->execute($update_login_record_array);

            //会话开始
            session_start();
            
            //将登录的用户id存入当前会话
            $_SESSION['id'] = $this->id;
            $_SESSION['wx_session_key'] = $this->session_key;
            
            //向前端返回登录成功的消息
            $go_back_value=[
                "error"=>0,
                "msg"=>"欢迎来到Hello全视界",
                "s_id"=>session_id()
            ];
            echo json_encode($go_back_value);
        } else {
            echo "登录失败！";
        }
    }
    public function decrypted_wxdata($s_id,$encrypted_data,$iv){
        //微信小程序向服务器发送加密信息，由服务器进行解密，解密后的信息插入数据库
        session_start([$s_id]);
        $appid='wxadb8d00ab3bb6891';
        $session_key=$_SESSION['wx_session_key'];
        $pc=new WXBizDataCrypt($appid,$session_key);
        $err_code=$pc->decryptData($encrypted_data,$iv,$data);
        if($err_code==0){
            //echo $data;
            $this->wx_user_info=json_decode($data);
            //echo json_last_error();
            //echo $this->wx_user_info->nickName;
                        
            require dirname(__FILE__) . '/../config/mydb.config.php';
            
            $current_user_array=[
                $_SESSION['id']
            ];
            
            //获得用户注册时间
            $pdo = new PDO($dsn, $mysql_username, $mysql_password);
            $get_reg_time_sql='SELECT u_reg_time FROM hp_user WHERE u_id=?';
            $get_reg_time_stmt=$pdo->prepare($get_reg_time_sql);
            $get_reg_time_stmt->execute($current_user_array);
            $get_reg_time_result=$get_reg_time_stmt->fetch(PDO::FETCH_ASSOC);
            $reg_time=$get_reg_time_result['u_reg_time'];
            
            //获得用户最近一次登录时间
            $get_last_login_time_sql='SELECT last_login_time FROM hp_user_login WHERE u_id=?';
            $get_last_login_time_stmt=$pdo->prepare($get_last_login_time_sql);
            $get_last_login_time_stmt->execute($current_user_array);
            $get_last_login_time_result=$get_last_login_time_stmt->fetch(PDO::FETCH_ASSOC);
            $last_login_time=$get_last_login_time_result['last_login_time'];
            
            
            //初次登录判断
            //如果注册时间与最近一次登录时间一致，则为第一次登录
            if(strcmp($reg_time, $last_login_time)==0){
                
                
                //保存用户昵称
                $save_nickname_sql = 'UPDATE hp_user SET u_nickname=? WHERE u_id=?';
                $save_nickname_stmt = $pdo->prepare($save_nickname_sql);
                $save_nickname_array = [
                    $this->wx_user_info->nickName,
                    $_SESSION['id']
                ];
                try {
                    $save_nickname_stmt->execute($save_nickname_array);
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                
                //获得uuid作为用户头像文件名
                $get_uuid_sql='SELECT uuid FROM hp_user WHERE u_id=?';
                $get_uuid_stmt=$pdo->prepare($get_uuid_sql);
                $get_uuid_stmt->execute($current_user_array);
                $get_uuid_result=$get_uuid_stmt->fetch(PDO::FETCH_ASSOC);
                $uuid=$get_uuid_result['uuid'];
                
                //保存用户头像
                $url = $this->wx_user_info->avatarUrl;
                try {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
                    $return_file=curl_exec($ch);
                    curl_close($ch);
                    $file_hanlder=fopen(dirname(__FILE__).'/../../cms_data/user_avatar/'.$uuid.'.jpeg', "a");
                    fwrite($file_hanlder, $return_file);
                    fclose($file_hanlder);
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
            $pdo=null;
        }else{
            echo $err_code;
        }
    }
}




