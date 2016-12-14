<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 10:00
 */

namespace app\common\service;


use app\common\access\MyAccess;
use app\common\access\MyException;
use app\common\access\MyService;
use think\Db;
use think\Exception;

/**补考信息
 * Class Makeup
 * @package app\common\service
 */
class Makeup extends  MyService {

    //生成补考名单
    public function  init($year,$term){
        MyAccess::checkAccess('E');
        //同步学生的修课方式
        $bind=["year"=>$year,"term"=>$term];
        $sql="update r32
            set coursetype=r12.coursetype
            from r32
            inner join studentplan on studentplan.studentno=r32.studentno
            inner join r30 on r30.majorplan_rowid=studentplan.majorplanid
            inner join r12 on r12.programno=r30.progno
            where r32.year=:year and r32.term=:term and r12.courseno=r32.courseno
            and  r32.coursetype!=r12.coursetype";
        Db::execute($sql,$bind);
        $sql="update scores
            set plantype=r12.coursetype
            from scores
            inner join studentplan on studentplan.studentno=scores.studentno
            inner join r30 on r30.majorplan_rowid=studentplan.majorplanid
            inner join r12 on r12.programno=r30.progno
            where scores.year=:year and scores.term=:term and r12.courseno=scores.courseno
            and  plantype!=r12.coursetype";
        Db::execute($sql,$bind);
        //一般课程添加到makeup表中
        $sql="insert into makeup(year,term,studentno,courseno)
            select year,term,studentno,courseno
            from scores
            where scores.examscore<60 and scores.testscore not in ('合格','及格','中等','良好','优秀') and qm not in ('缺考','违纪')
            and scores.year=:year and scores.term=:term and scores.plantype!='E'  and scores.[group] not in ('BY','ZX')
            and not exists (select * from makeup where makeup.year=scores.year and makeup.term=scores.term and scores.studentno=makeup.studentno and scores.courseno=makeup.courseno  )
            ";
        $rows=Db::execute($sql,$bind);
        //学位课程部分
        $sql="insert into makeup(year,term,studentno,courseno)
            select year,term,studentno,courseno
             from scores
            where year=:year and term=:term and examscore<75  and scores.[group] not in ('BY','ZX')
            and exists (select * from scheduleplan
                where scheduleplan.year=scores.year and scheduleplan.term=scores.term and scheduleplan.courseno+scheduleplan.[group]=scores.courseno+scores.[group] and degree=1 )
            and not  exists (select * from makeup where makeup.year=scores.year and makeup.term=scores.term and scores.studentno=makeup.studentno and scores.courseno=makeup.courseno)
        ";
        $rows+=Db::execute($sql,$bind);
        //缓考部分
        $sql="insert into makeup(year,term,studentno,courseno)
            select year,term,studentno,courseno
             from scores
            where year=:year and term=:term and delay!='A' and scores.[group] not in ('BY','ZX')
            and  not  exists (select * from makeup where makeup.year=scores.year and makeup.term=scores.term and scores.studentno=makeup.studentno and scores.courseno=makeup.courseno)
        ";
        $rows+=Db::execute($sql,$bind);
        return ["info"=>"成功,".$rows."条记录添加！","status"=>"1"];
    }

