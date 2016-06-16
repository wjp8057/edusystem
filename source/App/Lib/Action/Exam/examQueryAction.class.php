<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-1
 * Time: 下午3:07
 */
class examQueryAction extends RightAction {
    private $md;
    protected $message = array("type"=>"info","message"=>"","dbError"=>"");

    public function __construct(){
        parent::__construct();
        $this->md = M("SqlsrvModel:");
    }

    //todo:统一排考的页面
    public function examCourses(){
        $this->xiala('schools','schools');
        $this->display();
    }

    //todo:统一排考页面---->提交到数据库的方法
    public function insertPaikao(){
        $this->md->startTrans();
        foreach($_POST['bind'] as $val){
            $panduan=$this->md->sqlExecute("update SCHEDULEPLAN set exam={$val['exam']} where recno='{$val['recno']}'");
            if(!$panduan){
                $this->md->rollback();
                exit($val['recno']+'该条数据有误,请检查。');
            }
        }
        $this->md->commit();
        exit('已成功完成更新，请返回后刷新页面以获得最新数据！');

    }





}

?>