<?php
/**
 * 成绩模块
 * User: educk
 * Date: 13-12-27
 * Time: 上午8:20
 */
class ResultAction extends RightAction {
    private $model;

    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");
    }

    /**
     * [ACT]课程成绩
     */
    public function  qlist(){
        $this->display("qlist");
    }

    /**
     * 分学认定
     */
    public function account(){
        $this->display("account");
    }

}