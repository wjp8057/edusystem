<?php
/**
 * 教材管理首页
 * User: shencp
 * Date: 14-04-21
 * Time: 下午15:30
 */
class IndexAction extends RightAction {
    private $model;
    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");
    }
    /**
     * 教材管理首页
     */
    public function index(){
        $this->assign("yearTerm",$this->model->sqlFind("select * from YEAR_TERM where [TYPE]='C'"));
        $this->display();
    }
}