    //读取补考的学生信息
    public function getList($page=1,$rows=20,$year,$term,$courseno='%',$studentno='%',$courseschool='',$studentschool='',$examrem=''){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        $condition['makeup.year']=$year;
        $condition['makeup.term']=$term;
        if($courseno!='%') $condition['makeup.courseno']=array('like',$courseno);
        if($studentno!='%') $condition['makeup.studentno']=array('like',$studentno);
        if($courseschool!='') $condition['courses.school']=$courseschool;
        if($studentschool!='') $condition['classes.school']=$studentschool;
        if($examrem!='') $condition['scores.examrem']=$examrem;
        $data=$this->query->table('makeup')
            ->join('scores','makeup.year=scores.year and makeup.term=scores.term and makeup.courseno=scores.courseno and scores.studentno=makeup.studentno')
            ->join('students ',' students.studentno=scores.studentno')
            ->join('approachcode ',' approachcode.code=scores.approach')
            ->join('courses','courses.courseno=makeup.courseno')
            ->join('classes','classes.classno=students.classno')
            ->join('examremoptions','examremoptions.code=scores.examrem')
            ->join('schools cs','cs.school=courses.school')
            ->join('schools ss','ss.school=classes.school')
            ->join('plantypecode','plantypecode.code=scores.plantype')
            ->where($condition)->page($page,$rows)
            ->field("makeup.id,rtrim(case when testscore='' then cast(examscore as char) else rtrim(testscore) end) score,
            scores.studentno,rtrim(students.name) studentname,approachcode.name as approachname,examrem,rtrim(examremoptions.name) examremname,
            scores.courseno+scores.[group] courseno,rtrim(courses.coursename) coursename,courses.school courseschool,rtrim(cs.name) courseschoolname,ss.school studentschool,rtrim(ss.name) as studentschoolname,
            students.classno,rtrim(classes.classname) classname,rtrim(plantypecode.name) plantypename,scores.plantype")
            ->order('courseno,studentno')->select();
        $count= $this->query->table('makeup')
            ->join('scores','makeup.year=scores.year and makeup.term=scores.term and makeup.courseno=scores.courseno and scores.studentno=makeup.studentno')
            ->join('students ',' students.studentno=scores.studentno')
            ->join('courses','courses.courseno=makeup.courseno')
            ->join('classes','classes.classno=students.classno')
            ->where($condition)->count();// 查询满足要求的总记录数
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
    /**获取补考课程
     * @param int $page
     * @param int $rows
     * @param string $year
     * @param string $term
     * @param string $courseno
     * @param string $school
     * @param string $type 是否已经输入完成
     * @return array|null
     * @throws Exception
     */
    public function getCourseList($page=1,$rows=20,$year,$term,$courseno='%',$school='',$type=''){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        if($courseno!='%')
            $condition['makup.courseno']=array('like',$courseno);
        if($school!='')
            $condition['courses.school']=$school;
        if($type!='')
            $condition['t.uninput']=array('exp','is not null');
        $condition['makeup.year']=$year;
        $condition['makeup.term']=$term;

        $condition2=null;
        $condition2['makeup.year']=$year;
        $condition2['makeup.term']=$term;
        $subsql = Db::table('makeup')->where($condition2)->where('edate is null')->group('courseno,year,term')->field('year,term,courseno,count(*) uninput')->buildSql();
        $data=$this->query->table('makeup')
            ->join($subsql.'  t ',' t.courseno=makeup.courseno and t.year=makeup.year and t.term=makeup.term ','LEFT')
            ->join('courses','courses.courseno=makeup.courseno')
            ->join('schools','schools.school=courses.school')
            ->where($condition)->page($page,$rows)
            ->field("rtrim(coursename) coursename,courses.courseno,rtrim(schools.name) schoolname,count(*) amount,isnull(uninput,0) as uninput")
            ->group('coursename,courses.courseno,schools.name,t.uninput')
            ->order('courseno')->select();
        $count= $this->query->table('makeup')->join($subsql.'  t ',' t.courseno=makeup.courseno and t.year=makeup.year and t.term=makeup.term ','LEFT')
            ->join('courses','courses.courseno=makeup.courseno')->where($condition)->count('distinct makeup.courseno');// 查询满足要求的总记录数
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }

    /**获取课程学生成绩列表
     * @param int $page
     * @param int $rows
     * @param string $year
     * @param string $term
     * @param string $courseno
     * @return array|null
     * @throws \think\Exception
     */
    public function getStudentList($page=1,$rows=20,$year,$term,$courseno='%',$studentno='%',$courseschool='',$studentschool='',$examrem=''){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        $condition['makeup.year']=$year;
        $condition['makeup.term']=$term;
        if($courseno!='%') $condition['makeup.courseno']=array('like',$courseno);
        if($studentno!='%') $condition['makeup.studentno']=array('like',$studentno);
        if($courseschool!='') $condition['courses.school']=$courseschool;
        if($studentschool!='') $condition['classes.school']=$studentschool;
        if($examrem!='') $condition['scores.examrem']=$examrem;
        $data=$this->query->table('scores')
            ->join('students ',' students.studentno=scores.studentno')
            ->join('approachcode ',' approachcode.code=scores.approach')
            ->join('makeup','makeup.year=scores.year and makeup.term=scores.term and makeup.courseno=scores.courseno and scores.studentno=makeup.studentno')
            ->join('courses','courses.courseno=makeup.courseno')
            ->join('classes','classes.classno=students.classno')
            ->join('examremoptions','examremoptions.code=scores.examrem')
            ->where($condition)->page($page,$rows)
            ->field("makeup.lock,case when testscore2='' then cast(examscore2 as char) else rtrim(testscore2) end printscore,
            rtrim(case when testscore2='' then cast(examscore2 as char) else rtrim(testscore2) end)  score,
            scores.recno,scores.studentno,rtrim(students.name) name,scores.testscore2,scores.examscore2,approachcode.name as approachname,examrem,rtrim(examremoptions.name) examremname,
            makeup.courseno,rtrim(courses.coursename) ")
            ->order('courseno,studentno')->select();
        $count= $this->query->table('makeup')->where($condition)->count();// 查询满足要求的总记录数
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }



    /**更新考试成绩
     * @param $postData
     * 数据格式范例
    {updated:[{"lock": "0",  "score": "不合格", "recno": "1900821", "studentno": "133030702","testscore": "不合格", "examscore": 0, "approachname": "正常修课", "examrem": "N", "row_number": "1"}]
    examdate:2015-09-28
    year:2015
    term:1
    lock：1 如果有这个字段，且为1的话会所锁定该课程。
    courseno:160A01A4H}
     * @return array
     * @throws \Exception
     */
    public function updateScore($postData){
        $updateRow=0;
        $info='';
        $status=1;
        //更新部分
        //开始事务
       $this->query->startTrans();
        try {
            $examdate=$postData["examdate"];
            $year=$postData["year"];
            $term=$postData["term"];
            $courseno=$postData["courseno"];
            if(MyAccess::checkCourseSchool($courseno)) {
                if (isset($postData["updated"])) {
                    $updated = $postData["updated"];
                    $listUpdated = json_decode($updated);
                    foreach ($listUpdated as $one) {
                        $condition = null;
                        $condition['recno'] = (int)$one->recno;
                        $condition['makeup.lock'] = 0;
                        $data = null;
                        $data['examscore2'] = $one->examscore;
                        $data['testscore2'] = $one->testscore;
                        $data['examrem'] = $one->examrem;
                        $data['date2'] = date('Y-m-d H:i:s');
                        $updateRow += $this->query->table('scores')
                            ->join('makeup','scores.year=makeup.year and scores.term=makeup.term and scores.courseno=makeup.courseno and scores.studentno=makeup.studentno')
                            ->where($condition)->update($data);
                        $data=null;
                        $data['edate'] = $examdate;
                        $data['lock'] = 1;
                        $data['date'] = date('Y-m-d H:i:s');
                        $this->query->table('makeup')
                            ->join('scores','scores.year=makeup.year and scores.term=makeup.term and scores.courseno=makeup.courseno and scores.studentno=makeup.studentno')
                            ->where($condition)->update($data);
                    }
                }
                if (isset($postData["lock"]) && $postData["lock"] == 1) {
                    $condition = null;
                    $condition['courseno'] = $courseno;
                    $condition['year'] = $year;
                    $condition['term'] = $term;
                    $data = null;
                    $data['lock'] = 1;
                    $data['edate'] = $examdate;
                    $this->query->table('makeup')->where($condition)->update($data);
                    $info = '课程已最终提交并锁定！';
                }
            }

            else{
                $info="你无法修改其他学院的成绩";
                $status=0;
            }
        }
        catch(\Exception $e){
           $this->query->rollback();
            throw $e;
        }
       $this->query->commit();
        if($updateRow>0) $info.=$updateRow.'条更新！</br>';
        if($info=='') {
            $info="没有数据被更新";
            $status=0;
        }
        $result=array('info'=>$info,'status'=>$status);
        return $result;
    }

    /**获取课程的考试时间
     * @param string $year
     * @param string $term
     * @param string $courseno
     * @return bool|string
     * @throws \think\Exception
     */
    public function getCourseExamDate($year,$term,$courseno){
        $condition=null;
        $condition['courseno']=$courseno;
        $condition['year']=$year;
        $condition['term']=$term;
        $result=$this->query->table('makeup')->where($condition)->field('isnull(edate,getdate()) as edate')->order('edate desc')->find();
        if(is_array($result))
            return $result['edate'];
        else
            return date('Y-m-d');
    }

    /**获取某课程的各类成绩百分比，以数组返回
     * @param string $year
     * @param string $term
     * @param string $courseno
     * @return mixed
     * @throws \think\Exception
     */
    public function getCoursePercent($year,$term,$courseno){
        $condition=null;
        $result=[];
        $condition['makeup.courseno']=$courseno;
        $condition['makeup.year']=$year;
        $condition['makeup.term']=$term;
        $data=$this->query->table('scores')
            ->join('makeup','scores.year=makeup.year and scores.term=makeup.term and scores.courseno=makeup.courseno and scores.studentno=makeup.studentno')
            ->where($condition)->field('case when testscore2=\'\' then cast(examscore2 as varchar) else isnull(testscore2,\'\') end as score,examrem,makeup.edate,date2 date')->select();
        $total=count($data); //总数
        if($total>=0) {
            $excellent = 0;//优秀
            $good = 0;//良好
            $middle = 0; //中等
            $pass = 0; //及格
            $fail = 0;//不及格
            $exception = 0;//异常
            for ($i = 0; $i < $total; $i++) {
                $one = $data[$i]['score'];
                if ($data[$i]['examrem'] != 'N') //不为N的就是不正常
                    $exception++;
                if (is_numeric($one)) {
                    if ($one >= 90 && $one <= 100)
                        $excellent++;
                    else if ($one < 90 && $one >= 80)
                        $good++;
                    else if ($one < 80 && $one >= 70)
                        $middle++;
                    else if ($one < 70 && $one >= 60)
                        $pass++;
                    else
                        $fail++;
                } else if ($one == '')
                    $fail++;
                else if (rtrim($one) == "优秀")
                    $excellent++;
                else if (rtrim($one) == "良好")
                    $good++;
                else if (rtrim($one) == "中等" || rtrim($one) == "合格")
                    $middle++;
                else if (rtrim($one) == "及格")
                    $pass++;
                else
                    $fail++;
            }
            $result['excellent'] = round($excellent * 100 / $total, 2);
            $result['good'] = round($good * 100 / $total, 2);
            $result['middle'] = round($middle * 100 / $total, 2);
            $result['pass'] = round($pass * 100 / $total, 2);
            $result['fail'] = round($fail * 100 / $total, 2);
            $result['exception'] = round($exception * 100 / $total, 2);
            $result['edate'] = $data[0]['edate'];
            $result['date'] = $data[0]['date'];
        }
        return $result;
    }

    /**获取学期初补考课程信息
     * @param string $year
     * @param string $term
     * @param string $courseno
     * @return mixed
     * @throws Exception
     */
    public function getCourseInfo($year,$term,$courseno)
    {
        $condition=null;
        $condition['makeup.courseno']=$courseno;
        $condition['makeup.year']=$year;
        $condition['makeup.term']=$term;
        $data=$this->query->table('makeup')->join('courses','courses.courseno=makeup.courseno')
            ->join('schools','schools.school=courses.school')->where($condition)->group('makeup.courseno,coursename,schools.name')
            ->field('makeup.courseno,rtrim(coursename) coursename,rtrim(schools.name) schoolname,count(*) attendents')->select();
        $result=$data[0];
        return $result;
    }

    /**开锁补考课程
     * @param $year
     * @param $term
     * @param $courseno
     * @return array
     */
    public function  unlockCourse($year,$term,$courseno){
        $condition=null;
        $condition['year']=$year;
        $condition['term']=$term;
        $condition['course']=$courseno;
        $data=null;
        $data['lock']=1;
        $effectRow=$this->query->table('makeup')->where($condition)->update($data);
        if($effectRow>=0)
        {
            $info="开锁成功！";
            $status=0;
        }
        else {
            $info="没有开锁！";
            $status=1;
        }
        $result=array('info'=>$info,'status'=>$status);
        return $result;
    }
}