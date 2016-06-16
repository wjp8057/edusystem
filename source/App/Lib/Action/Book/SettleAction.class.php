<?php
/**
 * 教材管理 --教材结算
 * @author shencp
 * Date: 14-05-27
 * Time: 上午09:32
 */
class SettleAction extends RightAction {
	private $model;
	/**
	 * 构造
	 **/
	public function __construct(){
		parent::__construct();
		$this->model = M("SqlsrvModel:");
	}
	
	/**
	 * 按征订单发放
	 */
	public function BookSettle(){
		if($this->_hasJson){
			$bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
					":isbn"=>doWithBindStr($_POST["isbn"]),":bookname"=>doWithBindStr($_POST["bookname"]),
					":school"=>doWithBindStr($_POST["school"]),":classno"=>doWithBindStr($_POST["classno"]));
				
			$arr = array("total"=>0, "rows"=>array());
			$sql = $this->model->getSqlMap("Book/Settle/queryPaymentCount.sql");
			$count = $this->model->sqlCount($sql,$bind);
			$arr["total"] = intval($count);
				
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Book/Settle/queryPaymentList.sql", $this->_pageDataIndex, $this->_pageSize);
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
	 * 结算导出
	 */
	public function export(){
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
		$title=$_POST["year"]."学年第".$_POST["term"]."学期学生教材明细表";
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
		
		//设置个别列内容居中
		$objPHPExcel->getActiveSheet()->getStyle("D:E")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
		//设置宽度
		$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(20);
		
		//设置单元格字体加粗，居中样式
		$style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
		
		//标题设置
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($style);//设置样式
		$objPHPExcel->getActiveSheet()->mergeCells('A1:F1');//合并A1单元格到F1
		$objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
		$objPHPExcel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(14);//设置A1字体大小
		$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(24);//设置行高
		
		//列名设置
		$objPHPExcel->getActiveSheet()->getStyle("A2:F2")->applyFromArray($style);//字体样式
		//单元格内容写入
		$objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"学号");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"姓名");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"班级");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"原价");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"折扣价");
		$objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"备注");
		
		//插入查询数据
		$bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
		 ":isbn"=>$_POST["isbn"],":bookname"=>$_POST["bookname"],
				":school"=>$_POST["school"],":classno"=>$_POST["classno"]);
		$sql = $this->model->getSqlMap("Book/Settle/queryPaymentList.sql");
		$data=$this->model->sqlQuery($sql,$bind);
		$row=2;
		$sum1=0;
		$sum2=0;
		foreach($data as $val){
			$row++;
			$objPHPExcel->getActiveSheet()->setCellValueExplicit("A$row", trim($val['studentno']),PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['name']);
			$objPHPExcel->getActiveSheet()->setCellValue("C$row", trim($val['classname']));
			$objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['price']);
			$objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['dis_price']);
			$sum1+=(float)$val['price'];
			$sum2+=(float)$val['dis_price'];
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$row, "");
		}
		//边框设置
		$objPHPExcel->getActiveSheet(0)->getStyle("A2:F$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
		
		//总价写入
		$objPHPExcel->getActiveSheet()->setCellValue("D".($row+1), $sum1);
		$objPHPExcel->getActiveSheet()->setCellValue("E".($row+1), $sum2);
		
		//生成输出下载
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