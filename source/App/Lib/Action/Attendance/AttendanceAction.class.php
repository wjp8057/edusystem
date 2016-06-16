<?php
/**
 * 学生考勤管理
 * @author shencp
 * Date: 14-3-11
 * Time: 下午15:14
 */
class AttendanceAction extends RightAction {
	private $model;
	/**
	 * 构造
	 **/
	public function __construct(){
		parent::__construct();
		$this->model = M("SqlsrvModel:");
	}
	
	/**
	 * 考勤周报表
	 */
	public function report(){
		if($this->_hasJson){
			//添加学生考勤信息
			 $ary=array(":YEAR"=>$_POST["YEAR"],":TERM"=>$_POST["TERM"],":WEEK"=>$_POST["WEEK"],
			 		":DATETIME"=>$_POST["DATETIME"],":STUDENTNO"=>$_POST["STUDENTNO"],
			 		":TIMENO"=>$_POST["TIMENO"],":COURSENO"=>$_POST["COURSENO"],
			 		":TIMENUM"=>$_POST["TIMENUM"],":REASON"=>$_POST["REASON"],
			 		":BREAKTHERULE"=>$_POST["BREAKTHERULE"]);
			 
			 $sql=$this->model->getSqlMap("attendance/insertTimesectors.sql");
	         $bool=$this->model->sqlExecute($sql,$ary);
	         if($bool===false) echo false;
	         else echo true;
		}else{
			//获取当前学年学期
			$data = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
			$this->assign("yearTerm",$data);
			//获取所有上课节次
			$this->assign("timesectors",M("timesectors")->select());
			//获取所有请假理由
			$this->assign("reason",M("学生考勤表请假理由options")->select());
			$this->display();
		}
	}
	
	/**
	 * 学号与课号验证
	 */
	public function validation(){
		if($this->_hasJson){
			//查询课号是否存在
			$courseno=$this->model->sqlFind("SELECT COUNT(*) [COUNT] FROM SCHEDULEPLAN S WHERE S.COURSENO+S.[GROUP] = '".trim($_POST["VALUE"])."'");
			echo $courseno["COUNT"];
		}else{
			//查询学号是否存在
			$studentno=$this->model->sqlFind("SELECT COUNT(*) [COUNT] FROM STUDENTS WHERE STUDENTNO = '".trim($_POST["VALUE"])."'");
			echo $studentno["COUNT"];
		}
	}
	
