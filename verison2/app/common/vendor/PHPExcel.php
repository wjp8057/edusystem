<?php
// +----------------------------------------------------------------------
// |  [ MAKE YOUR WORK EASIER]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2016 http://www.nbcc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: fangrenfu <fangrenfu@126.com>
// +----------------------------------------------------------------------


namespace app\common\vendor;

use app\common\service\AddCredit;
use app\common\service\Student;
use app\teacher\controller\Score;

require ROOT_PATH . '/vendor/PHPExcel/PHPExcel.php';

class PHPExcel
{
    /**将文件保存为excel格式输出。
     * @param $filename
     * @param $PHPExcel
     */
    static private function save2File($filename,$PHPExcel){
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . iconv("UTF-8", "GB2312", $filename) . '.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        header('Set-Cookie: fileDownload=true; path=/'); //与fileDownload配合，否则无法出发成功事件
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $objWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5'); //设置保存的excel
        $objWriter->save('php://output');
        exit;
    }
    /**将一个数据集导出为excel表通用型
     * @param string $filename 保存的文件名
     * @param array $array 包含$filename保存的文件，$sheet表名，$title表格标题，$template字段模板，$data数据集，$string需要以字符存储的数组
     */
    static function  export2Excel($filename = '', $array = [])
    {
        $PHPExcel = new \PHPExcel();
        set_time_limit(0);
        /*$sheet,$title,$template,$data,$string*/
        $count = count($array);
        for ($now = 0; $now < $count; $now++) {
            $sheetIndex = $now;
            $sheet = mb_substr($array[$now]['sheet'],0,30);
            $template = $array[$now]['template'];
            $data = $array[$now]['data'];
            $string = $array[$now]['string'];
            //设置表名
            if ($sheetIndex != 0) $PHPExcel->createSheet(); //不是第一个表就新建一个。
            $PHPExcel->setActiveSheetIndex($sheetIndex);
            $PHPExcel->getActiveSheet()->setTitle($sheet);
            $rowIndex = 1; //行从1开始
            //如果标题不为空设置标题
            $colCount = count($template);
            if (isset($array[$now]['title'])&&$array[$now]['title'] != "") {
                $PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow(0, $rowIndex, $colCount - 1, $rowIndex);
                $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(0, $rowIndex, $array[$now]['title']);
                //设置font
                $styleArray = array(
                    'font' => array('name' => '黑体', 'size' => '14'),
                    'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                );
                $PHPExcel->getActiveSheet($sheetIndex)->getStyle('A'.$rowIndex)->applyFromArray($styleArray);
                $rowIndex++;
            }
            // 二级标题
            if (isset($array[$now]['subtitle'])&&$array[$now]['subtitle'] != "") {
                $PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow(0, $rowIndex, $colCount - 1, $rowIndex);
                $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(0, $rowIndex, $array[$now]['subtitle']);
                //设置font
                $styleArray = array(
                    'font' => array('name' => '宋体', 'size' => '10'),
                    'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                );
                $PHPExcel->getActiveSheet($sheetIndex)->getStyle('A'.$rowIndex)->applyFromArray($styleArray);
                $rowIndex++;
            }
            $contentstart = "A" . $rowIndex; //数据区域开始单元格。
            //设置基本信息
            if(isset($array[$now]['info'])) {
                $PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow(0, $rowIndex, $colCount - 1, $rowIndex);
                $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(0, $rowIndex, $array[$now]['info']);
                $rowIndex++;
            }
            $datastart = "A" . $rowIndex;
            $colIndex = 0;
            //设置列名
            foreach ($template as $v) {
                $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow($colIndex, $rowIndex, $v);
                $colIndex++;
            }
            $rowIndex++;
            foreach ($data as $row) {
                $colIndex = 0;
                foreach ($template as $k => $v) {
                    if (in_array($k, $string)) //字段名在字符串格式化列表中
                        $PHPExcel->getActiveSheet($sheetIndex)->setCellValueExplicitByColumnAndRow($colIndex, $rowIndex, $row[$k], \PHPExcel_Cell_DataType::TYPE_STRING);
                    else {
                        $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow($colIndex, $rowIndex, $row[$k]);
                    }
                    $colIndex++;
                }
                $rowIndex++;
            }
            //加边框
            $PHPExcel->getActiveSheet($sheetIndex)->getStyle($datastart . ':' . \PHPExcel_Cell::stringFromColumnIndex($colCount - 1) . ($rowIndex-1))->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $styleArray=array(
                'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical'=>\PHPExcel_Style_Alignment::VERTICAL_CENTER),
                'font' => array('name' => '宋体', 'size' => '9')
            );
            $PHPExcel->getActiveSheet($sheetIndex)->getStyle( $contentstart. ':' . \PHPExcel_Cell::stringFromColumnIndex($colCount - 1) . ($rowIndex-1))->applyFromArray($styleArray);
            $PHPExcel->getActiveSheet($sheetIndex)->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        }
        PHPExcel::save2File($filename,$PHPExcel);
    }

    /**打印课程考勤表
     * @param string $filename
     * @param array $array
     */
    static function  printCourseCheckIn($filename='',$array=[]){
        $PHPExcel = new \PHPExcel();
        set_time_limit(0);
        /*$sheet,$title,$template,$data,$string*/
        $count = count($array);
        for ($now = 0; $now < $count; $now++) {
            $sheetIndex = $now;
            $sheet = mb_substr($array[$now]['sheet'],0,30);

            $title = $array[$now]['title'];
            $info= $array[$now]['info'];
            $template = $array[$now]['template'];
            $data = $array[$now]['data'];
            $string = $array[$now]['string'];
            //设置表名
            if ($sheetIndex != 0) $PHPExcel->createSheet(); //不是第一个表就新建一个。
            $PHPExcel->setActiveSheetIndex($sheetIndex);
            $PHPExcel->getActiveSheet()->setTitle($sheet);
            $rowIndex = 1; //行从1开始
            //设置标题
            $colCount = 21; //4个基本信息+17周
            $PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow(0, 1, $colCount - 1, 1);
            $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(0, 1, $title);
            //设置font字体
            $styleArray = array(
                'font' => array('name' => '黑体', 'size' => '14'),
                'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical'=>\PHPExcel_Style_Alignment::VERTICAL_CENTER),
            );
            $PHPExcel->getActiveSheet($sheetIndex)->getStyle('A1')->applyFromArray($styleArray);
            $rowIndex++;
            $contentstart = "A" . $rowIndex;
            //设置课程基本信息
            $PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow(0, $rowIndex, $colCount - 1, $rowIndex);
            $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(0, $rowIndex, $info);
            $rowIndex++;

            $datastart = "A" . $rowIndex;
            //设置列名
            for($j=0;$j<3;$j++) {
                $PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow($j, $rowIndex, $j, $rowIndex + 2);
            }
            $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(0,$rowIndex,'学号');
            $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(1,$rowIndex,'姓名');
            $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(2,$rowIndex,'班级');
            $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(3,$rowIndex,'周次');
            $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(3,$rowIndex+1,'星期');
            $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(3,$rowIndex+2,'节次');
            $PHPExcel->getActiveSheet($sheetIndex)->getColumnDimensionByColumn(0)->setWidth(9);
            $PHPExcel->getActiveSheet($sheetIndex)->getColumnDimensionByColumn(1)->setWidth(7.5);
            $PHPExcel->getActiveSheet($sheetIndex)->getColumnDimensionByColumn(2)->setWidth(7.5);
            $PHPExcel->getActiveSheet($sheetIndex)->getColumnDimensionByColumn(3)->setWidth(7.5);
            for($j=1;$j<=17;$j++) {
                $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow($j+3,$rowIndex,$j);
                $PHPExcel->getActiveSheet($sheetIndex)->getColumnDimensionByColumn($j+3)->setWidth(3);
            }
            $rowIndex+=3;
            if(count($data)>0) {
                foreach ($data as $row) {
                    $colIndex = 0;
                    foreach ($template as $k => $v) {
                        $PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow(2, $rowIndex, 3, $rowIndex);
                        if (in_array($k, $string)) //字段名在字符串格式化列表中
                            $PHPExcel->getActiveSheet($sheetIndex)->setCellValueExplicitByColumnAndRow($colIndex, $rowIndex, $row[$k], \PHPExcel_Cell_DataType::TYPE_STRING);
                        else {
                            $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow($colIndex, $rowIndex, $row[$k]);
                        }
                        $colIndex++;
                    }
                    $rowIndex++;
                }
            }
            //加边框
            $PHPExcel->getActiveSheet($sheetIndex)->getStyle($datastart . ':' . \PHPExcel_Cell::stringFromColumnIndex($colCount - 1) . ($rowIndex-1))->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $styleArray=array(
                'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical'=>\PHPExcel_Style_Alignment::VERTICAL_CENTER),
                'font' => array('name' => '宋体', 'size' => '9')
            );
            $PHPExcel->getActiveSheet($sheetIndex)->getStyle( $contentstart. ':' . \PHPExcel_Cell::stringFromColumnIndex($colCount - 1) . ($rowIndex-1))->applyFromArray($styleArray);
            $PHPExcel->getActiveSheet($sheetIndex)->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        }
        PHPExcel::save2File($filename,$PHPExcel);
    }
    //获得一个经过处理的学生成绩记录
    private  static function getSingleScore($studentno){
        $obj=new \app\common\service\Score();
        $scoretemp=$obj->getScoreList(1,200,'','',$studentno)['rows'];
        $scoreamount=count($scoretemp);
        //清除没有成绩的项目
        $score=array();
        for($i=0;$i<$scoreamount;$i++) {
            if ($scoretemp[$i]['score']!='') {
                $single[] = array(
                    "year"=>$scoretemp[$i]['year'],
                    "term"=> $scoretemp[$i]['term'],
                    "courseapproachname"=>$scoretemp[$i]['courseapproachname'],
                    "coursename"=> $scoretemp[$i]['coursename'],
                    "credits"=>$scoretemp[$i]['credits'],
                    "score"=>$scoretemp[$i]['score'],
                    "makeup"=> $scoretemp[$i]['makeup']
                );
                $score = array_merge($score, $single);
                $single=null;
            }
        }
        return $score;
    }
    //打印成绩总表
    static function  printScore($filename='',$studentno='%',$classno='%',$graduate=0){
        $PHPExcel = new \PHPExcel();
        set_time_limit(0);
        $title_nbcc="宁波城市职业技术学院课程成绩总表";
        $title_nbu="宁波大学职教学院课程成绩总表";
        $title_style=array(
            'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical'=>\PHPExcel_Style_Alignment::VERTICAL_CENTER),
            'font' => array('name' => '黑体', 'size' => '17')
        );
        //学年学期子标题样式
        $subtitle_style=array(
            'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical'=>\PHPExcel_Style_Alignment::VERTICAL_CENTER),
            'font' => array('name' => '宋体', 'size' => '10','bold'=>true)
        );
        $normal_style=array(
            'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical'=>\PHPExcel_Style_Alignment::VERTICAL_CENTER),
            'font' => array('name' => '宋体', 'size' => '10'));
        $template=array("courseapproachname"=>"类型","coursename"=>"课程名称","credits"=>"学分","score"=>"成绩","makeup"=>"补考");
        $blank="--以下空白--";
        $addStr="创新、技能、素质学分共计";
        $graduateInfo="";//毕业论文信息
        $obj=new Student();
        $students=$obj->getList(1,200,$studentno,'%',$classno,'','','');
        $students=$students['rows'];
        $count = count($students);
        $rowIndex=1; //当前的行标
        $sheetIndex=0;
        $PHPExcel->setActiveSheetIndex($sheetIndex);
        $commonWidth=4.5;
        $courseWidth=28;
        $styles=array(
            "big"=>array( //45条
                'lines'=>30,
                'lineheight'=>23,
                'fontsize'=>10
            ),
            "normal"=>array( //60条
                'lines'=>43,
                'lineheight'=>16,
                'fontsize'=>10
            ),
            "small"=>array( //90条
                'lines'=>50,
                'lineheight'=>13.5,
                'fontsize'=>10
            ),
        );
        //设置列宽
        for($col=0;$col<10;$col++){
           $width=$commonWidth;
            if($col==1||$col==6) {
                $width = $courseWidth;
            }
            $PHPExcel->getActiveSheet($sheetIndex)->getColumnDimensionByColumn($col)->setWidth($width);
        }
        $start=0;
        for ($now = 0; $now < $count; $now++) {
            $studentno=$students[$now]['studentno'];
            //先输出学生基本信息部分
            //1.标题
            $title=$students[$now]['years']==4?$title_nbu:$title_nbcc;
            $PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow(0,$rowIndex,9, $rowIndex);
            $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(0, $rowIndex, $title);
            $PHPExcel->getActiveSheet($sheetIndex)->getStyle('A'.$rowIndex)->applyFromArray($title_style);
            $rowIndex++;

            //2.基本信息  学号 姓名  班级 学院
            $PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow(0,$rowIndex,9, $rowIndex);
            $studentinfo="学号：".$studentno." 姓名".$students[$now]['name']." 班级：".$students[$now]['classname']." 学院：".$students[$now]['schoolname'];
            $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(0, $rowIndex, $studentinfo);
            $PHPExcel->getActiveSheet($sheetIndex)->getStyle('A'.$rowIndex)->applyFromArray($subtitle_style);
            $PHPExcel->getActiveSheet($sheetIndex)->getRowDimension($rowIndex)->setRowHeight(18);
            $rowIndex++;
            //如果是毕业的，再输出一行信息
            if($graduate==1){
                $obj=new Student();
                $graduateInfo=$obj->getGraduate($studentno);
                $PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow(0,$rowIndex,9, $rowIndex);
                $studentinfo="学位:".$graduateInfo['degree']." ".$graduateInfo['majorname']."  证书号:".$graduateInfo['graduateno'];
                $info=$PHPExcel->getActiveSheet($sheetIndex)->getCellByColumnAndRow(0, $rowIndex-1);
                $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(0, $rowIndex-1,$info." 结论:".$graduateInfo['verdict']);
                $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(0, $rowIndex, $studentinfo);
                $PHPExcel->getActiveSheet($sheetIndex)->getStyle('A'.$rowIndex)->applyFromArray($subtitle_style);
                $PHPExcel->getActiveSheet($sheetIndex)->getRowDimension($rowIndex)->setRowHeight(18);
                $rowIndex++;
            }
            //读取学生成绩信息,经过处理的
            $score=self::getSingleScore($studentno);
            $scoreamount=count($score);
            if($scoreamount<=45)
                $style=$styles['big'];
            else if ($scoreamount>45&&$scoreamount<=68)
                $style=$styles['normal'];
            else
                $style=$styles['small'];
            $pageLines=$style['lines'];
            $lineHeight=$style['lineheight'];
            $normal_style["font"]["size"]=$style['fontsize'];
            //设置行高
            for($i=0;$i<$pageLines;$i++)
                $PHPExcel->getActiveSheet($sheetIndex)->getRowDimension($rowIndex+$i)->setRowHeight($lineHeight);
            $year='';
            $term='';
            $yearterm='';
            $toPrintTitle=false;
            $isFirstCol=true; //成绩两列输出，标记是否第一列
            $start=$rowIndex; //成绩记录开始的行标
            $colBase=0;
            $colBaseStr="A";
            $colBaseStrEnd="E";
            for($i=0;$i<$scoreamount;$i++){
                //快到底就剩下两行了，要判断一下
                if($rowIndex-$start<$pageLines&&$rowIndex-$start>=$pageLines-2&&($year!=$score[$i]['year']||$term!=$score[$i]['term'])){
                        //刚好切换学年学期了，那就输出以下空白
                        $PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow($colBase,$rowIndex,$colBase+4, $rowIndex);
                        $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow($colBase, $rowIndex, $blank);
                        $PHPExcel->getActiveSheet($sheetIndex)->getStyle($colBaseStr.$rowIndex.":".$colBaseStrEnd.$rowIndex)->applyFromArray($normal_style);
                        $isFirstCol=false;//切换到第二个列
                        $rowIndex=$start;
                }
                //已经到底了，到第二页，并打印标题行
                if($rowIndex-$start>=$pageLines){
                    $isFirstCol=false;
                    $toPrintTitle = true;
                    $rowIndex=$start;
                    if($year==$score[$i]['year']&&$term==$score[$i]['term']) {
                        $yearterm= $score[$i]['year']."-".( $score[$i]['year']+1)."学年 第".$term."学期(续)";
                    }
                    else{

                        $yearterm= $score[$i]['year']."-".( $score[$i]['year']+1)."学年 第".$term."学期";
                    }
                    $year = $score[$i]['year'];
                    $term = $score[$i]['term'];
                }

                $colBase=$isFirstCol?0:5;
                $colBaseStr=$isFirstCol?"A":"F";
                $colBaseStrEnd=$isFirstCol?"E":"J";
                //第二种情况，刚刚开始或者不同学年学期，需要打印标题行
                if($year==''||$year!=$score[$i]['year']||$term!=$score[$i]['term']) {
                    $year = $score[$i]['year'];
                    $term = $score[$i]['term'];
                    $yearterm=$year."-".($year+1)."学年 第".$term."学期";
                    $toPrintTitle=true;
                }
                if($toPrintTitle==true){
                    $PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow($colBase,$rowIndex,$colBase+4, $rowIndex);
                    $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow($colBase, $rowIndex, $yearterm);
                    $PHPExcel->getActiveSheet($sheetIndex)->getStyleByColumnAndRow($colBase,$rowIndex)->applyFromArray($subtitle_style);
                    $rowIndex++;
                    $colIndex=0;
                    foreach ($template as $v) {
                        $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow($colBase+$colIndex, $rowIndex, $v);
                        $colIndex++;
                    }
                    $PHPExcel->getActiveSheet($sheetIndex)->getStyle($colBaseStr.$rowIndex.":".$colBaseStrEnd.$rowIndex)->applyFromArray($subtitle_style);
                    $rowIndex++;
                    $toPrintTitle=false;
                }
                //有成绩的才输出
                if($score[$i]['score']!='') {
                    $colIndex = 0;
                    foreach ($template as $k => $v) {
                        $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow($colBase + $colIndex, $rowIndex, $score[$i][$k]);
                        $PHPExcel->getActiveSheet($sheetIndex)->getStyle($colBaseStr . $rowIndex . ":" . $colBaseStrEnd . $rowIndex)->applyFromArray($normal_style);
                        $colIndex++;
                    }
                    $rowIndex++;
                }
            }
            //创新技能学分部分。
            $obj=new AddCredit();
            $addcredit=$obj->getStudentSummary($studentno);
            $PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow($colBase, $rowIndex, $colBase + 4, $rowIndex);
            $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow($colBase, $rowIndex, $addStr.$addcredit);
            $PHPExcel->getActiveSheet($sheetIndex)->getStyle($colBaseStr . $rowIndex . ":" . $colBaseStrEnd . $rowIndex)->applyFromArray($subtitle_style);
            $rowIndex++;
            //最后打印一个以下空白
            if($rowIndex-$start<$pageLines) {
                $PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow($colBase, $rowIndex, $colBase + 4, $rowIndex);
                $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow($colBase, $rowIndex, $blank);
                $PHPExcel->getActiveSheet($sheetIndex)->getStyle($colBaseStr.$rowIndex.":".$colBaseStrEnd.$rowIndex)->applyFromArray($normal_style);
            }
            //设置课名的对齐方式
            $PHPExcel->getActiveSheet($sheetIndex)->getStyle("B". $start . ":B". ($start+$pageLines-1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setShrinkToFit(true);
            $PHPExcel->getActiveSheet($sheetIndex)->getStyle("G". $start . ":G". ($start+$pageLines-1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setShrinkToFit(true);
            //论文部分
            $rowIndex=$start+$pageLines;
            $PHPExcel->getActiveSheet($sheetIndex)->getRowDimension($rowIndex)->setRowHeight(40);
            $PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow(0, $rowIndex,5, $rowIndex);
            if($graduate==true) {
                $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(0, $rowIndex, "论文题目:" . $graduateInfo['thesis'] . "\n指导教师:" . $graduateInfo['mentor']);
                $PHPExcel->getActiveSheet($sheetIndex)->getStyle("A" . $rowIndex)->getAlignment()->setWrapText(true);
            }
            $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(6,$rowIndex,"制表人：".session('S_REAL_NAME')."\n制表日期：".date("Y年m月d日",time()));
            $PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow(7, $rowIndex,9, $rowIndex);
            $PHPExcel->getActiveSheet($sheetIndex)->getStyle("G".$rowIndex)->getAlignment()->setWrapText(true);
            $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(7,$rowIndex,"教务处盖章");
            $PHPExcel->getActiveSheet($sheetIndex)->getStyle("A". $rowIndex . ":J". $rowIndex)->applyFromArray($normal_style);
            $PHPExcel->getActiveSheet($sheetIndex)->getStyle("A". $rowIndex . ":G". $rowIndex)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $PHPExcel->getActiveSheet($sheetIndex)->getStyle( 'A'.$start.':J'.($start+$pageLines))->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $rowIndex++;
            $PHPExcel->getActiveSheet()->setBreak( 'A'.($start+$pageLines) , \PHPExcel_Worksheet::BREAK_ROW );
        }
        $PHPExcel->getActiveSheet($sheetIndex)->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $PHPExcel->getActiveSheet($sheetIndex)->getPageMargins()->setTop(0.5);
        $PHPExcel->getActiveSheet($sheetIndex)->getPageMargins()->setBottom(0.5);
        $PHPExcel->getActiveSheet($sheetIndex)->getPageMargins()->setLeft(0.5);
        $PHPExcel->getActiveSheet($sheetIndex)->getPageMargins()->setRight(0.5);
        PHPExcel::save2File($filename,$PHPExcel);

    }
}