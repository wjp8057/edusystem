<?php
/**
 * 课程统计
 * @author shencp
 * Date: 14-04-10
 * Time: 下午14:30
 */
class CourseAction extends RightAction {
	private $model;
	/**
	 * 构造
	 **/
	public function __construct(){
		parent::__construct();
		$this->model = M("SqlsrvModel:");
	}
	
	/**
	 * 开课门数统计（课号前7位相同）
	 */
	public function beginSeven(){
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		//当统计条件为空时指定学年学期默认为当前
		$year=trim($_POST["YEAR"])==""?$yearTerm["YEAR"]:trim($_POST["YEAR"]);
		$term=trim($_POST["TERM"])==""?$yearTerm["TERM"]:trim($_POST["TERM"]);
		
		if($this->_hasJson){
			$bind = array(":YEAR"=>$year,":TERM"=>$term,":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]));
			$sql=$this->model->getSqlMap("Statistic/course/queryBeginSevenCount.sql");
			$data = $this->model->sqlFind($sql,$bind);
			$arr["total"] = $data["COUNT"];
			
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/course/queryBeginSevenList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}else{$arr["rows"]=array();}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//所有学院
		$this->assign("school",M("schools")->select());
		$this->display();
	}
	
	/**
	 * 开课门次统计（课号前9位相同）
	 */
	public function beginNine(){
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		//当统计条件为空时指定学年学期默认为当前
		$year=trim($_POST["YEAR"])==""?$yearTerm["YEAR"]:trim($_POST["YEAR"]);
		$term=trim($_POST["TERM"])==""?$yearTerm["TERM"]:trim($_POST["TERM"]);
		
		if($this->_hasJson){
			$bind = array(":YEAR"=>$year,":TERM"=>$term,":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]));
			$sql=$this->model->getSqlMap("Statistic/course/querybeginNineCount.sql");
			$data = $this->model->sqlFind($sql,$bind);
			$arr["total"] = $data["COUNT"];
			
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/course/querybeginNineSevenList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}else{$arr["rows"]=array();}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//所有学院
		$this->assign("school",M("schools")->select());
		$this->display();
	}
	
	/**
	 * 实验课程统计
	 */
	public function experiment(){
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		//当统计条件为空时指定学年学期默认为当前
		$year=trim($_POST["YEAR"])==""?$yearTerm["YEAR"]:trim($_POST["YEAR"]);
		$term=trim($_POST["TERM"])==""?$yearTerm["TERM"]:trim($_POST["TERM"]);
		
		if($this->_hasJson){
			$bind = array(":YEAR"=>$year,":TERM"=>$term,":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]));
			$sql=$this->model->getSqlMap("Statistic/course/queryExperimentCount.sql");
			$data = $this->model->sqlFind($sql,$bind);
			$arr["total"] = $data["COUNT"];
			
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/course/queryExperimentList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}else{$arr["rows"]=array();}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//所有学院
		$this->assign("school",M("schools")->select());
		$this->display();
	}
	
	/**
	 * 实习（设计）课程统计
	 */
	public function design(){
		if($this->_hasJson){
			$bind = array(":S_YEAR"=>$_POST["YEAR"],":S_TERM"=>$_POST["TERM"],":YEAR"=>$_POST["YEAR"],":TERM"=>$_POST["TERM"],":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]));
			$arr = array("total"=>0, "rows"=>array());
			
			$sql = $this->model->getSqlMap("Statistic/course/queryDesignCount.sql");
			$count = $this->model->sqlCount($sql,$bind,true);
			$arr["total"] = intval($count);
			
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/course/queryDesignList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		//所有学院
		$this->assign("school",M("schools")->select());
		$this->display();
	}
	
	/**
	 * 根据学生修课方式统计开课门次
	 */
	public function courses(){
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		//当统计条件为空时指定学年学期默认为当前
		$year=trim($_POST["YEAR"])==""?$yearTerm["YEAR"]:trim($_POST["YEAR"]);
		$term=trim($_POST["TERM"])==""?$yearTerm["TERM"]:trim($_POST["TERM"]);
		
		if($this->_hasJson){
			//根据修课方式学院开课门次统计结果表
			$bind = array(":YEAR"=>$year,":TERM"=>$term,":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]),
					":COURSETYPE"=>doWithBindStr($_POST["COURSETYPE"]));
			$sql=$this->model->getSqlMap("Statistic/course/queryCoursesCount.sql");
			$data = $this->model->sqlFind($sql,$bind);
			$arr["total"] = $data["COUNT"];
				
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/course/queryCoursesList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}else{$arr["rows"]=array();}
			$this->ajaxReturn($arr,"JSON");
			exit; 
		}
		//所 有学院
		$this->assign("school",M("schools")->select());
		//修课方式
		$this->assign("approaches",M("courseapproaches")->select());
		$this->display();
	}
	
	/**
	 * 根据修课方式全校开课门次统计结果表
	 */
	public function getCoursesAll(){
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		//当统计条件为空时指定学年学期默认为当前
		$year=trim($_POST["YEAR"])==""?$yearTerm["YEAR"]:trim($_POST["YEAR"]);
		$term=trim($_POST["TERM"])==""?$yearTerm["TERM"]:trim($_POST["TERM"]);
		
		$bind = array(":YEAR"=>$year,":TERM"=>$term);
		$sql=$this->model->getSqlMap("Statistic/course/queryCoursesAllList.sql");
		$arr["rows"]=$this->model->sqlQuery($sql,$bind);
		$this->ajaxReturn($arr,"JSON");
	}
	
	/**
	 * 根据课程类型统计开课门次
	 */
	public function courseType(){
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		//当统计条件为空时指定学年学期默认为当前
		$year=trim($_POST["YEAR"])==""?$yearTerm["YEAR"]:trim($_POST["YEAR"]);
		$term=trim($_POST["TERM"])==""?$yearTerm["TERM"]:trim($_POST["TERM"]);
		
		if($this->_hasJson){
			//根据修课方式学院开课门次统计结果表
			$bind = array(":YEAR"=>$year,":TERM"=>$term,":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]),
					":NAME"=>doWithBindStr($_POST["NAME"]));
			$sql=$this->model->getSqlMap("Statistic/course/queryCourseTypeCount.sql");
			$data = $this->model->sqlFind($sql,$bind);
			$arr["total"] = $data["COUNT"];
		
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/course/queryCourseTypeList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}else{$arr["rows"]=array();}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//所 有学院
		$this->assign("school",M("schools")->select());
		//修课方式
		$this->assign("coursetype",M("coursetypeoptions")->select());
		$this->display();
	}
	
	/**
	 * 根据修课方式全校开课门次统计结果表
	 */
	public function getCourseTypeAll(){
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		//当统计条件为空时指定学年学期默认为当前
		$year=trim($_POST["YEAR"])==""?$yearTerm["YEAR"]:trim($_POST["YEAR"]);
		$term=trim($_POST["TERM"])==""?$yearTerm["TERM"]:trim($_POST["TERM"]);
	
		$bind = array(":YEAR"=>$year,":TERM"=>$term);
		$sql=$this->model->getSqlMap("Statistic/course/queryCourseTypeAllList.sql");
		$arr["rows"]=$this->model->sqlQuery($sql,$bind);
		$this->ajaxReturn($arr,"JSON");
	}
}