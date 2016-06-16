<?php

class DeferredExamAction extends RightAction {
    private $md;
    protected $message = array("type"=>"info","message"=>"","dbError"=>"");

    public function __construct(){
        parent::__construct();
        $this->md = M("SqlsrvModel:");
    }


    //todo:报名查询的页面
    public function enrollQuery(){
        $this->xiala('schools','schools');
        $this->display();

    }

    //todo:缓考报名的页面
    public function DeferredEnroll(){
        $exam=array('N','D');
        if($this->_hasJson){
             $this->md->startTrans();
           foreach($_POST['bind'] as $val){
               $boolean=$this->md->sqlExecute("update scores set examrem='{$exam[$val['rowr']]}' where recno='{$val['recno']}'");
                if(!$boolean){
                    $this->md->rollback();
                    exit('出错');
                }
           }
                $this->md->commit();
            exit('成功');

        }
        //todo:查询出自己的SCHOOL；
        $teacherSCHOOL=$this->md->sqlfind('select SCHOOL from TEACHERS where RTRIM(TEACHERNO)=:TEACHERNO',array(':TEACHERNO'=>$_SESSION['S_USER_INFO']['TEACHERNO']));
        $this->assign('teacherSCHOOL',$teacherSCHOOL['SCHOOL']);

        $this->xiala('schools','schools');
        $this->display();
    }



    //todo:其他原因缓考
    public function OtherDeferred(){


        $this->display();

    }


}


?>