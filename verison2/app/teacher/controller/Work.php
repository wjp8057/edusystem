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
use app\common\service\WorkFile;

class Work extends  MyController{
    public function query($page=1,$rows=20){
        try{
            $work=new WorkFile();
            $teacherno= session('S_TEACHERNO');
            $result=$work->getTeacherWorkList($page,$rows,$teacherno);
             return json($result);
        }
        catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
    }
}