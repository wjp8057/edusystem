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

require ROOT_PATH . '/vendor/PHPExcel/PHPExcel.php';

class PHPExcel
{
    /**将一个数据集导出为excel表
     * @param string $filename 保存的文件名
     * @param array $array 包含$filename保存的文件，$sheet表名，$title表格标题，$template字段模板，$data数据集，$string需要以字符存储的数组
     */
    static function  export2Excel($filename = '', $array = [])
    {
        $PHPExcel = new \PHPExcel();
        set_time_limit(0);
        /*$sheet,$title,$template,$data,$string*/
        $count = count($array);
        for ($i = 0; $i < $count; $i++) {
            $sheetIndex = $i;
            $sheet = $array[$i]['sheet'];
            $title = $array[$i]['title'];
            $template = $array[$i]['template'];
            $data = $array[$i]['data'];
            $string = $array[$i]['string'];
            //设置表名
            if ($sheetIndex != 0) $PHPExcel->createSheet(); //不是第一个表就新建一个。
            $PHPExcel->setActiveSheetIndex($sheetIndex);
            $PHPExcel->getActiveSheet()->setTitle($sheet);
            $rowIndex = 1; //行从1开始
            $start = "A" . $rowIndex; //数据区域开始单元格。
            //如果标题不为空设置标题
            $colCount = count($template);
            if ($title != "") {
                $PHPExcel->getActiveSheet($sheetIndex)->mergeCellsByColumnAndRow(0, 1, $colCount - 1, 1);
                $PHPExcel->getActiveSheet($sheetIndex)->setCellValueByColumnAndRow(0, 1, $title);
                //设置font
                $styleArray = array(
                    'font' => array('name' => '隶书', 'size' => '14', 'bold' => true),
                    'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                );
                $PHPExcel->getActiveSheet($sheetIndex)->getStyle('A1')->applyFromArray($styleArray);
                $rowIndex++;
                $start = "A" . $rowIndex;
            }
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
            $PHPExcel->getActiveSheet($sheetIndex)->getStyle($start . ':' . \PHPExcel_Cell::stringFromColumnIndex($colCount - 1) . (count($data) + 2))->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $PHPExcel->getActiveSheet($sheetIndex)->getStyle($start . ':' . \PHPExcel_Cell::stringFromColumnIndex($colCount - 1) . (count($data) + 2))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $PHPExcel->getActiveSheet($sheetIndex)->getColumnDimension()->setAutoSize(true);
            $PHPExcel->getActiveSheet($sheetIndex)->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        }
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
}