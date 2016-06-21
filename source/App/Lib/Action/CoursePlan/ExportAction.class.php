<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 14-5-27
 * Time: 下午2:28
 */
class ExportAction extends RightAction {
    private $PHPExcel;

    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");

        vendor("PHPExcel.PHPExcel");
        $this->PHPExcel = new PHPExcel();
        set_time_limit(0);
    }

    public function Excel1(){

        $template1 = array("COURSENO"=>"课号","GROUP"=>"组号","COURSENAME"=>"课名","CREDITS"=>"学分","LHOURS"=>"周讲课","EHOURS"=>"周实验","SHOURS"=>"周实训",
            "CHOURS"=>"周上机","KHOURS"=>"周讨论","ZHOURS"=>"周自学","WEEKS"=>"周次");
        $template2 = array("SCHOOLNAME"=>"开课学院","COURSETYPEVALUE"=>"修课方式","TIME"=>"时段要求","EXAMTYPEVALUE"=>"考核方式","ROOMTYPE"=>"教室要求",
            "FINALEXAM"=>"统一排考","DAYS"=>"排课天数","EMPROOM"=>"指定实验室","ESTIMATE"=>"预计人数","ATTENDENTS"=>"实际人数","REM"=>"备注");
        $template3 = array("CLASS"=>"上课班级","TEACHERTASK"=>"教师","CLASSTIME"=>"课程安排");
        $len = count($template1);
        //合并单元格
        $this->PHPExcel->getActiveSheet(0)->mergeCellsByColumnAndRow(0,1,$len-1,1);

        //设置标题
        $this->PHPExcel->getActiveSheet(0)->setCellValueByColumnAndRow(0,1,"第".$_REQUEST['YEAR']."学年，第".$_REQUEST['TERM']."学期的排课计划列表");
        //设置font
        $styleArray = array(
            'font' => array('name' => '隶书','size' => '14','bold' => true),
            'alignment' => array('horizontal' =>  PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
        );
        $this->PHPExcel->getActiveSheet(0)->getStyle('A1')->applyFromArray($styleArray);


        $bind = $this->model->getBind("YEAR,TERM,COURSENO,GROUP,SCHOOL,COURSETYPE,SCHEDULED,ROOMTYPE,CLASSNO,EXAMTYPE,ESTIMATEUP,ESTIMATEDOWN,ATTENDENTSUP,ATTENDENTSDOWN,DAYS",$_REQUEST,"%");
        $sql = $this->model->getSqlMap("coursePlan/ExportScheduleplan.sql");
        $data = $this->model->sqlQuery($this->formatScheduleplanSQL($sql), $bind);

        if($data!==false || count($data)>0){
            $index=2;
            foreach($data as $row){
                $j=0;
                foreach($template1 as $k=>$v){
                    $row['WEEKS'] = strrev(sprintf("%018s", decbin($row["WEEKS"])));
                    $this->PHPExcel->getActiveSheet(0)->setCellValueByColumnAndRow($j,$index,$v.'：'.$row[$k]);
                    $j++;
                }
                $this->PHPExcel->getActiveSheet(0)->getStyle('A'.$index.':'.PHPExcel_Cell::stringFromColumnIndex($len-1).$index)->applyFromArray(array("font"=>array("bold"=>true)));
                $this->PHPExcel->getActiveSheet(0)->getStyle('A'.$index.':'.PHPExcel_Cell::stringFromColumnIndex($len-1).$index)
                    ->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $this->PHPExcel->getActiveSheet(0)->getStyle('A'.$index.':'.PHPExcel_Cell::stringFromColumnIndex($len-1).$index)
                    ->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $this->PHPExcel->getActiveSheet(0)->getStyle('A'.$index.':'.PHPExcel_Cell::stringFromColumnIndex($len-1).$index)
                    ->getFill()->getStartColor()->setARGB('E0E0E0');
                $index++;

                $j=0;
                foreach($template2 as $k=>$v){
                    $this->PHPExcel->getActiveSheet(0)->setCellValueByColumnAndRow($j,$index,$v.'：'.$row[$k]);
                    $j++;
                }
                $index++;

                foreach($template3 as $k=>$v){
                    $this->PHPExcel->getActiveSheet(0)->setCellValueByColumnAndRow(0,$index,$v.'：'.$row[$k]);
                    $this->PHPExcel->getActiveSheet(0)->mergeCellsByColumnAndRow(0,$index,$len-1,$index);
                    $index++;
                }
                $this->PHPExcel->getActiveSheet(0)->getStyle('A'.($index-1).':'.PHPExcel_Cell::stringFromColumnIndex($len-1).($index-1))
                    ->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $this->PHPExcel->getActiveSheet(0)->getStyle('A'.($index-4).':A'.($index-1))
                    ->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $this->PHPExcel->getActiveSheet(0)->getStyle(PHPExcel_Cell::stringFromColumnIndex($len-1).($index-4).':'.PHPExcel_Cell::stringFromColumnIndex($len-1).($index-1))
                    ->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            }
        }
        $this->sendExcel($_REQUEST['YEAR'].'年'.$_REQUEST['TERM'].'学期Excel报表.xls');
    }

    public function Excel2(){

        $template = array("COURSENO"=>"课号","COURSENAME"=>"课名","COURSETYPEVALUE"=>"修课方式","EXAMTYPEVALUE"=>"考核方式","CREDITS"=>"学分",
            "TOTAL"=>"总学时","LHOURS"=>"周讲课","EHOURS"=>"周实验","CHOURS"=>"周上机","SHOURS"=>"周实训","KHOURS"=>"周讨论","ZHOURS"=>"周自学",
            "SCHOOLNAME"=>"学院","TIME"=>"时段要求","ROOMTYPE"=>"教室要求","FINALEXAM"=>"统一排考","DAYS"=>"排课天数","EMPROOM"=>"指定实验室",
            "ESTIMATE"=>"预计人数","ATTENDENTS"=>"实际人数","REM"=>"备注","CLASSNAME"=>"上课班级","TEACHERTASK"=>"上课教师","SXJNAME"=>"教师姓名",
            "SXJPOTION"=>"职称","SXJTASK"=>"授课任务","CLASSTIME"=>"课程安排","COURSETYPE"=>"课程类型");
        $len = count($template);

        //合并单元格
        $this->PHPExcel->getActiveSheet(0)->mergeCellsByColumnAndRow(0,1,$len-1,1);

        //设置标题
        $this->PHPExcel->getActiveSheet(0)->setCellValueByColumnAndRow(0,1,"第".$_REQUEST['YEAR']."学年，第".$_REQUEST['TERM']."学期的排课计划列表");
        $i=0;
        foreach($template as $v){
            $this->PHPExcel->getActiveSheet(0)->setCellValueByColumnAndRow($i,2,$v);
            $i++;
        }

        //设置窗口冻结
        $this->PHPExcel->getActiveSheet(0)->freezePane("A2");
        $this->PHPExcel->getActiveSheet(0)->freezePane("A3");

        //设置font
        $styleArray = array(
            'font' => array('name' => '隶书','size' => '14','bold' => true),
            'alignment' => array('horizontal' =>  PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
        );
        $this->PHPExcel->getActiveSheet(0)->getStyle('A1')->applyFromArray($styleArray);
        $this->PHPExcel->getActiveSheet(0)->getStyle('A2')->applyFromArray(array("font"=>array("bold"=>true,"size"=>12)));
        $this->PHPExcel->getActiveSheet(0)->duplicateStyle( $this->PHPExcel->getActiveSheet()->getStyle('A2'), 'B2:'.PHPExcel_Cell::stringFromColumnIndex($len-1)."2" );

        $bind = $this->model->getBind("YEAR,TERM,COURSENO,GROUP,SCHOOL,COURSETYPE,SCHEDULED,ROOMTYPE,CLASSNO,EXAMTYPE,ESTIMATEUP,ESTIMATEDOWN,ATTENDENTSUP,ATTENDENTSDOWN,DAYS",$_REQUEST,"%");
        $sql = $this->model->getSqlMap("coursePlan/ExportScheduleplan.sql");
        $data = $this->model->sqlQuery($this->formatScheduleplanSQL($sql), $bind);
        if($data!==false || count($data)>0){
            $i=3;
            foreach($data as $row){
                $j=0;
                foreach($template as $k=>$v){
                    if($k=='COURSENO')
                        $this->PHPExcel->getActiveSheet(0)->setCellValueByColumnAndRow($j,$i,$row[$k].$row['GROUP']);
                    else
                        $this->PHPExcel->getActiveSheet(0)->setCellValueByColumnAndRow($j,$i,$row[$k]);
                    $j++;
                }
                $i++;
            }
        }
        $this->PHPExcel->getActiveSheet(0)->getStyle('A2:'.PHPExcel_Cell::stringFromColumnIndex($len-1).(count($data)+2))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

        $this->sendExcel($_REQUEST['YEAR'].'年'.$_REQUEST['TERM'].'学期Excel报表(横排).xls');

    }

    public function Excel3(){
        $template = array("COURSENO"=>"课号","COURSENAME"=>"课名","CREDITS"=>"学分","LHOURS"=>"周讲课","EHOURS"=>"周实验","CHOURS"=>"周上机",
            "SHOURS"=>"周实训","KHOURS"=>"周讨论","ZHOURS"=>"周自学","ESTIMATE"=>"预计人数","ATTENDENTS"=>"实际人数",12=>"周数",
            "COURSETYPEVALUE"=>"修课",14=>"类型","HOURS"=>"每周课时",16=>"类别","标准班","班型系数","校正系数","重复课系数","工作量","重复工作量",
            "REM"=>"备注","CLASSNAME"=>"上课班级","SXJNAME"=>"教师姓名","SXJPOTION"=>"职称","SCHOOLNAME"=>"开课学院");
        $len = count($template);

        //合并单元格
        $this->PHPExcel->getActiveSheet(0)->mergeCellsByColumnAndRow(0,1,$len-1,1);

        //设置标题
        $this->PHPExcel->getActiveSheet(0)->setCellValueByColumnAndRow(0,1,"第".$_REQUEST['YEAR']."学年，第".$_REQUEST['TERM']."学期的排课计划列表");
        $i=0;
        foreach($template as $v){
            $this->PHPExcel->getActiveSheet(0)->setCellValueByColumnAndRow($i,2,$v);
            $i++;
        }

        //设置窗口冻结
        $this->PHPExcel->getActiveSheet(0)->freezePane("A2");
        $this->PHPExcel->getActiveSheet(0)->freezePane("A3");

        //设置font
        $styleArray = array(
            'font' => array('name' => '隶书','size' => '14','bold' => true),
            'alignment' => array('horizontal' =>  PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
        );
        $this->PHPExcel->getActiveSheet(0)->getStyle('A1')->applyFromArray($styleArray);
        $this->PHPExcel->getActiveSheet(0)->getStyle('A2')->applyFromArray(array("font"=>array("bold"=>true,"size"=>12)));
        $this->PHPExcel->getActiveSheet(0)->duplicateStyle( $this->PHPExcel->getActiveSheet()->getStyle('A2'), 'B2:'.PHPExcel_Cell::stringFromColumnIndex($len-1)."2" );

        $bind = $this->model->getBind("YEAR,TERM,COURSENO,GROUP,SCHOOL,COURSETYPE,SCHEDULED,ROOMTYPE,CLASSNO,EXAMTYPE,ESTIMATEUP,ESTIMATEDOWN,ATTENDENTSUP,ATTENDENTSDOWN,DAYS",$_REQUEST,"%");
        $sql = $this->model->getSqlMap("coursePlan/ExportScheduleplan.sql");
        $data = $this->model->sqlQuery($this->formatScheduleplanSQL($sql), $bind);
        if($data!==false || count($data)>0){
            $i=3;
            foreach($data as $row){
                $j=0;
                foreach($template as $k=>$v){
                    if($k=='COURSENO')
                        $this->PHPExcel->getActiveSheet(0)->setCellValueByColumnAndRow($j,$i,$row[$k].$row['GROUP']);
                    else
                        $this->PHPExcel->getActiveSheet(0)->setCellValueByColumnAndRow($j,$i,$row[$k]);
                    $j++;
                }
                $i++;
            }
        }
        $this->PHPExcel->getActiveSheet(0)->getStyle('A2:'.PHPExcel_Cell::stringFromColumnIndex($len-1).(count($data)+2))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

        $this->sendExcel($_REQUEST['YEAR'].'年'.$_REQUEST['TERM'].'学期Excel报表(工作量).xls');
    }


    private function  formatScheduleplanSQL($sql){
        if(isset($_REQUEST["LOCK"]) && ($_REQUEST["LOCK"]==1 || $_REQUEST["LOCK"]==0))
            $sql = str_replace('{$SQL.LOCK}',"AND SCHEDULEPLAN.LOCK=".intval($_REQUEST["LOCK"]),$sql);
        else
            $sql = str_replace('{$SQL.LOCK}',"",$sql);
        if(isset($_REQUEST["EXAM"]) && ($_REQUEST["EXAM"]==1 || $_REQUEST["EXAM"]==0))
            $sql = str_replace('{$SQL.EXAM}',"AND SCHEDULEPLAN.EXAM=".intval($_REQUEST["EXAM"]),$sql);
        else
            $sql = str_replace('{$SQL.EXAM}',"",$sql);
        $searchType = intval($_REQUEST["SEARCHTYPE"]);
        if($searchType==2){
            $sql = str_replace('{$SQL.SRARCHTYPE}',"AND THETEACHERS.TEACHERNAME IS NULL",$sql);
        }elseif($searchType==3){
            $sql = str_replace('{$SQL.SRARCHTYPE}',"AND (THETEACHERS.TEACHERNAME IS NOT NULL AND L_ZC.JB='初级')",$sql);
        }elseif($searchType==4){
            $sql = str_replace('{$SQL.SRARCHTYPE}',"AND COURSEPLAN.CLASSNO LIKE '000000%'",$sql);
        }else{
            $sql = str_replace('{$SQL.SRARCHTYPE}',"AND (THETEACHERS.SCHOOL LIKE '".$_REQUEST["TEACHERNO"]."' OR THETEACHERS.SCHOOL IS NULL)",$sql);
        }
        return $sql;
    }

    private function sendExcel($fileName,$ver='5'){
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.iconv("UTF-8","GB2312",$fileName).'"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
		
ob_end_clean();
        $objWriter = PHPExcel_IOFactory::createWriter($this->PHPExcel, 'Excel'.$ver);
        $objWriter->save('php://output');
        exit;
    }
}