<?php
/**
 * 用于处理网站用户登陆注册请求
 * @date: 2018年5月18日 下午7:54:42
 * @author: JackieHan
 */
require_once 'public_function.inc.php';

class user
{

    public $id;

    public $name;

    public $pwd;

    public $salt;

    public $group;

    public $email;

    public $status;

    public $reg_time;

    public $last_login_time;

    public $login_ip;

    public $nick_name;

    public $uuid;

    public $error;

    public $error_msg = [
        "1000" => "您输入的用户名或密码有误",
        "1001" => "您输入的验证码有误",
        "1002" => "请输入用户名和密码"
    ];

    public function get_ip()
    {
        return $this->login_ip = getenv('REMOTE_ADDR');
    }

    
    
    // 用户登录方法
    public function user_login()
    {
        require dirname(__FILE__) . '/../config/mydb.config.php';
        // 前端传来的id和key已经设置并且不是空的
        if (! empty($_POST['name']) && isset($_POST['name']) && ! empty($_POST['password'] && isset($_POST['password']))) {
            $pdo = new PDO($dsn, $mysql_username, $mysql_password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // 查用户表
            $user_sql = '
SELECT
  `hp_user`.`u_id`,
  `hp_user`.`u_name`,
  `hp_user`.`u_pwd`,
  `hp_user`.`u_salt`,
  `hp_user`.`ug_id`,
  `hp_user`.`u_email`,
  `hp_user_login`.`u_status`,
  `hp_user`.`u_reg_time`,
  `hp_user_login`.`last_login_time`  
FROM
  `hp_user`
  INNER JOIN `hp_user_login` ON `hp_user`.`u_id` = `hp_user_login`.`u_id`
WHERE
  `hp_user`.`u_name` = :u_name;
';
            $user_stmt = $pdo->prepare($user_sql);
            $user_data_array = array(
                ":u_name" => $_POST['name']
            );
            
            try {
                $user_stmt->execute($user_data_array);
                if ($user_stmt->rowCount() == 0) {
                    echo $this->error_msg['1000'];
                    $pdo = null;
                    return false;
                } else {
                    $result = $user_stmt->fetch(PDO::FETCH_ASSOC);
                    $this->id = $result['u_id'];
                    $this->name = $result['u_name'];
                    $this->pwd = $result['u_pwd'];
                    $this->salt = $result['u_salt'];
                    $this->group = $result['ug_id'];
                    $this->email = $result['u_email'];
                    $this->status = $result['u_status'];
                    $this->reg_time = $result['u_reg_time'];
                    $this->last_login_time = $result['last_login_time'];
                    unset($result);
                }
                
                if ($this->name == '' || $this->name == null || empty($this->name) || $this->id == '' || $this->id == null || empty($this->id) || $this->pwd == '' || $this->pwd == null || empty($this->pwd)) {
                    $pdo = null;
                    echo $this->error_msg['1000'];
                    return false;
                } else {
                    $md5_pwd = md5(sha1($_POST['password'] . $this->salt));
                    if ($md5_pwd != $this->pwd) {
                        $pdo = null;
                        echo $this->error_msg['1000'];
                        return false;
                    } else {
                        try {
                            $update_login_time_sql = "
                        UPDATE hp_user_login SET last_login_time=? WHERE u_id=?
                        ";
                            $update_login_time_stmt = $pdo->prepare($update_login_time_sql);
                            $update_login_time_array = [
                                now(),
                                $this->id
                            ];
                            $update_login_time_stmt->execute($update_login_time_array);
                            $pdo = null;
                            echo "登录成功";
                            
                            session_start();
                            $_SESSION['id'] = $this->id;
                            header('Location: /output.php');
                            
                            // $_SESSION['wx_session_key'] = $this->session_key;
                            
                            return true;
                        } catch (Exception $e) {}
                    }
                }
            } catch (Exception $e) {
                die($e->getMessage());
            }
        } else {
            echo $this->error_msg['1002'];
            return false;
        }
    }
    
    // 用户注册方法
    public function user_reg()
    {
        require dirname(__FILE__) . '/../config/mydb.config.php';
        $this->name = $_POST['reg_name'];
        $this->salt = get_random_char(8, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890-=_+~@#&<>');
        $this->password = md5(sha1($_POST['reg_password'] . $this->salt));
        $this->group = 1;
        $this->email = $_POST['reg_email'];
        $this->status = 1;
        $this->reg_time = now();
        $this->last_login_time = "0000-00-00 00:00:00";
        
        $pdo = new PDO($dsn, $mysql_username, $mysql_password);
        // 把管理员的注册信息插入到用户中心表中
        $user_reg_sql = 'INSERT INTO hp_user
            (u_name,
            u_pwd,
            u_salt,
            ug_id,
            u_email,
            u_reg_time)
            VALUES
            (?,?,?,?,?,?)';
        $user_reg_get_id_sql = 'SELECT u_id FROM hp_user WHERE u_name=?';
        $user_reg_status_sql = '
            INSERT INTO hp_user_login 
            (u_id,
            last_login_time,
            u_status)
            VALUES
            (?,?,?)
            ';
        $user_reg_stmt = $pdo->prepare($user_reg_sql);
        $user_reg_get_id_stmt = $pdo->prepare($user_reg_get_id_sql);
        $user_reg_status_stmt = $pdo->prepare($user_reg_status_sql);
        
        $user_reg_array = array(
            $this->name,
            $this->password,
            $this->salt,
            $this->group,
            $this->email,
            $this->reg_time
        );
        
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $user_reg_stmt->execute($user_reg_array);
            
            $user_reg_get_id_array = array(
                $this->name
            );
            $user_reg_get_id_stmt->execute($user_reg_get_id_array);
            $result = $user_reg_get_id_stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $result['u_id'];
            
            $user_reg_status_array = array(
                $this->id,
                $this->last_login_time,
                $this->status
            );
            
            $user_reg_status_stmt->execute($user_reg_status_array);
            
            return true;
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }
}
// class admin_sign_in{
//     public $input_username;
//     public $input_password;
//     public $result_admin_id;
//     public $result_username;
//     public $result_password;
//     public $result_salt;
//     public $result_admin_status;
//     public $result_user_group;
//     var $check_password;
//     public function sign_out(){
//         session_start();
//         session_destroy();
//     }
//     public function check_input(){
//         require dirname(__FILE__).'/../config/mydb.config.php';
//         $this->input_username=$_POST['name'];
//         $this->input_password=$_POST['password'];
//         $pdo=new PDO($dsn,$mysql_username,$mysql_password);
//         $sql='SELECT
//                   `h_cube_admin_list`.`admin_id`,
//                   `h_cube_passport_center`.`user_name`,
//                   `h_cube_passport_center`.`user_password`,
//                   `h_cube_passport_center`.`user_salt`,
//                   `h_cube_admin_list`.`admin_status`,
//                   `h_cube_passport_center`.`user_group`
//                   FROM
//                   `h_cube_admin_list`
//                   INNER JOIN `h_cube_passport_center`
//                   ON `h_cube_admin_list`.`admin_passport_name` =
//                   `h_cube_passport_center`.`user_name`
//                   WHERE `h_cube_passport_center`.`user_name`=:input_username';
//         $stmt=$pdo->prepare($sql);
//         $data_array=array(":input_username"=>$this->input_username);
//         try{
//             $stmt->execute($data_array);
//             while($result=$stmt->fetch(PDO::FETCH_ASSOC)){
//               $this->result_admin_id=$result['admin_id'];
//               $this->result_username=$result['user_name'];
//               $this->result_password=$result['user_password'];
//               $this->result_salt=$result['user_salt'];
//               $this->result_admin_status=$result['admin_status'];
//               $this->result_user_group=$result['user_group'];
//             }
//         }catch(Exception $e){
//             echo $e->getMessage();
//             return false;
//         }
//         $check_password=md5(sha1($this->input_password.base64_encode($this->result_salt)));
//         if($check_password==$this->result_password){
//             //echo '密码正确';
//             return true;
//         }else{
//             echo '密码错误';
//             return false;
//         }
//     }
// }