<?php
/**
*本文件用于定义查看文章类
*@date:2017年11月22日 下午8:08:52
*@author:JackieHan<gogoend@qq.com>
*/
require 'public_function.inc.php';


class view_article{
    public $id;
    public $title;
    public $from;
    public $author;
    public $top_article;
    public $favor_article;
    public $public_article;
    public $comment_article;
    public $lang;
    public $content_code;
    public $publish_time;
    public $category;
    public $second_category;
    public $status;
    public $click_rate;
    public $licence;
    public $article_result;
    public function query_article(){
        if(!isset($_GET['view_id'])||$_GET['view_id']==''||$_GET['view_id']==null){
            header('HTTP/1.1 404 Not Found');
            header('Location:404.html');
            return false;
        }
        require dirname(__FILE__).'/../config/mydb.config.php';
            $pdo=new PDO($dsn,$mysql_username,$mysql_password);
            $sql="SELECT * FROM h_cube_cms_article_table WHERE article_id=:article_id";
            $stmt=$pdo->prepare($sql);
            $add_click_rate_sql="UPDATE h_cube_cms_article_table SET article_click_rate=article_click_rate+1 WHERE article_id=:article_id";
            $add_click_rate_stmt=$pdo->prepare($add_click_rate_sql);
            $data_array=array(":article_id"=>$_GET['view_id']);
            
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try{
            $stmt->execute($data_array);
             while($result=$stmt->fetch(PDO::FETCH_ASSOC)){
              $this->title=$result['article_title'];
              $this->author=$result['article_author_id'];
              $this->from=$result['article_from'];
              $this->content_code=$result['article_content'];
              $this->publish_time=$result['article_publish_time'];
              $this->status=$result['article_status'];
              $this->category=$result['article_category'];
              $this->lang=$result['article_lang'];
              $this->top_article=$result['article_if_top'];
              $this->favor_article=$result['article_if_favor'];
              $this->private_article=$result['article_if_private'];
              $this->click_rate=$result['article_click_rate'];
              $this->licence=$result['article_licence'];
              $this->second_category=$result['article_second_category'];
              $this->comment_article=$result['article_if_comment'];
            }
            
            if ($this->title==''||$this->title==null){
                $pdo=null;
                header('HTTP/1.1 404 Not Found');
                header('Location:404.html');
                return false;
            }
            if($this->click_rate<1000){
            $add_click_rate_stmt->execute($data_array);}
            
        }catch (Exception $e){
            die ($e->getMessage());
        }
    }
    
