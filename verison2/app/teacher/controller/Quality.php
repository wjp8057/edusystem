<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:42
 */

namespace app\teacher\controller;
use app\common\access\MyAccess;

use app\common\access\MyController;
use app\common\service\QualityFile;

class Quality extends MyController {
    /*
     * 读取教师个人教学质量评估得分
     */
    public function query($page=1,$rows=20){
        try{
            $quality=new QualityFile();
            $teacherno= session('S_TEACHERNO');
            $result=$quality->getTeacherQualityList($page,$rows,$teacherno);
            return json($result);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
}