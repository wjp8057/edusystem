<?php
/**
 * 教学档案
 * @author shencp
 * Date: 14-04-03
 * Time: 上午09:32
 */
class ArchiveAction extends RightAction {
	private $model;
	/**
	 * 构造
	 **/
	public function __construct(){
		parent::__construct();
		$this->model = M("SqlsrvModel:");
	}
	
	/**
	 * 添加新教师
	 */
	public function add(){
		if($this->_hasJson){
			//获取添加新教师信息
			 $ary=array(":NAME"=>$_POST["NAME"],":TEACHERNO"=>$_POST["TEACHERNO"],
			 		":POSITION"=>$_POST["POSITION"],":SCHOOL"=>$_POST["SCHOOL"],
			 		":SEX"=>$_POST["SEX"],":YEAR"=>$_POST["YEAR"],
			 		":TYPE"=>$_POST["TYPE"],":USERNAME"=>$_POST["TEACHERNO"],
			 		":PWD"=>$_POST["PWD"],":TEACHER_NO"=>$_POST["TEACHERNO"]);
			 //开始添加
			 $sql=$this->model->getSqlMap("Archive/insertTeacher.sql");
	         $bool=$this->model->sqlExecute($sql,$ary);
	         if($bool===false) echo false;
	         else echo true;
		}else{
			//获取当前学年
			$year = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
			$this->assign("year",$year["YEAR"]);
			//获取教师类型
			$this->assign("teachertype",M("teachertype")->select());
			//获取教师职称
			$this->assign("position",M("positions")->select());
			//所有学院
			$this->assign("school",M("schools")->select());
			//性别
			$sexcode = M("sexcode")->query("select rtrim(code) code,rtrim(name) name from __TABLE__");
			$this->assign("sex",$sexcode);
			//当前登录用户所在学院
			$teacherNo=$_SESSION["S_USER_INFO"]["TEACHERNO"];
			$user_school = $this->model->sqlFind("SELECT T.SCHOOL FROM TEACHERS T WHERE T.TEACHERNO='$teacherNo'");
			$this->assign("user_school",$user_school["SCHOOL"]);
			
			$this->display();
		}
	}
	
	/**
	 * 教师号验证
	 */
	public function validation(){
		if($this->_hasJson){
			//查询教师号是否存在
			$teacherno=$this->model->sqlFind("SELECT COUNT(*) [COUNT] FROM TEACHERS T WHERE T.TEACHERNO='".trim($_POST["VALUE"])."'");
			echo $teacherno["COUNT"];
		}
	}
	
