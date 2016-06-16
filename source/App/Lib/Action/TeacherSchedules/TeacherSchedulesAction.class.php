<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 13-12-2
 * Time: 下午3:01
 **/
class TeacherSchedulesAction extends RightAction
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
        $this->base='TeacherSchedules/';
    }

    public function queryTeacher(){
        $this->assign('teacherno',$_SESSION['S_USER_NAME']);
       /* dump($_SESSION);
        exit;*/

        $this->xiala('schools','schools');
        $this->assign("yearTerm",$this->md->sqlFind("select * from YEAR_TERM where [TYPE]='O'"));
        $this->display();

    }







}