	/**
	 *考勤情况查询
	 */
	public function query(){
 		//查询学生考勤表
		if($this->_hasJson){
			$bind = array(":YEAR"=>trim($_POST["YEAR"]),":TERM"=>trim($_POST["TERM"]),":STUDENTNO"=>doWithBindStr($_POST["STUDENTNO"]),
					":NAME"=>doWithBindStr($_POST["NAME"]),":COURSENO"=>doWithBindStr($_POST["COURSENO"]),
					":WEEK"=>doWithBindStr($_POST["WEEK"]),":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]));
			$arr = array("total"=>0, "rows"=>array());
			
			$sql = $this->model->getSqlMap("attendance/queryTimesectorsCount.sql");
			$count = $this->model->sqlCount($sql,$bind);
			$arr["total"] = intval($count);
			
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"attendance/queryTimesectorsList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		
		//当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		//所有学院
		$this->assign("school",M("schools")->select());
		//获取所有上课节次
		$timesectors=M("timesectors");
		$time=$timesectors->select();
		$tjson=array();
		//把上课节次数据转成json格式给前台的combobox使用
		foreach($time as $val){
			$jsonMap["text"]=trim($val["VALUE"]);
			$jsonMap["value"]=$val["NAME"];
			array_push($tjson,$jsonMap);
		}
		$tjson=json_encode($tjson);
		$this->assign("tjson",$tjson);
		
		//获取所有请假理由
		$op=M("学生考勤表请假理由options");
		$reason=$op->select();
		$rjson=array();
		//把请假理由数据转成json格式给前台的combobox使用
		foreach($reason as $val){
			$jsonMap["text"]=trim($val["name"]);
			$jsonMap["value"]=$val["code"];
			array_push($rjson,$jsonMap);
		}
		$rjson=json_encode($rjson);
		$this->assign("rjson",$rjson);
		
		//违纪情况设置默认值，格式：json
		$wjson=array();
		$jsonMap["text"]="使用手机";
		$jsonMap["value"]="使用手机";
		array_push($wjson,$jsonMap);
		$jsonMap["text"]="吃东西";
		$jsonMap["value"]="吃东西";
		array_push($wjson,$jsonMap);
		$jsonMap["text"]="讲话";
		$jsonMap["value"]="讲话";
		array_push($wjson,$jsonMap);
		$jsonMap["text"]="打瞌睡";
		$jsonMap["value"]="打瞌睡";
		array_push($wjson,$jsonMap);
		$jsonMap["text"]="其他";
		$jsonMap["value"]="其他";
		array_push($wjson,$jsonMap);
		$wjson=json_encode($wjson);
		$this->assign("wjson",$wjson);
		
		$this->display();
	}
	
	/**
	 * 删除
	 */
	public function del(){
		$newids="";
		foreach($_POST["in"] as $val){
			$newids.="'".$val."',";
		}
		$newids=rtrim($newids,",");
		$sql="delete from 学生考勤表 where recno in ($newids)";
		$row=$this->model->sqlExecute($sql);
		if($row) echo true;
		else echo false;
	}
	
	/**
	 * 修改更新
	 */
	public function update(){
		$courseNo_sql=trim($_POST["COURSENO"]);//课号
		$studentNo_sql=trim($_POST["STUDENTNO"]);//学号
		$timeNo_sql=trim($_POST["TIMENO"]);//节次
		$reason_sql=trim($_POST["REASON"]);//请假理由
		//查询课号是否存在
		$courseno=$this->model->sqlFind("SELECT COUNT(*) [COUNT] FROM SCHEDULEPLAN S WHERE S.COURSENO+S.[GROUP] = '$courseNo_sql'");
		//查询学号是否存在
		$studentno=$this->model->sqlFind("SELECT COUNT(*) [COUNT] FROM STUDENTS WHERE STUDENTNO = '$studentNo_sql'");
		
		if($courseno["COUNT"] <= 0){
			echo -1;
		}else if($studentno["COUNT"] <= 0){
			echo -2;
		}else{
			//开始更新
			$sql="UPDATE [学生考勤表] SET [YEAR]='".trim($_POST["YEAR"])."',TERM='".trim($_POST["TERM"])."',[周数]='"
					.trim($_POST["WEEK"])."',[上课时间]=CAST('".trim($_POST["DATETIME"])."' AS datetime),[学时]=".
					trim($_POST["TIMENUM"]).",[备注]='".trim($_POST["BREAKTHERULE"])."',COURSENO='$courseNo_sql',STUDENTNO='$studentNo_sql'";
			
			if(strlen($timeNo_sql) <= 3){
				$sql=$sql.",[节次]='$timeNo_sql'";
			}
			if(strlen($reason_sql) <= 3){
				$sql=$sql.",[请假理由]='$reason_sql'";
			}
			$sql=$sql." WHERE RECNO = '".trim($_POST["RECNO"])."'";
			
			$bool=$this->model->sqlQuery($sql);
	        if($bool===false) echo false;
	        else echo true;
		}
	}
	
	/**
	 * 考勤统计
	 */
	public function statis(){
		//设置默认数值
		$start=trim($_POST["STARTDATA"])==""?0:trim($_POST["STARTDATA"]);
		$end=trim($_POST["ENDDATA"])==""?50:trim($_POST["ENDDATA"]);
		
		//查询学生考勤表
		if($this->_hasJson){
			$bind = array(":YEAR"=>trim($_POST["YEAR"]),":TERM"=>trim($_POST["TERM"]),":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]),
						":START"=>$start,":END"=>$end);
			$arr = array("total"=>0, "rows"=>array());
				
			$sql = $this->model->getSqlMap("attendance/statisTimesectorsCount.sql");
			$count = $this->model->sqlCount($sql,$bind,true);
			$arr["total"] = intval($count);
				
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"attendance/statisTimesectorsList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		//所有学院
		$this->assign("school",M("schools")->select());
		$this->display();
	}
	
	/**
	 * 统计旷课课时超过该课程三分之一的学生
	 */
	public function statisTimeout(){
		$bind = array(":YEAR"=>trim($_POST["YEAR"]),":TERM"=>trim($_POST["TERM"]),
				":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]),":WEEK"=>doWithBindStr($_POST["WEEK"]));
		$arr = array("total"=>0, "rows"=>array());
		
		$sql = $this->model->getSqlMap("attendance/statisTimeoutTimesectorsCount.sql");
		$count = $this->model->sqlCount($sql,$bind,true);
		$arr["total"] = intval($count);
		
		if($arr["total"] > 0){
			//因参数别名放在where前面导致sql无法执行。于是出此下策
			$sql="SELECT 学生考勤表.STUDENTNO, STUDENTS.NAME STUDENTNAME,CLASSES.CLASSNAME,".
					"SCHOOLS.NAME SCHOOLSNAME,COURSES.COURSENAME,COURSES.HOURS WEEKHOURS,".
					"(COURSES.HOURS * ".$bind[":WEEK"].") HOURS".$this->model->getSqlMap("attendance/statisTimeoutTimesectorsList.sql");
			
			$sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
			$arr["rows"] = $this->model->sqlQuery($sql,$bind);
		}
		$this->ajaxReturn($arr,"JSON");
	}
}