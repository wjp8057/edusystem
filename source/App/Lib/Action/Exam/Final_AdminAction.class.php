<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-1
 * Time: 下午3:07
 */
class Final_adminAction extends RightAction {
    private $model;
    protected $message = array("type"=>"info","message"=>"","dbError"=>"");

    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");
    }

    //todo:统一排考的页面
    public function Tongyi_paikao(){
        $this->xiala('schools','schools');
        $this->display();
    }
}

?>