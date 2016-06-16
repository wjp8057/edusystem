<?php
/**
 * 系统首页
 * User: cwebs
 * Date: 13-11-23
 * Time: 上午8:47
 */
class IndexAction extends RightAction {
    /**
     * 成绩管理首页
     */
    private $model;

    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");
    }

    public function index(){
        $this->assign("yearTerm",$this->model->sqlFind("select * from YEAR_TERM where [TYPE]='J'"));
        $this->display();
    }

    public function cjfx(){
        $this->index();
    }

}