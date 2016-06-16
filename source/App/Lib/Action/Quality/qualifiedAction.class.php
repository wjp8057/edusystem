<?php
/*
 * 合格课程模块
 */
class qualifiedAction extends RightAction{
    private $md;
    private $base;
    public function __construct(){
        parent::__construct();
        $this->md=new SqlsrvModel();
        $this->base='kaoping/';
    }

    /*
     * 合格课程页面
     */
    public function qualifiedCourse(){
        if($this->_hasJson){
            $total=$this->md->sqlFind($this->md->getSqlMap($this->base.'Four_countQualifiedCourse.SQL'),array(':year'=>$_POST['year'],':term'=>$_POST['term']));
            if($arr['total']=$total[''])
                $arr['rows']=$this->md->sqlQuery($this->md->getSqlMap($this->base.'Four_selectQualifiedCourse.SQL'),array(':year'=>$_POST['year'],':term'=>$_POST['term'],':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize));
            else
                $arr['rows']=array();
            echo json_encode($arr);
            exit;
        }
        $this->assign('yearterm',$this->md->sqlFind($this->md->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C")));
            $this->display();
    }

    /*
     *明细用到的方法
     */
    public function mingxiList(){
        if($this->_hasJson){
            $total=$this->md->sqlFind($this->md->getSqlMap($this->base.'Four_countMingxi.SQL'),array(':recno'=>$_POST['recno']));
            if($arr['total']=$total[''])
                $arr['rows']=$total=$this->md->sqlquery($this->md->getSqlMap($this->base.'Four_selectMingxi.SQL'),array(':recno'=>$_POST['recno'],':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize));
            else
                $arr['rows']=array();
                $this->ajaxReturn($arr,'JSON');
            exit;
        }
        $coursename=$this->md->sqlFind($this->md->getSqlMap($this->base.'Four_CourseName.SQL'),array(':recno'=>$_POST['recno']));
        $teachername=$this->md->sqlFind($this->md->getSqlMap($this->base.'Four_CourseTeacher.SQL'),array(':recno'=>$_POST['recno']));
            echo "<p align='center'> <b>课名：{$coursename['coursename']}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;任课教师：{$teachername['teacher']}</b><p>";
    }

    /*
     * 删除明细&&明细平均分
     */
    public function deleteMingxi(){
        if($this->_hasJson){
            $total['rows']=$this->md->sqlQuery($this->md->getSqlMap($this->base.'Four_pingjunMingxi.SQL'),array(':recno'=>$_POST['recno']));
            $total['total']=1;
            $this->ajaxReturn($total,'JSON');
            exit;
        }
        $bool=$this->md->sqlExecute($this->md->getSqlMap($this->base.'Four_deleteMingxi.SQL'),array(':recno'=>$_POST['recno']));
        var_dump($bool);
    }

    public function insertMingxi(){
        $bool=$this->md->sqlExecute($this->md->getSqlMap($this->base.'Four_insertMingxi.SQL'),array(':map'=>$_POST['recno'],':one'=>$_POST['a1'],':two'=>$_POST['a2'],':three'=>$_POST['a3'],':four'=>$_POST['a4'],':five'=>$_POST['a5'],':six'=>$_POST['a6'],':seven'=>$_POST['a7'],':eight'=>$_POST['a8'],':nine'=>$_POST['a9'],':ten'=>$_POST['a10'],':total'=>$_POST['total']));
        if($bool)
            echo '插入成功';
    }

}

?>