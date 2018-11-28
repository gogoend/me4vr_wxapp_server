<?php
    $sid=$_COOKIE['PHPSESSID'];
    $target_obj=$_POST['obj_id'];
    $action=$_POST['action'];

    session_start($sid);
    var_dump($_SESSION);