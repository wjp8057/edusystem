<?php
//todo:班级选课管理
class ExcelAction extends RightAction{
    private $objPHPExcel;
    private $model;
    private $style;
    private $user_school;
    public function __construct(){
        parent::__construct();
        $this->model=new SqlsrvModel();
        //当前登录用户所在学院
        $teacherNo=$_SESSION["S_USER_INFO"]["TEACHERNO"];
        $school = $this->model->sqlFind("SELECT T.SCHOOL FROM TEACHERS T WHERE T.TEACHERNO=:TEACHERNO",array(":TEACHERNO"=>$teacherNo));
        $this->user_school=$school["SCHOOL"];
        //生成工作簿
        vendor("PHPExcel.PHPExcel");
        $this->objPHPExcel = new PHPExcel();
        $this->objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
        //设置默认宽度
        $this->objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(10);
        //设置单元格自动换行
        $this->objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);
        //设置默认内容垂直居左
        $this->objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $this->objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        //设置单元格加粗，居中样式
        $this->style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
    }
    public function download($title){
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

        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     * 按订单号发放导出
     */
    public function orderExport(){
        //重命名工作表名称
        $title="{$_POST["year"]}学年{$_POST["term"]}学期征订单发放记录表";
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //设置默认行高
        //$this->objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(22);
        //设置列J到O内容垂直居中
        $this->objPHPExcel->getActiveSheet()->getStyle("J:O")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置宽度
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(18);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(30);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(40);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(18);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("M")->setWidth(12);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("Q")->setWidth(30);
        //标题设置
        $this->objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($this->style);//设置样式
        $this->objPHPExcel->getActiveSheet()->mergeCells('A1:Q1');//合并A1单元格到Q1
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
        $this->objPHPExcel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(14);//设置字体大小
        $this->objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(26);//设置行高
        //列名设置
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:Q2")->applyFromArray($this->style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(22);//设置行高
        //单元格内容写入
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"上课班级");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"课号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"课名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"修课方式");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"订单编号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"ISBN");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"教材名称");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"主编");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"出版社");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"发放数");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('K2',"单价");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('L2',"折扣率");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('M2',"单价(实洋)");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('N2',"码洋");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('O2',"实付金额");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('P2',"学院");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('Q2',"教师姓名");
        //插入查询数据
        $bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
            ":courseno"=>$_POST["courseno"],":coursename"=>$_POST["coursename"],
            ":school"=>$_POST["school"],":classno"=>$_POST["classno"]);
        $sql = $this->model->getSqlMap("Book/Excel/orderExportList.sql");
        $data=$this->model->sqlQuery($sql,$bind);
        $row=2;
        foreach($data as $val){
            $row++;
            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row", $val['classname']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['courseno']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row", $val['coursename']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['value']);
            $this->objPHPExcel->getActiveSheet()->setCellValueExplicit("E$row", $val['oderno'],PHPExcel_Cell_DataType::TYPE_STRING);
            $this->objPHPExcel->getActiveSheet()->setCellValueExplicit("F$row", $val['isbn'],PHPExcel_Cell_DataType::TYPE_STRING);
            $this->objPHPExcel->getActiveSheet()->setCellValueExplicit("G$row", $val['bookname'],PHPExcel_Cell_DataType::TYPE_STRING);
            $this->objPHPExcel->getActiveSheet()->setCellValue("H$row", $val['author']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("I$row", $val['press']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("J$row", $val['issue_nym']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("K$row", $val['price']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("L$row", $val['dis_rate']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("M$row", $val['dis_price']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("N$row", $val['m_price']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("O$row", $val['s_price']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("P$row", $val['schoolname']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("Q$row", $val['teachername']);
        }
        //边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:Q$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
        $this->download($title);
    }
    /**
     * 按教材号发放导出
     */
    public function bookExport(){
         //重命名工作表名称
         $title="第{$_POST["year"]}学年，第{$_POST["term"]}学期教材发放记录表";
         $this->objPHPExcel->getActiveSheet()->setTitle($title);
         //设置个别列内容居中
         $this->objPHPExcel->getActiveSheet()->getStyle("F:K")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
         //设置宽度
         $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(20);
         $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(20);
         $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(40);
         $this->objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(40);
         $this->objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(20);
         $this->objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(12);
         //标题设置
         $this->objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($this->style);//设置样式
         $this->objPHPExcel->getActiveSheet()->mergeCells('A1:K1');//合并A1单元格到F1
         $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
         $this->objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(14);//设置字体大小
         $this->objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(24);//设置行高
         //列名设置
         $this->objPHPExcel->getActiveSheet()->getStyle("A2:K2")->applyFromArray($this->style);//字体样式
         $this->objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(22);//设置行高
         //单元格内容写入
         $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"上课班级");
         $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"ISBN");
         $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"教材名称");
         $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"主编");
         $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"出版社");
         $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"发放数");
         $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"单价");
         $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"折扣率");
         $this->objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"单价(实洋)");
         $this->objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"码洋");
         $this->objPHPExcel->setActiveSheetIndex()->setCellValue('K2',"实付金额");
        //插入查询数据
         $bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
            ":isbn"=>$_POST["isbn"],":bookname"=>$_POST["bookname"]);
         $sql = $this->model->getSqlMap("Book/Excel/bookExportList.sql");
         $data=$this->model->sqlQuery($sql,$bind);
         $row=2;
         foreach($data as $val){
             $row++;
             $this->objPHPExcel->getActiveSheet()->setCellValue('A'.$row, trim($val['classname']));
             $this->objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$row, $val['isbn'],PHPExcel_Cell_DataType::TYPE_STRING);
             $this->objPHPExcel->getActiveSheet()->setCellValueExplicit('C'.$row, trim($val['bookname']),PHPExcel_Cell_DataType::TYPE_STRING);
             $this->objPHPExcel->getActiveSheet()->setCellValue('D'.$row, $val['author']);
             $this->objPHPExcel->getActiveSheet()->setCellValue('E'.$row, $val['press']);
             $this->objPHPExcel->getActiveSheet()->setCellValue('F'.$row, $val['count']);
             $this->objPHPExcel->getActiveSheet()->setCellValue('G'.$row, $val['price']);
             $this->objPHPExcel->getActiveSheet()->setCellValue('H'.$row, $val['dis_rate']);
             $this->objPHPExcel->getActiveSheet()->setCellValue('I'.$row, $val['dis_price']);
             $this->objPHPExcel->getActiveSheet()->setCellValue('J'.$row, $val['m_price']);
             $this->objPHPExcel->getActiveSheet()->setCellValue('K'.$row, $val['s_price']);
         }
         //边框设置
         $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:K$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
         $this->download($title);
    }
    /**
     * 按班号导出教材
     */
    public function classExport(){
        $class = $this->model->sqlFind("select rtrim(classno) classno,rtrim(classname) classname from classes where classno like '{$_POST["classno"]}'");
        //重命名工作表名称
        $title="{$_POST["year"]}学年{$_POST["term"]}学期{$class["classname"]}班级教材发放表";
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("E:H")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        //设置宽度
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(15);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(40);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(30);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(30);
        //标题设置
        $this->objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($this->style);//设置样式
        $this->objPHPExcel->getActiveSheet()->mergeCells('A1:H1');//合并A1单元格到F1
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
        $this->objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(14);//设置字体大小
        $this->objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(24);//设置行高
        //列名设置
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:H2")->applyFromArray($this->style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(22);//设置行高
        //单元格内容写入
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"ISBN");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"教材名称");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"出版社");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"编者");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"单价");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"折扣价");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"数量");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"确认");
        //插入查询数据
        $bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],":classno"=>$_POST["classno"]);
        $sql = $this->model->getSqlMap("Book/Issue/Q_bookByClass.sql");
        $data=$this->model->sqlQuery($sql,$bind);
        $row=2;
        foreach($data as $val){
            $row++;
            $this->objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$row, $val['isbn'],PHPExcel_Cell_DataType::TYPE_STRING);
            $this->objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$row, trim($val['bookname']),PHPExcel_Cell_DataType::TYPE_STRING);
            $this->objPHPExcel->getActiveSheet()->setCellValue('C'.$row, $val['press']);
            $this->objPHPExcel->getActiveSheet()->setCellValue('D'.$row, $val['author']);
            $this->objPHPExcel->getActiveSheet()->setCellValue('E'.$row, $val['price']);
            $this->objPHPExcel->getActiveSheet()->setCellValue('F'.$row, $val['dis_price']);
            $this->objPHPExcel->getActiveSheet()->setCellValue('G'.$row, $val['num']);
            $this->objPHPExcel->getActiveSheet()->setCellValue('H'.$row, "");
        }
        //边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:H$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
        $this->download($title);
    }
    /**
     * 班级发放汇总表导出
     */
    public function classSumExport(){
        //重命名工作表名称
        $title="{$_POST["year"]}学年{$_POST["term"]}学期班级发放汇总表";
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //设置列内容垂直居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->objPHPExcel->getActiveSheet()->getStyle("D:F")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->objPHPExcel->getActiveSheet()->getStyle("A2")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置宽度
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(30);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(20);
        //标题设置
        $this->objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($this->style);//设置样式
        $this->objPHPExcel->getActiveSheet()->mergeCells('A1:F1');//合并A1单元格到F1
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
        $this->objPHPExcel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(14);//设置字体大小
        $this->objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(26);//设置行高
        //列名设置
        $this->objPHPExcel->getActiveSheet()->getStyle("A3:F2")->applyFromArray($this->style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(22);//设置行高
        //单元格内容写入
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"班号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"班级");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"学院");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"原价");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"折扣价");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"签字");
        //插入查询数据
        $bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
            ":classno"=>doWithBindStr($_POST["classno"]),":classname"=>doWithBindStr($_POST["classname"]),
            ":school"=>doWithBindStr($_POST["school"]) );
        $sql = $this->model->getSqlMap("Book/Summary/Q_summaryByClass.sql");
        $data=$this->model->sqlQuery($sql,$bind);
        $row=2;
        foreach($data as $val){
            $row++;
            $this->objPHPExcel->getActiveSheet()->setCellValueExplicit("A$row", $val['classno'],PHPExcel_Cell_DataType::TYPE_STRING);
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['classname']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row", $val['schoolname']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['price']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['dis_price']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("F$row",'');
        }
        //边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:F$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
        $this->download($title);
    }
    /**
     * 班级学生发放汇总表导出
     */
    public function studentByClassNo(){
        $class = $this->model->sqlFind("select rtrim(classno) classno,rtrim(classname) classname from classes where classno like '{$_POST["classno"]}'");
        //重命名工作表名称
        $title="{$_POST["year"]}学年{$_POST["term"]}学期{$class["classname"]}班学生教材发放汇总表";
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //设置列内容垂直居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->objPHPExcel->getActiveSheet()->getStyle("D:F")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->objPHPExcel->getActiveSheet()->getStyle("A2")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置宽度
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(12);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(12);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(12);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(15);
        //标题设置
        $this->objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($this->style);//设置样式
        $this->objPHPExcel->getActiveSheet()->mergeCells('A1:F1');//合并A1单元格到F1
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
        $this->objPHPExcel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(14);//设置字体大小
        $this->objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(26);//设置行高
        //列名设置
        $this->objPHPExcel->getActiveSheet()->getStyle("A3:F2")->applyFromArray($this->style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(22);//设置行高
        //单元格内容写入
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"学号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"姓名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"班级");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"原价（总价）");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"折扣价（总价）");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"签字");
        //插入查询数据
        $bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],":studentno"=>doWithBindStr($_POST["studentno"])
        ,":name"=>doWithBindStr($_POST["name"]),":classno"=>doWithBindStr($_POST["classno"]));
        $sql = $this->model->getSqlMap("Book/Summary/Q_studentByClassNo.sql");
        $data=$this->model->sqlQuery($sql,$bind);
        $row=2;
        foreach($data as $val){
            $row++;
            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row", $val['studentno']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['name']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row", $val['classname']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['price']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['dis_price']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("F$row",'');
        }
        //边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:F$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
        $this->download($title);
    }
    /**
     * 学生发放汇总表导出
     */
    public function stuSumExport(){
        //重命名工作表名称
        $title="{$_POST["year"]}学年{$_POST["term"]}学期学生教材发放汇总表";
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //设置列内容垂直居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->objPHPExcel->getActiveSheet()->getStyle("D:F")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->objPHPExcel->getActiveSheet()->getStyle("A2")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置宽度
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(12);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(12);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(12);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(15);
        //标题设置
        $this->objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($this->style);//设置样式
        $this->objPHPExcel->getActiveSheet()->mergeCells('A1:F1');//合并A1单元格到F1
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
        $this->objPHPExcel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(14);//设置字体大小
        $this->objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(26);//设置行高
        //列名设置
        $this->objPHPExcel->getActiveSheet()->getStyle("A3:F2")->applyFromArray($this->style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(22);//设置行高
        //单元格内容写入
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"学号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"姓名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"班级");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"原价（总价）");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"折扣价（总价）");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"签字");
        //插入查询数据
        $bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],":studentno"=>doWithBindStr($_POST["studentno"])
        ,":name"=>doWithBindStr($_POST["name"]),":school"=>doWithBindStr($_POST["school"]));
        $sql = $this->model->getSqlMap("Book/Summary/Q_summaryByStudent.sql");
        $data=$this->model->sqlQuery($sql,$bind);
        $row=2;
        foreach($data as $val){
            $row++;
            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row", $val['studentno']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['name']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row", $val['classname']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['price']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['dis_price']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("F$row",'');
        }
        //边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:F$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
        $this->download($title);
    }
    /**
     * 征订检索-征订单导出
     */
    public function expApply(){
        //重命名工作表名称
        $title="第{$_POST["YEAR"]}学年，第{$_POST["TERM"]}学期教材征订单";
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //设置宽度
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(12);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(22);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(16);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(12);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(22);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(45);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(40);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("M")->setWidth(12);
        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->objPHPExcel->getActiveSheet()->getStyle("C")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->objPHPExcel->getActiveSheet()->getStyle("E")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->objPHPExcel->getActiveSheet()->getStyle("G")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->objPHPExcel->getActiveSheet()->getStyle("L")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->objPHPExcel->getActiveSheet()->getStyle("M")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //标题设置
        $this->objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($this->style);//设置样式
        $this->objPHPExcel->getActiveSheet()->mergeCells('A1:M1');//合并A1单元格到M1
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
        $this->objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(14);//设置字体大小
        $this->objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(26);//设置行高
        //列名设置
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:M2")->applyFromArray($this->style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(22);//设置行高
        //单元格内容写入
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"课号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"课程名称");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"修课方式");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"上课班级");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"预计人数");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"任课教师");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"开课学院");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"书号（ISBN）");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"教材名称");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"主编");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('K2',"出版社");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('L2',"征订状态");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('M2',"征订时间");


        //获取导出征订信息
        $school=$this->user_school=="A1"?$_POST["SCHOOL"]:$this->user_school;
        $bind = array(":YEAR"=>$_POST["YEAR"],":TERM"=>$_POST["TERM"],
            ":COURSETYPE"=>doWithBindStr($_POST["COURSETYPE"]),":COURSENO"=>doWithBindStr($_POST["COURSENO"]),
            ":COURSENAME"=>doWithBindStr($_POST["COURSENAME"]),":CLASSNO"=>doWithBindStr($_POST["CLASSNO"]),
            ":SCHOOL"=>doWithBindStr("$school"),":STATUS"=>doWithBindStr($_POST["STATUS"]),":TEACHERNAME"=>doWithBindStr($_POST["TEACHERNAME"]));

        $sql = $this->model->getSqlMap("Book/Apply/expApplyList.sql");
        $data = $this->model->sqlQuery($sql,$bind);
        $row=2;
        foreach($data as $val){
            $row++;
            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row", $val['courseno']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['coursename']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row", $val['coursetype']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['classname']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['attendents']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("F$row", $val['teachername']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("G$row", $val['schoolname']);
            $this->objPHPExcel->getActiveSheet()->setCellValueExplicit("H$row", $val['isbn'],PHPExcel_Cell_DataType::TYPE_STRING);
            $this->objPHPExcel->getActiveSheet()->setCellValueExplicit("I$row", $val['bookname'],PHPExcel_Cell_DataType::TYPE_STRING);
            $this->objPHPExcel->getActiveSheet()->setCellValue("J$row", $val['author']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("K$row", $val['press']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("L$row", $val['status']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("M$row", $val['booktime']);
        }
        //边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:M$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
        $this->download($title);
    }
    /**
     * 确认生成订单及已征订的订单导出
     */
    public function createOrder(){
        $year=$_POST["year"];
        $term=$_POST["term"];
        $status=$_POST["status"];
        $apply_ids=trim($_POST["apply_ids"]);
        $school=$_POST["school"];
        $school=$school==""?"%":$school;
        //设置宽度
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(22);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(12);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(22);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(14);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(30);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(22);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(45);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(40);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(22);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("N")->setWidth(12);
        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->objPHPExcel->getActiveSheet()->getStyle("B")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->objPHPExcel->getActiveSheet()->getStyle("D")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->objPHPExcel->getActiveSheet()->getStyle("E")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->objPHPExcel->getActiveSheet()->getStyle("F")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->objPHPExcel->getActiveSheet()->getStyle("G")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->objPHPExcel->getActiveSheet()->getStyle("L")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //重命名工作表名称
        $title="{$year}学年{$term}学期教材征订单";
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //标题设置
        $this->objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($this->style);//设置样式
        $this->objPHPExcel->getActiveSheet()->mergeCells('A1:N1');//合并A1单元格到H1
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
        $this->objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(14);//设置字体大小
        $this->objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(26);//设置行高

        //列名设置
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:N2")->applyFromArray($this->style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(22);//设置行高
        //单元格内容写入
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"生成时间");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"课号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"课程名称");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"修课方式");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"上课班级");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"班级预计人数");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"上课教师");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"订书编号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"征订代号（ISBN）");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"教材名称");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('K2',"主编");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('L2',"出版社");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('M2',"征订数量");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('N2',"开课学院");

        //查询导出数据
        $exp_sql = $this->model->getSqlMap("Book/Manage/queryExpApplyList.sql");
        $bind=array(":year"=>$year,":term"=>$term,":status"=>$status,":school"=>$school);
        if($apply_ids!=""){
            $apply_ids=rtrim($apply_ids,",");
            $exp_sql.=" and b.apply_id in ($apply_ids)";
        }
        $exp_sql.=" order by b.courseno";
        $ary=$this->model->sqlQuery($exp_sql,$bind);
        $row=2;
        foreach($ary as $val){
            $row++;
            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row", date("Y-m-d H:i:s"));
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['courseno']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row", $val['coursename']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['approaches']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['classname']);
            $this->objPHPExcel->getActiveSheet()->setCellValueExplicit("F$row", $val['students'],PHPExcel_Cell_DataType::TYPE_STRING);
            $this->objPHPExcel->getActiveSheet()->setCellValue("G$row", $val['teachername']);
            $this->objPHPExcel->getActiveSheet()->setCellValueExplicit("H$row", $val['oderno'],PHPExcel_Cell_DataType::TYPE_STRING);
            $this->objPHPExcel->getActiveSheet()->setCellValueExplicit("I$row", $val['isbn'],PHPExcel_Cell_DataType::TYPE_STRING);
            $this->objPHPExcel->getActiveSheet()->setCellValueExplicit("J$row", $val['bookname'],PHPExcel_Cell_DataType::TYPE_STRING);
            $this->objPHPExcel->getActiveSheet()->setCellValue("K$row", $val['author']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("L$row", $val['press']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("M$row", $val['sum']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("N$row", $val['name']);
        }
        if($status=="0"){
            //获取征订列表
            foreach($ary as $val){
                //更新订单状态
                $sql="update bookapply set status = '1',booktime=getdate() where apply_id=:apply_id";
                $this->model->sqlExecute($sql,array(":apply_id"=>$val["apply_id"]));
                //新增教材当前学年学期价格信息
                $sql = "select count(*) from bookprice where [year]=:year and term=:term and book_id=:book_id";
                $array=array(":year"=>$year,":term"=>$term,":book_id"=>$val["book_id"]);
                $count = $this->model->sqlCount($sql,$array);
                if(intval($count) < 1){
                    $sql="insert into bookprice(year,term,price,dis_price,dis_rate,book_id)"
                        ." values(:year,:term,null,null,null,:book_id)";
                    $this->model->sqlExecute($sql,$array);
                }
            }
        }
        //边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:N$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
        $this->download($title);
    }
}