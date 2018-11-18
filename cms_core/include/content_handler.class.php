<?php
/**
 *本文件用于处理CMS系统文章
 *@date:2017年11月7日 下午10:39:28
 *@author:JackieHan<gogoend@qq.com>
 */
header("Content-type:text/html;charset=utf-8");
class article
{
    protected $title;
    protected $from;
    protected $author;
    protected $top_article;
    protected $favor_article;
    protected $private_article;
    protected $comment_article;
    protected $lang;
    protected $content_code;
    protected $publish_time;
    protected $category;
    protected $status;
    protected $licence;
    protected $second_category;
    protected function get_time(){
        date_default_timezone_set("Asia/Shanghai");//设置时区
        return $this->publish_time=date("Y-m-d H:i:s");
    }
    public function preview_submit(){
        $this->publish_time=$this->get_time();
        $this->title=$_POST['article_title'];
        $this->category=$_POST['article_category'];
        if($_POST['article_second_category']!=null)
        {
        $this->second_category=$_POST['article_second_category'];
        }
        if (isset($_POST['article_from'])&&$_POST['article_from']!='') {
            $this->from=$_POST['article_from'];
        }
        if (isset($_POST['article_author'])&&$_POST['article_author']!='') {
            $this->author=$_POST['article_author'];
        }
        if (isset($_POST['if_top_article'])) {
            $this->top_article=$_POST['if_top_article'];
        }
        if (isset($_POST['if_favor_article'])) {
            $this->favor_article=$_POST['if_favor_article'];
        }
        if (isset($_POST['if_private_article'])) {
            $this->private_article=$_POST['if_private_article'];
        }
        if (isset($_POST['if_no_comment'])) {
            $this->comment_article='false';
        }else{
            $this->comment_article='true';
        }
        $this->lang= $_POST['article_lang'];
        $this->licence=$_POST['article_licence'];
        $this->content_code=$_POST['article_content_code'];
        $this->status="published";
    }

    public function insert_into_mydb() {
        require dirname(__FILE__).'/../config/mydb.config.php';
        $pdo = new PDO($dsn,$mysql_username,$mysql_password);
        $sql='INSERT INTO h_cube_cms_article_table (
            article_title,
            article_author_id,
            article_from,
            article_content,
            article_publish_time,
            article_status,
            article_category,
            article_second_category,
            article_lang,
            article_licence,
            article_if_top,
            article_if_favor,
            article_if_private,
            article_if_comment
            )  VALUES (
            :article_title,
            :article_author_id,
            :article_from,
            :article_content,
            :article_publish_time,
            :article_status,
            :article_category,
            :article_second_category,
            :article_lang,
            :article_licence,
            :article_if_top,
            :article_if_favor,
            :article_if_private,
            :article_if_comment
            )';
		$stmt=$pdo->prepare($sql);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $data_array=array(
		    ":article_title"=>$this->title,
		    ":article_author_id"=>$this->author,
		    ":article_from"=>$this->from,
		    ":article_content"=>$this->content_code,
		    ":article_publish_time"=>$this->publish_time,
		    ":article_status"=>$this->status,
		    ":article_category"=>$this->category,
            ":article_second_category"=>$this->second_category,
		    ":article_lang"=>$this->lang,
            ":article_licence"=>$this->licence,
		    ":article_if_top"=>$this->top_article,
		    ":article_if_favor"=>$this->favor_article,
		    ":article_if_private"=>$this->private_article,
            ":article_if_comment"=>$this->comment_article
		);
        try{
		$stmt->execute($data_array);
		$pdo=null;
        }catch(PDOException $e){
			    die ($e->getMessage());
			}
        
    }
}