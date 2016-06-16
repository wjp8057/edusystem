<?php
/**
 * 数据统计 --教材中心
 * @author shencp
 * Date: 14-06-24
 * Time: 上午11:32
 */
class TextbookAction extends RightAction {
	private $model;
	/**
	 * 构造
	 **/
	public function __construct(){
		parent::__construct();
		$this->model = M("SqlsrvModel:");
	}
	
	/**
	 * 教师信息表
	 */
	public function teacher(){
		if($this->_hasJson){
			$bind = array(":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]),":POSITION"=>doWithBindStr($_POST["POSITION"]));
			$arr = array("total"=>0, "rows"=>array());
	
			$sql = $this->model->getSqlMap("Statistic/Textbook/queryTeacherCount.sql");
			$count = $this->model->sqlCount($sql,$bind);
			$arr["total"] = intval($count);
	
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/Textbook/queryTeacherList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//教师职称
		$this->assign("position",M("positions")->select());
		//学院
		$this->assign("school",M("schools")->select());
		
		$this->display();
	}
	
	/**
	 * 班级信息表
	 */
	public function class_info(){
		$year=trim($_POST["YEAR"]);
		if($this->_hasJson){
			$bind = array(":CLASSNO"=>doWithBindStr($_POST["CLASSNO"]),
					":CLASSNAME"=>doWithBindStr($_POST["CLASSNAME"]),
					":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]));
			$arr = array("total"=>0, "rows"=>array());
			
			$where="";
			if($year!="" && $year!=null){
				$where=" AND (C.[YEAR]>=CAST('".$year." 00:00:00"."' AS DATETIME) AND C.[YEAR]<=CAST('".$year." 23:59:59"."' AS DATETIME))";
			}
			
			$sql = $this->model->getSqlMap("Statistic/Textbook/queryClass_infoCount.sql");
			$count = $this->model->sqlCount($sql.$where,$bind);
			$arr["total"] = intval($count);
	
			if($arr["total"] > 0){
				$order=" ORDER BY C.SCHOOL,YEAR DESC";
				$sql = $this->model->getSqlMap("Statistic/Textbook/queryClass_infoList.sql");
				$sql = $this->model->getPageSql($sql.$where.$order,null, $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//学院
		$this->assign("school",M("schools")->select());
		
		$this->display();
	}
	
	/**
	 * 学期课表
	 */
	public function course(){
		if($this->_hasJson){
			$bind = array(":YEAR"=>$_POST["YEAR"],":TERM"=>$_POST["TERM"],":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]));
			$arr = array("total"=>0, "rows"=>array());
	
			$sql = $this->model->getSqlMap("Statistic/Textbook/queryCourseCount.sql");
			$count = $this->model->sqlCount($sql,$bind,true);
			$arr["total"] = intval($count);
	
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/Textbook/queryCourseList.sql", $this->_pageDataIndex, $this->_pageSize);
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
	 * 选课表
	 */
	public function courseSelection(){
		if($this->_hasJson){
			$bind = array(":YEAR"=>$_POST["YEAR"],":TERM"=>$_POST["TERM"],
					":COURSENO"=>doWithBindStr($_POST["COURSENO"]),
					":CLASSNO"=>doWithBindStr($_POST["CLASSNO"]));
			$arr = array("total"=>0, "rows"=>array());
	
			$sql = $this->model->getSqlMap("Statistic/Textbook/queryC_SelectionCount.sql");
			$count = $this->model->sqlCount($sql,$bind);
			$arr["total"] = intval($count);
	
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/Textbook/queryC_SelectionList.sql", $this->_pageDataIndex, $this->_pageSize);
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
	 * 导出排课表
	 */
	public function coursePlan(){
		if($this->_hasJson){
			$bind = array(":YEAR"=>$_POST["YEAR"],":TERM"=>trim($_POST["TERM"]),
					":COURSENO"=>doWithBindStr($_POST["COURSENO"]),":GROUP"=>doWithBindStr($_POST["GROUP"]),
					":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]),":TYPE"=>doWithBindStr($_POST["COURSETYPE"]),
					":SCHEDULED"=>doWithBindStr($_POST["SCHEDULED"]),":ROOMTYPE"=>doWithBindStr($_POST["ROOMTYPE"]),
					":BLOCK"=>trim($_POST["BLOCK"]),":LOCK"=>trim($_POST["LOCK"]),
					":BESTIMATE"=>trim($_POST["BESTIMATE"]),":ESTIMATE"=>trim($_POST["ESTIMATE"]),
					":BATTENDENTS"=>trim($_POST["BATTENDENTS"]),":ATTENDENTS"=>trim($_POST["ATTENDENTS"]),
					":EXAM"=>doWithBindStr($_POST["EXAM"]),":DAYS"=>doWithBindStr($_POST["DAYS"]),
					":CLASSNO"=>doWithBindStr($_POST["CLASSNO"]));

			$arr = array("total"=>0, "rows"=>array());
	
			$sql = $this->model->getSqlMap("Statistic/Textbook/queryCoursePlanCount.sql");
			$count = $this->model->sqlCount($sql,$bind,true);
			$arr["total"] = intval($count);
	
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/Textbook/queryCoursePlanList.sql", $this->_pageDataIndex, $this->_pageSize);
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
		//教室类型
		$this->assign("room",M("roomoptions")->select());
		//课程类型
		$this->assign("coursetype",M("coursetypeoptions")->select());
		
		$this->display();
	}
	
	/**
	 * 导出Excel--教师信息导出
	 */
	public function teacherExp(){
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
		$title="教师信息表";
		$objPHPExcel->getActiveSheet()->setTitle($title);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($style);//设置样式
		$objPHPExcel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(16);//设置字体大小
		$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(26);//设置行高
		$objPHPExcel->getActiveSheet()->mergeCells('A1:E1');//合并A1单元格到E1
		$objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
		//设置宽度
		$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(25);
		
		//列名设置
		$objPHPExcel->getActiveSheet()->getStyle("A2:E2")->applyFromArray($style);//字体样式
		//单元格内容写入
		$objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"教师姓名");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"教师号");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"职称");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"性别");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"所在学院");
		//设置个别列内容居中
		$objPHPExcel->getActiveSheet()->getStyle("A:E")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		//数据导出
		$bind = array(":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]),":POSITION"=>doWithBindStr($_POST["POSITION"]));
		$sql = $this->model->getSqlMap("Statistic/Textbook/queryTeacherList.sql");
		$data=$this->model->sqlQuery($sql,$bind);
		$row=2;
		foreach($data as $val){
			$row++;
			$objPHPExcel->getActiveSheet()->setCellValue("A$row", $val['NAME']);
			$objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['TEACHERNO']." ");
			$objPHPExcel->getActiveSheet()->setCellValue("C$row", $val['VALUE']);
			$objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['SEX']);
			$objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['SCHOOLNAME']);
		}
		//边框设置
		$objPHPExcel->getActiveSheet(0)->getStyle("A2:E$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
		
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
	
	/**
	 * 导出Excel--班级信息表
	 */
	public function classExp(){
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
		$title="班级信息表";
		$objPHPExcel->getActiveSheet()->setTitle($title);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($style);//设置样式
		$objPHPExcel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(16);//设置字体大小
		$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(26);//设置行高
		$objPHPExcel->getActiveSheet()->mergeCells('A1:F1');//合并A1单元格到E1
		$objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
		//设置宽度
		$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(35);
		$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(20);
	
		//列名设置
		$objPHPExcel->getActiveSheet()->getStyle("A2:F2")->applyFromArray($style);//字体样式
		//单元格内容写入
		$objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"班级编号");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"班级名称");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"年级");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"预计人数");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"入学日期");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"所属学院");
		//设置个别列内容居中
		$objPHPExcel->getActiveSheet()->getStyle("A:F")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
		//数据导出
		$year=trim($_POST["YEAR"]);
		$bind = array(":CLASSNO"=>doWithBindStr($_POST["CLASSNO"]),
					":CLASSNAME"=>doWithBindStr($_POST["CLASSNAME"]),
					":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]));
		$where="";
		if($year!="" && $year!=null){
			$where=" AND (C.[YEAR]>=CAST('".$year." 00:00:00"."' AS DATETIME) AND C.[YEAR]<=CAST('".$year." 23:59:59"."' AS DATETIME))";
		}
		
		$sql = $this->model->getSqlMap("Statistic/Textbook/queryClass_infoList.sql");
		$order=" ORDER BY C.SCHOOL,YEAR DESC";
		$data=$this->model->sqlQuery($sql.$where.$order,$bind);
		$row=2;
		foreach($data as $val){
			$row++;
			$objPHPExcel->getActiveSheet()->setCellValue("A$row", $val['CLASSNO']." ");
			$objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['CLASSNAME']);
			$objPHPExcel->getActiveSheet()->setCellValue("C$row", $val['GRADE']);
			$objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['STUDENTS']);
			$objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['YEAR']);
			$objPHPExcel->getActiveSheet()->setCellValue("F$row", $val['SCHOOLNAME']);
		}
		//边框设置
		$objPHPExcel->getActiveSheet(0)->getStyle("A2:F$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
	
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
	
	/**
	 * 导出Excel--学期课表
	 */
	public function courseExp(){
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
		$title="学期课表";
		$objPHPExcel->getActiveSheet()->setTitle($title);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($style);//设置样式
		$objPHPExcel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(16);//设置字体大小
		$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(26);//设置行高
		$objPHPExcel->getActiveSheet()->mergeCells('A1:G1');//合并A1单元格到E1
		$objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
		//设置宽度
		$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(40);
		$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(35);
		$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(40);
	
		//列名设置
		$objPHPExcel->getActiveSheet()->getStyle("A2:G2")->applyFromArray($style);//字体样式
		//单元格内容写入
		$objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"课号");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"课程名称");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"课程类型");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"开课学院");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"班级名称");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"预计人数");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"主讲教师");
		//设置个别列内容居中
		$objPHPExcel->getActiveSheet()->getStyle("A:G")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	
		//数据导出
		$bind = array(":YEAR"=>$_POST["YEAR"],":TERM"=>$_POST["TERM"],":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]));
	
		$sql = $this->model->getSqlMap("Statistic/Textbook/queryCourseList.sql");
		$data=$this->model->sqlQuery($sql,$bind);
		$row=2;
		foreach($data as $val){
			$row++;
			$objPHPExcel->getActiveSheet()->setCellValue("A$row", $val['COURSENOGROUP']." ");
			$objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['COURSENAME']);
			$objPHPExcel->getActiveSheet()->setCellValue("C$row", $val['COURSETYPE']);
			$objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['SCHOOLNAME']);
			$objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['CLASSNONAME']);
			$objPHPExcel->getActiveSheet()->setCellValue("F$row", $val['ATTENDENTS']);
			$objPHPExcel->getActiveSheet()->setCellValue("G$row", $val['TEACHER']);
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
	
	/**
	 * 导出Excel--选课信息表
	 */
	public function courseSelectionExp(){
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
		$title="选课信息表";
		$objPHPExcel->getActiveSheet()->setTitle($title);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($style);//设置样式
		$objPHPExcel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(16);//设置字体大小
		$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(26);//设置行高
		$objPHPExcel->getActiveSheet()->mergeCells('A1:H1');//合并A1单元格到E1
		$objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
		//设置宽度
		$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(35);
		$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(40);
	
		//列名设置
		$objPHPExcel->getActiveSheet()->getStyle("A2:H2")->applyFromArray($style);//字体样式
		//单元格内容写入
		$objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"学年");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"学期");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"学号");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"学生姓名");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"班级名称");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"课号");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"课程名称");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"课程类型");
		//设置个别列内容居中
		$objPHPExcel->getActiveSheet()->getStyle("A:H")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	
		//数据导出
		$bind = array(":YEAR"=>$_POST["YEAR"],":TERM"=>$_POST["TERM"],
					":COURSENO"=>doWithBindStr($_POST["COURSENO"]),
					":CLASSNO"=>doWithBindStr($_POST["CLASSNO"]));
	
		$sql = $this->model->getSqlMap("Statistic/Textbook/queryC_SelectionList.sql");
		$data=$this->model->sqlQuery($sql,$bind);
		$row=2;
		foreach($data as $val){
			$row++;
			$objPHPExcel->getActiveSheet()->setCellValue("A$row", $val['YEAR']);
			$objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['TERM']);
			$objPHPExcel->getActiveSheet()->setCellValue("C$row", $val['STUDENTNO']." ");
			$objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['NAME']);
			$objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['CLASSNAME']);
			$objPHPExcel->getActiveSheet()->setCellValue("F$row", $val['COURSENO']." ");
			$objPHPExcel->getActiveSheet()->setCellValue("G$row", $val['COURSENAME']);
			$objPHPExcel->getActiveSheet()->setCellValue("H$row", $val['COURSETYPE']);
		}
		//边框设置
		$objPHPExcel->getActiveSheet(0)->getStyle("A2:H$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
	
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
	
	/**
	 * 导出Excel--排课信息表
	 */
	public function coursePlanExp(){
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
		$title="排课信息表";
		$objPHPExcel->getActiveSheet()->setTitle($title);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($style);//设置样式
		$objPHPExcel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(16);//设置字体大小
		$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(26);//设置行高
		$objPHPExcel->getActiveSheet()->mergeCells('A1:T1');//合并A1单元格到E1
		$objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
		//设置宽度
		$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(40);
		$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension("M")->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension("R")->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension("S")->setWidth(40);
		$objPHPExcel->getActiveSheet()->getColumnDimension("T")->setWidth(40);
	
		//列名设置
		$objPHPExcel->getActiveSheet()->getStyle("A2:T2")->applyFromArray($style);//字体样式
		//单元格内容写入
		$objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"课号");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"组号");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"课程名称");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"修课方式");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"考核方式");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"学分");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"周讲课");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"周实验");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"周次");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"学院名称");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('K2',"时段要求");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('L2',"教室要求");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('M2',"统一排考");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('N2',"排课天数");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('O2',"指定实验室");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('P2',"预计人数");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('Q2',"实际人数");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('R2',"备注");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('S2',"上课班级");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('T2',"教师");
		//设置个别列内容居中
		$objPHPExcel->getActiveSheet()->getStyle("A:B")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle("D:K")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $bind = array(":YEAR"=>$_POST["YEAR"],":TERM"=>trim($_POST["TERM"]),
            ":COURSENO"=>doWithBindStr($_POST["COURSENO"]),":GROUP"=>doWithBindStr($_POST["GROUP"]),
            ":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]),":TYPE"=>doWithBindStr($_POST["COURSETYPE"]),
            ":SCHEDULED"=>doWithBindStr($_POST["SCHEDULED"]),":ROOMTYPE"=>doWithBindStr($_POST["ROOMTYPE"]),
            ":BLOCK"=>trim($_POST["BLOCK"]),":LOCK"=>trim($_POST["LOCK"]),
            ":BESTIMATE"=>trim($_POST["BESTIMATE"]),":ESTIMATE"=>trim($_POST["ESTIMATE"]),
            ":BATTENDENTS"=>trim($_POST["BATTENDENTS"]),":ATTENDENTS"=>trim($_POST["ATTENDENTS"]),
            ":EXAM"=>doWithBindStr($_POST["EXAM"]),":DAYS"=>doWithBindStr($_POST["DAYS"]),
            ":CLASSNO"=>doWithBindStr($_POST["CLASSNO"]));
        /*
		//数据导出
		$bind = array(":YEAR"=>$_POST["YEAR"],":TERM"=>trim($_POST["TERM"]),
					":COURSENO"=>trim($_POST["COURSENO"]),":GROUP"=>trim($_POST["GROUP"]),
					":SCHOOL"=>trim($_POST["SCHOOL"]),":TYPE"=>trim($_POST["COURSETYPE"]),
					":SCHEDULED"=>trim($_POST["SCHEDULED"]),":ROOMTYPE"=>$_POST["ROOMTYPE"],
					":BLOCK"=>trim($_POST["BLOCK"]),":LOCK"=>trim($_POST["LOCK"]),
					":BESTIMATE"=>trim($_POST["BESTIMATE"]),":ESTIMATE"=>trim($_POST["ESTIMATE"]),
					":BATTENDENTS"=>trim($_POST["BATTENDENTS"]),":ATTENDENTS"=>trim($_POST["ATTENDENTS"]),
					":EXAM"=>trim($_POST["EXAM"]),":DAYS"=>trim($_POST["DAYS"]),
					":CLASSNO"=>trim($_POST["CLASSNO"]));
	*/
		$sql = $this->model->getSqlMap("Statistic/Textbook/queryCoursePlanList.sql");
		$data=$this->model->sqlQuery($sql,$bind);
		$row=2;
		foreach($data as $val){
			$row++;
			$objPHPExcel->getActiveSheet()->setCellValue("A$row", $val['COURSENO']);
			$objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['GROUP']);
			$objPHPExcel->getActiveSheet()->setCellValue("C$row", $val['COURSENAME']);
			$objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['COURSETYPE']);
			$objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['EXAMTYPE']);
			$objPHPExcel->getActiveSheet()->setCellValue("F$row", $val['CREDITS']);
			$objPHPExcel->getActiveSheet()->setCellValue("G$row", $val['LHOURS']);
			$objPHPExcel->getActiveSheet()->setCellValue("H$row", $val['EHOURS']);
			$objPHPExcel->getActiveSheet()->setCellValue("I$row", $this->transformWeeks($val['WEEKS']));
			$objPHPExcel->getActiveSheet()->setCellValue("J$row", $val['SCHOOLNAME']);
			$objPHPExcel->getActiveSheet()->setCellValue("K$row", $val['TIME']);
			$objPHPExcel->getActiveSheet()->setCellValue("L$row", $val['ROOMVALUE']);
			$objPHPExcel->getActiveSheet()->setCellValue("M$row", $val['EXAM']);
			$objPHPExcel->getActiveSheet()->setCellValue("N$row", $val['DAYS']);
			$objPHPExcel->getActiveSheet()->setCellValue("O$row", $val['EMPROOM']);
			$objPHPExcel->getActiveSheet()->setCellValue("P$row", $val['ESTIMATE']);
			$objPHPExcel->getActiveSheet()->setCellValue("Q$row", $val['ATTENDENTS']);
			$objPHPExcel->getActiveSheet()->setCellValue("R$row", $val['REM']);
			$objPHPExcel->getActiveSheet()->setCellValue("S$row", $val['CLASSNAME']);
			$objPHPExcel->getActiveSheet()->setCellValue("T$row", $val['TEACHERNAME']);
		}
		//边框设置
		$objPHPExcel->getActiveSheet(0)->getStyle("A2:T$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
	
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
	//计算周次
	public function transformWeeks($week){
		$weekString=0;
		$temp=1;
		for($j=0;$j<18;$j++){
			if(($temp&$week)!=0){
				$weekString++;
			}
			$temp=$temp<<1;
		}
		return $weekString;
	}
}