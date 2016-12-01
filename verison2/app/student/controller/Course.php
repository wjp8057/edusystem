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
// | Created:2016/12/1 14:52
// +----------------------------------------------------------------------

namespace app\student\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\R32;
use app\common\service\ViewScheduleTable;

class Course extends MyController {
    public function query($page=1,$rows=20,$year,$term,$courseno='%',$classno='%',$coursename='%',$teachername='%',$school='',$weekday='',$time=''){
        try {
            $obj=new ViewScheduleTable();
            return $obj->getList($page,$rows,$year,$term,$courseno,$classno,$coursename,$teachername,$school,$weekday,$time);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
    public function update()
    {
        $result = null;
      //  try {
            $obj = new R32();
            $result = $obj->updateByStudent($_POST);
       /* } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }*/
        return json($result);
    }
}