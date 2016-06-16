<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 13-12-2
 * Time: 下午3:01
 **/
class BookAction extends RightAction
{
    private $md;        //存放模型对象
    private $base;      //路径
    /**
     *  班级管理
     *
     **/
    public function __construct(){
        parent::__construct();
        $this->md=new SqlsrvModel();
        $this->base='Book/';
    }



    //todo:测试执行
    public function Bexecute(){

        switch($_POST[':ID']){
            case 'EXE':
                if(isset($_POST['bind'][':date']))$_POST['bind'][':date']=date('Y-m-d H:i:s');
                $content=$this->md->sqlExecute($this->md->getSqlMap($_POST['exe']),$_POST['bind']);
                break;
            case 'QUERY':
                $content=$this->md->sqlQuery($this->md->getSqlMap($_POST['exe']),$_POST['bind']);
                break;
            case 'EXE2':
                $content=$this->md->sqlExecute($_POST['exe'],$_POST['bind']);
                break;
        }
        echo json_encode($content);
    }

    
    









}
?>
