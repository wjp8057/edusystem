<?php

//todo:班级选课管理
class ExcelAction extends RightAction
{
     private $objPHPExcel;
     private  $md;
    public function __construct(){
        parent::__construct();
        $this->md=new SqlsrvModel();
        vendor("PHPExcel.PHPExcel");   
        $this->objPHPExcel = new PHPExcel();
        $this->objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");

    }


        //todo:选课管理------》选课统计------》申请免听、免修学生名单统计  导出excel
    public function exportexcel(){


        $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(25);
        //重命名工作表名称
        $title=$_POST["year"]."学年第".$_POST["term"]."学期学生教材明细表";
        $this->objPHPExcel->getActiveSheet()->setTitle('gagagagag');
        //合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:G1");   //todo:标题合并单元格
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',"{$_POST['e_YEAR']}学年第{$_POST['e_TERM']}学期所开课程申请免听、免修汇总表");

        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A:G")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置宽度
        //$this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动


        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        /*
                //标题设置
                $this->objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($style);//设置样式
                $this->objPHPExcel->getActiveSheet()->mergeCells('A1:F1');//合并A1单元格到F1
                $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
                $this->objPHPExcel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(14);//设置A1字体大小*/

        //列名设置
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:F2")->applyFromArray($style);//字体样式
        //列名设置
        $this->objPHPExcel->getActiveSheet()->getStyle("A1:F1")->applyFromArray($style);//字体样式
        //单元格内容写入
        //todo:边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:G2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框

        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"学号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"姓名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"课名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"课程名称");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"课程类型");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"学分");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"修课方式");
        //插入查询数据
        /*$bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
            ":isbn"=>$_POST["isbn"],":bookname"=>$_POST["bookname"],
            ":school"=>$_POST["school"],":classno"=>$_POST["classno"]);
        $sql = $this->model->getSqlMap("Book/Settle/queryPaymentList.sql");
        $data=$this->model->sqlQuery($sql,$bind);
        */
        $data=$this->md->sqlQuery($this->md->getSqlMap('CourseManager/Excel.SQL'),
            array(':year'=>$_POST['e_YEAR'],':term'=>$_POST['e_TERM'],':courseschool'=>doWithBindStr($_POST['e_CSCHOOL']),':studentschool'=>doWithBindStr($_POST['e_SSCHOOL'])));

        $row=2;

        foreach($data as $val){
            $row++;
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:G$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",trim($val['xh']).' ');
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",$val['xm']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",trim($val['kh']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",trim($val['kcmc']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("E$row",$val['kclx']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("F$row",$val['xf']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("G$row",$val['xkfs']);

        }
        $this->shuchu($title);


    }

    public function shuchu($title){
        //生成输出下载
        header('Content-Type: application/vnd.ms-excel');
        $filename = $title;//文件名转码
        header("Content-Disposition: attachment;filename=".iconv('UTF-8','GB2312',$filename.".xls"));
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');

        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header ('Cache-Control: cache, must-revalidate');
        header ('Pragma: public');

        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }


    //todo:毕业重修免听考管理
    public function exportRoom(){
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(15);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(15);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(10);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(10);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(10);

        $title = '教室列表';
        //重命名工作表名称
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:K1");   //todo:标题合并单元格
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);

        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A:K")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        $style2=array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        $this->objPHPExcel->getActiveSheet()->getStyle("A1:K1")->applyFromArray($style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:K2")->applyFromArray($style);//字体样式
        //单元格内容写入
        //todo:边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:K2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"教室号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"房间号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"简称");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"楼名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"校区");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"座位数");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"考位数");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"设施 ");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"优先学院");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"排课约束 ");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('K2',"是否保留 ");

        IF($_POST['RESERVED']>1){
            $RESERVED=0;                                                         //所有情况
            $UNRESERVED=1;
        }else{
            $RESERVED=$_POST['RESERVED'];
            $UNRESERVED=$_POST['RESERVED'];                                              //非所有情况
        };
        if($_POST['STATUS']>1){
            $STATUS=0;
            $UNSTATUS=1;                                                         //所有情况
        }else{
            $STATUS=$_POST['STATUS'];
            $UNSTATUS=$_POST['STATUS'];                                         //非所有情况
        }
        $bind=array(':ROOMNO'=>doWithBindStr($_POST['ROOMNO']),':AREA'=>doWithBindStr($_POST['AREA']),':BUILDING'=>doWithBindStr($_POST['BUILDING']),':EQUIPMENT'=>doWithBindStr($_POST['EQUIPMENT']),
            ':NO'=>doWithBindStr($_POST['NO']),':PRIORITY'=>doWithBindStr($_POST['PRIORITY']),':RESERVED'=>$RESERVED,':UNRESERVED'=>$UNRESERVED,':STATUS'=>$STATUS,':UNSTATUS'=>$UNSTATUS,
            ':SEATSDOWN'=>$_POST['SEATSDOWN'],':SEATSUP'=>$_POST['SEATSUP'],':TESTERSDOWN'=>$_POST['TESTERSDOWN'],':TESTERSUP'=>$_POST['TESTERSUP'],
            ':USAGE'=>doWithBindStr($_POST['USAGE']));
        $data =$this->md->sqlQuery($this->md->getSqlMap('room/roomexcel.SQL'),$bind);


        $row=2;//第二行开始

