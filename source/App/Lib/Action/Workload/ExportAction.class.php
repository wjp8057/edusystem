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
        vendor("PHPExcel.PHPExcel");
        $this->PHPExcel = new PHPExcel();
        set_time_limit(0);
    }

    public function workExportAll($year='2014',$term='2',$coursename='',$courseno='',$school='',$checked='',$worktype=''){

        //第一张总表
        $sheetIndex=0;
        $template = array("courseno"=>"课号","coursename"=>"课名","schoolname"=>"开课学院","classname"=>"主修班级","worktypename"=>"工作量类型","stand"=>"标准班人数","attendent"=>"人数","total"=>"总课时"
        ,"week"=>"周数","factor"=>"系数","addfactor"=>"加成系数","work"=>"工作量","settled"=>"任课分配","psettled"=>"实践分配","rem"=>"备注");
        $len = count($template);

        $this->PHPExcel->getActiveSheet()->setTitle('课程总工作量');
        $this->PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow(0,1,$len-1,1);
        //设置标题
        $this->PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(0,1,$year."学年第".$term."学期工作量分配总表");
        //设置font
        $styleArray = array(
            'font' => array('name' => '隶书','size' => '14','bold' => true),
            'alignment' => array('horizontal' =>  PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
        );
        $this->PHPExcel->getActiveSheet($sheetIndex)->getStyle('A1')->applyFromArray($styleArray);
        $i=0;
        foreach($template as $v){
            $this->PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow($i,2,$v);
            $i++;
        }
        $Obj=D('works');
        $condition['works.year']=$year;
        $condition['works.term']=$term;
        $condition['works.disable']=0;
        if($coursename!='')  $condition['courses.coursename']=array('like',$coursename);
        if($courseno!='')  $condition['works.courseno']=array('like',$courseno);
        if($school!='')  $condition['works.school']=array('like',$school);
        if($worktype!='')  $condition['works.worktype']=$worktype;
        if($checked!='')  $condition['works.checked']=(int)$checked;
        $data=$Obj->join('courses on works.courseno=courses.courseno')
            ->join('schools on schools.school=works.school')
            ->join('worktype on worktype.type=works.worktype')
            ->join('left join courseclassname as t on t.year=works.year and t.term=works.term and t.courseno=works.courseno and t.[group]=works.[groups]')
            //字段名不区分大小写
            ->field('id,works.year,works.term,works.checked, works.courseno+works.[groups] as courseno,courses.coursename,works.school,schools.name as schoolname,works.total,works.worktype,worktype.name as worktypename,works.stand,works.week
            ,works.rem,works.work,works.attendent,works.factor,works.disable,t.classname,works.settled,works.psettled,works.addfactor')
            ->where($condition)->order('courseno')->select();

        if($data!==false || count($data)>0){
            $i=3;
            foreach($data as $row){
                $j=0;
                foreach($template as $k=>$v){
                        $this->PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow($j,$i,$row[$k]);
                    $j++;
                }
                $i++;
            }
        }
        //加边框
        $this->PHPExcel->getActiveSheet($sheetIndex)->getStyle('A2:'.PHPExcel_Cell::stringFromColumnIndex($len-1).(count($data)+2))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

        $this->PHPExcel->createSheet()->setTitle("个人分配表");

        $sheetIndex=1;
        $this->PHPExcel->setActiveSheetIndex($sheetIndex);
        $template = array("courseno"=>"课号","coursename"=>"课名","schoolname"=>"开课学院","classname"=>"主修班级","worktypename"=>"工作量类型","stand"=>"标准班人数","attendent"=>"人数","total"=>"总课时"
        ,"week"=>"周数","factor"=>"系数","addfactor"=>"加成系数","work"=>"总工作量","teacherno"=>"教师号","teachername"=>"教师姓名","personalwork"=>"个人工作量","repeat"=>"重复系数",
            "repeatwork"=>"重复工作量","teachertype"=>"任务类型","teacherschoolname"=>"教师学院");
        $len = count($template);
        $this->PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow(0,1,$len-1,1);
        //设置标题
        $this->PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(0,1,$year."学年第".$term."学期个人分配表");
        //设置font
        $styleArray = array(
            'font' => array('name' => '隶书','size' => '14','bold' => true),
            'alignment' => array('horizontal' =>  PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
        );
        $this->PHPExcel->getActiveSheet($sheetIndex)->getStyle('A1')->applyFromArray($styleArray);
        $i=0;
        foreach($template as $v){
            $this->PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow($i,2,$v);
            $i++;
        }
        $Obj=D('works');
        $condition['works.year']=$year;
        $condition['works.term']=$term;
        $condition['works.disable']=0;
        if($coursename!='')  $condition['courses.coursename']=array('like',$coursename);
        if($courseno!='')  $condition['works.courseno']=array('like',$courseno);
        if($school!='')  $condition['works.school']=array('like',$school);
        if($worktype!='')  $condition['works.worktype']=$worktype;
        if($checked!='')  $condition['works.checked']=(int)$checked;
        $data=$Obj->join('courses on works.courseno=courses.courseno')
            ->join('schools on schools.school=works.school')
            ->join('worktype on worktype.type=works.worktype')
            ->join('left join courseclassname as t on t.year=works.year and t.term=works.term and t.courseno=works.courseno and t.[group]=works.[groups]')
            ->join('workdetail on workdetail.map=works.id')
            ->join('teachers on teachers.teacherno=workdetail.teacherno')
            ->join('workteachertype on workteachertype.type=workdetail.type')
            ->join('schools as s on s.school=teachers.school')
            //字段名不区分大小写
            ->field('works.year,works.term,works.checked, works.courseno+works.[groups] as courseno,courses.coursename,works.school,schools.name as schoolname,works.total,works.worktype,worktype.name as worktypename,works.stand,works.week
            ,works.rem,works.work,works.attendent,works.factor,works.disable,t.classname,works.settled,works.psettled,works.addfactor,workdetail.teacherno,workteachertype.name as teachertype,teachers.name teachername
            ,workdetail.work personalwork,workdetail.repeat as repeat,cast(workdetail.work*workdetail.repeat as decimal(8,2)) as repeatwork,s.name as teacherschoolname')
            ->where($condition)->order('courseno')->select();

        if($data!==false || count($data)>0){
            $i=3;
            foreach($data as $row){
                $j=0;
                foreach($template as $k=>$v){
                    if($k=='teacherno')
                        $this->PHPExcel->getActiveSheet($sheetIndex)->setCellValueExplicitByColumnAndRow($j,$i,$row[$k],PHPExcel_Cell_DataType::TYPE_STRING);
                    else
                        $this->PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow($j,$i,$row[$k]);
                    $j++;
                }
                $i++;
            }
        }
        //加边框
        $this->PHPExcel->getActiveSheet($sheetIndex)->getStyle('A2:'.PHPExcel_Cell::stringFromColumnIndex($len-1).(count($data)+2))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $this->sendExcel($year.'年'.$term.'学期课程工作量汇总表.xls');
    }
    public function external($year='2014',$term='2'){

        //第一张总表
        $sheetIndex=0;
        $template = array("schoolname"=>"开课学院","typename"=>"类型","levelname"=>"职称","work"=>"工作量","perwork"=>"课时标准","charge"=>"课时费");
        $len = count($template);

        $this->PHPExcel->getActiveSheet()->setTitle('外聘教师课时费统计表');
        $this->PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow(0,1,$len-1,1);
        //设置标题
        $this->PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(0,1,$year."学年第".$term."学期外聘教师课时费统计表");
        //设置font
        $styleArray = array(
            'font' => array('name' => '黑体','size' => '14'),
            'alignment' => array('horizontal' =>  PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );
        $this->PHPExcel->getActiveSheet($sheetIndex)->getStyle('A1')->applyFromArray($styleArray);
        $this->PHPExcel->getActiveSheet($sheetIndex)->getRowDimension(1)->setRowHeight(30);
        $this->PHPExcel->getActiveSheet($sheetIndex)->getColumnDimension('A')->setWidth(14);
        $this->PHPExcel->getActiveSheet($sheetIndex)->getColumnDimension('B')->setWidth(14);
        $this->PHPExcel->getActiveSheet($sheetIndex)->getColumnDimension('C')->setWidth(14);
        $this->PHPExcel->getActiveSheet($sheetIndex)->getColumnDimension('D')->setWidth(14);
        $this->PHPExcel->getActiveSheet($sheetIndex)->getColumnDimension('E')->setWidth(14);
        $i=0;
        foreach($template as $v){
            $this->PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow($i,2,$v);
            $i++;
        }
        $Obj=D('works');
        $condition=null;
        $condition['works.year']=$year;
        $condition['works.term']=$term;
        $data=$Obj->join('schools on schools.school=works.school')
            ->join('workdetail on workdetail.map=works.id')
            ->join('workteacher on  workteacher.teacherno=workdetail.teacherno  and workteacher.year=works.year')
            ->join('teachertype on teachertype.NAME=workteacher.type')
            ->join('positions on positions.name=workteacher.position')
            ->join('teacherlevel on teacherlevel.level=positions.level')
            ->join('workstand on workstand.level=positions.level and workstand.type=workteacher.type')
            ->where($condition)->where("workteacher.type in ('C','D') and works.disable=0  and workteacher.disable=0")
            ->field('rtrim(schools.name) schoolname,rtrim(teachertype.value) typename,rtrim(teacherlevel.name) levelname,teacherlevel.level as level,cast(sum(workdetail.work*workdetail.repeat) as decimal(8,2)) as work,
                  workstand.perwork,cast(sum(workdetail.work*workdetail.repeat)*workstand.perwork as decimal(8,2)) as charge')
            ->group('schools.school,schools.name,teachertype.value,teacherlevel.name,teacherlevel.level,workstand.perwork')->order('schoolname,typename,level')->select();
        if($data!==false || count($data)>0){
            $i=3;
            foreach($data as $row){
                $j=0;
                foreach($template as $k=>$v){
                    $this->PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow($j,$i,$row[$k]);
                    $j++;
                }
                $i++;
            }
        }
        //加边框
        $this->PHPExcel->getActiveSheet($sheetIndex)->getStyle('A2:'.PHPExcel_Cell::stringFromColumnIndex($len-1).(count($data)+2))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

        $this->PHPExcel->createSheet()->setTitle("外聘教师管理费统计表");
        $sheetIndex++;
        $this->PHPExcel->setActiveSheetIndex($sheetIndex);
        $template = array("schoolname"=>"开课学院","typename"=>"类型","levelname"=>"职称","work"=>"工作量","exceedperwork"=>"管理费标准","charge"=>"管理费");
        $len = count($template);
        $this->PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow(0,1,$len-1,1);
        //设置标题
        $this->PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(0,1,$year."学年第".$term."学期外聘教师管理费统计表");
        //设置font
        $this->PHPExcel->getActiveSheet($sheetIndex)->getStyle('A1')->applyFromArray($styleArray);
        $this->PHPExcel->getActiveSheet($sheetIndex)->getRowDimension(1)->setRowHeight(30);
        $this->PHPExcel->getActiveSheet($sheetIndex)->getColumnDimension('A')->setWidth(14);
        $this->PHPExcel->getActiveSheet($sheetIndex)->getColumnDimension('B')->setWidth(14);
        $this->PHPExcel->getActiveSheet($sheetIndex)->getColumnDimension('C')->setWidth(14);
        $this->PHPExcel->getActiveSheet($sheetIndex)->getColumnDimension('D')->setWidth(14);
        $this->PHPExcel->getActiveSheet($sheetIndex)->getColumnDimension('E')->setWidth(14);
        $i=0;
        foreach($template as $v){
            $this->PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow($i,2,$v);
            $i++;
        }
        $Obj=D('works');
        $condition=null;
        $condition['works.year']=$year;
        $condition['works.term']=$term;
        $data=$Obj->join('schools on schools.school=works.school')
            ->join('workdetail on workdetail.map=works.id')
            ->join('workteacher on  workteacher.teacherno=workdetail.teacherno  and workteacher.year=works.year')
            ->join('teachertype on teachertype.NAME=workteacher.type')
            ->join('positions on positions.name=workteacher.position')
            ->join('teacherlevel on teacherlevel.level=positions.level')
            ->join('workstand on workstand.level=positions.level and workstand.type=workteacher.type')
            ->where($condition)->where("workteacher.type in ('C','D') and works.disable=0  and workteacher.disable=0")
            ->field('teacherlevel.level as level,rtrim(schools.name) schoolname,rtrim(teachertype.value) typename,rtrim(teacherlevel.name) levelname,cast(sum(workdetail.work*workdetail.repeat) as decimal(8,2)) as work,
                  workstand.exceedperwork,cast(sum(workdetail.work*workdetail.repeat)*workstand.exceedperwork as decimal(8,2)) as charge')
            ->group('schools.school,schools.name,teachertype.value,teacherlevel.name,teacherlevel.level,workstand.exceedperwork')->order('schoolname,typename,level')->select();

        if($data!==false || count($data)>0){
            $i=3;
            foreach($data as $row){
                $j=0;
                foreach($template as $k=>$v){
                     $this->PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow($j,$i,$row[$k]);
                    $j++;
                }
                $i++;
            }
        }
        //加边框
        $this->PHPExcel->getActiveSheet($sheetIndex)->getStyle('A2:'.PHPExcel_Cell::stringFromColumnIndex($len-1).(count($data)+2))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

        $this->PHPExcel->createSheet()->setTitle("外聘教师工作量详细表");
        $sheetIndex++;
        $this->PHPExcel->setActiveSheetIndex($sheetIndex);
        $template = array("teacherno"=>"教师号","teachername"=>"姓名","positionname"=>"职称","typename"=>"类型","levelname"=>"级别","schoolname"=>"开课学院","courseno"=>"课号","coursename"=>"课名",'work'=>'工作量');
        $len = count($template);
        $this->PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow(0,1,$len-1,1);
        //设置标题
        $this->PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(0,1,$year."学年第".$term."学期外聘教师工作量详细表");
        //设置font
        $this->PHPExcel->getActiveSheet($sheetIndex)->getStyle('A1')->applyFromArray($styleArray);
        $this->PHPExcel->getActiveSheet($sheetIndex)->getRowDimension(1)->setRowHeight(30);
        $i=0;
        foreach($template as $v){
            $this->PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow($i,2,$v);
            $i++;
        }
        $Obj=D('works');
        $condition=null;
        $condition['works.year']=$year;
        $condition['works.term']=$term;
        $data=$Obj->join('schools on schools.school=works.school')
            ->join('workdetail on workdetail.map=works.id')
            ->join('workteacher on  workteacher.teacherno=workdetail.teacherno and workteacher.year=works.year')
            ->join('teachertype on teachertype.NAME=workteacher.type')
            ->join('positions on positions.name=workteacher.position')
            ->join('teacherlevel on teacherlevel.level=positions.level')
            ->join('workstand on workstand.level=positions.level and workstand.type=workteacher.type')
            ->join('courses on courses.courseno=works.courseno')
            ->join('teachers on teachers.teacherno=workdetail.teacherno')
            ->where($condition)->where("workteacher.type in ('C','D') and works.disable=0  and workteacher.disable=0")
            ->field('teachers.teacherno,rtrim(teachers.name) teachername,works.courseno+works.groups as courseno,
                    courses.coursename,cast(workdetail.work*workdetail.repeat as decimal(8,2)) as work,
                    rtrim(schools.name) as schoolname ,rtrim(teachertype.value) typename,rtrim(teacherlevel.NAME) as levelname,
                    rtrim(POSITIONS.value) as positionname')
            ->select();

        if($data!==false || count($data)>0){
            $i=3;
            foreach($data as $row){
                $j=0;
                foreach($template as $k=>$v){
                    $this->PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow($j,$i,$row[$k]);
                    $j++;
                }
                $i++;
            }
        }
        //加边框
        $this->PHPExcel->getActiveSheet($sheetIndex)->getStyle('A2:'.PHPExcel_Cell::stringFromColumnIndex($len-1).(count($data)+2))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

        $this->sendExcel($year.'年'.$term.'学期外聘教师工作量汇总表.xls');
    }
    private function sendExcel($fileName,$ver='5'){
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.iconv("UTF-8","GB2312",$fileName).'"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        header('Set-Cookie: fileDownload=true; path=/');
        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
        $objWriter = PHPExcel_IOFactory::createWriter($this->PHPExcel, 'Excel'.$ver);
ob_end_clean();
        $objWriter->save('php://output');
        exit;
    }
}