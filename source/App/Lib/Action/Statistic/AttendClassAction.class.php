<?php
/**
 * 数据统计 --教师上课统计
 * @author shencp
 * Date: 14-06-13
 * Time: 上午09:32
 */
class AttendClassAction extends RightAction {
	private $model;
	/**
	 * 构造
	 **/
	public function __construct(){
		parent::__construct();
		$this->model = M("SqlsrvModel:");
	}
	
	/**
	 * 查询各类上课教师基本信息
	 */
	public function basic(){
		if($this->_hasJson){
			$bind = array(":YEAR"=>$_POST["YEAR"],":TERM"=>$_POST["TERM"],
					":TEACHERNO"=>doWithBindStr($_POST["TEACHERNO"]),
					":TEACHERNAME"=>doWithBindStr($_POST["TEACHERNAME"]),
					":POSITIONS"=>doWithBindStr($_POST["POSITIONS"]),
					":JB"=>doWithBindStr($_POST["JB"]),
					":ZhuJiangZhiGe"=>doWithBindStr($_POST["ZhuJiangZhiGe"]),
					":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]));
			
			$arr = array("total"=>0, "rows"=>array());
			
			$sql = $this->model->getSqlMap("Statistic/AttendClass/queryBasicCount.sql");
			$count = $this->model->sqlCount($sql,$bind);
			$arr["total"] = intval($count);
			
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/AttendClass/queryBasicList.sql", $this->_pageDataIndex, $this->_pageSize);
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
		//教师职称
		$this->assign("positions",M("positions")->select());
		
		//主讲资格
		$zjzg = M("positions")->query("select rtrim(zhujiangzhige) code from __TABLE__ group by rtrim(zhujiangzhige) order by code");
		$this->assign("zjzg",$zjzg);
		
		//职称级别
		$jb = M("positions")->query("select rtrim(jb) code from __TABLE__ group by rtrim(jb) order by code");
		$this->assign("jb",$jb);
		
		$this->display();
	}
	
	/**
	 * 查询无主讲教师资格的教师单独上的课程
	 */
	public function noQualify(){
		if($this->_hasJson){
			$bind = array(":YEAR"=>$_POST["YEAR"],":TERM"=>$_POST["TERM"],
					":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]));
			
			$arr = array("total"=>0, "rows"=>array());
			
			$sql = $this->model->getSqlMap("Statistic/AttendClass/queryNoQualifyCount.sql");
			$count = $this->model->sqlCount($sql,$bind);
			$arr["total"] = intval($count);
			
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/AttendClass/queryNoQualifyList.sql", $this->_pageDataIndex, $this->_pageSize);
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
	 * 调停课情况统计
	 */
	public function mediation(){
		if($this->_hasJson){
			$bind = array(":YEAR"=>$_POST["YEAR"],":TERM"=>$_POST["TERM"],
					":COURSENO"=>doWithBindStr($_POST["COURSENO"]),
					":COURSENAME"=>doWithBindStr($_POST["COURSENAME"]),
					":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]));
			
			$arr = array("total"=>0, "rows"=>array());
			
			$sql = $this->model->getSqlMap("Statistic/AttendClass/queryMediationCount.sql");
			$count = $this->model->sqlCount($sql,$bind);
			$arr["total"] = intval($count);
			
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/AttendClass/queryMediationList.sql", $this->_pageDataIndex, $this->_pageSize);
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
	 * 正副教授没上课名单
	 */
	public function professor(){
		if($this->_hasJson){
			$bind = array(":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]),
					":YEAR"=>$_POST["YEAR"],":TERM"=>trim($_POST["TERM"]));
			
			$arr = array("total"=>0, "rows"=>array());
			
			$sql = $this->model->getSqlMap("Statistic/AttendClass/queryProfessorCount.sql");
			$count = $this->model->sqlCount($sql,$bind);
			$arr["total"] = intval($count);
			
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Statistic/AttendClass/queryProfessorList.sql", $this->_pageDataIndex, $this->_pageSize);
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
	 * 各类上课教师基本信息导出
	 */
	public function basicExport(){
		$year=$_POST["YEAR"];
		$term=$_POST["TERM"];
		$bool=false;
		
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
		$title="第".$year."学年，第".$term."学期教师上课统计结果表";
		$objPHPExcel->getActiveSheet()->setTitle($title);
		
		//设置默认字体和大小
		$objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
		
		//设置默认行高
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(18);
		
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
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($style);//设置样式
		$objPHPExcel->getActiveSheet()->mergeCells('A1:P1');//合并A1单元格到H1
		$objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(14);//设置字体大小
		$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(24);//设置行高
		
		//列名设置
		$objPHPExcel->getActiveSheet()->getStyle("A2:P2")->applyFromArray($style);//字体样式
		//单元格内容写入
		$objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"学年");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"学期");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"学院代号");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"学院名称");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"教师号");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"教师名");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"职称");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"级别");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"主讲资格");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"课号");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('K2',"课程名称");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('L2',"课程类型");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('M2',"学分");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('N2',"总学时");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('O2',"实验学时");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('P2',"上机学时");
		
		//设置宽度
		$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(16);
		$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(16);
		$objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(40);
		$objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(16);
		
		//设置个别列内容居中
		$objPHPExcel->getActiveSheet()->getStyle("A:P")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
		//查询导出数据
		$bind = array(":YEAR"=>$year,":TERM"=>$term,
					":TEACHERNO"=>doWithBindStr($_POST["TEACHERNO"]),
					":TEACHERNAME"=>doWithBindStr($_POST["TEACHERNAME"]),
					":POSITIONS"=>doWithBindStr($_POST["POSITIONS"]),
					":JB"=>doWithBindStr($_POST["JB"]),
					":ZhuJiangZhiGe"=>doWithBindStr($_POST["ZhuJiangZhiGe"]),
					":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]));
		
		$sql = $this->model->getSqlMap("Statistic/AttendClass/queryBasicList.sql");
		$ary=$this->model->sqlQuery($sql,$bind);
		$row=2;
		foreach($ary as $val){
			$row++;
			$objPHPExcel->getActiveSheet()->setCellValue("A$row", $val['YEAR']);
			$objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['TERM']);
			$objPHPExcel->getActiveSheet()->setCellValue("C$row", $val['SCHOOL']);
			$objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['SCHOOLNAME']);
			$objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['TEACHERNO']);
			$objPHPExcel->getActiveSheet()->setCellValue("F$row", $val['TEACHERNAME']);
			$objPHPExcel->getActiveSheet()->setCellValue("G$row", $val['VALUE']);
			$objPHPExcel->getActiveSheet()->setCellValue("H$row", $val['JB']);
			$objPHPExcel->getActiveSheet()->setCellValue("I$row", $val['ZhuJiangZhiGe']);
			$objPHPExcel->getActiveSheet()->setCellValue("J$row", $val['COURSENO']);
			$objPHPExcel->getActiveSheet()->setCellValue("K$row", $val['COURSENAME']);
			$objPHPExcel->getActiveSheet()->setCellValue("L$row", $val['TYPE']);
			$objPHPExcel->getActiveSheet()->setCellValue("M$row", $val['CREDITS']);
			$objPHPExcel->getActiveSheet()->setCellValue("N$row", $val['HOURS']);
			$objPHPExcel->getActiveSheet()->setCellValue("O$row", $val['EXPERIMENTS']);
			$objPHPExcel->getActiveSheet()->setCellValue("P$row", $val['COMPUTING']);
		}
		//边框设置
		$objPHPExcel->getActiveSheet(0)->getStyle("A2:P$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
		
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
	 * 无主讲教师资格的教师单独上的课程信息导出
	 */
	public function noQualifyExport(){
		$year=$_POST["YEAR"];
		$term=$_POST["TERM"];
		$type=$_POST["TYPE"];
		$bool=false;
	
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
		$title="第".$year."学年，第".$term."学期无主讲教师资格的教师单独开课情况表";
		$objPHPExcel->getActiveSheet()->setTitle($title);
	
		//设置默认字体和大小
		$objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
	
		//设置默认行高
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(18);
	
		//设置默认宽度
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(10);
	
		//设置单元格自动换行
		$objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);
	
		//设置默认内容垂直居左
		$objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	
		//设置单元格加粗，居中样式
		$style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
		if(intval($type)==0){
			//标题设置
			$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($style);//设置样式
			$objPHPExcel->getActiveSheet()->mergeCells('A1:Q1');//合并A1单元格到H1
			$objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(14);//设置字体大小
			$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(24);//设置行高
			
			//列名设置
			$objPHPExcel->getActiveSheet()->getStyle("A2:Q2")->applyFromArray($style);//字体样式
			//单元格内容写入
			$objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"学年");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"学期");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"学院代号");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"学院名称");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"教师号");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"教师名");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"职称");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"级别");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"主讲资格");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"课号");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('K2',"课程名称");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('L2',"课程类型");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('M2',"学分");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('N2',"总学时");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('O2',"实验学时");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('P2',"上机学时");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('Q2',"授课班级班名");
			
			//设置宽度
			$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(16);
			$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(16);
			$objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(40);
			$objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(16);
			$objPHPExcel->getActiveSheet()->getColumnDimension("Q")->setWidth(50);
			
			//设置个别列内容居中
			//$objPHPExcel->getActiveSheet()->getStyle("A:P")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			
			//查询导出数据
			$bind = array(":YEAR"=>$year,":TERM"=>$term,
					":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]));
			
			$sql = $this->model->getSqlMap("Statistic/AttendClass/queryNoQualifyList.sql");
			$ary=$this->model->sqlQuery($sql,$bind);
			$row=2;
			foreach($ary as $val){
				$row++;
				$objPHPExcel->getActiveSheet()->setCellValue("A$row", $val['YEAR']);
				$objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['TERM']);
				$objPHPExcel->getActiveSheet()->setCellValue("C$row", $val['SCHOOL']." ");
				$objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['SCHOOLNAME']);
				$objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['TEACHERNO']);
				$objPHPExcel->getActiveSheet()->setCellValue("F$row", $val['TEACHERNAME']);
				$objPHPExcel->getActiveSheet()->setCellValue("G$row", $val['P_VALUE']);
				$objPHPExcel->getActiveSheet()->setCellValue("H$row", $val['JB']);
				$objPHPExcel->getActiveSheet()->setCellValue("I$row", $val['ZhuJiangZhiGe']);
				$objPHPExcel->getActiveSheet()->setCellValue("J$row", $val['COURSENO']);
				$objPHPExcel->getActiveSheet()->setCellValue("K$row", $val['COURSENAME']);
				$objPHPExcel->getActiveSheet()->setCellValue("L$row", $val['VALUE']);
				$objPHPExcel->getActiveSheet()->setCellValue("M$row", $val['CREDITS']);
				$objPHPExcel->getActiveSheet()->setCellValue("N$row", $val['HOURS']);
				$objPHPExcel->getActiveSheet()->setCellValue("O$row", $val['EXPERIMENTS']);
				$objPHPExcel->getActiveSheet()->setCellValue("P$row", $val['COMPUTING']);
				$objPHPExcel->getActiveSheet()->setCellValue("Q$row", $val['CLASSNAME']);
			}
			//边框设置
			$objPHPExcel->getActiveSheet(0)->getStyle("A2:Q$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
		}else{
			//标题设置
			$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($style);//设置样式
			$objPHPExcel->getActiveSheet()->mergeCells('A1:Q1');//合并A1单元格到H1
			$objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(14);//设置字体大小
			$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(24);//设置行高
				
			//列名设置
			$objPHPExcel->getActiveSheet()->getStyle("A2:Q2")->applyFromArray($style);//字体样式
			//单元格内容写入
			$objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"学年");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"学期");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"课号");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"课程名称");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"课程类型");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"学分");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"总学时");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"实验学时");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"上机学时");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"学院代号");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('K2',"学院名称");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('L2',"教师号");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('M2',"教师名");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('N2',"职称");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('O2',"级别");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('P2',"主讲资格");
			$objPHPExcel->setActiveSheetIndex()->setCellValue('Q2',"授课班级班名");
			
			//设置宽度
			$objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(16);
			$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(16);
			$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(40);
			$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(16);
			$objPHPExcel->getActiveSheet()->getColumnDimension("Q")->setWidth(50);
				
			//设置个别列内容居中
			//$objPHPExcel->getActiveSheet()->getStyle("A:P")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				
			//查询导出数据
			$bind = array(":YEAR"=>$year,":TERM"=>$term,
					":SCHOOL"=>doWithBindStr($_POST["SCHOOL"]));
				
			$sql = $this->model->getSqlMap("Statistic/AttendClass/queryNoQualifyList.sql");
			$ary=$this->model->sqlQuery($sql,$bind);
			$row=2;
			foreach($ary as $val){
				$row++;
				$objPHPExcel->getActiveSheet()->setCellValue("A$row", $val['YEAR']);
				$objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['TERM']);
				$objPHPExcel->getActiveSheet()->setCellValue("C$row", $val['COURSENO']);
				$objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['COURSENAME']);
				$objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['VALUE']);
				$objPHPExcel->getActiveSheet()->setCellValue("F$row", $val['CREDITS']);
				$objPHPExcel->getActiveSheet()->setCellValue("G$row", $val['HOURS']);
				$objPHPExcel->getActiveSheet()->setCellValue("H$row", $val['EXPERIMENTS']);
				$objPHPExcel->getActiveSheet()->setCellValue("I$row", $val['COMPUTING']);
				$objPHPExcel->getActiveSheet()->setCellValue("J$row", $val['SCHOOL']." ");
				$objPHPExcel->getActiveSheet()->setCellValue("K$row", $val['SCHOOLNAME']);
				$objPHPExcel->getActiveSheet()->setCellValue("L$row", $val['TEACHERNO']);
				$objPHPExcel->getActiveSheet()->setCellValue("M$row", $val['TEACHERNAME']);
				$objPHPExcel->getActiveSheet()->setCellValue("N$row", $val['P_VALUE']);
				$objPHPExcel->getActiveSheet()->setCellValue("O$row", $val['JB']);
				$objPHPExcel->getActiveSheet()->setCellValue("P$row", $val['ZhuJiangZhiGe']);				
				$objPHPExcel->getActiveSheet()->setCellValue("Q$row", $val['CLASSNAME']);
			}
			//边框设置
			$objPHPExcel->getActiveSheet(0)->getStyle("A2:Q$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
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
	
}