        foreach($data as $val){
            $row++;

            $this->objPHPExcel->getActiveSheet()->getStyle("A$row:K$row")->applyFromArray($style2);//字体样式
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:K$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",trim($val['ROOMNO']).' ');
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",trim($val['NO']).'');
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",trim($val['JSN']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",trim($val['BUILDING']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("E$row",trim($val['AREAVALUE']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("F$row",trim($val['SEATS']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("G$row",trim($val['TESTERS']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("H$row",trim($val['EQUIPMENTVALUE']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("I$row",trim($val['SCHOOLNAME']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("J$row",trim($val['USAGEVALUE']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("K$row",intval(trim($val['RESERVED']))?'是':'否');

        }
        $this->shuchu($title);
    }








    //todo;工作量管理--->工作量查看-->导出excel
    public function workload_excel(){




        $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(15);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("S")->setWidth(18);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("T")->setWidth(18);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(38);
     /*   $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(25);*/
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("P")->setWidth(38);
        //重命名工作表名称
        $title=$_POST["year_form"]."学年第".$_POST["term_form"]."学期教师工作量";
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:V1");   //todo:标题合并单元格
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        //$this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);

        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A:V")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置宽度
        //$this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动


        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        /*
                //标题设置
                $this->objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($style);//设置样式
                $this->objPHPExcel->getActiveSheet()->mergeCells('A1:F1');//合并A1单元格到F1
                $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
                $this->objPHPExcel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(14);//设置A1字体大小*/

        //列名设置
        $this->objPHPExcel->getActiveSheet()->getStyle("A1:V1")->applyFromArray($style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:V2")->applyFromArray($style);//字体样式
        //单元格内容写入
        //todo:边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:V2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"课号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"课名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"学分");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"预计人数");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"实际人数");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"周数");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"修课类型");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"课程类型");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"每周课时");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"标准班");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('K2',"班级系数");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('L2',"校正系数");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('M2',"重复系数");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('N2',"工作量");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('O2',"重复工作量");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('P2',"上课班级");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('Q2',"教师姓名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('R2',"职称");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('S2',"开课学院");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('T2',"教师所在学院");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('U2',"教师号");
         $this->objPHPExcel->setActiveSheetIndex()->setCellValue('V2',"岗位类型");
        /*
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"序号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"课号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"课名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"学分");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"预计人数");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"实际人数");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"周数");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"修课类型");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"课程类型");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"每周课时");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('K2',"标准班");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('L2',"班型系数");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('M2',"工作量");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('N2',"上课班级");*/
        //插入查询数据
        /*$bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
            ":isbn"=>$_POST["isbn"],":bookname"=>$_POST["bookname"],
            ":school"=>$_POST["school"],":classno"=>$_POST["classno"]);
        $sql = $this->model->getSqlMap("Book/Settle/queryPaymentList.sql");
        $data=$this->model->sqlQuery($sql,$bind);
        */
        /*where t.classno like :CLASSNO and RTRIM(t.courseno)+t.[group] like :COURSENO
        and t.teacherno like :TEACHERNO and t.code
like :CODE  and t.SCHOOL
like :SCHOOL*/



        $data=$this->md->sqlQuery($this->md->getSqlMap('Workload/Two_Four_excel.SQL'),array(
        ':CLASSNO'=>doWithBindStr($_POST['classno_form']),':COURSENO'=>doWithBindStr($_POST['courseno_form']),':TEACHERNO'=>doWithBindStr($_POST['teacherno_form']),':CODE'=>doWithBindStr($_POST['code_form']),
            ':SCHOOL'=>doWithBindStr($_POST['school_form'])));

       // $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(9);
        $row=2;

        foreach($data as $val){
            $row++;
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:V$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
            $km=$val['km'];
     /*       $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"课号");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"课名");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"学分");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"预计人数");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"实际人数");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"周数");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"修课类型");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"课程类型");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"每周课时");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"标准班");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('K2',"工作量");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('L2',"重复工作量");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('M2',"上课班级");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('N2',"教师名称");*/

            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",$val['courseno']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",RTRIM($val['coursename']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",trim($val['credit']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",trim($val['Estimate']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("E$row",trim($val['Attendent']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("F$row",trim($val['W_number']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("G$row",trim($val['xklx']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("H$row",trim($val['kclx']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("I$row",trim($val['mzks']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("J$row",trim($val['Standard']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("K$row",trim($val['bxxs']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("L$row",trim($val['jiaozhengxishu']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("M$row",trim($val['CFXS']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("N$row",trim($val['work']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("O$row",trim($val['cfgzl']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("P$row",trim($val['Classname']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("Q$row",trim($val['teachername']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("R$row",trim($val['zc']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("S$row",trim($val['kkxy']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("T$row",trim($val['jsxy']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("U$row",trim($val['teacherno']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("V$row",trim($val['gwlx']));

       //     $this->objPHPExcel->getActiveSheet()->setCellValue("T$row",trim($val['teachername']));
      //      $this->objPHPExcel->getActiveSheet()->setCellValue("N$row",trim($val['teachername']));
        }


        $this->shuchu($title);




    }



    //todo;考务管理--->统一排考-->导出excel
    public function kaowu_excel(){

        $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(65);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("N")->setWidth(38);
        //重命名工作表名称
        $title=$_POST["e_year"]."学年第".$_POST["e_term"].'学期名单';
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //合并单元格
      $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:H1");   //todo:标题合并单元格
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',"{$_POST['e_year']}学年第{$_POST['e_term']}学期的期末统一排考表");

        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A:H")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置宽度
        //$this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动


        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
       $style2=array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        /*
                //标题设置
                $this->objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($style);//设置样式
                $this->objPHPExcel->getActiveSheet()->mergeCells('A1:F1');//合并A1单元格到F1
                $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);//写入A1单元格内容
                $this->objPHPExcel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(14);//设置A1字体大小*/

        //列名设置
        $this->objPHPExcel->getActiveSheet()->getStyle("A1:H1")->applyFromArray($style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:H2")->applyFromArray($style);//字体样式
        //单元格内容写入
        //todo:边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:H2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框

        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"课号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"课名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"开课学院");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"主修班级");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"人数");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"统一排考");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"考试类型");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"备注");
        //插入查询数据
        /*$bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
            ":isbn"=>$_POST["isbn"],":bookname"=>$_POST["bookname"],
            ":school"=>$_POST["school"],":classno"=>$_POST["classno"]);
        $sql = $this->model->getSqlMap("Book/Settle/queryPaymentList.sql");
        $data=$this->model->sqlQuery($sql,$bind);
        */

        $data=$this->md->sqlQuery($this->md->getSqlMap('exam/examQuery_Typk_excel.SQL'),array(':year'=>$_POST['e_year'],':term'=>$_POST['e_term']
        ,':school'=>doWithBindStr($_POST['e_school']),':EXAMNO'=>doWithBindStr($_POST['e_examno'])));

        $row=2;

        foreach($data as $val){
            $row++;

            $this->objPHPExcel->getActiveSheet()->getStyle("A$row:H$row")->applyFromArray($style2);//字体样式
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:H$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
            $km=$val['km'];

            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",trim($val['kh']).' ');
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",trim($val['kcmc']).'');
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",trim($val['kkxy']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",trim($val['bj']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("E$row",$val['rs']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("F$row",$val['exam']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("G$row",$val['examtype']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("H$row",' ');
        }
        $this->shuchu($title);
    }


    //todo:考务模块->期末考试查询->导出Excel
    public function examExcel(){
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(15);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(45);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(14);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(14);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(30);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(14);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(30);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(14);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(30);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("M")->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("N")->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("O")->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("P")->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("Q")->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("R")->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("S")->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("T")->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("U")->setWidth(30);
        //重命名工作表名称
        $title=$_POST["e_YEAR"]."-".$_POST["e_TERM"].'学期';
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:U1");   //todo:标题合并单元格
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',"{$_POST['e_YEAR']}学年第{$_POST['e_TERM']}学期考试课程列表");

        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A:H")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置宽度
        //$this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动


        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        $style2=array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        //列名设置
        $this->objPHPExcel->getActiveSheet()->getStyle("A1:U1")->applyFromArray($style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:U2")->applyFromArray($style);//字体样式
        //单元格内容写入
        //todo:边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:U2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框

        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"课号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"课名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"选课人数");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"主修班级");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"考场1");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"考场1考位");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"考场2");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"考场2考位");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"考场3");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"考场3考位");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('K2',"考试时间");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('L2',"考场1教师1姓名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('M2',"考场1教师2姓名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('N2',"考场1教师3姓名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('O2',"考场2教师1姓名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('P2',"考场2教师2姓名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('Q2',"考场2教师3姓名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('R2',"考场3教师1姓名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('S2',"考场3教师2姓名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('T2',"考场3教师3姓名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('U2',"备注");
        $data=$this->md->sqlQuery($this->md->getSqlMap('exam/FinalExamQuery_excel.SQL'),array(':YEAR'=>$_POST['e_YEAR'],':TERM'=>$_POST['e_TERM']
        ,':CLASSNO'=>doWithBindStr($_POST['e_CLASSNO']),':CHANGCI'=>doWithBindStr($_POST['e_CHANGCI']),
            ':TONE'=>doWithBindStr($_POST['e_TEACHERNO']),':TTWO'=>doWithBindStr($_POST['e_TEACHERNO']),':TTHREE'=>doWithBindStr($_POST['e_TEACHERNO']),
            ':TFOUR'=>doWithBindStr($_POST['e_TEACHERNO']),':TFIVE'=>doWithBindStr($_POST['e_TEACHERNO']),':TSIX'=>doWithBindStr($_POST['e_TEACHERNO']),
            ':TSEVEN'=>doWithBindStr($_POST['e_TEACHERNO']),':TEIGHT'=>doWithBindStr($_POST['e_TEACHERNO']),':TNINE'=>doWithBindStr($_POST['e_TEACHERNO']),
        ':SONE'=>doWithBindStr($_POST['e_SCHOOL']),':STWO'=>doWithBindStr($_POST['e_SCHOOL']),':STHREE'=>doWithBindStr($_POST['e_SCHOOL']),':SFOUR'=>doWithBindStr($_POST['e_SCHOOL']),
            ':SFIVE'=>doWithBindStr($_POST['e_SCHOOL']),':SSIX'=>doWithBindStr($_POST['e_SCHOOL']),':SSEVEN'=>doWithBindStr($_POST['e_SCHOOL']),':SEIGHT'=>doWithBindStr($_POST['e_SCHOOL']),':SNINE'=>doWithBindStr($_POST['e_SCHOOL']),
            ':COURSESCHOOL'=>doWithBindStr($_POST['e_SCHOOLTWO'])));

        $row=2;


        $sql = 'SELECT  COURSEPLAN.CLASSNO as classno,COURSEPLAN.COURSENO+COURSEPLAN.[GROUP] as cls,RTRIM(CLASSES.CLASSNAME) as classname from COURSEPLAN
                INNER JOIN CLASSES on COURSEPLAN.CLASSNO = CLASSES.CLASSNO
                WHERE COURSEPLAN.[YEAR] = :year and COURSEPLAN.TERM = :term ';
        $data2 = $this->md->sqlQuery($sql,array(':year'=>$_POST['e_YEAR'],':term'=>$_POST['e_TERM']));
        if($data === false){
            exit('查询失败 L567');
        }
        $courseMap = array();
        foreach($data2 as $val){
            if(!array_key_exists($val['cls'],$courseMap)){
                $courseMap[$val['cls']] = "[{$val['classno']}]{$val['classname']}";
            }else{
                $courseMap[$val['cls']] .= " [{$val['classno']}] {$val['classname']} ";
            }
        }
        foreach($data as $key => $val){
            $data[$key]['CLASS'] =$courseMap[trim($val['kh'])];
        }


        foreach($data as $val){
            $row++;

            $this->objPHPExcel->getActiveSheet()->getStyle("A$row:U$row")->applyFromArray($style2);//字体样式
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:U$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
            $km=$val['km'];

            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",trim($val['kh']).' ');
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",trim($val['km']).'');
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",$val['xkrs']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",$val['CLASS']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("E$row",trim($val['kcc1']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("F$row",$val['kw1']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("G$row",trim($val['kcc2']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("H$row",$val['kw2']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("I$row",trim($val['kcc3']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("J$row",trim($val['kw3']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("K$row",trim($val['kssj']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("L$row",trim($val['JSXM1']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("M$row",trim($val['JSXM2']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("N$row",trim($val['JSXM3']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("O$row",trim($val['JSXM4']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("P$row",trim($val['JSXM5']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("Q$row",trim($val['JSXM6']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("R$row",trim($val['JSXM7']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("S$row",trim($val['JSXM8']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("T$row",trim($val['JSXM9']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("U$row",trim($val['rem']));
        }
        $this->shuchu($title);
    }

    //todo:缓考报名--> 报名查询---->导出excel
    public function baomingExcel(){

        $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(55);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(45);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(25);

        //重命名工作表名称
        $title=$_POST["e_YEAR"]."学年第".$_POST["e_TERM"].'学期名单';
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:F1");   //todo:标题合并单元格
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',"{$_POST['e_YEAR']}学年第{$_POST['e_TERM']}学期缓考报名汇总表");

        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A:F")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置宽度
        //$this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动


        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        $style2=array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        $this->objPHPExcel->getActiveSheet()->getStyle("A1:F1")->applyFromArray($style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:F2")->applyFromArray($style);//字体样式
        //单元格内容写入
        //todo:边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:F2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"学号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"姓名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"课号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"课程名称");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"开课学院");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"缓考原因");


        $data=$this->md->sqlQuery($this->md->getSqlMap('exam/enrollQuery_excel.SQL'),array(':YEAR'=>$_POST['e_YEAR'],':TERM'=>$_POST['e_TERM']
        ,':CLASSSCHOOL'=>doWithBindStr($_POST['e_SCHOOL']),':COURSESCHOOL'=>doWithBindStr($_POST['e_SCHOOLTWO']),
            ':COURSENO'=>doWithBindStr($_POST['e_COURSENO']),':STUDENTNO'=>doWithBindStr($_POST['e_STUDENTNO'])
            ));

        $row=2;




        foreach($data as $val){
            $row++;

            $this->objPHPExcel->getActiveSheet()->getStyle("A$row:F$row")->applyFromArray($style2);//字体样式
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:F$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
            $km=$val['km'];

            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",trim($val['xh']).' ');
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",trim($val['xm']).'');
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",trim($val['kh']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",trim($val['kcmc']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("E$row",trim($val['kkxy']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("F$row",trim($val['hkyy']));
        }
        $this->shuchu($title);
    }

    //todo:毕业重修免听考管理
    public function GraduationMtQuery_excel(){

        $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(55);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(25);

        //重命名工作表名称
        $title=$_POST["e_YEAR"]."学年第".$_POST["e_TERM"].'学期名单';
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:J1");   //todo:标题合并单元格
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',"{$_POST['e_YEAR']}学年第{$_POST['e_TERM']}学期申请毕业重修免听考名单");

        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A:J")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置宽度
        //$this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动


        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        $style2=array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        $this->objPHPExcel->getActiveSheet()->getStyle("A1:J1")->applyFromArray($style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:J2")->applyFromArray($style);//字体样式
        //单元格内容写入
        //todo:边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:j2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"所在学院");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"所在班级");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"学号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"姓名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"课号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"课程名称");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"学分");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"开课学院");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"修课类型");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"修课方式");

        $data=$this->md->sqlQuery($this->md->getSqlMap('exam/GuaduationMtQuery_Two_excel.SQL'),array(':YEAR'=>$_POST['e_YEAR'],':TERM'=>$_POST['e_TERM']
        ,':SSCHOOLNO'=>doWithBindStr($_POST['e_SCHOOL']),':CSCHOOLNO'=>doWithBindStr($_POST['e_SCHOOLTWO']),
            ':STUDENTNO'=>doWithBindStr($_POST['e_STUDENTNO']),':COURSENO'=>doWithBindStr($_POST['e_COURSENO']),
        ));

        $row=2;




        foreach($data as $val){
            $row++;

            $this->objPHPExcel->getActiveSheet()->getStyle("A$row:J$row")->applyFromArray($style2);//字体样式
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:J$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
            $km=$val['km'];


            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"所在学院");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"所在班级");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"学号");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"姓名");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"课号");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"课程名称");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"学分");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"开课学院");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"修课类型");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"修课方式");


            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",trim($val['szxy']).' ');
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",trim($val['szbj']).'');
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",$val['xh']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",$val['xm']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("E$row",trim($val['kh']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("F$row",trim($val['kcmc']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("G$row",trim($val['xf']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("H$row",trim($val['kkxy']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("I$row",trim($val['xklx']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("J$row",trim($val['xkfs']));

        }
        $this->shuchu($title);
    }



    //todo:学期初补考考务查询的EXcel
    public function xqCkaowuQuery_excel(){



        //重命名工作表名称
        $title=$_POST["e_YEAR"]."学年第".$_POST["e_TERM"].'学期名单';
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:N1");   //todo:标题合并单元格
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',"{$_POST['e_YEAR']}学年第{$_POST['e_TERM']}学期考务安排");

        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A:N")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置宽度
        //$this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动


        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        $style2=array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        $this->objPHPExcel->getActiveSheet()->getStyle("A1:N1")->applyFromArray($style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:N2")->applyFromArray($style);//字体样式
        //单元格内容写入
        //todo:边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:N2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"课号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"考场1");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"考位1");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"人数1");

        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"考场2");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"考位2");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"人数2");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"考场3");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"考位3");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"人数3");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('K2',"考场1监考");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('L2',"考场2监考");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('M2',"考场3监考");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('N2',"备注");


        $data=$this->md->sqlQuery($this->md->getSqlMap('exam/Excel_C_select.SQL'),array(':year'=>$_POST['e_YEAR'],':term'=>$_POST['e_TERM'],':COURSENO'=>doWithBindStr($_POST['e_COURSENO']),':SCHOOL'=>$_POST['e_school']
        ));

        $row=2;





        foreach($data as $val){
            $row++;

            $this->objPHPExcel->getActiveSheet()->getStyle("A$row:N$row")->applyFromArray($style2);//字体样式
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:N$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
            $km=$val['km'];




            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",trim($val['kh']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",trim($val['kc1']).'');
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",$val['kw1']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",$val['rs1']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("E$row",trim($val['kc2']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("F$row",trim($val['kw2']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("G$row",trim($val['rs2']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("H$row",trim($val['kc3']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("I$row",trim($val['kw3']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("J$row",trim($val['rs3']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("K$row",trim($val['kc1jk']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("L$row",trim($val['kc2jk']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("M$row",trim($val['kc3jk']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("N$row",trim($val['rem']));
        }

        $this->shuchu($title);
    }

    //todo:开课计划的excel
    public function coursePlan_one(){
  //重命名工作表名称
        $title=$_POST["e_YEAR"]."学年第".$_POST["e_TERM"].'学期名单';
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:N1");   //todo:标题合并单元格
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',"{$_POST['e_YEAR']}学年第{$_POST['e_TERM']}学期考务安排");

        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A:N")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置宽度
        //$this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动


        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        $style2=array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        $this->objPHPExcel->getActiveSheet()->getStyle("A1:N1")->applyFromArray($style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:N2")->applyFromArray($style);//字体样式
        //单元格内容写入
        //todo:边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:N2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"课号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"考场1");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"考位1");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"人数1");

        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"考场2");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"考位2");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"人数2");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"考场3");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"考位3");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"人数3");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('K2',"考场1监考");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('L2',"考场2监考");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('M2',"考场3监考");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('N2',"备注");


        $data=$this->md->sqlQuery($this->md->getSqlMap('exam/Excel_C_select.SQL'),array(':year'=>$_POST['e_YEAR'],':term'=>$_POST['e_TERM'],':COURSENO'=>doWithBindStr($_POST['e_COURSENO'])
        ));

        $row=2;





        foreach($data as $val){
            $row++;

            $this->objPHPExcel->getActiveSheet()->getStyle("A$row:N$row")->applyFromArray($style2);//字体样式
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:N$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
            $km=$val['km'];




            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",trim($val['kh']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",trim($val['kc1']).'');
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",$val['kw1']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",$val['rs1']);
            $this->objPHPExcel->getActiveSheet()->setCellValue("E$row",trim($val['kc2']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("F$row",trim($val['kw2']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("G$row",trim($val['rs2']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("H$row",trim($val['kc3']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("I$row",trim($val['kw3']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("J$row",trim($val['rs3']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("K$row",trim($val['kc1jk']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("L$row",trim($val['kc2jk']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("M$row",trim($val['kc3jk']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("N$row",trim($val['rem']));
        }

        $this->shuchu($title);
    }


    //todo:班级导出excel功能
    public function classExcel(){
        $title="班级列表";


        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:F1");   //todo:标题合并单元格
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',"班级列表");
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(30);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(30);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(30);

        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A:F")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置宽度
        //$this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动


        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        $style2=array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        $this->objPHPExcel->getActiveSheet()->getStyle("A1:F1")->applyFromArray($style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:F2")->applyFromArray($style);//字体样式
        //单元格内容写入
        //todo:边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:F2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"班级编号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"班级名称");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"所属学院");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"预计人数");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"实际人数");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"入学日期");

      /* WHERE Classes.ClassNo like :CLASSNO
and Classes.ClassName like :CLASSNAME
and Classes.School like :SCHOOL
and CAST(YEAR(Classes.YEAR) AS CHAR) LIKE :YEAR*/
       $str= $this->md->getSqlMap('Class/classExcel.SQL');

        $bind = array(':CLASSNO'=>doWithBindStr($_POST['classno_e']),
            ':CLASSNAME'=>doWithBindStr($_POST['classname_e']),':SCHOOL'=>doWithBindStr($_POST['school_e']),
            ':YEAR'=>$_POST['year_e']."%" );
        $data=$this->md->sqlQuery($this->md->getSqlMap('Class/classExcel.SQL'),$bind);
//        varDebug($data);
        $row=2;



        foreach($data as $val){
            $row++;

            $this->objPHPExcel->getActiveSheet()->getStyle("A$row:F$row")->applyFromArray($style2);//字体样式
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:F$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
            $km=$val['km'];




            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",' '.trim($val['CLASSNO']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",trim($val['CLASSNAME']).'');
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",trim($val['SCHOOLNAME']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",trim($val['STUDENTS']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("E$row",trim($val['COUNTS']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("F$row",trim($val['YEAR']));
        }

        $this->shuchu($title);
    }


    public function class_studentList(){
        $title=$_POST['classno_e']."学生列表";
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:D1");   //todo:标题合并单元格
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $tt=$this->md->sqlFind('select CLASSNAME from classes where classno=:classno',array(':classno'=>$_POST['classno_e']));
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',trim($tt['CLASSNAME'])."的学生列表");

        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(30);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(30);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(30);

        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A:D")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置宽度
        //$this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动


        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        $style2=array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        $this->objPHPExcel->getActiveSheet()->getStyle("A1:D1")->applyFromArray($style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:D2")->applyFromArray($style);//字体样式
        //单元格内容写入
        //todo:边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:D2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"学号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"姓名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"性别");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"学籍");



        $data=$this->md->sqlQuery($this->md->getSqlMap('Class/studentListExcel.SQL'),array(':ClassNo'=>$_POST['classno_e']));

        $row=2;



        foreach($data as $val){
            $row++;

            $this->objPHPExcel->getActiveSheet()->getStyle("A$row:D$row")->applyFromArray($style2);//字体样式
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:D$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框



            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",' '.trim($val['StudentNo']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",trim($val['Name']).'');
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",trim($val['SEXNAME']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",trim($val['Status']));
  /*          $this->objPHPExcel->getActiveSheet()->setCellValue("E$row",trim($val['Scores']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("F$row",trim($val['SchoolName']));*/
        }


        header('Content-Type: application/vnd.ms-excel');
        $filename = $title;//文件名转码
        header("Content-Disposition: attachment;filename=".iconv('UTF-8','GB2312',$title.".xls"));
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');

        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header ('Cache-Control: cache, must-revalidate');
        header ('Pragma: public');

        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
      //  $this->shuchu($title);
    }


    public function class_programList(){
        $title=$_POST['classno_e']."教学计划列表";
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:E1");   //todo:标题合并单元格
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$_POST['classno_e']."教学计划列表");
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(30);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(30);


        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A:E")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置宽度
        //$this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动


        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        $style2=array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        $this->objPHPExcel->getActiveSheet()->getStyle("A1:E1")->applyFromArray($style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:E2")->applyFromArray($style);//字体样式
        //单元格内容写入
        //todo:边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:E2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"教学计划号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"教学计划名称");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"制订学院");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"计划类别");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"计划备注");


        /* WHERE Classes.ClassNo like :CLASSNO
  and Classes.ClassName like :CLASSNAME
  and Classes.School like :SCHOOL
  and CAST(YEAR(Classes.YEAR) AS CHAR) LIKE :YEAR*/
        //   $str= $this->md->getSqlMap('Class/classExcel.SQL');

        $data=$this->md->sqlQuery($this->md->getSqlMap('Class/programExcel.SQL'),array(':Classno'=>$_POST['classno_e']));

        $row=2;




        foreach($data as $val){
            $row++;

            $this->objPHPExcel->getActiveSheet()->getStyle("A$row:E$row")->applyFromArray($style2);//字体样式
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:E$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
            // $km=$val['km'];

/*            columns:[[{field:'CLASSNO',checkbox:true},{field:'PROGRAMNO',title:'教学计划号',align:'center',width:100},
                {field:'PROGRAMNAME',title:'教学计划名称',align:'center',width:250},
                {field:'SCHOOLNAME',title:'制订学院',align:'center',width:100},
                {field:'PROGRAMTYPEVALUE',title:'计划类别',align:'center',width:100},
                {field:'REM',title:'计划备注',align:'center',width:150}*/

            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",' '.trim($val['PROGRAMNO']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",trim($val['PROGRAMNAME']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",trim($val['SCHOOLNAME']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",trim($val['PROGRAMTYPEVALUE']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("E$row",trim($val['REM']));
        }

        $this->shuchu($title);
    }

    public function TeacherCourse_Excel(){
        $title="学生列表";
        $this->objPHPExcel->getActiveSheet()->setTitle($title);

        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(9);
        $this->objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(4);

        //设置个别列内容居中方式
        //设置默认内容垂直居左
        $this->objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        $style2=array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        //合并单元格,标题
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:U1");   //todo:标题合并单元格
        $this->objPHPExcel->getActiveSheet()->getStyle("A1")->applyFromArray($style);
        $this->objPHPExcel->getActiveSheet()->getStyle("A1")->getFont()->setSize(14);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',"宁波城市学院课堂考勤记录表");

        //小标题
        $courseNo = substr($_POST['e_courseno'],0,7);
        $group = substr($_POST['e_courseno'],7);
        $class= $this->md->sqlFind("select dbo.GROUP_CONCAT_MERGE(RTRIM(cl.CLASSNAME),';') CLASSNAME from COURSEPLAN c LEFT JOIN CLASSES cl on c.CLASSNO=cl.CLASSNO where c.courseno = :courseno AND c.[GROUP]=:group AND c.YEAR=:YEAR AND c.TERM=:TERM ",array(":courseno"=>$courseNo,":group"=>$group,':YEAR'=>$_POST['e_year'],':TERM'=>$_POST['e_term']));
        $classname=$class["CLASSNAME"];
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A2:U2");
        $this->objPHPExcel->getActiveSheet()->getStyle("A2")->applyFromArray($style2);
        $this->objPHPExcel->getActiveSheet()->getStyle("A2")->getFont()->setSize(10);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"课号:{$_POST['e_courseno']} 课名:".trim($_POST['e_coursename'])." ".trim($_POST['e_teachername'])." 班级:$classname");
        //设置个别宽度
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(11);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(10);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(12);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(5);
        //设置个别行高
        $this->objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $this->objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
        $this->objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(12);//默认行高

        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A3:A5");   //todo:标题合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("B3:B5");   //todo:标题合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("C3:C5");   //todo:标题合并单元格
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A3',"学号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B3',"姓名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C3',"所在班级");

        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D3',"周次");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E3',"1");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F3',"2");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G3',"3");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H3',"4");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('I3',"5");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('J3',"6");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('K3',"7");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('L3',"8");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('M3',"9");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('N3',"10");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('O3',"11");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('P3',"12");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('Q3',"13");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('R3',"14");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('S3',"15");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('T3',"16");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('U3',"17");

        $sql="SELECT DISTINCT RTRIM(CAST(s.DAY AS CHAR)) [day] FROM SCHEDULEPLAN sp ".
            "LEFT OUTER JOIN SCHEDULE s ON (sp.YEAR=s.YEAR AND sp.TERM=s.TERM AND sp.COURSENO=s.COURSENO AND sp.[GROUP]=s.[GROUP]) ".
            "LEFT OUTER JOIN TIMESECTIONS t ON s.TIME=t.NAME ".
            "WHERE sp.YEAR = :YEAR AND sp.TERM = :TERM AND sp.COURSENO = :COURSENO AND sp.[GROUP] = :GROUP";
        $xq= $this->md->sqlFind($sql,array(':YEAR'=>$_POST['e_year'],':TERM'=>$_POST['e_term'],":COURSENO"=>$courseNo,":GROUP"=>$group));
        $day=$xq["day"];
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D4',"星期");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E4',$day);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F4',$day);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G4',$day);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H4',$day);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('I4',$day);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('J4',$day);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('K4',$day);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('L4',$day);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('M4',$day);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('N4',$day);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('O4',$day);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('P4',$day);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('Q4',$day);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('R4',$day);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('S4',$day);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('T4',$day);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('U4',$day);

        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D5',"节次");

        //单元格内容写入
        //todo:边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A3:U3")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A4:U4")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A5:U5")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

        $data=$this->md->sqlQuery($this->md->getSqlMap('TeacherSchedule/Excel.SQL'),array(':YEAR'=>$_POST['e_year'],':TERM'=>$_POST['e_term'],':COURSENO'=>$_POST['e_courseno']));

        $row=5;
        foreach($data as $val){
            $row++;
            $this->objPHPExcel->getActiveSheet()->getStyle("A$row:U$row")->applyFromArray($style2);//字体样式
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:U$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框

            $this->objPHPExcel->getActiveSheet()->setCellValueExplicit("A$row",trim($val['studentno']),PHPExcel_Cell_DataType::TYPE_STRING);
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",trim($val['studentname']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",trim($val['studentclass']));
        }
        $row++;
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A$row:U$row");
        $num=$row-6;
        $this->objPHPExcel->getActiveSheet()->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue("A$row","总人数：$num");

        $this->shuchu($title);
}




    public function jidian_Excel_one(){
        $classname=$this->md->sqlFind("select CLASSNAME from CLASSES where CLASSNO='{$_POST['e_classno']}'");
        $title=trim($_POST['e_year'])."学年第".trim($_POST['e_term']).'学期 '.trim($classname['CLASSNAME'])."[{$_POST['e_classno']}]的积点分排名表";
        //$title="{$_POST['e_classno']}的积点分排名表";
        //$this->objPHPExcel->getActiveSheet()->setTitle(str_replace("*","",$title));
        //合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:D1");   //todo:标题合并单元格
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(30);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(30);
        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A:D")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        //设置宽度
        //$this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动

        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        $style2=array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        $this->objPHPExcel->getActiveSheet()->getStyle("A1:D1")->applyFromArray($style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:D2")->applyFromArray($style);//字体样式
        //单元格内容写入
        //todo:边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:D2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"序号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"学号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"姓名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"积点和");

       $data=$this->md->sqlQuery($this->md->getSqlMap('Results/jidian_Excel_one.SQL'),
           array(':YEAR'=>$_POST['e_year'],':TERM'=>$_POST['e_term'],':CLASSNO'=>$_POST['e_classno']));

        $row=2;


        foreach($data as $val){
            $row++;

            $this->objPHPExcel->getActiveSheet()->getStyle("A$row:D$row")->applyFromArray($style2);//字体样式
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:D$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",' '.trim($val['row']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",trim($val['xh']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",trim($val['xm']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",trim($val['jdh']));
        }
        $this->shuchu(str_replace("*","",$title));
    }




    public function jidian_Excel_three(){

        $classname=$this->md->sqlFind("select CLASSNAME from CLASSES where CLASSNO='{$_POST['e_classno']}'");
        $title=trim($classname['CLASSNAME'])."{$_POST['e_classno']}的总成绩表";
        //$title="{$classname['CLASSNAME']}{$_POST['e_classno']}的积点分排名表";


        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:D1");   //todo:标题合并单元格
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(30);
        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A:D")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        //设置宽度
        //$this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动

        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        $style2=array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        $this->objPHPExcel->getActiveSheet()->getStyle("A1:D1")->applyFromArray($style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:D2")->applyFromArray($style);//字体样式
        //单元格内容写入
        //todo:边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:D2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"序号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"学号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"姓名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"积点和");

        $data=$this->md->sqlQuery($this->md->getSqlMap('Results/jidian_Excel_three.SQL'),
            array(':YONE'=>$_POST['e_year'],':TONE'=>$_POST['e_term'],':YTWO'=>$_POST['e_year_two'],':TTWO'=>$_POST['e_term_two'],':CLASSNO'=>$_POST['e_classno']));

        $row=2;


        foreach($data as $val){
            $row++;

            $this->objPHPExcel->getActiveSheet()->getStyle("A$row:D$row")->applyFromArray($style2);//字体样式
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:D$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",' '.trim($val['row']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",trim($val['xh']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",trim($val['xm']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",trim($val['jdh']));
        }
        $this->shuchu($title);
    }


    public function jidian_Excel_two(){
        $cnm=$this->md->sqlFind("select CLASSNAME from CLASSES where CLASSNO='{$_POST['e_classno']}'");

        $title=trim($cnm['CLASSNAME'])."{$_POST['e_classno']}的总成绩表";
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:D1");   //todo:标题合并单元格
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(24);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(24);
        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A:D")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        //设置宽度
        //$this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动

        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        $style2=array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        $this->objPHPExcel->getActiveSheet()->getStyle("A1:D1")->applyFromArray($style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:D2")->applyFromArray($style);//字体样式
        //单元格内容写入
        //todo:边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:D2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"学号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"姓名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"班级");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"总积点和");

        $data=$this->md->sqlQuery($this->md->getSqlMap('Results/jidian_Excel_two.SQL'),
            array(':CLASSNO'=>$_POST['e_classno']));

        $row=2;


        foreach($data as $val){
            $row++;

            $this->objPHPExcel->getActiveSheet()->getStyle("A$row:D$row")->applyFromArray($style2);//字体样式
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:D$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框

            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",' '.trim($val['xh']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",trim($val['xm']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",trim($val['bj']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",trim($val['jdh']));
        }
        $this->shuchu($title);
    }

    public function Excel_XJF_order(){
        $classname=$this->md->sqlFind("select CLASSNAME from CLASSES where CLASSNO='{$_POST['e_classno']}'");
        if($_POST['e_term']==0){
            $str='';
        }else{
            $str='第'.$_POST['e_term'].'学期';
        }
        $title=trim($classname['CLASSNAME'])."{$_POST['e_classno']}的{$_POST['e_year']}学年{$str}学绩分排名表";
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:D1");   //todo:标题合并单元格
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(30);
        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A:D")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        //设置宽度
        //$this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动

        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        $style2=array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        $this->objPHPExcel->getActiveSheet()->getStyle("A1:D1")->applyFromArray($style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:D2")->applyFromArray($style);//字体样式
        //单元格内容写入
        //todo:边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:D2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"序号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"学号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"姓名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"学绩分");

        $data=$this->md->sqlQuery($this->md->getSqlMap('Results/Excel_XJF_order.SQL'),
            array(':CLASSNO'=>$_POST['e_classno'],':YEAR'=>$_POST['e_year'],':TERM'=>$_POST['e_term']));

        $row=2;


        foreach($data as $val){
            $row++;

            $this->objPHPExcel->getActiveSheet()->getStyle("A$row:D$row")->applyFromArray($style2);//字体样式
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:D$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",' '.trim($val['row']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",trim($val['xh']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",trim($val['xm']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",trim($val['xjf']));
        }
        $this->shuchu($title);
    }
	
	//todo:浏览开课计划 excel
    public function courseplanExcel(){

        $title="{$_POST['year_e']}学年第{$_POST['term_e']}学期开课计划表";
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:K1");   //todo:标题合并单元格
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(30);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(23);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(18);
        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A:K")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        //设置宽度
        //$this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动

        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        $style2=array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        $this->objPHPExcel->getActiveSheet()->getStyle("A1:K1")->applyFromArray($style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:K2")->applyFromArray($style);//字体样式
        //单元格内容写入
        //todo:边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:K2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


        $data=$this->md->sqlQuery($this->md->getSqlMap('coursePlan/courseplanExcel.SQL'),
            array(':YEAR'=>$_POST['year_e'],':TERM'=>$_POST['term_e'],':COURSENO'=>doWithBindStr($_POST['courseno_e']),
            ':GROUP'=>doWithBindStr($_POST['group_e']),':SCHOOL'=>doWithBindStr($_POST['school_e']),':COURSETYPE'=>doWithBindStr($_POST['coursetype_e']),
                ':CLASSNO'=>'%',':EXAMTYPE'=>doWithBindStr($_POST['kstype_e'])));


        $row=2;

        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"课号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"组号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"课名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"修课");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"考核");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"周次");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"开课单位");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"人数");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"学分");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"周学时");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('K2',"班级");
        //$this->objPHPExcel->setActiveSheetIndex()->setCellValue('K2',"总学时");
        foreach($data as $val){
            $row++;

            $this->objPHPExcel->getActiveSheet()->getStyle("A$row:K$row")->applyFromArray($style2);//字体样式
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:K$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",' '.trim($val['COURSENO']));
            $this->objPHPExcel->getActiveSheet()->setCellValueExplicit("B$row", trim($val['GROUP']), PHPExcel_Cell_DataType::TYPE_STRING);
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",trim($val['COURSENAME']));

            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",trim($val['COURSETYPE']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("E$row",trim($val['EXAMTYPE']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("F$row",' '.str_pad(trim(strrev(decbin($val['WEEKS']))),18,0));
            $this->objPHPExcel->getActiveSheet()->setCellValue("G$row",trim($val['SCHOOLNAME']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("H$row",trim($val['ATTENDENTS']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("I$row",trim($val['CREDITS']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("J$row",trim($val['HOURS']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("K$row",trim($val['CLASSNAME']));
       //     $this->objPHPExcel->getActiveSheet()->setCellValue("K$row",trim($val['TOTAL']));
        }
        $this->shuchu($title);
    }


    //todo:考试地点设置Excel
    public function roomExcel(){
        $title="考试安排";
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:K1");   //todo:标题合并单元格
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(30);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(10);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(15);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(28);
        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A:K")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        //设置宽度
        //$this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动

        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        $style2=array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        $this->objPHPExcel->getActiveSheet()->getStyle("A1:K1")->applyFromArray($style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:K2")->applyFromArray($style);//字体样式
        //单元格内容写入
        //todo:边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:K2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


        $data=$this->md->sqlQuery($this->md->getSqlMap('exam/roomExcel.SQL'),
            array(':COURSESCHOOL'=>'%',':YEAR'=>$_POST['e_year'],
            ':TERM'=>$_POST['e_term'],
            ':CLASSSCHOOL'=>'%',
                         ':CLASSNO'=>'%',':CHANGCI'=>'%',':COURSENO'=>'%',':examType'=>'M'));


        $row=2;

        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"课号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"课名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"选课人数");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"考场1");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"考位1");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"考场2");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"考位2");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"考场3");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"考位3");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"考试时间");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('K2',"班级");
        //$this->objPHPExcel->setActiveSheetIndex()->setCellValue('K2',"总学时");
        foreach($data as $val){
            $row++;

            $this->objPHPExcel->getActiveSheet()->getStyle("A$row:K$row")->applyFromArray($style2);//字体样式
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:K$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",' '.trim($val['kh']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",trim($val['km']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",trim($val['xkrs']));

            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",trim($val['kcc1']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("E$row",trim($val['kw1']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("F$row",trim($val['kcc2']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("G$row",trim($val['kw2']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("H$row",trim($val['kcc3']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("I$row",trim($val['kw3']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("J$row",trim($val['kssj']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("K$row",trim($val['bj']));
            //     $this->objPHPExcel->getActiveSheet()->setCellValue("K$row",trim($val['TOTAL']));
        }
        $this->shuchu($title);

    }


    public function cet4Excel(){
        $title="成绩列表";
        $data = array();
        //$this->objPHPExcel->getActiveSheet()->setTitle($title);  //移到后面
        //合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:G1");   //todo:标题合并单元格
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);
    /*    $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(30);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(10);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(15);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(28);*/
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(20);
        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A:G")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        //设置宽度
        //$this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动

        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        $style2=array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        $this->objPHPExcel->getActiveSheet()->getStyle("A1:G1")->applyFromArray($style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:G2")->applyFromArray($style);//字体样式
        //单元格内容写入
        //todo:边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:G2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框

        /**
        if($_POST['e_type']=='A'){//todo:A级成绩
            $data=$this->md->sqlQuery($this->md->getSqlMap('Results/One_Five_yingyuA_Excel.SQL'),
                array(':STUDENTNO'=>$_POST['e_studentno'],':CLASSNO'=>$_POST['e_classno'],':school'=>$_POST['e_school']));
        }else{
            $data=$this->md->sqlQuery($this->md->getSqlMap('Results/One_Five_yingyu3_Excel.SQL'),
                array(':STUDENTNO'=>$_POST['e_studentno'],':CLASSNO'=>$_POST['e_classno'],':school'=>$_POST['e_school']));
        }
        */
        switch($_POST['e_type']){
            case 'A';
                $title="应用英语A级成绩查询";
                $data=$this->md->sqlQuery($this->md->getSqlMap('Results/One_Five_yingyuA_Excel.SQL'),
                    array(':STUDENTNO'=>doWithBindStr($_POST['e_studentno']),':CLASSNO'=>doWithBindStr($_POST['e_classno']),':school'=>doWithBindStr($_POST['e_school'])));
                break;
            case '3';
                $title="CET-3成绩查询";
                $data=$this->md->sqlQuery($this->md->getSqlMap('Results/One_Five_yingyu3_Excel.SQL'),
                    array(':STUDENTNO'=>doWithBindStr($_POST['e_studentno']),':CLASSNO'=>doWithBindStr($_POST['e_classno']),':school'=>doWithBindStr($_POST['e_school'])));
                break;
            case 'B';
                $title="应用英语B级成绩查询";
                $data=$this->md->sqlQuery($this->md->getSqlMap('Results/One_Five_yingyuBExcel.SQL'),
                    array(':STUDENTNO'=>doWithBindStr($_POST['e_studentno']),':CLASSNO'=>doWithBindStr($_POST['e_classno']),':school'=>doWithBindStr($_POST['e_school'])));
                break;
            case 'Si';
                $title="CET-4成绩查询";
                $data=$this->md->sqlQuery($this->md->getSqlMap('Results/One_Five_yingyu4Excel.SQL'),
                    array(':STUDENTNO'=>doWithBindStr($_POST['e_studentno']),':CLASSNO'=>doWithBindStr($_POST['e_classno']),':school'=>doWithBindStr($_POST['e_school'])));
                break;
            case 'Liu';
                $title="CET-6成绩查询";
                $data=$this->md->sqlQuery($this->md->getSqlMap('Results/One_Five_yingyu6Excel.SQL'),
                    array(':STUDENTNO'=>doWithBindStr($_POST['e_studentno']),':CLASSNO'=>doWithBindStr($_POST['e_classno']),':school'=>doWithBindStr($_POST['e_school'])));
                break;
            case 'Jisuanji';
                $title="计算机等级统考成绩查询";
                $data=$this->md->sqlQuery($this->md->getSqlMap('Results/One_Five_jisuanjiExcel.SQL'),
                    array(':STUDENTNO'=>doWithBindStr($_POST['e_studentno']),':CLASSNO'=>doWithBindStr($_POST['e_classno']),':school'=>doWithBindStr($_POST['e_school'])));
                break;
            case 'Putonghua';
                $title="普通话等级考试成绩查询";
                $data=$this->md->sqlQuery($this->md->getSqlMap('Results/One_Five_putonghuaExcel.SQL'),
                    array(':STUDENTNO'=>doWithBindStr($_POST['e_studentno']),':CLASSNO'=>doWithBindStr($_POST['e_classno']),':school'=>doWithBindStr($_POST['e_school'])));
                break;


            default:
                $title='系统异常';
        }
/*
        var_dump($_POST);echo "<br />";
        var_dump($data);echo "<br />";
        var_dump($title);echo "<br />";
        var_dump($this->md->getDbError());echo "<br />";
        var_dump($this->md->getLastSql());echo "<br />";
        exit;
*/
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);
/*        $data=$this->md->sqlQuery($this->md->getSqlMap('exam/roomExcel.SQL'),
            array(':COURSESCHOOL'=>'%',':YEAR'=>$_POST['e_year'],
                ':TERM'=>$_POST['e_term'],
                ':CLASSSCHOOL'=>'%',
                ':CLASSNO'=>'%',':CHANGCI'=>'%',':COURSENO'=>'%',':examType'=>'M'));*/


        $row=2;

        if(($_POST['e_type'] == 'Jisuanji') || ($_POST['e_type'] == 'Putonghua')){
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"年");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"月份");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"学号");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"姓名");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"班级");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"成绩");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"等级");
            foreach($data as $val){
                $row++;
                $this->objPHPExcel->getActiveSheet()->getStyle("A$row:G$row")->applyFromArray($style2);//字体样式
                //todo:边框设置
                $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:G$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
                $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",' '.trim($val['n']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",trim($val['yf']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",' '.trim($val['xh']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",trim($val['xm']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("E$row",trim($val['bj']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("F$row",trim($val['cj']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("G$row",trim($val['dj']));
                //     $this->objPHPExcel->getActiveSheet()->setCellValue("K$row",trim($val['TOTAL']));
            }
        }else{
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"年");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"月份");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"学号");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"姓名");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"班级");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"学院");
            $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"成绩");
            foreach($data as $val){
                $row++;
                $this->objPHPExcel->getActiveSheet()->getStyle("A$row:G$row")->applyFromArray($style2);//字体样式
                //todo:边框设置
                $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:G$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
                $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",' '.trim($val['n']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",trim($val['yf']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",' '.trim($val['xh']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",trim($val['xm']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("E$row",trim($val['bj']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("F$row",trim($val['xy']));
                $this->objPHPExcel->getActiveSheet()->setCellValue("G$row",trim($val['cj']));
                //     $this->objPHPExcel->getActiveSheet()->setCellValue("K$row",trim($val['TOTAL']));
            }
        }

        $this->shuchu($title);
    }


    public function workload_huizong(){
       $title="工作量汇总";
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:H1");   //todo:标题合并单元格
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);
        /*    $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(20);
            $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(30);
            $this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(10);
            $this->objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(15);
            $this->objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);
            $this->objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(25);
            $this->objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(28);*/
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(20);
        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A:H")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        //设置宽度
        //$this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动

        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        $style2=array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        $this->objPHPExcel->getActiveSheet()->getStyle("A1:H1")->applyFromArray($style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:H2")->applyFromArray($style);//字体样式
        //单元格内容写入
        //todo:边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:H2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框

            $data=$this->md->sqlQuery($this->md->getSqlMap('workload/Two_Five_excel.SQL'),
                array(':code'=>doWithBindStr($_POST['e_code']),':name'=>doWithBindStr($_POST['e_name']),':school'=>doWithBindStr($_POST['e_school'])));
        /*        $data=$this->md->sqlQuery($this->md->getSqlMap('exam/roomExcel.SQL'),
                    array(':COURSESCHOOL'=>'%',':YEAR'=>$_POST['e_year'],
                        ':TERM'=>$_POST['e_term'],
                        ':CLASSSCHOOL'=>'%',
                        ':CLASSNO'=>'%',':CHANGCI'=>'%',':COURSENO'=>'%',':examType'=>'M'));*/


        $row=2;
       // 教师所在部门 开课学院 教师号 姓名 性别 职称 岗位类型 工作量总计
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"教师所在部门");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"开课学院");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"教师号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"姓名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"性别");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"职称");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"岗位类型");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"工作量总计");

        foreach($data as $val){
            $row++;

            $this->objPHPExcel->getActiveSheet()->getStyle("A$row:H$row")->applyFromArray($style2);//字体样式
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:H$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",' '.trim($val['tname']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",trim($val['cname']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",' '.trim($val['teacherno']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",trim($val['teachername']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("E$row",trim($val['sex']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("F$row",trim($val['zc']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("G$row",trim($val['gwlx']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("H$row",trim($val['work']));

            //     $this->objPHPExcel->getActiveSheet()->setCellValue("K$row",trim($val['TOTAL']));
        }
        $this->shuchu($title);
    }

    //导出全校课表
    public function scheduleExcel(){
        $title="第".$_POST["YEAR"]."学年，第".$_POST["TERM"]."学期课程总表";
        $this->objPHPExcel->getActiveSheet()->setTitle($title);
        //合并单元格
        $this->objPHPExcel->getActiveSheet(0)->mergeCells("A1:N1");   //todo:标题合并单元格
        //设置默认字体和大小
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName("宋体");
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A1',$title);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(30);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(25);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(20);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(30);
        //设置个别列内容居中
        $this->objPHPExcel->getActiveSheet()->getStyle("A:N")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        //设置宽度
        //$this->objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);//设置宽度主动

        //设置单元格字体加粗，居中样式
        $style=array('font' => array('bold' => true,'color'=>array('argb' => '00000000')),'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        $style2=array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

        $this->objPHPExcel->getActiveSheet()->getStyle("A1:N1")->applyFromArray($style);//字体样式
        $this->objPHPExcel->getActiveSheet()->getStyle("A2:N2")->applyFromArray($style);//字体样式
        //单元格内容写入
        //todo:边框设置
        $this->objPHPExcel->getActiveSheet(0)->getStyle("A2:N2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框


        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('A2',"课号");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('B2',"课名");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('C2',"星期");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('D2',"时段");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('E2',"教师");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('F2',"教室");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('G2',"教学任务");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('H2',"周总学时");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('I2',"单双周");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('J2',"周次");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('K2',"班级");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('L2',"开课学院");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('M2',"选课人数");
        $this->objPHPExcel->setActiveSheetIndex()->setCellValue('N2',"类型");

        $bind = $this->md->getBind("YEAR,TERM,COURSENO,GROUP,CLASSNO,APPROACHES,EXAMTYPE,ROOMR,UNIT,DAY,TIME,ROOMNO,CLASSNAME,TEACHERNO,TEACHERNAME,COURSENAME,SCHOOL,COURSETYPE,ROOMTYPE,ORDER",$_REQUEST,"%");
        $sql = $this->md->getSqlMap("Schedule/scheduleQueryByCourseNo.sql");
        $data=$this->md->sqlQuery($sql." ORDER BY {$_POST['ORDER']}",$bind);

        $row=2;
        foreach($data as $val){
            $row++;
            $this->objPHPExcel->getActiveSheet()->getStyle("A$row:N$row")->applyFromArray($style2);//字体样式
            //todo:边框设置
            $this->objPHPExcel->getActiveSheet(0)->getStyle("A$row:N$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);//设置边框
            $this->objPHPExcel->getActiveSheet()->setCellValue("A$row",trim($val['COURSENO']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("B$row",trim($val['COURSENAME']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("C$row",trim($val['DAY']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("D$row",trim($val['TIME']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("E$row",trim($val['TEACHERNAME']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("F$row",trim($val['ROOMNAME']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("G$row",trim($val['TASKNAME']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("H$row",trim($val['HOURS']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("I$row",trim($val['OEWNAME']));
            $this->objPHPExcel->getActiveSheet()->setCellValueExplicit("J$row",trim(strrev(sprintf("%018s",decbin($val['WEEKS'])))),PHPExcel_Cell_DataType::TYPE_STRING);
            $this->objPHPExcel->getActiveSheet()->setCellValue("K$row",trim($val['CLASSNAME']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("L$row",trim($val['SCHOOLNAME']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("M$row",trim($val['ATTENDENTS']));
            $this->objPHPExcel->getActiveSheet()->setCellValue("N$row",trim($val['COURSETYPE2']));
        }
        $this->shuchu($title);
    }
}