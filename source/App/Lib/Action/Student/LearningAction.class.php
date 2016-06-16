<?php
/**
 * 我的学业
 * User: educk
 * Date: 13-12-25
 * Time: 下午3:36
 */
class LearningAction extends RightAction {
    private $model;

    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");
    }

    /**
     * 我的教材
     */
    public function mybooks(){
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());
            $bind = $this->model->getBind("STUDENTNO",array(session("S_USER_NAME")));
            $sql = $this->model->getSqlMap("Learning/myBooks.sql");
            $count = $this->model->sqlCount($sql, $bind ,true);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql, $bind);
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $this->display("mybooks");
    }
    /**
     * 我的教材 (新加)
     */
    public function mybooks_new(){
    	if($this->_hasJson){
    		$json = array("total"=>0, "rows"=>array());
    		$bind = $this->model->getBind("STUDENTNO",array(session("S_USER_NAME")));
    		$sql = $this->model->getSqlMap("Learning/myBooks_new.sql");
    		$count = $this->model->sqlCount($sql, $bind ,true);
    		$json["total"] = intval($count);
    
    		if($json["total"]>0){
    			$sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
    			$json["rows"] = $this->model->sqlQuery($sql, $bind);
    		}
    		$this->ajaxReturn($json,"JSON");
    		exit;
    	}
    	$this->display("mybooks_new");
    }
    /**
     * {ACT]培养方案
     */
    public function  program(){
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());
            if(isset($_REQUEST["SCHOOLCODE"])){
                $bind = $this->model->getBind("SCHOOL",array($_REQUEST["SCHOOLCODE"]));
                $sql = $this->model->getSqlMap("Learning/studentQueryProgram.sql");
            }else{
                $bind = $this->model->getBind("STUDENTNO",array(session("S_USER_NAME")));
                $sql = $this->model->getSqlMap("Learning/studentr28.sql");
            }
            $count = $this->model->sqlCount($sql, $bind ,true);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql, $bind);
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $this->display("program");
    }

    /**
     * {ACT]培养方案详细列表
     */
    public function programDetail(){
        $json = array("total"=>0, "rows"=>array());
        $bind = $this->model->getBind("PROGRAMNO",array($_REQUEST['programNo']));
        $json["rows"] = $this->model->sqlQuery($this->model->getSqlMap("Learning/programCourse.sql"), $bind);
        $json["total"] = count($json["rows"]);

        for($i=0; $i<$json["total"]; $i++){
            $json["rows"][$i]["WEEKS"] = decbin($json["rows"][$i]["WEEKS"]);
        }
        $this->ajaxReturn($json,"JSON");
        exit;
    }

    /**
     * 学籍信息
     */
    public function student(){
        $data = $this->model->sqlFind($this->model->getSqlMap("Learning/statusQueryStudent.sql"),array(":STUDENTNO"=>session("S_USER_NAME")));
        $list = $this->model->sqlFind($this->model->getSqlMap("Learning/queryStudentRegistry.sql"),array(":STUDENTNO"=>session("S_USER_NAME")));
        $this->assign("info",$data);
        $this->assign("list",$list);
        $this->display("student");
    }

    /**
     * [ACT]等级考试
     */
    public function level(){
    	if($this->_hasJson){
    		$json = array("total"=>0, "rows"=>array());
    		$bind = $this->model->getBind("STUDENTNO",array(session("S_USER_NAME")));
    		
    		$sql = $this->model->getSqlMap("exam/level_query.sql");
    		$count = $this->model->sqlCount($sql, $bind ,true);
    		$json["total"] = intval($count);
    		
    		if($json["total"]>0){
    			$order=" ORDER BY TYPE,[YEAR] DESC,[MONTH] DESC";
    			$sql = $this->model->getPageSql($sql.$order,null, $this->_pageDataIndex, $this->_pageSize);
    			$json["rows"] = $this->model->sqlQuery($sql, $bind);
    		}
    		$this->ajaxReturn($json,"JSON");
    		exit;
    	}
        $this->display("level");
    }

}