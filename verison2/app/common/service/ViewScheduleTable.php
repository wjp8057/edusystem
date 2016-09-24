<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:13
 */

namespace app\common\service;


use app\common\access\MyException;
use app\common\access\MyService;

class ViewScheduleTable extends MyService {
    /**更新数据，同步课程总表
     * @param $year
     * @param $term
     * @return array
     * @throws \Exception
     */
    public function update($year,$term){
        $condition['year']=$year;
        $condition['term']=$term;
        $this->query->startTrans();
        //更新部分
        try {
        $this->query->table('viewscheduletable')->where($condition)->delete();
        $this->query->table('viewschedule')->where($condition)
            ->field('coursenogroup, coursename, credits, weekhours, weekexpehours, coursetype,examtype, schoolname, classnoname,
            teachernoname, rem, year, term, courseno, [group], school,type, classno, classname, teachername, teacherno, day,
            time, roomno, recno, approach, exam,value, jsn, dayntime, schedulerecno, map, coursetypename, estimate, attendents,
            seats, oew, name,timebits, zc')
            ->selectInsert('coursenogroup, coursename, credits, weekhours, weekexpehours, coursetype,examtype, schoolname, classnoname,
            teachernoname, rem, year, term, courseno, [group], school,type, classno, classname, teachername, teacherno, day,
            time, roomno, recno, approach, exam,value, jsn, dayntime, schedulerecno, map, coursetypename, estimate, attendents,
            seats, oew, name,timebits, zc','viewscheduletable');
        }
        catch (\Exception $e) {
            $this->query->rollback();
            throw $e;
        }
        $this->query->commit();
        return ["status"=>1,"info"=>"同步成功完成！"];
    }

    /**获取教师课程信息
     * @param int $page
     * @param int $rows
     * @param string $year
     * @param string $term
     * @param string $teacherno
     * @return array|null
     * @throws \think\Exception
     */
    function getTeacherCourseList($page=1,$rows=20,$year='',$term='',$teacherno=''){
        if($year==''||$term==''||$teacherno=='')
            throw new \think\Exception('year term teacherno is empty', MyException::PARAM_NOT_CORRECT);

        $result=null;
        $condition=null;
        $condition['year']=$year;
        $condition['term']=$term;
        $condition['teacherno']=$teacherno;
        $count= $this->query->table('viewscheduletable')->where($condition)->count('distinct coursenogroup');// 查询满足要求的总记录数
        $data=$this->query->table('viewscheduletable')->join('courses ',' courses.courseno=viewscheduletable.courseno')
            ->join('coursetypeoptions2 ',' coursetypeoptions2.name=courses.type2')
            ->where($condition)->page($page,$rows)->group('coursenogroup,coursetype,examtype,attendents,courses.coursename,courses.credits,schoolname,coursetypeoptions2.value')
            ->field("coursenogroup courseno,schoolname,courses.coursename,courses.credits,coursetype as studytype,examtype,dbo.GROUP_CONCAT(distinct rtrim(classname),',') as classname,
dbo.GROUP_CONCAT(distinct rtrim(dayntime),',') time,attendents,coursetypeoptions2.value coursetype")->select();
        //这里有奇怪的错误，英文“,”号出现两次，第二个无法正确解析
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }

    /**获取开课课程课程基本信息
     * @param string $year
     * @param string $term
     * @param string $courseno
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\Exception
     */
    function getCourseInfo($year='',$term='',$courseno=''){
        if($year==''||$term==''||$courseno=='')
            throw new \think\Exception('year term courseno is empty', MyException::PARAM_NOT_CORRECT);

        $result=null;
        $condition=null;
        $condition['year']=$year;
        $condition['term']=$term;
        $condition['coursenogroup']=$courseno;
        $data=$this->query->table('viewscheduletable')->where($condition)->group('coursenogroup,coursetype,examtype,attendents,coursename,credits,schoolname')
            ->field("coursenogroup courseno,schoolname,coursename,credits,coursetype,examtype,dbo.GROUP_CONCAT(distinct rtrim(classname),'，') as classname,
dbo.GROUP_CONCAT(distinct rtrim(teachername),'，') as teachername,attendents")->find();
        return $data;
    }

}