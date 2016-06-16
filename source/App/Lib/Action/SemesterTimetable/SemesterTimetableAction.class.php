<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 13-12-2
 * Time: 下午3:01
 **/
class SemesterTimetableAction extends RightAction
{
    private $md;        //存放模型对象
    private $base;      //路径
    /**
     *   学期课表
     *
     **/
    public function __construct(){
        parent::__construct();
        $this->md=new SqlsrvModel();
        $this->base='SemesterTimetable/';
    }

    //todo:查询课表
    public function queryTimetable(){
        $this->xiala('schools','schools');                          //todo:学院
        $this->xiala('coursetypeoptions','coursetypeoptions');   //todo:课程类别
        $this->xiala('courseapproaches','courseapproaches');     //todo:修课方式
        $this->xiala('examoptions','examoptions');                 //todo:考核方式
        $this->xiala('positions','positions');                        //todo;教师职称
        $this->xiala('timesections','timesections');                //todo:时段
        $this->display();
    }




    //todo:查询课表_按教室日期
    public function queryTimetable_room(){
        $this->xiala('schools','schools');                          //todo:学院
        $this->xiala('coursetypeoptions','coursetypeoptions');   //todo:课程类别
        $this->xiala('courseapproaches','courseapproaches');     //todo:修课方式
        $this->xiala('examoptions','examoptions');                 //todo:考核方式
        $this->xiala('positions','positions');                        //todo;教师职称
        $this->xiala('timesections','timesections');                //todo:时段
        $this->display();
    }




