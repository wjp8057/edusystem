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
// | Created:2016/11/24 7:59
// +----------------------------------------------------------------------

namespace app\common\vendor;

require ROOT_PATH . '/vendor/PHPWord/PHPWord.php';
define('TEMPLATE_PATH',dirname(__FILE__).DS.'Template');
class PHPWord {
    private static function setHeader($filename){
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/msword');
        header('Content-Disposition: attachment;filename="' . iconv("UTF-8", "GB2312", $filename) . '.docx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        header('Set-Cookie: fileDownload=true; path=/'); //与fileDownload配合，否则无法出发成功事件
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
    }
    private static  function save2File($filename,$PHPWord){
        self::setHeader($filename);
        $objWriter = \PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
        $objWriter->save('php://output');
        exit;
    }
    public static function save2Template($data,$template,$filename){
        $temp=time().'.docx';
        $PHPWord = new \PHPWord();
        $document = $PHPWord->loadTemplate(TEMPLATE_PATH.DS.$template.'.docx');
        foreach($data as $field=>$value ){
            $document->setValue($field,$value);
        }
        $document->save($temp);
        //读取二进制文件时，需要将第二个参数设置成'rb'
        $handle = fopen($temp, "r");
        //通过filesize获得文件大小，将整个文件一下子读到一个字符串中
        $contents = fread($handle, filesize ($temp));
        fclose($handle);
        unlink($temp); //删除临时文件
        self::setHeader($filename); //设置头部信息
        echo $contents;
        exit;
    }
}