	/**
	 * 教师查询
	 */
	public function query(){
		if($this->_hasJson){
			$bind = array(":TEACHERNO"=>doWithBindStr($_POST["TEACHERNO"]),
					":NAME"=>doWithBindStr($_POST["NAME"]),":SEX"=>$_POST["SEX"],
					":POSITION"=>$_POST["POSITION"],":SCHOOL"=>$_POST["SCHOOL"]); 
			$arr = array("total"=>0, "rows"=>array());

			$sql = $this->model->getSqlMap("Archive/queryTeacherCount.sql");
			$count = $this->model->sqlCount($sql,$bind);
			$arr["total"] = intval($count);
				
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Archive/queryTeacherList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
            //var_dump($bind);
            //echo $this->model->getLastSql();
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//所有学院
		$this->assign("school",M("schools")->select());

		//性别，格式：json
		//性别
		$sexcode = M("sexcode")->query("select rtrim(code) code,rtrim(name) name from __TABLE__");
		$this->assign("sex",$sexcode);
		$sex=array();
		//性别数据转成json格式给前台的combobox使用
		foreach($sexcode as $val){
			$jsonMap["text"]=trim($val["name"]);
			$jsonMap["value"]=$val["code"];
			array_push($sex,$jsonMap);
		}
		$this->assign("sjson",json_encode($sex));

		//获取教师职称
		$position=M("positions")->select();
		$this->assign("position",$position);
		$pjson=array();
		//把教师职称数据转成json格式给前台的combobox使用
		foreach($position as $val){
			$jsonMap["text"]=trim($val["VALUE"]);
			$jsonMap["value"]=$val["NAME"];
			array_push($pjson,$jsonMap);
		}
		$this->assign("pjson",json_encode($pjson));
		
		//获取教师类型
		$teachertype=M("teachertype")->select();
		$tjson=array();
		//把教师类型据转成json格式给前台的combobox使用
		foreach($teachertype as $val){
			$jsonMap["text"]=trim($val["VALUE"]);
			$jsonMap["value"]=$val["NAME"];
			array_push($tjson,$jsonMap);
		}
		$this->assign("tjson",json_encode($tjson));
		
		//当前用户所在学院
		$teacherNo=$_SESSION["S_USER_INFO"]["TEACHERNO"];
		$user_school = $this->model->sqlFind("SELECT T.SCHOOL FROM TEACHERS T WHERE T.TEACHERNO='$teacherNo'");
		$this->assign("user_school",$user_school["SCHOOL"]);
		
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
		$sql="delete from teachers where teacherno in($newids);delete from users where teacherno in($newids)";
		$row=$this->model->sqlExecute($sql);
		if($row) echo true;
		else echo false;
	}
	
	/**
	 * 修改更新
	 */
	public function update(){
		if($this->_hasJson){
            $shuju=new SqlsrvModel();
			$sex_sql=trim($_POST["SEX"]);//性别
			$position_sql=trim($_POST["POSITION"]);//职称
			$type_sql=trim($_POST["TYPE"]);//类型
			//开始更新
			$sql="UPDATE TEACHERS SET NAME='".trim($_POST["NAME"])."'";


				
			if(strlen($sex_sql) < 3){
				$sql=$sql.",SEX='$sex_sql'";
			}
			if(strlen($position_sql) < 3){
				$sql=$sql.",POSITION='$position_sql'";
			}
			if(strlen($type_sql) < 3){
				$sql=$sql.",TYPE='$type_sql'";
			}
			$sql=$sql." WHERE TEACHERNO = '".trim($_POST["TEACHERNO"])."'";

          // $this->model->sqlExecute("update teachers set name='{$_POST["NAME"]}',SEX='{$_POST["SEX"]}',POSITION='{$_POST["POSITION"]}',TYPE='{$_POST["TYPE"]}' where TEACHERNO={}");
			$bool=$shuju->SqlExecute($sql);
			if($bool===false) echo false;
			else echo true;
		}else{
			//修改口令
			$pwd=$_POST['PWD'];$no=$_POST['TEACHERNO'];
			$sql="UPDATE USERS SET PASSWORD = '$pwd' WHERE TEACHERNO = '$no'";
			$bool=$this->model->sqlQuery($sql);
			if($bool===false) echo false;
			else echo true;
		}
	}
	
	/**
	 * 编辑详细页面
	 */
	public function edit(){
		if($this->_hasJson){
			//更新教师基本信息
			$Birth=trim($_POST["Birth"])==""?null:trim($_POST["Birth"]);
			$HDate=trim($_POST["HDate"])==""?null:trim($_POST["HDate"]);
			$PDate=trim($_POST["PDate"])==""?null:trim($_POST["PDate"]);
			$teacherno=doWithBindStr($_POST["TEACHERNO"]);
			
			$bind=array(":SEX"=>trim($_POST["SEX"]),":Birth"=>$Birth,":ID"=>trim($_POST["ID"]),
			":Nationality"=>trim($_POST["Nationality"]),":Party"=>trim($_POST["Party"]),":Department"=>trim($_POST["Department"]),
			":HeadShip"=>trim($_POST["HeadShip"]),":HDate"=>$HDate,":Profession"=>trim($_POST["Profession"]),
			":PDate"=>$PDate,":PSubject"=>trim($_POST["PSubject"]),":EduLevel"=>trim($_POST["EduLevel"]),
			":ESchool"=>trim($_POST["ESchool"]),":Degree"=>trim($_POST["Degree"]),":DSchool"=>trim($_POST["DSchool"]),
			":Tel"=>trim($_POST["Tel"]),":Email"=>trim($_POST["Email"]),":TEACHERNO"=>$teacherno);
			
			if($teacherno != null && $teacherno != ""){
				$sql=$this->model->getSqlMap("Archive/updateTeacher.sql");
				$row=$this->model->sqlExecute($sql,$bind);
				if($row) echo true;
				else echo false;
			}
		}else{
			$teacherno=iconv("gb2312","utf-8",trim($_GET["TEACHERNO"]));
			//教师详情相关数据查询
			$this->assign("national",M("nationalitycode")->select());//民族
			$this->assign("partycode",M("partycode")->select());//政治面貌
			$this->assign("pro",M("professioncode")->select());//现任职称
			$this->assign("edu",M("edulevelcode")->select());//最高学历
			$this->assign("deg",M("degreecode")->select());//最高学位
			$this->assign("honorl",M("honorlevelcode")->select());//获奖级别
			$sexcode = M("sexcode")->query("select rtrim(code) code,rtrim(name) name from __TABLE__");//性别
			$this->assign("sex",$sexcode);
			
			if($teacherno != null && $teacherno != ""){
				//获取教师基本信息
				$sql=$this->model->getSqlMap("Archive/getTeacher.sql");
				$data = $this->model->sqlFind($sql,array(":TEACHERNO"=>$teacherno));
				$this->assign("t",$data);
				
				//判断登录用户所在学院是否与待编辑教师相同
				$is_school="false";
				$user_teacherno=$_SESSION["S_USER_INFO"]["TEACHERNO"];
				$user_school = $this->model->sqlFind("SELECT T.SCHOOL FROM TEACHERS T WHERE T.TEACHERNO='$user_teacherno'");
				if($data["SCHOOL"] == $user_school["SCHOOL"] || $user_school["SCHOOL"]=="A1"){
					$is_school="true";
				}
				$this->assign("is_school",$is_school);
				
				//获取教师学习与工作经历
				$study = M("study");
				$sql="select convert(varchar(10), startdate, 23) startdate,convert(varchar(10),enddate, 23) enddate,".
				"school,recno from __TABLE__ where teacherno = '$teacherno' order by startdate asc";
				$studyData=$study->query($sql);
				$this->assign("study",$studyData);
				
				//获取各类获奖情况
				$honor = M("honor");
				$sql="select h1.name,convert(varchar(10),[date], 23) [date],department,h2.name [level],".
					"myorder,recno from __TABLE__ h1 left join honorlevelcode h2 on ".
					"h1.[level]=h2.code where teacherno='$teacherno' order by [date] asc";
				$honorData=$honor->query($sql);
				$this->assign("honor",$honorData);
				
				//获取论文、编写教材、科研教研成果一览表
				$thesis = M("thesis");
				$sql="select name,publish,content,honor,recno from __TABLE__ where teacherno = '$teacherno'";
				$thesisData=$thesis->query($sql);
				$this->assign("thesis",$thesisData);
			}
			$this->display();
		}
	}
	
	/**
	 * 编辑学习与工作经历
	 */
	public function editStudy(){
		if($this->_hasJson){
			//新增
			$enddate=trim($_POST["ENDDATE"])==""?null:trim($_POST["ENDDATE"]);
			$ary=array(":STARTDATE"=>$_POST["STARTDATE"],":ENDDATE"=>$enddate,
						":SCHOOL"=>$_POST["SCHOOL"],":TEACHERNO"=>$_POST["TEACHERNO"]);
			
			$sql=$this->model->getSqlMap("Archive/insertStudy.sql");
			$row=$this->model->sqlExecute($sql,$ary);
			if($row) echo true;
			else echo false;
		}else{
			//删除
			$recno=trim($_POST["recno"]);
			if($recno!="" && $recno!=null){
				$row=$this->model->sqlExecute("delete from study where recno ='$recno'");
				if($row) echo true;
				else echo false;
			}
		}
	}
	
	/**
	 * 编辑各类获奖情况
	 */
	public function editHonor(){
		if($this->_hasJson){
			//新增
			$date=trim($_POST["DATE"])==""?null:trim($_POST["DATE"]);
			$ary=array(":NAME"=>$_POST["NAME"],":DATE"=>$date,":DEPARTMENT"=>$_POST["DEPARTMENT"],
					":LEVEL"=>$_POST["LEVEL"],":MYORDER"=>$_POST["MYORDER"],":TEACHERNO"=>$_POST["TEACHERNO"]);
			
			$sql=$this->model->getSqlMap("Archive/insertHonor.sql");
			$row=$this->model->sqlExecute($sql,$ary);
			if($row) echo true;
			else echo false;
		}else{
			//删除
			$recno=trim($_POST["recno"]);
			if($recno!="" && $recno!=null){
				$row=$this->model->sqlExecute("delete from honor where recno ='$recno'");
				if($row) echo true;
				else echo false;
			}
		}
	}
	
	/**
	 * 编辑论文、编写教材、科研教研成果一览表
	 */
	public function editThesis(){
		if($this->_hasJson){
			//新增
			$ary=array(":NAME"=>$_POST["NAME"],":PUBLISH"=>$_POST["PUBLISH"],":CONTENT"=>$_POST["CONTENT"],
					":HONOR"=>$_POST["HONOR"],":TEACHERNO"=>$_POST["TEACHERNO"]);
			
			$sql=$this->model->getSqlMap("Archive/insertThesis.sql");
			$row=$this->model->sqlExecute($sql,$ary);
			if($row) echo true;
			else echo false;
		}else{
			//删除
			$recno=trim($_POST["recno"]);
			if($recno!="" && $recno!=null){
				$row=$this->model->sqlExecute("delete from thesis where recno ='$recno'");
				if($row) echo true;
				else echo false;
			}
		}
	}
}