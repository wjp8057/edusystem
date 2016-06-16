<?php
/**
 * 选课、退课模块
 * User: educk
 * Date: 13-12-25
 * Time: 下午2:59
 */
class CourseAction extends RightAction {
    private $model;
    private $_array = array(1=>"MON","TUE","WES","THU","FRI","SAT","SUN");

    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");
    }



    /**
     * [ACT]没选上的课
     */
    public function dump(){
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());
            $bind = $this->model->getBind("YEAR,TERM,STUDENTNO",array(intval($_REQUEST["YEAR"]), intval($_REQUEST["TERM"]), session("S_USER_NAME")));
            $sql = $this->model->getSqlMap("course/studentDump.sql");
            $count = $this->model->sqlCount($sql, $bind ,true);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql, $bind);
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $this->display();
    }

    /**
     * {ACT]有空的公选课
     */
    public function free(){
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());
            $bind = $this->model->getBind("R32YEAR,R32TERM,YEAR,TERM",array(intval($_REQUEST["YEAR"]), intval($_REQUEST["TERM"]),intval($_REQUEST["YEAR"]), intval($_REQUEST["TERM"])));
            $sql = $this->model->getSqlMap("course/freeCourse.sql");
            $count = $this->model->sqlCount($sql, $bind ,true);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql, $bind);
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $this->assign('year_term',$this->model->sqlFind("select * from YEAR_TERM WHERE TYPE='C'"));        //TODO:year_term
        $this->xiala('schools','schools');
        $this->display();
    }


}