    public function list_all_article($option){
        require dirname(__FILE__).'/../config/mydb.config.php';
        
        $pdo=new PDO($dsn,$mysql_username,$mysql_password);
        
        if($option=='everything'){
        $sql_list_all='
            SELECT
                 *
            FROM
                `h_cube_cms_article_table`
            ';
        }
        if($option=='normal')
        {
            $sql_list_all='
                SELECT
                 `article_id`,
                 `article_title`,
                 `article_publish_time`,
                 `article_status`
            FROM
                `h_cube_cms_article_table`
            WHERE
                (`article_status`=\'published\')
                ';
        }
        $result_list_all=$pdo->query($sql_list_all);
        while($row=$result_list_all->fetch()){
            /*
            echo '<a href="view.php?view_id='.$this->article_result['article_id'].'">
                <li> 
                <span class="article_title">'.                
                $this->article_result['article_title']                
                .'</span>
                <span class="article_date">'.   
                date("Y-m-d",strtotime($this->article_result['article_publish_time']))              
                .'</span>
                </li>
                </a>';
                */
            $this->article_result[]=$row;
        }
        unset($row);
    }
    
    
    public function list_article_by_click_rate($count){
        require dirname(__FILE__).'/../config/mydb.config.php';
        $pdo=new PDO($dsn,$mysql_username,$mysql_password);
    
        $sql="
            SELECT
                 `article_id`,
                 `article_title`,
                 `article_category`,
                 `article_click_rate`,
                 `article_status`
            FROM
                 `h_cube_cms_article_table`
            WHERE
                `article_status`='published'
            ORDER BY
                 `article_click_rate`
            DESC
            LIMIT $count;
            ";
        try{
            $result=$pdo->query($sql);
            while($row=$result->fetch()){
                /*echo '<a href="view.php?view_id='.$row['article_id'].
                 '"><li>
                 <span class="article_title">'.
                 $row['article_title']
                 .'</span>
                 <span class="article_date">'.
                 date("Y-m-d",strtotime($row['article_publish_time']))
                 .'</span>
                 </li>
                 </a>';*/
                $this->article_result[]=$row;
                //var_dump($this->article_result);
            }
            unset($row);
        }catch (Exception $e){
            die($e->getMessage());
        }
    }
    
    
    
    
    public function list_article_by_category(){
        require dirname(__FILE__).'/../config/mydb.config.php';
        
        $pdo=new PDO($dsn,$mysql_username,$mysql_password);
        
        $sql='
            SELECT
                 `article_id`,
                 `article_title`,
                 `article_publish_time`,
                 `article_category`
            FROM
                 `h_cube_cms_article_table`
            WHERE
                `article_category`=:article_category
            ORDER BY
                 `article_publish_time`
            DESC
            ';
        $stmt=$pdo->prepare($sql);
        $data_array=array(":article_category"=>$_GET['category']);
        try{
        $stmt->execute($data_array);
        while($row=$stmt->fetch()){
            /*echo '<a href="view.php?view_id='.$row['article_id'].
            '"><li>
                <span class="article_title">'.
                        $row['article_title']
                        .'</span>
                <span class="article_date">'.
                        date("Y-m-d",strtotime($row['article_publish_time']))
                        .'</span>
                </li>
                </a>';*/
            $this->article_result[]=$row;
            //var_dump($this->article_result);
        }
        unset($row);
        }catch (Exception $e){
            die($e->getMessage());
        }
    }
    
    
    
    public function list_article_by_category_part($category,$count){
        require dirname(__FILE__).'/../config/mydb.config.php';
        
        $pdo=new PDO($dsn,$mysql_username,$mysql_password);
        
        $sql="
            SELECT
                 `article_id`,
                 `article_title`,
                 `article_publish_time`,
                 `article_category`
            FROM
                 `h_cube_cms_article_table`
            WHERE
                `article_category`=:article_category
            ORDER BY
                `article_publish_time`
            DESC
            LIMIT $count
            ";
        $stmt=$pdo->prepare($sql);
        $data_array=array(
            ":article_category"=>$category,
        );
        try{
            $stmt->execute($data_array);
            while($row=$stmt->fetch()){
                $this->article_result[]=$row;
            }
        }catch (Exception $e){
            die($e->getMessage());
        }
    }
    
    
    
    public function list_article_by_if_top($count){
        require dirname(__FILE__).'/../config/mydb.config.php';
        $pdo=new PDO($dsn,$mysql_username,$mysql_password);
        $sql="
        SELECT
        `article_id`,
        `article_title`,
        `article_if_top`,
        `article_category`
        FROM
        `h_cube_cms_article_table`
        WHERE
        `article_if_top`='top'
        ORDER BY
        `article_publish_time`
        DESC
        LIMIT $count
        ";
        try{
            $result=$pdo->query($sql);
            while($row=$result->fetch()){
                $this->article_result[]=$row;
            }
        }catch (Exception $e){
            die($e->getMessage());
        }
    }
    
    
    
    public function list_article_by_second_category_part($second_category,$count){
        require dirname(__FILE__).'/../config/mydb.config.php';
        
        $pdo=new PDO($dsn,$mysql_username,$mysql_password);
        
        $sql="
        SELECT
        `article_id`,
        `article_title`,
        `article_publish_time`,
        `article_category`,
        `article_second_category`
        FROM
        `h_cube_cms_article_table`
        WHERE
        `article_second_category`=:article_second_category
        ORDER BY
        `article_publish_time`
        LIMIT $count
        ";
        $stmt=$pdo->prepare($sql);
        $data_array=array(
            ":article_second_category"=>$second_category,
        );
        try{
            $stmt->execute($data_array);
            while($row=$stmt->fetch()){
                $this->article_result[]=$row;
            }
        }catch (Exception $e){
            die($e->getMessage());
        }
    }
    
    
    
    
    
    
    
    
}
//$target_article=new view_article();
//$target_article->query_article();