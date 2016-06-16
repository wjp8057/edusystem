<?php

//todo:班级选课管理
class course_countAction extends RightAction
{

    private $md;        //存放模型对象
    private $base;      //路径

    public function __construct(){
        parent::__construct();
        $this->md=new Sqlsrvmodel();
        $this->base='course_count/';
    }


    public function one(){
        $this->xiala('schools','schools');
        $this->display();
    }

    public function two(){
        $this->xiala('schools','schools');
        $this->display();
    }

    public function three(){
        $this->xiala('schools','schools');
        $this->display();
    }

    public function four(){
        $this->display();
    }

    public function five(){
        $this->xiala('schools','schools');
        $this->display();
    }

    public function six(){
        $this->xiala('schools','schools');
        $this->display();
    }

    public function seven(){
        $this->xiala('schools','schools');
        $this->display();
    }






}