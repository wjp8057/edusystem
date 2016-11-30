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
// | Created:2016/11/29 16:23
// +----------------------------------------------------------------------

namespace app\student\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\service\QualityStudentDetail;

class Quality extends MyController{
    public function query($page,$rows,$year,$term)
    {
        try {
            $obj=new QualityStudentDetail();
            $studentno=session('S_USER_NAME');
            return $obj->getList($page,$rows,$year,$term,$studentno);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }

    }

    public function update()
    {
        $result = null;
        try {
            $obj = new QualityStudentDetail();
            $result = $obj->updateScore($_POST);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }
}