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

namespace app\base\controller;


use app\common\access\MyAccess;
use app\common\access\MyController;
use app\common\vendor\PHPExcel;
use app\common\service\Position;
use app\common\service\TeacherLevel;
use app\common\service\TeacherType;
use app\common\service\User;

class Teacher extends MyController
{
    //显示教师类型列表信息
    public function type($page = 1, $rows = 20)
    {
        $result = null;
        try {
            $type = new TeacherType();
            $result = $type->getList($page, $rows);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //更新教师类型
    public function updatetype()
    {
        $result = null;
        try {
            $type = new TeacherType();
            $result = $type->update($_POST);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //读取职称级别
    public function level($page = 1, $rows = 20)
    {
        $result = null;
        try {
            $level = new TeacherLevel();
            $result = $level->getList($page, $rows);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //更新职称级别
    public function updatelevel()
    {
        $result = null;
        try {
            $level = new TeacherLevel();
            $result = $level->update($_POST);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //读取职称
    public function position($page = 1, $rows = 20)
    {
        $result = null;
        try {
            $position = new Position();
            $result = $position->getList($page, $rows);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //更新职称
    public function updateposition()
    {
        $result = null;
        try {
            $position = new Position();
            $result = $position->update($_POST);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //读取教师列表
    public function teacherlist($page = 1, $rows = 20, $teacherno = '%', $name = '%', $school = '')
    {
        $result = null;
        try {
            $teacher = new \app\common\service\Teacher();
            $result = $teacher->getList($page, $rows, $teacherno, $name, $school);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //更新教师信息
    public function teacherupdate()
    {
        $result = null;
        try {
            $teacher = new \app\common\service\Teacher();
            $result = $teacher->update($_POST);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    //修改密码
    public function changepassword($teacherno, $password)
    {
        $result = null;
        try {
            $user = new User();
            $result = $user->changeUserPassword($teacherno, $password);

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
        return json($result);
    }

    /**导出
     * @param $teacherno
     * @param $name
     * @param $school
     */
    public function export($teacherno, $name, $school)
    {
        try {
            $teacher = new \app\common\service\Teacher();
            $result = $teacher->getList(1, 10000, $teacherno, $name, $school);
            $data = $result['rows'];
            $file = "教师名单";
            $sheet = '全部';
            $title = '';
            $template = array("teacherno" => "教师号", "name" => "姓名", "sexname" => "性别", "positionname" => "职称", "typename" => "类型",
                "jobname" => "岗位", "enteryear" => "入校年份", "schoolname" => "学院", "rem" => "备注");
            $string = array("teacherno");
            $array[] = array("sheet" => $sheet, "title" => $title, "template" => $template, "data" => $data, "string" => $string);
            PHPExcel::export2Excel($file, $array);
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(), $e->getMessage());
        }
    }
}