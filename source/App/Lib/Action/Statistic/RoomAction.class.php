<?php
/**
 * 数据统计 --教室统计
 * @author shencp
 * Date: 14-06-19
 * Time: 上午13:32
 */
class RoomAction extends RightAction {
	private $model;
	/**
	 * 构造
	 **/
	public function __construct(){
		parent::__construct();
		$this->model = M("SqlsrvModel:");
	}
	
	/**
	 * 查看教室利用率（按每周50学时计算）
	 */
	public function utilization_5(){
		if($this->_hasJson){
			$bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
					":seats_gt"=>trim($_POST["seats_gt"]),":seats_lt"=>trim($_POST["seats_lt"]),
					":jsn"=>doWithBindStr($_POST["jsn"]),":area"=>doWithBindStr($_POST["area"]),
					":roomno"=>doWithBindStr($_POST["roomno"]),":equipment"=>doWithBindStr($_POST["equipment"]),
					":code"=>doWithBindStr($_POST["code"]));
			
			$arr = array("total"=>0, "rows"=>array());
			
			$sql = $this->model->getSqlMap("Statistic/Room/queryUtilization_5Count.sql");
			$count = $this->model->sqlCount($sql,$bind,true);
			$arr["total"] = intval($count);
			
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/Room/queryUtilization_5List.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		//是否可用
		$this->assign("roomstatus",M("roomstatus")->select());
		//校区
		$this->assign("areas",M("areas")->select());
		//设施
		$this->assign("roomoptions",M("roomoptions")->select());
		
		$this->display();
	}
	
	/**
	 * 查看教室利用率（按每周40学时计算）
	 */
	public function utilization_4(){
		if($this->_hasJson){
			$bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
					":seats_gt"=>trim($_POST["seats_gt"]),":seats_lt"=>trim($_POST["seats_lt"]),
					":jsn"=>doWithBindStr($_POST["jsn"]),":area"=>doWithBindStr($_POST["area"]),
					":roomno"=>doWithBindStr($_POST["roomno"]),":equipment"=>doWithBindStr($_POST["equipment"]),
					":code"=>doWithBindStr($_POST["code"]));
			
			$arr = array("total"=>0, "rows"=>array());
			
			$sql = $this->model->getSqlMap("Statistic/Room/queryUtilization_4Count.sql");
			$count = $this->model->sqlCount($sql,$bind,true);
			$arr["total"] = intval($count);
			
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/Room/queryUtilization_4List.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		//是否可用
		$this->assign("roomstatus",M("roomstatus")->select());
		//校区
		$this->assign("areas",M("areas")->select());
		//设施
		$this->assign("roomoptions",M("roomoptions")->select());
		
		$this->display();
	}
	
	/**
	 * 合计
	 */
	public function sum(){
		$type=trim($_POST["type"]);
		$bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
				":seats_gt"=>trim($_POST["seats_gt"]),":seats_lt"=>trim($_POST["seats_lt"]),
				":jsn"=>doWithBindStr($_POST["jsn"]),":area"=>doWithBindStr($_POST["area"]),
				":roomno"=>doWithBindStr($_POST["roomno"]),":equipment"=>doWithBindStr($_POST["equipment"]),
				":code"=>doWithBindStr($_POST["code"]));
		
		$sum=$this->model->sqlFind($this->model->getSqlMap("Statistic/Room/queryUtilization_".$type."_sum.sql"),$bind);
		echo json_encode($sum);
	}
	
	/**
	 * 综合统计
	 */
	public function synthesize(){
		if($this->_hasJson){
			$bind = array(":YEAR"=>$_POST["YEAR"],":TERM"=>$_POST["TERM"],
					":COURSENO"=>doWithBindStr($_POST["COURSENO"]),
					":COURSENAME"=>doWithBindStr($_POST["COURSENAME"]),
					":COURSETYPE"=>doWithBindStr($_POST["COURSETYPE"]),
					":NAME"=>doWithBindStr($_POST["NAME"]),
					":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]));
			$arr = array("total"=>0, "rows"=>array());
			
			$sql = $this->model->getSqlMap("Statistic/Room/querySynthesizeCount.sql");
			$count = $this->model->sqlCount($sql,$bind);
			$arr["total"] = intval($count);
			
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/Room/querySynthesizeList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//获取当前学年学期roomoptions
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		//学院
		$this->assign("school",M("schools")->select());
		//课程类别
		$this->assign("approaches",M("courseapproaches")->select());
		//教室设施
		$this->assign("options",M("roomoptions")->select());
		$this->display();
	}
}