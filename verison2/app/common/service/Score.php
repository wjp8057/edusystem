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
use think\Exception;

/**成绩
 * Class Score
 * @package app\common\service
 */
class Score extends  MyService {

    /**获取课程学生成绩列表
     * @param int $page
     * @param int $rows
     * @param string $year
     * @param string $term
     * @param string $courseno
     * @return array|null
     * @throws \think\Exception
     */
    public function getStudentList($page=1,$rows=20,$year='',$term='',$courseno=''){
        if($year==''||$term==''||$courseno=='')
            throw new Exception('year term courseno is empty', MyException::PARAM_NOT_CORRECT);

        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        $condition['courseno']=substr($courseno,0,7);
        $condition['group']=substr($courseno,7,2);
        $condition['year']=$year;
        $condition['term']=$term;
        $count= $this->query->table('scores')->where($condition)->count();// 查询满足要求的总记录数
        $data=$this->query->table('scores')->join('students ',' students.studentno=scores.studentno')
            ->join('approachcode ',' approachcode.code=scores.approach')
            ->where($condition)->page($page,$rows)
            ->field("scores.lock,case when testscore='' then cast(examscore as char) else rtrim(testscore) end printscore,isnull(rtrim(qm),'') as score,
            scores.recno,scores.studentno,students.name,scores.testscore,scores.examscore,approachcode.name as approachname,examrem,delay")
            ->order('studentno')->select();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
    /**更新期末考试成绩
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
            if(MyAccess::checkCourseTeacher($year,$term,$courseno)) {
                if (isset($postData["updated"])) {
                    $updated = $postData["updated"];
                    $listUpdated = json_decode($updated);
                    foreach ($listUpdated as $one) {
                        $condition = null;
                        $condition['recno'] = (int)$one->recno;
                        $condition['lock'] = 0;
                        $data = null;
                        $data['examscore'] = $one->examscore;
                        $data['testscore'] = $one->testscore;
                        $data['qm'] = $one->score;
                        $data['ps'] = '无';
                        $data['edate'] = $examdate;
                        $data['lock'] = 1;
                        $data['date'] = date('Y-m-d H:i:s');
                        $data['examrem'] = $one->examrem;
                        $updateRow += $this->query->table('scores')->where($condition)->update($data);
                    }
                }
                if (isset($postData["lock"]) && $postData["lock"] == 1) {
                    $condition = null;
                    $condition['courseno'] = substr($courseno, 0, 7);
                    $condition['group'] = substr($courseno, 7, 2);
                    $condition['year'] = $year;
                    $condition['term'] = $term;
                    $data = null;
                    $data['lock'] = 1;
                    $data['edate'] = $examdate;
                    $this->query->table('scores')->where($condition)->update($data);
                    $info = '课程已最终提交并锁定！';
                }
            }

            else{
                $info="你无法修改其他教师的成绩";
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
    public function getCourseExamDate($year='',$term='',$courseno=''){
        if($year==''||$term==''||$courseno=='')
            throw new  Exception('year term courseno is empty ', MyException::PARAM_NOT_CORRECT);
        $condition=null;
        $condition['courseno']=substr($courseno,0,7);
        $condition['group']=substr($courseno,7,2);
        $condition['year']=$year;
        $condition['term']=$term;
        $result=$this->query->table('scores')->where($condition)->field('isnull(edate,getdate()) as edate,examscore')->order('examscore desc')->find();
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
    public function getCoursePercent($year='',$term='',$courseno=''){
        if($year==''||$term==''||$courseno=='')
            throw new  Exception('year term courseno is empty ', MyException::PARAM_NOT_CORRECT);
        $condition=null;
        $result=[];
        $condition['courseno']=substr($courseno,0,7);
        $condition['group']=substr($courseno,7,2);
        $condition['year']=$year;
        $condition['term']=$term;
        $data=$this->query->table('scores')->where($condition)->field('case when testscore=\'\' then cast(examscore as varchar) else isnull(testscore,\'\') end as score,examrem,edate,date')->select();
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

    /**获取成绩列表
     * @param int $page
     * @param int $rows
     * @param string $year
     * @param string $term
     * @param string $studentno
     * @return array
     */
    public function getScoreList($page=1,$rows=20,$year='',$term='',$studentno){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        if($studentno!='%') $condition['scores.studentno']=array('like',$studentno);
        if($year!='') $condition['scores.year']=$year;
        if($term!='') $condition['scores.term']=$term;
        $count= $this->query->table('scores')->where($condition)->count();// 查询满足要求的总记录数
        $data=$this->query->table('scores')
            ->join('students ',' students.studentno=scores.studentno')
            ->join('approachcode ',' approachcode.code=scores.approach')
            ->join('courses',' courses.courseno=scores.courseno')
            ->join('examremoptions','examremoptions.code=scores.examrem')
            ->join('examoptions','examoptions.name=scores.testtype')
            ->join('courseapproaches','courseapproaches.name=scores.plantype')
            ->where($condition)->page($page,$rows)
            ->field("scores.year,scores.term,students.studentno,students.name studentname,scores.courseno+scores.[group] courseno,
                courses.credits,rtrim(courses.coursename) coursename,rtrim(isnull(case when testscore = '' then cast(examscore as char) else rtrim(testscore) end,'')) as score,
                rtrim(isnull(case when testscore2 = '' then cast(examscore2 as char) else rtrim(testscore2) end,'')) as makeup,
                rtrim(examremoptions.name) examremname,rtrim(approachcode.name) as approachname,point,rtrim(examoptions.value) as examtypename,
                rtrim(courseapproaches.value) courseapproachname")
            ->order('year,term,courseno')->select();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }

    //获取学期积点分列表
    public function getPointList($page=1,$rows=20,$studentno){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        $condition['studentno']=$studentno;
        $data=$this->query->table('scores')->page($page,$rows)
            ->where($condition)
            ->field('year,term,studentno,sum(point) point')
            ->group('year,term,studentno')
            ->order('year,term,studentno')->select();
        $count= $this->query->table('scores')->where($condition)->count('distinct studentno');
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }

    //获取学生的详细信息
    public function getStudentDetail($page=1,$rows=20,$year,$term,$courseno='%',$studentno='%',$courseschool='',$studentschool='',$delay=''){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        $condition['scores.year']=$year;
        $condition['scores.term']=$term;
        if($courseno!='%') $condition['scores.courseno+scores.[group]']=array('like',$courseno);
        if($studentno!='%') $condition['scores.studentno']=array('like',$studentno);
        if($courseschool!='') $condition['courses.school']=$courseschool;
        if($studentschool!='') $condition['classes.school']=$studentschool;
        if($delay=='')
            $condition['scores.delay']=array('neq','A');
        else
            $condition['scores.delay']=$delay;
        $data=$this->query->table('scores')
            ->join('students ',' students.studentno=scores.studentno')
            ->join('approachcode ',' approachcode.code=scores.approach')
            ->join('courses','courses.courseno=scores.courseno')
            ->join('classes','classes.classno=students.classno')
            ->join('delaycode','delaycode.delay=scores.delay')
            ->join('schools cs','cs.school=courses.school')
            ->join('schools ss','ss.school=classes.school')
            ->join('plantypecode','plantypecode.code=scores.plantype')
            ->where($condition)->page($page,$rows)
            ->field("scores.recno,scores.studentno,rtrim(students.name) studentname,approachcode.name as approachname,scores.delay,rtrim(delaycode.name) delayname,
            scores.courseno+scores.[group] courseno,rtrim(courses.coursename) coursename,courses.school courseschool,rtrim(cs.name) courseschoolname,ss.school studentschool,
            rtrim(ss.name) as studentschoolname,students.classno,rtrim(classes.classname) classname,rtrim(plantypecode.name) plantypename,scores.plantype")
            ->order('courseno,studentno')->select();
        $count= $this->query->table('scores')
            ->join('students ',' students.studentno=scores.studentno')
            ->join('courses','courses.courseno=scores.courseno')
            ->join('classes','classes.classno=students.classno')
            ->where($condition)->count();// 查询满足要求的总记录数
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
    //更新考试备注 修改缓考之类的信息。
    public function updateDelay($postData){
        $updateRow=0;
        $info='';
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
                    $condition['recno'] = $one->recno;
                    $data['delay'] = $one->delay;
                    $updateRow += $this->query->table('scores')->where($condition)->update($data);
                }
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
}