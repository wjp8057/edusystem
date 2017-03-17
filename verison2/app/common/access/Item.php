<?php
// +----------------------------------------------------------------------
// |  [ MAKE YOUR WORK EASIER]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2016 http://www.nbcc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: fangrenfu <fangrenfu@126.com> 2016/6/19 10:20
// +----------------------------------------------------------------------

namespace app\common\access;

use think\Db;
use think\Exception;

/**获取各种对象的关键属性
 * Class Item
 * @package app\common\access
 */
class Item {
    /**根据教室号获取教室信息
     * @param $roomno
     * @param bool $alert
     * @return null
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public static function  getRoomItem($roomno,$alert=true){
        $condition=null;
        $result=null;
        $condition['roomno']=$roomno;
        $data=Db::table('classrooms')->where($condition)->field('rtrim(jsn) name,status,reserved')->select();
        if(!is_array($data)||count($data)!=1) {
            if($alert)
                throw new Exception('roomno:' . $roomno, MyException::ITEM_NOT_EXISTS);
        }
        else
            $result=$data[0];
        return $result;
    }

    /**根据课号获取课程
     * @param string $courseno
     * @param bool $alert
     * @return null
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public static function getCourseItem($courseno,$alert=true){
        $result=null;
        $condition=null;
        $condition['courseno']=substr($courseno,0,7);
        $data=Db::table('courses')
            ->where($condition)->field('rtrim(coursename) as coursename,courseno,credits,hours,school,worktype')->select();
        if(!is_array($data)||count($data)!=1) {
            if($alert)
                throw new Exception('courseno:'.$courseno, MyException::ITEM_NOT_EXISTS);
        }
        else
            $result=$data[0];
        return $result;
    }

    /**获取教学计划
     * @param $programno
     * @param bool $alert
     * @return null
     * @throws Exception
     */
    public static function getProgramItem($programno,$alert=true){
        $result=null;
        $condition=null;
        $condition['programno']=$programno;
        $data=Db::table('programs')->where($condition)->field('rtrim(progname) as progname,rtrim(programno) programno')->select();
        if(!is_array($data)||count($data)!=1) {
            if($alert)
                throw new Exception('programno:' . $programno, MyException::ITEM_NOT_EXISTS);
        }
        else
            $result=$data[0];
        return $result;
    }

