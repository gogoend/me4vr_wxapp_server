<?php
class captcha{
    public $captcha;
    //检查验证码
    public function check(){
        if(isset($_REQUEST['captcha'])&&!empty($_REQUEST['captcha'])){
            session_start();
            if(!isset($_SESSION['captcha'])){
                echo '非法访问';
                return false;
            }else{
                if(strtolower($_REQUEST['captcha'])==strtolower($_SESSION['captcha'])){
                    return true;
                }else{
                    echo '验证码不正确';
                    return false;
                }
            }
        }else{
            echo '请输入验证码';
            return false;
        }
    }   
}