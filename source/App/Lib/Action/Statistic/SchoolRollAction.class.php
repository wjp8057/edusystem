<?php
/**
 * 学籍情况统计
 * @author shencp
 * Date: 14-04-14
 * Time: 下午10:21
 */
class SchoolRollAction extends RightAction {
	private $model;
	/**
	 * 构造
	 **/
	public function __construct(){
		parent::__construct();
		$this->model = M("SqlsrvModel:");
	}
	
	/**
	 * 检索学籍情况
	 */
	public function querySchoolRoll(){
		if($this->_hasJson){
			$bind = array(":STUDENTNO"=>doWithBindStr($_POST["STUDENTNO"]),
					":NAME"=>doWithBindStr($_POST["NAME"]),
					":CLASSNO"=>doWithBindStr($_POST["CLASSNO"]),
					":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]),
					":STATUS"=>doWithBindStr($_POST["STATUS"]));
			
			$sql=$this->model->getSqlMap("Statistic/schoolRoll/querySchoolRollCount.sql");
			$data = $this->model->sqlFind($sql,$bind);
			$arr["total"] = $data["COUNT"];
			
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/schoolRoll/querySchoolRollList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}else{$arr["rows"]=array();}
			$this->ajaxReturn($arr,"JSON"); 
			exit;
		}
		//所有学院
		$this->assign("school",M("schools")->select());
		$this->assign("status",M("statusoptions")->select());
		
		//当前登录用户所在学院
		$teacherNo=$_SESSION["S_USER_INFO"]["TEACHERNO"];
		$user_school = $this->model->sqlFind("SELECT T.SCHOOL FROM TEACHERS T WHERE T.TEACHERNO='$teacherNo'");
		$this->assign("user_school",$user_school["SCHOOL"]);
		
