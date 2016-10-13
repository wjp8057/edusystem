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

class Makeup extends  MyService {
    public function getCourseList($page=1,$rows=20,$year='',$term='',$courseno='%',$school='',$type=''){
        if($year==''||$term=='')
            throw new Exception('year term is empty', MyException::PARAM_NOT_CORRECT);

        $result=null;
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
    public function getStudentList($page=1,$rows=20,$year='',$term='',$courseno=''){
        if($year==''||$term==''||$courseno=='')
            throw new Exception('year term courseno is empty', MyException::PARAM_NOT_CORRECT);

        $result=null;
        $condition=null;
        $condition['makeup.courseno']=$courseno;
        $condition['makeup.year']=$year;
        $condition['makeup.term']=$term;
        $count= $this->query->table('makeup')->where($condition)->count();// 查询满足要求的总记录数
        $data=$this->query->table('scores')->join('students ',' students.studentno=scores.studentno')
            ->join('approachcode ',' approachcode.code=scores.approach')
            ->join('makeup','makeup.year=scores.year and makeup.term=scores.term and makeup.courseno=scores.courseno and scores.studentno=makeup.studentno')
            ->where($condition)->page($page,$rows)
            ->field("makeup.lock,case when testscore2='' then cast(examscore2 as char) else rtrim(testscore2) end printscore,
            rtrim(case when testscore2='' then cast(examscore2 as char) else rtrim(testscore2) end)  score,
            scores.recno,scores.studentno,students.name,scores.testscore2,scores.examscore2,approachcode.name as approachname,examrem")
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
    public function getCourseExamDate($year='',$term='',$courseno=''){
        if($year==''||$term==''||$courseno=='')
            throw new  Exception('year term courseno is empty ', MyException::PARAM_NOT_CORRECT);
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
    public function getCoursePercent($year='',$term='',$courseno=''){
        if($year==''||$term==''||$courseno=='')
            throw new  Exception('year term courseno is empty ', MyException::PARAM_NOT_CORRECT);
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
    public function getCourseInfo($year='',$term='',$courseno='')
    {
        if ($year == '' || $term == '' || $courseno == '')
            throw new  Exception('year term courseno is empty ', MyException::PARAM_NOT_CORRECT);
        $condition['makeup.courseno']=$courseno;
        $condition['makeup.year']=$year;
        $condition['makeup.term']=$term;
        $data=$this->query->table('makeup')->join('courses','courses.courseno=makeup.courseno')
            ->join('schools','schools.school=courses.school')->where($condition)->group('makeup.courseno,coursename,schools.name')
            ->field('makeup.courseno,rtrim(coursename) coursename,rtrim(schools.name) schoolname,count(*) attendents')->select();
        $result=$data[0];
        return $result;
    }

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