    //todo:课程列表页面
    public function is_where(){

         if($this->_hasJson){
             $one=$this->md->sqlFind($this->md->getSqlMap($this->base.'count_Timetable.SQL'),$_POST['bind']);
             if($arr['total']=$one['ROWS']){
                 $arr['rows']=$this->md->sqlQuery($this->md->getSqlMap($this->base.'select_Timetable.SQL'),array_merge($_POST['bind'],array(':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize)));
             }else{
                 $arr['rows']=array();
             }
             $this->ajaxReturn($arr,'JSON');
             exit;
         }
        $arr=array();
            array_pop($_GET);
        foreach($_GET as $key=>$val){
            $arr[$key]=urldecode($val);
        }

        $this->assign('posta',$arr);
        $this->display();
    }

    //todo:课程列表页面
    public function is_where_heng(){
        if($this->_hasJson){
             session('where',$_POST['bind']);

            $one=$this->md->sqlFind($this->md->getSqlMap($this->base.'count_Timetable.SQL'),$_POST['bind']);
            if($arr['total']=$one['ROWS']){
                $arr['rows']=$this->md->sqlQuery($this->md->getSqlMap($this->base.'select_Timetable.SQL'),array_merge($_POST['bind'],array(':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize)));
            }else{
                $arr['rows']=array();
            }
            $this->ajaxReturn($arr,'JSON');
            exit;
        }
        $arr=array();
        array_pop($_GET);
        foreach($_GET as $key=>$val){
            $arr[$key]=urldecode($val);
        }

        $this->assign('posta',$arr);
        $this->display();
    }


    //todo:只列出有时间安排的课程
    public function is_where_havetime(){
        if($this->_hasJson){

            $one=$this->md->sqlFind($this->md->getSqlMap($this->base.$_POST['count']),$_POST['bind']);
            if($arr['total']=$one['ROWS']){
                $arr['rows']=$this->md->sqlQuery($this->md->getSqlMap($this->base.$_POST['select']),array_merge($_POST['bind'],array(':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize)));
            }else{
                $arr['rows']=array();
            }
            $this->ajaxReturn($arr,'JSON');
            exit;
        }

        $arr=array();
        array_pop($_GET);
        foreach($_GET as $key=>$val){
            $arr[$key]=urldecode($val);
        }

        $this->assign('posta',$arr);
        $this->display();
    }






    //todo:导出excel
    public function exportexcel(){
        ini_set('max_execution_time', '100');
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
        $title=$_GET["year"]."学年第".$_GET["term"]."学期课程列表";
        $objPHPExcel->getActiveSheet()->setTitle('gagagagag');

        //设置默认字体和大小
        $objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(11);

        //设置默认内容居左
        $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        //设置个别列内容居中
        $objPHPExcel->getActiveSheet()->getStyle("A:O")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置宽度
        //$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动
        $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension("N")->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension("O")->setWidth(30);
        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        /*
                //标题设置
                $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($style);//设置样式
                $objPHPExcel->getActiveSheet()->mergeCells('A1:F1');//合并A1单元格到F1
                $objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
                $objPHPExcel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(14);//设置A1字体大小*/

      $arrr=$this->md->sqlQuery($this->md->getSqlMap($this->base.'select_Timetable2.SQL'),session('where'));

        $row=1;

        $ss=session('where');
        //todo:设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        //todo:设置学年学期加粗
        $objPHPExcel->getActiveSheet()->getStyle("A$row")->applyFromArray($style);
        //todo:设置边框


        $objPHPExcel->getActiveSheet(0)->mergeCells("A$row:O$row");   //todo:标题合并单元格
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A'.$row,'第'.$ss[':YEAR'].'学年,第'.$ss[':TERM'].'学期课程列表');
    $row++;
        $objPHPExcel->getActiveSheet(0)->getStyle("A$row:O$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A'.$row,"课号");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B'.$row,"课名");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C'.$row,"学分");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D'.$row,"周学时");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E'.$row,"周实验");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F'.$row,"修课");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G'.$row,"考核");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('H'.$row,"学院");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('I'.$row,"班级");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('J'.$row,"教师");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('K'.$row,"备注");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('L'.$row,"课程安排");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('M'.$row,"选课人数");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('N'.$row,"课程类型");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('O'.$row,"教师号");

        foreach($arrr as $val){

            $row++;
            //todo:设置边框
            $objPHPExcel->getActiveSheet(0)->getStyle("A$row:O$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('A'.$row,trim($val['kh']));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B'.$row,trim($val['km']));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C'.$row,trim($val['xf']));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D'.$row,trim($val['zxs']));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E'.$row,trim($val['zsy']));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F'.$row,trim($val['xk']));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G'.$row,trim($val['kh2']));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('H'.$row,trim($val['kkxy']));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('I'.$row,trim($val['bj']));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('J'.$row,trim($val['js']));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('K'.$row,trim($val['bz']));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('L'.$row,trim($val['kcap']));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('M'.$row,trim($val['xkrs']));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('N'.$row,trim($val['kclx']));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('O'.$row,trim($val['jsh']));

        }

 /*       //插入查询数据
        /*$bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
            ":isbn"=>"%".$_POST["isbn"]."%",":bookname"=>"%".$_POST["bookname"]."%",
            ":school"=>"%".$_POST["school"]."%",":classno"=>"%".$_POST["classno"]."%");
        $sql = $this->model->getSqlMap("Book/Settle/queryPaymentList.sql");
        $data=$this->model->sqlQuery($sql,$bind);
        */
 /*       $data=$this->md->sqlQuery($this->md->getSqlMap($_POST['select']),
            array(':YEAR'=>$_POST['e_YEAR'],':TERM'=>$_POST['e_TERM'],':STUDENTNO'=>'%'.$_POST['e_STUDENTNO'].'%',
                ':NAME'=>'%'.$_POST['e_NAME'].'%',':PROJECTNAME'=>'%'.$_POST['e_PROJECTNAME'].'%',':PROJECTTYPE'=>'%'.$_POST['e_PROJECTTYPE'].'%',
                ':CONE'=>$_POST['e_CONE'],':CTWO'=>$_POST['e_CTWO'],':SCHOOLNAME'=>$_POST['e_SCHOOLNAME']));*/

      /*  $row=1;
        $sum1=0;
        $sum2=0;
        foreach($data as $val){
            $row++;
            $objPHPExcel->getActiveSheet()->setCellValue("A$row", trim($val['Studentno']));
            $objPHPExcel->getActiveSheet()->setCellValue("B$row", $val['NAME']);
            $objPHPExcel->getActiveSheet()->setCellValue("C$row", trim($val['projectname']));
            $objPHPExcel->getActiveSheet()->setCellValue("D$row", $val['credit']);
            $objPHPExcel->getActiveSheet()->setCellValue("E$row", $val['certficatetime']);
            $objPHPExcel->getActiveSheet()->setCellValue("F$row", $val['firmno'].' ');
            $objPHPExcel->getActiveSheet()->setCellValue("G$row", $val['createdate']);

        }*/


        //生成输出下载
        header('Content-Type: application/vnd.ms-excel');
        $filename = urlencode($title);//文件名转码
        header("Content-Disposition: attachment;filename=$filename.xls");
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

/*
        $this->assign('YEAR',$_POST['YEAR']);
        $this->assign('TERM',$_POST['TERM']);
        $this->assign('COURSENO',$_POST['COURSENO']);
        $this->assign('COURSENAME',$_POST['COURSENAME']);
        $this->assign('GROUP',$_POST['GROUP']);
        $this->assign('SCHOOL',$_POST['SCHOOL']);
        $this->assign('COURSETYPE',$_POST['COURSETYPE']);
        $this->assign('CLASSNO',$_POST['CLASSNO']);
        $this->assign('CLASSNAME',$_POST['CLASSNAME']);
        $this->assign('APPROACHES',$_POST['APPROACHES']);
        $this->assign('EXAMTYPE',$_POST['EXAMTYPE']);
        $this->assign('ZCSELECT',$_POST['ZCSELECT']);
        $this->assign('TEACHERNAME',$_POST['TEACHERNAME']);
        $this->assign('TEACHERNO',$_POST['TEACHERNO']);
        $this->assign('TIME',$_POST['TIME']);
        $this->assign('TEACHERSCHOOL',$_POST['TEACHERSCHOOL']);
        $this->assign('DAY',$_POST['DAY']);
        $this->assign('ORDER',$_POST['ORDER']);*/