<?php
// +----------------------------------------------------------------------
// |  [ MAKE YOUR WORK EASIER]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2016 http://www.nbcc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: fangrenfu <fangrenfu@126.com> 2016/11/26 14:17
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyAccess;
use app\common\access\MyService;
use think\Db;

class TestCourse extends MyService {
    //读取期末考试课程列表
    function getFinalList($page = 1, $rows = 20,$year,$term,$courseno='%',$classno='%')
    {
        $result=['total'=>0,'rows'=>[]];
        $condition = null;
        $condition['courseplan.year']=$year;
        $condition['courseplan.term']=$term;
        $condition['testcourse.type']='A';
        if($courseno!='%') $condition['testcourse.courseno']=array('like',$courseno);
        if($classno!='%') $condition['courseplan.classno']=array('like',$classno);
        $data = $this->query->table('testcourse')->page($page, $rows)
            ->join('courses','courses.courseno=substring(testcourse.courseno,1,7)')
            ->join('courseplan','courseplan.courseno+courseplan.[group]=testcourse.courseno and testcourse.year=courseplan.year and courseplan.term=testcourse.term')
            ->join('classes','classes.classno=courseplan.classno')
            ->join('schools','schools.school=courses.school')
            ->field("testcourse.id,testcourse.lock,testcourse.flag,testcourse.courseno,testcourse.courseno2,rtrim(courses.coursename)coursename,
            courses.school,rtrim(schools.name) schoolname,dbo.GROUP_CONCAT(rtrim(classes.classname),',') classname")
            ->group('testcourse.id,courses.school,testcourse.lock,testcourse.flag,testcourse.courseno,courses.coursename,schools.name,testcourse.courseno2')
            ->order('courseno')
            ->where($condition)->select();
        $count = $this->query->table('testcourse')
            ->join('courseplan','courseplan.courseno+courseplan.[group]=testcourse.courseno and testcourse.year=courseplan.year and courseplan.term=testcourse.term')
            ->where($condition)->count('distinct testcourse.courseno');
        if (is_array($data) && count($data) > 0)
            $result = array('total' => $count, 'rows' => $data);
        return $result;
    }
    //载入期末考试课程
    function  loadFinalCourse($year,$term){
        $bind=array('year'=>$year,'term'=>$term);
        MyAccess::checkAccess('E');
        $sql="delete from testcourse where year=:year and term=:term and type='A'";
        Db::execute($sql,$bind);

        $sql="insert into testcourse(year,term,type,courseno,courseno2,coursename)
            select scheduleplan.year,scheduleplan.term,'A',scheduleplan.courseno+scheduleplan.[group],scheduleplan.courseno+scheduleplan.[group],courses.coursename
            from scheduleplan
            inner join courses on courses.courseno=scheduleplan.courseno
            where year=:year and term=:term and exam=1";
        $rows=Db::execute($sql,$bind);
        return array('info'=>$rows.'门课程成功载入！','status'=>'1');
    }
    //锁定，解锁
    public function setFinalCourseStatus($year,$term,$courseno='%',$classno='%',$lock=1){
        $row=0;
        $info=$lock==1?'锁定':'解锁';
        try {
            $condition['courseplan.year']=$year;
            $condition['courseplan.term']=$term;
            $data['lock']=$lock;
            if($courseno!='%') $condition['testcourse.courseno']=array('like',$courseno);
            $condition['courseplan.classno']=array('like',$classno);
            $row=$this->query->table('testcourse')
                ->join('courseplan','testcourse.courseno=courseplan.courseno+courseplan.[group]')
                ->where($condition)->update($data);
        }
        catch(\Exception $e){
            throw $e;
        }
        return ["info"=>"锁定成功！".$row."条记录".$info,"status"=>"1"];
    }
    //更新信息
    public function  update($postData){
        $updateRow=0;
        $deleteRow=0;
        $insertRow=0;
        $errorRow=0;
        $info="";
        $status=1;
        //更新部分
        //开始事务
        $this->query->startTrans();
        try {
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $data = null;
                    $condition['id'] = $one->id;
                    $condition['courseno']=$one->courseno;
                    $data['courseno2'] = $one->courseno2;
                    $data['lock'] = $one->lock;
                    if(MyAccess::checkCourseSchool(substr($one->courseno,0,7)))
                        $updateRow += $this->query->table('testcourse')->where($condition)->update($data);
                    else{
                        $info.=$one->courseno.'不是本学院班级，无法更改信息</br>';
                        $errorRow++;
                        $status=0;
                    }

                }
            }
            //删除部分
            if (isset($postData["deleted"])) {
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['id'] = $one->id;
                    $condition['courseno']=$one->courseno;
                    if(MyAccess::checkCourseSchool(substr($one->courseno,0,7)))
                        $deleteRow += $this->query->table('testcourse')->where($condition)->delete();
                    else{
                        $info.=$one->courseno.'不是本学院课程，无法删除</br>';
                        $errorRow++;
                        $status=0;
                    }
                }
            }
        }
        catch(\Exception $e){
            $this->query->rollback();
            throw $e;
        }
        $this->query->commit();
        if($updateRow+$deleteRow+$insertRow+$errorRow==0){
            $status=0;
            $info="没有数据更新";
        }
        else {
            if ($updateRow > 0) $info .= $updateRow . '条更新！</br>';
            if ($deleteRow > 0) $info .= $deleteRow . '条删除！</br>';
            if ($insertRow > 0) $info .= $insertRow . '条添加！</br>';
        }
        $result=array('info'=>$info,'status'=>$status);
        return $result;
    }
    //读取已排的最大场场次数
    private static function getMinFlag($year,$term,$type){
        $bind=array('year'=>$year,'term'=>$term,'type'=>$type);
        $sql="select isnull(max(flag),0) flag
            from testcourse
            where testcourse.year=:year and testcourse.term=:term and testcourse.type=:type and lock=1";
        return Db::query($sql,$bind)[0]['flag'];
    }
    //获取最多场次的学生场次数作为最少的场次数
    private static function getMinTimes($year,$term,$type){
        $bind=array('year'=>$year,'term'=>$term,'type'=>$type);
        $sql="select isnull(max(amount),0) amount from (
            select count(studentno) as amount
            from testcourse inner join teststudent on teststudent.map=testcourse.id
            where testcourse.year=:year and testcourse.term=:term  and testcourse.type=:type and lock=0
            group by studentno ) as t";
        return Db::query($sql,$bind)[0]['amount'];
    }
    //获取课程信息
    private static function getCourseInfo($year,$term,$type){
        $bind=array('year'=>$year,'term'=>$term,'type'=>$type);
        $sql="select courses.courseno,rtrim(courses.coursename) coursename,count(*) amount
            from testcourse inner join courses on courses.courseno=substring(testcourse.courseno,1,7)
            inner join teststudent on teststudent.map=testcourse.id
            where testcourse.year=:year and testcourse.term=:term and testcourse.type=:type and testcourse.lock=0
            group by courses.courseno,courses.coursename
            order by amount desc";
        return Db::query($sql,$bind);
    }
    //初始化
    public function  init($year,$term,$type){
        $total=0; //总人数
        MyAccess::checkAccess('E');
        TestStudent::clear();
        switch($type){
            case 'A': //期末考试
            $total=TestStudent::loadFinal($year,$term);
            break;
            case 'B': //期初补考
                $total=TestStudent::loadFinal($year,$term);
                break;
            case 'C': //毕业前补考
                $total=TestStudent::loadFinal($year,$term);
                break;
            default:
                return  array('info'=>'排考类型参数错误！','status'=>'0');
                break;
        }
        $flag=self::getMinFlag($year,$term,$type);
        $times=self::getMinTimes($year,$term,$type);
        $data=self::getCourseInfo($year,$term,$type);
        return array('info'=>$total.'位学生成功载入！已排到最大场次'.$flag.'，至少排'.$times.'场!','status'=>'1','total'=>$total,'flag'=>$flag,'times'=>$times,'data'=>$data);
    }
    //创建Flag临时表,并添加flag
    private static function creatFlagTable($start,$end){
        $sql="if exists (select 1
            from  sysobjects
            where  id = object_id('FLAGTEMP')
            and   type = 'U')
           drop table FLAGTEMP";
        Db::execute($sql);
        $sql="CREATE TABLE [dbo].[FLAGTEMP](
            [FLAG] [int] NOT NULL,
            [TOTAL] [int] NULL,
            CONSTRAINT [PK_FLAGTEMP] PRIMARY KEY CLUSTERED
          ([FLAG] ASC
        )WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
        ) ON [PRIMARY]";
        Db::execute($sql);
        $dataset=[];
        for($i=$start;$i<=$end;$i++)
            $dataset[]=array('flag'=>$i,'total'=>0);
        Db::table('flagtemp')->insertAll($dataset);
    }
    //清空flag
    private static function  resetFlag($year,$term,$type){
        $bind=array('year'=>$year,'term'=>$term,'type'=>$type);
        $sql="update testcourse set flag=0
            where testcourse.year=:year and testcourse.term=:term and testcourse.type=:type and lock=0";
        Db::execute($sql,$bind);
        $sql="update teststudent set flag=0 ";
        Db::execute($sql);

    }
    //检索flag
    private static function findFlag($year,$term,$type,$courseno){
        $bind=array('year'=>$year,'term'=>$term,'type'=>$type,'courseno'=>$courseno);
        $sql="select top 1 flag from flagtemp
            where not exists (select * from teststudent
            where teststudent.flag=flagtemp.flag and exists(select * from testcourse inner join teststudent as t on t.map=testcourse.id
            where testcourse.year=:year and testcourse.term=:term and substring(testcourse.courseno,1,7)=:courseno and type=:type
             and t.studentno=teststudent.studentno
            ))
            order by total,flag";
        return Db::query($sql,$bind);
    }
    //flag增加数值
    private static function addFlagTotal($flag,$amount){
        $bind=array('flag'=>$flag,'amount'=>$amount);
        $sql='update flagtemp set total=total+:amount where flag=:flag';
        Db::execute($sql,$bind);
    }
    //设置两个表的flag
    private static function setFlag($year,$term,$type,$courseno,$flag){
        $bind=array('year'=>$year,'term'=>$term,'type'=>$type,'courseno'=>$courseno,'flag'=>$flag);
        $sql='update testcourse set flag=:flag where year=:year and term=:term and type=:type and substring(courseno,1,7)=:courseno';
        Db::execute($sql,$bind);

        $sql='update teststudent set flag=:flag from testcourse inner join teststudent on testcourse.id=teststudent.map
        where year=:year and term=:term and type=:type and substring(courseno,1,7)=:courseno';
        Db::execute($sql,$bind);
    }
    //排考
    public function  schedule($year,$term,$type,$courseno,$amount,$init=0,$start,$end){
        $total=0; //总人数
        MyAccess::checkAccess('E');
        if($init==1) {
            self::creatFlagTable($start, $end);
            self::resetFlag($year,$term,$type);
        }
        $data=self::findFlag($year,$term,$type,$courseno);
        if(count($data)>0){
            $flag=$data[0]['flag'];
            self::addFlagTotal($flag,$amount);
            self::setFlag($year,$term,$type,$courseno,$flag);
            return array('info'=>'排考完成，场次为'.$flag,'status'=>'1');
        }
        return array('info'=>'排考失败，建议增加场次数','status'=>'0');
    }
}