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

use think\Log;

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
            $sheet = $array[$now]['sheet'];
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
                $PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow(0, 1, $colCount - 1, 1);
                $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(0, 1, $array[$now]['title']);
                //设置font
                $styleArray = array(
                    'font' => array('name' => '黑体', 'size' => '14'),
                    'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                );
                $PHPExcel->getActiveSheet($sheetIndex)->getStyle('A1')->applyFromArray($styleArray);
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
            $sheet = $array[$now]['sheet'];
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

    /**打印成绩单
     * @param string $filename
     * @param array $array
     */
    static function  printScore($filename='',$array=[]){

    }
}