<?php
/**
 * 数据统计 --选课统计
 * @author shencp
 * Date: 14-06-24
 * Time: 上午11:32
 */
class SelectionAction extends RightAction {
	private $model;
	/**
	 * 构造
	 **/
	public function __construct(){
		parent::__construct();
		$this->model = M("SqlsrvModel:");
	}
	
	/**
	 * 跟班重修学生统计
	 */
	public function anewStu(){
		if($this->_hasJson){
			$bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
					":cSchool"=>doWithBindStr($_POST["cSchool"]),":school"=>doWithBindStr($_POST["school"]));
			$arr = array("total"=>0, "rows"=>array());
	
			$sql = $this->model->getSqlMap("Statistic/Selection/queryAnewStuCount.sql");
            $count = $this->model->sqlCount($sql,$bind);
            $arr["total"] = intval($count);
	
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/Selection/queryAnewStuList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		//学院
		$this->assign("school",M("schools")->select());
		$this->display();
	}

	/**
	 * 重修班选课学生统计
	 */
	public function anewSelection(){
		if($this->_hasJson){
			$bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
					":cSchool"=>doWithBindStr($_POST["cSchool"]),":school"=>doWithBindStr($_POST["school"]));
			$arr = array("total"=>0, "rows"=>array());
	
			$sql = $this->model->getSqlMap("Statistic/Selection/queryAnewSelectionCount.sql");
			$count = $this->model->sqlCount($sql,$bind,true);
			$arr["total"] = intval($count);
	
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/Selection/queryAnewSelectionList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		//学院
		$this->assign("school",M("schools")->select());
		$this->display();
	}
	/**
	 * 申请免听、免修学生名单统计
	 */
	public function exemption(){
		if($this->_hasJson){
			$bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
					":cSchool"=>doWithBindStr($_POST["cSchool"]),":school"=>doWithBindStr($_POST["school"]));
			$arr = array("total"=>0, "rows"=>array());
	
			$sql = $this->model->getSqlMap("Statistic/Selection/queryExemptionCount.sql");
			$count = $this->model->sqlCount($sql,$bind);
			$arr["total"] = intval($count);
	
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/Selection/queryExemptionList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		//学院
		$this->assign("school",M("schools")->select());
		$this->display();
	}
	/**
	 * 检索预计人数小于教室座位数课程
	 */
	public function predict(){
		if($this->_hasJson){
			$bind = array(":YEAR"=>$_POST["year"],":TERM"=>$_POST["term"],
					":RS"=>trim($_POST["rs"]));
			$arr = array("total"=>0, "rows"=>array());
	
			$sql = $this->model->getSqlMap("Statistic/Selection/queryPredictCount.sql");
			$count = $this->model->sqlCount($sql,$bind,true);
			$arr["total"] = intval($count);
	
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/Selection/queryPredictList.sql", $this->_pageDataIndex, $this->_pageSize);
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
	/**
	 * 各门课程选课人数统计
	 */
	public function courseSelection(){
		if($this->_hasJson){
			$year=$_POST["YEAR"];
			$term=$_POST["TERM"];
			$bind = array(":R_YEAR"=>$year,":R_TERM"=>$term,":YEAR"=>$year,":TERM"=>$term,
					":COURSENOGROUP"=>doWithBindStr($_POST["COURSENOGROUP"]),
					":COURSETYPE"=>doWithBindStr($_POST["COURSETYPE"]),
					":CLASSNO"=>doWithBindStr($_POST["CLASSNO"]),
					":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]),
					":TYPENAME"=>doWithBindStr($_POST["COURSETYPENAME"]),
					":EXAMTYPE"=>doWithBindStr($_POST["EXAMTYPE"]));
			$arr = array("total"=>0, "rows"=>array());
	
			$sql = $this->model->getSqlMap("Statistic/Selection/queryC_selectionCount.sql");
			$count = $this->model->sqlCount($sql,$bind,true);
			$arr["total"] = intval($count);
	
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/Selection/queryC_selectionList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		//课程类型
		$this->assign("approaches",M("courseapproaches")->select());
		//开课学院
		$this->assign("school",M("schools")->select());
		//课程类别
		$this->assign("typeoptions",M("coursetypeoptions")->select());
		//考核方式
		$this->assign("examoptions",M("examoptions")->select());
		
		$this->display();
	}
	/**
	 * 查看选课人数小于座位数的课程
	 */
	public function peopleNum_lt(){
		if($this->_hasJson){
			$year=$_POST["YEAR"];
			$term=$_POST["TERM"];
			$bind = array(":R_YEAR"=>$year,":R_TERM"=>$term,":YEAR"=>$year,":TERM"=>$term,
					":COURSENOGROUP"=>doWithBindStr($_POST["COURSENOGROUP"]),
					":COURSETYPE"=>doWithBindStr($_POST["COURSETYPE"]),
					":CLASSNO"=>doWithBindStr($_POST["CLASSNO"]),
					":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]),
					":TYPENAME"=>doWithBindStr($_POST["COURSETYPENAME"]),
					":EXAMTYPE"=>doWithBindStr($_POST["EXAMTYPE"]));
			$arr = array("total"=>0, "rows"=>array());
	
			$sql = $this->model->getSqlMap("Statistic/Selection/queryPeopleNum_ltCount.sql");
			$count = $this->model->sqlCount($sql,$bind,true);
			$arr["total"] = intval($count);
	
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/Selection/queryPeopleNum_ltList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		//课程类型
		$this->assign("approaches",M("courseapproaches")->select());
		//开课学院
		$this->assign("school",M("schools")->select());
		//课程类别
		$this->assign("typeoptions",M("coursetypeoptions")->select());
		//考核方式
		$this->assign("examoptions",M("examoptions")->select());
		
		$this->display();
	}
	/**
	 * 各类课程选课总人数统计
	 */
	public function courseSum(){
		if($this->_hasJson){
			$bind = array(":YEAR"=>$_POST["YEAR"],":TERM"=>$_POST["TERM"],
					":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]));
			$arr = array("total"=>0, "rows"=>array());
	
			$sql = $this->model->getSqlMap("Statistic/Selection/queryCourseSumCount.sql");
			$count = $this->model->sqlCount($sql,$bind,true);
			$arr["total"] = intval($count);
	
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/Selection/queryCourseSumList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		//开课学院
		$this->assign("school",M("schools")->select());
		$this->display();
	}
	/**
	 * 全校选课总人数统计结果表
	 */
	public function courseSumAll(){
		$bind = array(":YEAR"=>$_POST["YEAR"],":TERM"=>$_POST["TERM"]);
		$sql=$this->model->getSqlMap("Statistic/Selection/queryCourseSumAllList.sql");
		$arr["rows"]=$this->model->sqlQuery($sql,$bind);
		$this->ajaxReturn($arr,"JSON");
	}
	
	/**2010学年第1学期 10应用英语外贸1(1031910)的选课汇总表
	 * 班级选课汇总表
	 */
	public function classSum(){
		if($this->_hasJson){
			$classno=trim($_POST["CLASSNO"]);
			$bind = array(":YEAR"=>$_POST["YEAR"],":TERM"=>$_POST["TERM"],":CLASSNO"=>$classno);
			//获取班级名称
			$data=$this->model->sqlFind("SELECT RTRIM(CLASSNAME) CLASSNAME FROM CLASSES WHERE CLASSNO='$classno'");
			$className=$data["CLASSNAME"];
			//获取班级学生课程信息
			$sql=$this->model->getSqlMap("Statistic/Selection/queryCourseList.sql");
			$course=$this->model->sqlQuery($sql,$bind);
			if($course!=null && $course!=""){
				//获取学生信息
				$sql=$this->model->getSqlMap("Statistic/Selection/queryStudentList.sql");
				$student=$this->model->sqlQuery($sql,$bind);
				//获取课程类别
				$sql=$this->model->getSqlMap("Statistic/Selection/queryCourseTypeList.sql");
				$courseType=$this->model->sqlQuery($sql,$bind);
				$stu=array();
				foreach($student as $val){
					foreach($courseType as $c){
						if($val["STUDENTNO"]==$c["STUDENTNO"]){
							$val[strtoupper($c["COURSENO"])]=$c["COURSETYPE"];
						}
					}
					array_push($stu,$val);
				}
				$ary=array("course"=>$course,"classname"=>$className,"student"=>$stu);
				echo json_encode($ary);
			}
			exit;
		}
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		$this->display();
	}
	/**
	 * 班级选课汇总表打印
	 */
	public function classSumPrint(){
		$this->assign("year",trim($_POST["YEAR"]));
		$this->assign("term",trim($_POST["TERM"]));
		$this->assign("classno",trim($_POST["CLASSNO"]));
		$this->display();
	}
	/**
	 * 查看班级周课表
	 */
	public function weekTable(){
		$year = trim($_POST["YEAR"]);
        $term = trim($_POST["TERM"]);
        $classno=trim($_POST["CLASSNO"]);
        if($year=="" || $term==""){
            $data = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
            $year = $data['YEAR'];
            $term = $data['TERM'];
        }
        $this->assign("YEAR",$year);
        $this->assign("TERM",$term);
        $this->assign("CLASSNO",$classno);
        //获取班级名称
        $data=$this->model->sqlFind("SELECT RTRIM(CLASSNAME) CLASSNAME FROM CLASSES WHERE CLASSNO='$classno'");
        if($data!=null)
        	$this->assign("CLASSNAME",$data["CLASSNAME"]."在");
        
        if($classno!=""){
        	$bind = $this->model->getBind("YEAR,TERM,CLASSNO",array($year, $term,$classno));
        	$data = $this->model->sqlQuery($this->model->getSqlMap("timeTable/getClass.sql"),$bind);
        	$this->assign("list",$this->getTimeTable($data));
        }
        $this->display();
	}
	
	private function getTimeTable($data, $rowspan=2){
		$list = array();
		if(!count($data)) return $list;
	
		$timeData = $this->model->sqlQuery("select NAME,VALUE,UNIT,TIMEBITS from TIMESECTORS");
		//所有课时列表以NAME为索引
		$timesectors = array_reduce($timeData, "myTimesectorsReduce");
		//取得单节课时自然数为索引
		$countTimesectors = array_reduce($timeData, "myCountTimesectors");
		//单双周
		$both = array("B"=>"","E"=>"（双周）","O"=>"（单周）");
	
		foreach($data as $row){
			$list = $this->makeTime($list,$row,$rowspan, $both, $timesectors,$countTimesectors);
		}
		return $list;
	}
	
	private function makeTime($list, $times, $rowspan, $both, $timesectors, $countTimesectors){
		$list = (array)$list;
		$split = str_split($times["TIME"]);
		if($split[0]=='0') return $list[0] .= $times["COURSE"]."<br/>";
	
		$_time = $timesectors[$split[0]];
		for($i=1;$i<count($countTimesectors); $i+=$rowspan){
			//现在是以双节排
			for($j=0; $j<$rowspan; $j++){
				//说明有课跳出循环
				if(($timesectors[$countTimesectors[$i-1+$j]]['TIMEBITS'] & $_time['TIMEBITS'])>0){
					$list[($i-1)/$rowspan+1][$split[1]] .= ($timesectors[$split[0]]['UNIT']=="1" ? '('.trim($timesectors[$split[0]]['VALUE']).')' : '').$both[$split[2]].$times["COURSE"]."<br/>";
					break;
				}
			}
		}
		return $list;
	}
	/**
	 * 导出Excel--申请免听、免修学生名单统计
	 */
	public function exemptionExp(){
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
		//设置单元格加粗，居中样式
		$style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
		//标题设置
		$title="申请免听、免修学生名单";
		$objPHPExcel->getActiveSheet()->setTitle($title);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($style);//设置样式
		$objPHPExcel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(16);//设置字体大小
		$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(26);//设置行高
		$objPHPExcel->getActiveSheet()->mergeCells('A1:G1');//合并A1单元格到E1
		$objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
		//设置宽度
		$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(40);
	
		//列名设置
		$objPHPExcel->getActiveSheet()->getStyle("A2:G2")->applyFromArray($style);//字体样式
		//单元格内容写入
		$objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"学号");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"姓名");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"课号");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"课程名称");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"课程类型");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"学分");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"修课方式");
		//设置个别列内容居中
		$objPHPExcel->getActiveSheet()->getStyle("A:G")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
	
		//数据导出
		$bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
				":cSchool"=>doWithBindStr($_POST["cSchool"]),":school"=>doWithBindStr($_POST["school"]));
		
		$sql = $this->model->getSqlMap("Statistic/Selection/queryExemptionList.sql");
		$data=$this->model->sqlQuery($sql,$bind);
		
		$row=2;
		foreach($data as $val){
			$row++;
			$objPHPExcel->getActiveSheet()->setCellValue("A$row", $val['studentno'].' ');
			$objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['name']);
			$objPHPExcel->getActiveSheet()->setCellValue("C$row", $val['courseno'].' ');
			$objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['coursename']);
			$objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['courseType']);
			$objPHPExcel->getActiveSheet()->setCellValue("F$row", $val['credits']);
			$objPHPExcel->getActiveSheet()->setCellValue("G$row", $val['appType']);
		}
		//边框设置
		$objPHPExcel->getActiveSheet(0)->getStyle("A2:G$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
	
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
}