		$this->display();
	}
	
	/**
	 * 跳转到编辑页面
	 */
	public function edit(){
		if($this->_hasJson){
			//更新学生信息
			$ary=array(":NAME"=>$_POST["NAME"],":SEX"=>$_POST["SEX"],
					":ENTERDATE"=>$_POST["ENTERDATE"],":YEARS"=>$_POST["YEARS"],
					":CLASSNO"=>$_POST["CLASSNO"],":WARN"=>$_POST["WARN"],
					":STATUS"=>$_POST["STATUS"],":CONTACT"=>$_POST["CONTACT"],
					":SCHOOL"=>$_POST["SCHOOL"],":STUDENTNO"=>$_POST["STUDENTNO"],
					":MAJOR"=>$_POST["MAJOR"],":CLASS"=>$_POST["CLASS"],
					":P_STUDENTNO"=>$_POST["STUDENTNO"]);
			//开始更新
			$sql=$this->model->getSqlMap("Statistic/schoolRoll/updateStudent.sql");
			$bool=$this->model->sqlExecute($sql,$ary);
			if($bool===false) echo false;
			else echo true;
			exit;
		}
		$studentno=trim($_GET["STUDENTNO"]);
		if($studentno!="" && $studentno != null){
			//获取当前学年学期
			$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
			//查询学生基本信息
			$sql=$this->model->getSqlMap("Statistic/schoolRoll/queryStudent.sql");
			$data = $this->model->sqlFind($sql,array(":STUDENTNO"=>$studentno));
			$this->assign("stu",$data);
			
			//判断登录用户所在学院是否与待编辑学生是否相同
			$is_school="false";
			$user_teacherno=$_SESSION["S_USER_INFO"]["TEACHERNO"];
			$user_school = $this->model->sqlFind("SELECT T.SCHOOL FROM TEACHERS T WHERE T.TEACHERNO='$user_teacherno'");
			if($data["SCHOOL"] == $user_school["SCHOOL"] || $user_school["SCHOOL"]=="A1"){
				$is_school="true";
			}
			$this->assign("is_school",$is_school);
			
			//查询当前学年学期是否有注册数据
			$isNo=false;
			$sql="SELECT COUNT(*) [COUNT] FROM REGDATA WHERE STUDENTNO = '$studentno' AND [YEAR] = '".$yearTerm['YEAR']."' AND TERM='".$yearTerm["TERM"]."'";
			$data = $this->model->sqlFind($sql);
			if($data["COUNT"] < 1) $isNo=true;
			$this->assign("isNo",$isNo);
			
			//查询学生学年学期注册信息
			$sql="select [year],term,[value],regcode  from __TABLE__ left join regcodeoptions on regcode=name where studentno='$studentno'";
			$regdata = M("regdata")->query($sql);		
			$this->assign("regdata",$regdata);
			
			$sexcode = M("sexcode")->query("select rtrim(code) code,rtrim(name) name from __TABLE__");
			$this->assign("sex",$sexcode);//性别
			$this->assign("yearTerm",$yearTerm);//当前学年学期
			$this->assign("school",M("schools")->select());//全部学校
			$this->assign("status",M("statusoptions")->select());//学籍状态
			$this->assign("major",M("majorcode")->select());//所有专业
			$this->assign("classcode",M("classcode")->select());//来源
			$this->assign("regcode",M("regcodeoptions")->select());//注册状态
		}
		$this->display();
	}
	
	/**
	 * 班号验证
	 */
	public function validation(){
		//查询班号
		$class=$this->model->sqlFind("SELECT RTRIM(CLASSNAME) CLASSNAME FROM CLASSES WHERE CLASSNO = '".trim($_POST["CLASSNO"])."'");
		echo $class["CLASSNAME"];
	}
	
	/**
	 * 更新学生学年学期注册信息
	 */
	public function saveReg(){
		$year=$_POST["YEAR"];
		$term=$_POST["TERM"];
		$stedentno=$_POST["STUDENTNO"];
		$regcode=$_POST["REGCODE"];
		if($this->_hasJson){
			$sql="UPDATE REGDATA SET REGCODE='$regcode' WHERE STUDENTNO='$stedentno' AND [YEAR]='$year' AND TERM='$term'";
		}else{
			$sql="INSERT INTO REGDATA VALUES('$stedentno','$year','$term',GETDATE(),'$regcode')";
		}
		$bool=$this->model->sqlQuery($sql);
		if($bool===false) echo false;
		else echo true;
	}
	
	/**
	 * 检索学生个人情况
	 */
	public function queryStudent(){
		if($this->_hasJson){
			$bind = array(":STUDENTNO"=>doWithBindStr($_POST["STUDENTNO"]),
					":NAME"=>doWithBindStr($_POST["NAME"]),
					":CLASSNO"=>doWithBindStr($_POST["CLASSNO"]),
					":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]),
					":STATUS"=>doWithBindStr($_POST["STATUS"]));
			
			$sql=$this->model->getSqlMap("Statistic/schoolRoll/querySchoolRollCount.sql");
			$data = $this->model->sqlFind($sql,$bind);
			$arr["total"] = $data["COUNT"];
			
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/schoolRoll/queryStudentList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}else{$arr["rows"]=array();}
			$this->ajaxReturn($arr,"JSON"); 
			exit;
		}
		//所有学院
		$this->assign("school",M("schools")->select());
		$this->assign("status",M("statusoptions")->select());
		$this->display();
	}
	/**
	 * 注销学籍
	 */
	public function cancelled(){
		$count=0;
		$graduation=trim($_POST["graduation"]);
		foreach($_POST["studentnos"] as $val){
			//todo:启动回滚
			$this->model->startTrans();
			//todo:查询出学生的信息
			$arr=$this->model->sqlFind("select * from STUDENTS where STUDENTNO='$val'");
			//todo:删除学生基本信息表
			$arr1=$this->model->sqlExecute("delete from PERSONAL where STUDENTNO='$val'");
			//todo:删除学生表信息
			$arr2=$this->model->sqlExecute("delete from STUDENTS where STUDENTNO='$val'");
			//todo:删除R32表的信息
			$arr3=$this->model->sqlExecute("delete from R32 where STUDENTNO='$val'");
			//todo:删除R4表信息
			$arr4=$this->model->sqlExecute("delete from R4 where STUDENTNO='$val'");
			//todo:删除R28表信息
			$arr5=$this->model->sqlExecute("delete from R28 where STUDENTNO='$val'");
			//todo:向毕业生表插入信息
			$arr6=$this->model->sqlExecute($this->model->getSqlMap("status/Five_insertGRADUATES.SQL"),
					array(":studentno"=>$arr["STUDENTNO"],":name"=>$arr["NAME"],
							":sex"=>$arr["SEX"],":graddate"=>date("Y-m-d H:i:s"),
							":enterdate"=>$arr["ENTERDATE"],":years"=>$arr["YEARS"],
							":graduation"=>$graduation,":credits"=>0));
			
			$bool=$arr&&$arr6?true:false;
			if($bool){
				$this->model->commit();
				$count++;
			}else{
				$this->model->rollback();
			}
		}
		if($count > 0){
			echo "除名成功，学生的成绩记录和异动记录被保留，同时学生也被保留在毕业生库中，其它相关记录被删除！";
		}else{
			echo "除名失败，请确认数据之后重新操作！";
		}
	}
	/**
	 * EXCEL学生名册
	 */
	public function export(){
		$type=trim($_POST["type"]);
		vendor("PHPExcel.PHPExcel");
		//创建一个新的对象
		$objPHPExcel = new PHPExcel();
		
		//设置文件属性
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
		->setLastModifiedBy("Maarten Balliauw")
		->setTitle("Office 2007 XLSX Test Document")
		->setSubject("Office 2007 XLSX Test Document")
		->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
		->setKeywords("office 2007 openxml php")
		->setCategory("Test result file");
		
		//重命名工作表名称
		$title=$type=="schoolRoll"?"学生学籍情况表":"学生个人情况表";
		$objPHPExcel->getActiveSheet()->setTitle($title);
		
		//设置默认字体和大小
		$objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
		
		//设置默认行高
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
		
		//设置默认宽度
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(10);
		
		//设置单元格自动换行
		$objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);
		
		//设置默认内容垂直居左
		$objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		
		//插入查询数据
		$bind = array(":STUDENTNO"=>doWithBindStr($_POST["STUDENTNO"]),
				":NAME"=>doWithBindStr($_POST["NAME"]),
				":CLASSNO"=>doWithBindStr($_POST["CLASSNO"]),
				":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]),
				":STATUS"=>doWithBindStr($_POST["STATUS"]));
		
		if($type=="schoolRoll"){
			/**
			 * 学籍情况信息导出
			 */
			//设置个别列内容居中
			$objPHPExcel->getActiveSheet()->getStyle("A:J")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			
			//设置宽度
			$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(14);
			$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(25);
			$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(18);
			
			//设置单元格加粗，居中样式
			$style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
			
			//标题设置
			$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($style);//设置样式
			$objPHPExcel->getActiveSheet()->mergeCells('A1:J1');//合并A1单元格到Q1
			$objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
			$objPHPExcel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(16);//设置字体大小
			$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(26);//设置行高
			
			//列名设置
			$objPHPExcel->getActiveSheet()->getStyle("A2:J2")->applyFromArray($style);//字体样式
			//单元格内容写入
			$objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"学号");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"学名");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"性别");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"主修班级");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"学籍状态");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"退学警告次数");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"积点分");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"选课学分");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"完成学分");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"所在学院");
			
			$sql = $this->model->getSqlMap("Statistic/schoolRoll/querySchoolRollList.sql");
			$data=$this->model->sqlQuery($sql,$bind);
			$row=2;
			foreach($data as $val){
				$row++;
				$objPHPExcel->getActiveSheet()->setCellValue("A$row", $val['STUDENTNO']." ");
				$objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['NAME']);
				$objPHPExcel->getActiveSheet()->setCellValue("C$row", $val['SEX']);
				$objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['CLASSNAME']);
				$objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['STATUS']);
				$objPHPExcel->getActiveSheet()->setCellValue("F$row", $val['WARN']);
				$objPHPExcel->getActiveSheet()->setCellValue("G$row", $val['POINTS']);
				$objPHPExcel->getActiveSheet()->setCellValue("H$row", $val['TAKEN']);
				$objPHPExcel->getActiveSheet()->setCellValue("I$row", $val['PASSED']);
				$objPHPExcel->getActiveSheet()->setCellValue("J$row", $val['SCHOOLNAME']);
			}
			//边框设置
			$objPHPExcel->getActiveSheet(0)->getStyle("A2:J$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
		}else{
			/**
			 * 学生个人情况信息导出
			 */
			//设置个别列内容居中
			$objPHPExcel->getActiveSheet()->getStyle("A:R")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			
			//设置宽度
			$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(14);
			$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(16);
			$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(28);
			$objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(18);
			$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(14);
			$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(16);
			$objPHPExcel->getActiveSheet()->getColumnDimension("N")->setWidth(50);
			$objPHPExcel->getActiveSheet()->getColumnDimension("O")->setWidth(35);
			$objPHPExcel->getActiveSheet()->getColumnDimension("Q")->setWidth(25);
			$objPHPExcel->getActiveSheet()->getColumnDimension("R")->setWidth(22);
			
			//设置单元格加粗，居中样式
			$style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
			
			//标题设置
			$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($style);//设置样式
			$objPHPExcel->getActiveSheet()->mergeCells('A1:R1');//合并A1单元格到Q1
			$objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
			$objPHPExcel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(16);//设置字体大小
			$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(26);//设置行高
			
			//列名设置
			$objPHPExcel->getActiveSheet()->getStyle("A2:R2")->applyFromArray($style);//字体样式
			//单元格内容写入
			$objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"学号");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"姓名");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"性别");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"入学日期");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"年级");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"班号");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"班级");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"学警告次数");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"学籍状态");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"出生日期");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('K2',"民族");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('L2',"政治面貌");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('M2',"邮编");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('N2',"地址");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('O2',"联系电话");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('P2',"高考总分");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('Q2',"毕业中学");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('R2',"身份证号");
			
			$sql = $this->model->getSqlMap("Statistic/schoolRoll/queryStudentList.sql");
			$data=$this->model->sqlQuery($sql,$bind);
			$row=2;
			foreach($data as $val){
				$row++;
				$objPHPExcel->getActiveSheet()->setCellValue("A$row", $val['STUDENTNO']." ");
				$objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['NAME']);
				$objPHPExcel->getActiveSheet()->setCellValue("C$row", $val['SEX']);
				$objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['ENTERDATE']);
				$objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['YEARS']);
				$objPHPExcel->getActiveSheet()->setCellValue("F$row", $val['CLASSNO']." ");
				$objPHPExcel->getActiveSheet()->setCellValue("G$row", $val['CLASSNAME']);
				$objPHPExcel->getActiveSheet()->setCellValue("H$row", $val['WARN']);
				$objPHPExcel->getActiveSheet()->setCellValue("I$row", $val['STATUS']);
				$objPHPExcel->getActiveSheet()->setCellValue("J$row", $val['BIRTHDAY']);
				$objPHPExcel->getActiveSheet()->setCellValue("K$row", $val['NATIONALITY']);
				$objPHPExcel->getActiveSheet()->setCellValue("L$row", $val['PARTY']);
				$objPHPExcel->getActiveSheet()->setCellValue("M$row", $val['POSTCODE']);
				$objPHPExcel->getActiveSheet()->setCellValue("N$row", $val['ADDRESS']);
				$objPHPExcel->getActiveSheet()->setCellValue("O$row", $val['TEL']." ");
				$objPHPExcel->getActiveSheet()->setCellValue("P$row", $val['SCORE']);
				$objPHPExcel->getActiveSheet()->setCellValue("Q$row", $val['MIDSCHOOL']);
				$objPHPExcel->getActiveSheet()->setCellValue("R$row", $val['ID']." ");
			}
			//边框设置
			$objPHPExcel->getActiveSheet(0)->getStyle("A2:R$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
		}
		
		//开始下载
		$filename = $title.".xls";
		$ua = $_SERVER["HTTP_USER_AGENT"];
		
		header('Content-Type:application/vnd.ms-excel');
		if(preg_match("/MSIE/", $ua)){
			header('Content-Disposition:attachment;filename="'.urlencode($filename).'"');
		} else if (preg_match("/Firefox/", $ua)) {
			header('Content-Disposition:attachment;filename*="utf8\'\''.$filename.'"');
		} else {
			header('Content-Disposition:attachment;filename="'.$filename.'"');
		}
		header('Cache-Control: max-age=0');
		header('Cache-Control: max-age=1');
		
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header ('Cache-Control: cache, must-revalidate');
		header ('Pragma: public');
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	//返回转码标题
	private function getTitle(){
		
		
		
	}
}