    /**获取开课计划
     * @param $year
     * @param $term
     * @param $courseno
     * @param bool $alert
     * @return null
     * @throws Exception
     */
    public static function getSchedulePlanItem($year,$term,$courseno,$alert=true){
        $result=null;
        $condition=null;
        $condition['scheduleplan.year']=$year;
        $condition['scheduleplan.term']=$term;
        $condition['scheduleplan.courseno+scheduleplan.[group]']=$courseno;
        $data=Db::table('scheduleplan')
            ->join('courses','courses.courseno=scheduleplan.courseno')->where($condition)
            ->field('rtrim(coursename) as coursename,scheduleplan.courseno+scheduleplan.[group] courseno,
            scheduleplan.estimate,scheduleplan.attendents,scheduleplan.year,scheduleplan.term,halflock,lock')
            ->select();
        if(!is_array($data)||count($data)!=1) {
            if($alert)
                throw new Exception('year:' . $year.',term:'.$term.',courseno'.$courseno, MyException::ITEM_NOT_EXISTS);
        }
        else
            $result=$data[0];
        return $result;
    }

    /**获取教师
     * @param $teacherno
     * @param bool $alert
     * @return null
     * @throws Exception
     */
    public static function getTeacherItem($teacherno,$alert=true){
        $result=null;
        $condition=null;
        $condition['teacherno']=$teacherno;
        $data=Db::table('teachers')
            ->join('schools','schools.school=teachers.school')->where($condition)->field('rtrim(teachers.name) as teachername,teacherno,rtrim(schools.name) schoolname')->select();
        if(!is_array($data)||count($data)!=1) {
            if($alert)
                throw new Exception('teacherno:' . $teacherno, MyException::ITEM_NOT_EXISTS);
        }
        else
            $result=$data[0];
        return $result;
    }

    /**获取班级信息
     * @param $classno
     * @param bool $alert
     * @return null
     * @throws Exception
     */
    public static function getClassItem($classno,$alert=true){
        $result=null;
        $condition=null;
        $condition['classno']=$classno;
        $data=Db::table('classes')
            ->join('schools','schools.school=classes.school')
            ->field('rtrim(classname) as classname,classno,schools.school,rtrim(schools.name) schoolname')
            ->where($condition)->select();
        if(!is_array($data)||count($data)!=1) {
            if($alert)
                throw new Exception('classno:' . $classno, MyException::ITEM_NOT_EXISTS);
        }
        else
            $result=$data[0];
        return $result;
    }

    /**获取单双周信息
     * @param $oew
     * @return mixed
     * @throws Exception
     */
    public static  function getOEWItem($oew,$alert=true){
        $condition['code']=$oew;
        $result=Db::table('oewoptions')->field('rtrim(name) name,timebit,timebit2')->where($condition)->select();
        if(!is_array($result)||count($result)!=1) {
            if($alert)
            throw new Exception('oew' . $oew, MyException::ITEM_NOT_EXISTS);
        }
        return $result[0];
    }

    /**获取时间段信息
     * @param $time
     * @return mixed
     * @throws Exception
     */
    public static function getTimeSectionItem($time,$alert=true)
    {
        $condition['name']=$time;
        $result=Db::table('timesections')->field('rtrim(value) value,timebits,timebits2')->where($condition)->select();
        if(!is_array($result)||count($result)!=1) {
            if($alert)
            throw new Exception('time' . $time, MyException::ITEM_NOT_EXISTS);
        }
        return $result[0];
    }
    //获取某个开设专业
    public static function getMajorItem($majorschool,$alert=true)
    {
        $condition['majorschool']=$majorschool;
        $result=Db::table('majors')->join('schools','schools.school=majors.school')
            ->join('majorcode','majorcode.code=majors.majorno')
            ->join('majordirection','majordirection.direction=majors.direction')
            ->field('majors.majorschool,rtrim(schools.name) schoolname,majors.direction,rtrim(majordirection.name) directionname,
            majors.majorno,rtrim(majorcode.name) majorname,majors.rowid')->where($condition)->select();
        if(!is_array($result)||count($result)!=1) {
            if($alert)
            throw new Exception('majorschool' . $majorschool, MyException::ITEM_NOT_EXISTS);
        }
        return $result[0];
    }
    //获取有效时间段
    public static function getValidItem($type,$alert=true){
        $condition=null;
        $condition['type']=$type;
        $data=Db::table('valid')->where($condition)->field('start,stop')->find();
        if(!is_array($data)) {
            if($alert)
            throw new Exception('type' . $type, MyException::ITEM_NOT_EXISTS);
        }
        return $data;
    }
    //获取创新技能学分项目
    public static function getProjectItem($id,$alert=true){
        $condition=null;
        $condition['id']=$id;
        $data=Db::table('project')->where($condition)->field('id,rtrim(name) name,credit,date')->find();
        if(!is_array($data)) {
            if($alert)
                throw new Exception('id' . $id, MyException::ITEM_NOT_EXISTS);
        }
        return $data;
    }
    //获取学生个人信息
    public static function getStudentItem($studentno,$alert=true){
        $condition=null;
        $condition['studentno']=$studentno;
        $data=Db::table('students')->where($condition)->field('studentno,rtrim(name) name,classno')->find();
        if(!is_array($data)) {
            if($alert)
                throw new Exception('studentno' . $studentno, MyException::ITEM_NOT_EXISTS);
        }
        return $data;
    }

    //获取统考科目信息
    public static function getExamItem($recno,$alert=true){
        $condition=null;
        $condition['recno']=$recno;
        $data=Db::table('standardexams')->where($condition)
            ->field('rtrim(courseno) courseno,rtrim(examname) examname,rtrim(schoolcode) schoolcode,
            rtrim(testcode) testcode,rtrim(rem) rem,recno')
            ->find();
        if(!is_array($data)) {
            if($alert)
                throw new Exception('recno' . $recno, MyException::ITEM_NOT_EXISTS);
        }
        return $data;
    }
//   /读取学生评教信息
    public static function getQualityStudentItem($id,$alert=true){
        $condition=null;
        $condition['id']=$id;
        $data=Db::table('qualitystudent')->where($condition)
            ->field('id,year,term,teacherno,type,courseno')
            ->find();
        if(!is_array($data)) {
            if($alert)
                throw new Exception('id' . $id, MyException::ITEM_NOT_EXISTS);
        }
        return $data;
    }
    public static function getExamNotificationItem($id,$alert=true){
        $condition=null;
        $condition['e.recno']=$id;
        $data=Db::table('examnotification e')
            ->join('standardexams se','se.recno=e.map')
            ->where($condition)
            ->field('year,term,fee,rtrim(e.rem) rem,rtrim(examname) examname,rtrim(testcode) testcode,rtrim(schoolcode) schoolcode,deadline')
            ->find();
        if(!is_array($data)) {
            if($alert)
                throw new Exception('id' . $id, MyException::ITEM_NOT_EXISTS);
        }
        return $data;
    }



}