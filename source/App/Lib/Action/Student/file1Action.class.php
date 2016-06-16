<?php
/**
 * 考试模块
 * User: educk
 * Date: 13-12-25
 * Time: 下午2:59
 */
class file1Action extends RightAction {
    private $model;

    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");
    }
    public function xkxz2014(){
        $this->display();

    }
    public function xiazai(){
       // echo pathinfo();
            $file_dir='../APP/Tpl/Student/file1/';

        $file = fopen($file_dir.$_GET['name'].'.'.$_GET['type'],"r");

        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: ".filesize($file_dir .$_GET['name'].'.'.$_GET['type']));
        Header("Content-Disposition: attachment; filename=".$_GET['name'].'.'.$_GET['type']);
        echo fread($file, filesize($file_dir.$_GET['name'].'.'.$_GET['type']));
        fclose($file);
        exit;
           echo $file_dir.$_GET['name'].'.'.$_GET['type'];
            if (!file_exists($file_dir.$_GET['name'].'.'.$_GET['type'])){
                header("Content-type: text/html; charset=utf-8");
                echo "File not found!";
                exit;
            } else {
                $file = fopen($file_dir.$_GET['name'].'.'.$_GET['type'],"r");
                Header("Content-type: application/octet-stream");
                Header("Accept-Ranges: bytes");
                Header("Accept-Length: ".filesize($file_dir .$_GET['name'].'.'.$_GET['type']));
                Header("Content-Disposition: attachment; filename=".$_GET['name'].'.'.$_GET['type']);
                echo fread($file, filesize($file_dir.$_GET['name'].'.'.$_GET['type']));
                fclose($file);
            }

    }


}