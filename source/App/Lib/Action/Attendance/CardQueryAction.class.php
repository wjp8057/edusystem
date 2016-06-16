<?php
/**
 * 刷卡考勤查询
 * @author shencp
 * Date: 14-3-11
 * Time: 下午15:03
 */
class CardQueryAction extends RightAction {
	private $model;
	/**
	 * 构造
	 **/
	public function __construct(){
		parent::__construct();
		$this->model = M("SqlsrvModel:");
	}
	
	/**
	 * 考勤详单查询
	 */
	public function query(){
		//查询开始
		if($this->_hasJson){
			$bind = array(":YEAR"=>trim($_POST["YEAR"]),":TERM"=>trim($_POST["TERM"]),":SCHOOL"=>trim($_POST["SCHOOL"]),
				":STUDENTNO"=>doWithBindStr($_POST["STUDENTNO"]),":DAY"=>doWithBindStr($_POST["DAY"]),
				":TIME"=>doWithBindStr($_POST["TIME"]),":TYPE"=>doWithBindStr($_POST["TYPE"]),":COURSENO"=>doWithBindStr($_POST["COURSENO"]));
			$arr = array("total"=>0, "rows"=>array());
			
			$sql=$this->model->getSqlMap("attendance/cardQuery/queryKqabsentCount.sql");
			$count = $this->model->sqlCount($sql,$bind);
			$arr["total"] = intval($count);
			
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"attendance/cardQuery/queryKqabsentList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		//获取所有上课节次
		$this->assign("timesectors",M("timesectors")->select());
		//所有学院
		$this->assign("school",M("schools")->select());
		
		$this->display();
	}
	/**
	 * 考勤详单查询--根据课号查询
	 */
	public function queryByCourseNo(){
		$type=trim($_POST["TYPE"])=="到课"?"D":"A";
		$bind = array(":YEAR"=>trim($_POST["YEAR"]),":TERM"=>trim($_POST["TERM"]),":COURSENO"=>trim($_POST["COURSENO"]),":TYPE"=>$type);
		$arr = array("total"=>0, "rows"=>array());
			
		$sql=$this->model->getSqlMap("attendance/cardQuery/queryByCourseNoCount.sql");
		$count = $this->model->sqlCount($sql,$bind);
		$arr["total"] = intval($count);
		
		if($arr["total"] > 0){
			$sql = $this->model->getPageSql(null,"attendance/cardQuery/queryByCourseNoList.sql", $this->_pageDataIndex, $this->_pageSize);
			$arr["rows"] = $this->model->sqlQuery($sql,$bind);
		}
		$this->ajaxReturn($arr,"JSON");
	}
	
	/**
	 * 根据课号查询课程名称、教师及选课人数
	 */
	public function queryCourse(){
		$bind = array(":YEAR"=>trim($_POST["YEAR"]),":TERM"=>trim($_POST["TERM"]),
				":COURSENO"=>trim($_POST["COURSENO"]));
		
		$sql=$this->model->getSqlMap("attendance/cardQuery/queryCourse.sql");
		$data = $this->model->sqlFind($sql,$bind);
		
		if($data!=null){
			echo json_encode($data);
		}
	}
	
	/**
	 * 课程考勤查询
	 */
	public function course(){
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		
		$this->display();
	}
	
	/**
	 * 考勤汇总查询
	 */
	public function summary(){
		//当查询条件为空时指定学年学期默认为当前
		$count=trim($_POST["COUNT"])==""?30:trim($_POST["COUNT"]);
		//查询开始
		if($this->_hasJson){
			$bind = array(":YEAR"=>trim($_POST["YEAR"]),":TERM"=>trim($_POST["TERM"]),":COUNT"=>$count);
			$arr = array("total"=>0, "rows"=>array());
			
			$sql=$this->model->getSqlMap("attendance/cardQuery/summaryKqabsentCount.sql");
			$count = $this->model->sqlCount($sql,$bind);
			$arr["total"] = intval($count);
				
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"attendance/cardQuery/summaryKqabsentList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		$this->display();
	}
}