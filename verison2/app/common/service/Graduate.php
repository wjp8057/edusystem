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

namespace app\common\service;


use app\common\access\MyAccess;
use app\common\access\MyService;

/**毕业审核详情
 * Class Graduate
 * @package app\common\service
 */
class Graduate extends MyService{

     //获取学生的某个培养方案的所有教学计划。
    function getProgram($majorplanid,$studentno){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        $condition['graduate.majorplanid']=$majorplanid;
        $condition['graduate.studentno']=$studentno;
        $data=$this->query->table('graduate')
            ->join('programs','programs.programno=graduate.programno')
            ->join('programform','programform.name=graduate.form')
            ->field('rowid,graduate.programno,rtrim(progname) progname,credits,mcredits,gcredits,rtrim(programform.value) formname ,form')
            ->where($condition)
            ->order('form,programno')->select();
        $count= $this->query->table('graduate')->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
    //获取某一个教学计划的未通过课程列表
    function getCourse($page = 1, $rows = 20,$studentno='%',$name='%',$classno='%',$courseno='%',$school='',$form='',$rowid=''){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        if($studentno!='%') $condition['students.studentno']=array('like',$studentno);
        if($name!='%') $condition['students.name']=array('like',$name);
        if($classno!='%') $condition['students.classno']=array('like',$classno);
        if($courseno!='%') $condition['courses.courseno']=array('like',$courseno);
        if($school!='') $condition['classes.school']= $school;
        if($form!='') $condition['graduate.form']= $form;
        if($rowid!='') $condition['map']=$rowid;
        $data=$this->query->table('graduate')
            ->join('courses','courses.courseno=graduate.courseno')
            ->join('programs','programs.programno=graduate.programno')
            ->join('graduateform','graduateform.name=graduate.form')
            ->join('students','students.studentno=graduate.studentno')
            ->join('classes','classes.classno=students.classno')
            ->join('statusoptions','statusoptions.name=students.status')
            ->field('rtrim(students.name) name,students.studentno,rtrim(classes.classname) classname,courses.courseno,rtrim(courses.coursename) coursename,courses.credits,
            rtrim(programs.progname) progname,programs.programno,graduate.form,rtrim(graduateform.value) formname,rtrim(statusoptions.value) statusname')
            ->where($condition)->page($page,$rows)
            ->order('studentno,courseno')->select();
        $count= $this->query->table('graduate')
            ->join('students','students.studentno=graduate.studentno')
            ->join('classes','classes.classno=students.classno')
            ->join('courses','courses.courseno=graduate.courseno')
            ->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }

    public function printByClassNo($classno="000000"){
        $resultString='';
        $obj=new Studentplan();
        $data=$obj->getGraduate(1,200,'%','%',$classno,'','','')["rows"];
        $amount=count($data);
        foreach($data as $one){
            $resultString.=$this->printByStudentNo($one['majorplanid'],$one['studentno'])['data'];
        }
        return array("amount"=>$amount,"data"=>$resultString);
    }

    /**按学号审核
     * @param $majorplanid
     * @param $studentno
     * @return string
     */
    public function printByStudentNo($majorplanid,$studentno){
        $resultString='';
        $obj=new Studentplan();
        $data=$obj->getGraduate(1,1,$studentno,'%','%','','',$majorplanid)["rows"];
        $data=$data[0];
        $ok="<span class='green'> 通过√</span>";
        $fail="<span class='warn'> 未通过×</span>";
        $unselect="<span class='warn'> 未选修×</span>";
        $process="<span class='orange'>未结束○</span>";
        $totalresult=$ok;
        if(((float)$data['credits'])>((float)$data['gcredits'])+((float)$data['addcredits'])||((float)$data['allplan'])>((float)$data['allpass'])||((float)$data['allpartplan'])>((float)$data['allpartpass'])
            ||((float)$data['partplan'])>((float)$data['partpass'])||((float)$data['publicplan'])>((float)$data['publicpass']))
            $totalresult=$fail;
        $data['totalcredits']=$data['gcredits']+$data['addcredits'];
        $data['totalresult']=$totalresult;
        $resultString.='<div class="student">学号：'.$data['studentno'].' 姓名：'.$data['name'].' 学院：'.$data['schoolname'].' 班级：'.$data['classname'].'专业方向：'.$data['directionname'].' 模块方向：'.$data['module'].'审核时间：'.$data['date'].'</div>';
        $resultString.='<div class="subtitle">培养方案应获得学分：'.$data['credits'].' ,已获得课程学分：'.$data['gcredits'].' ,
                创新技能素质学分（已包含在公共选修课学分中）：'.$data['addcredits'].',获得总学分：'.$data['totalcredits'].',总体结论：'.$data['totalresult'].'</div>';
        $detail='';
        $graduate=new Graduate();
        $program=$graduate->getProgram($majorplanid,$studentno)['rows'];
        foreach ($program as  $oneprogram) {
            $result=$ok;
            $course=$graduate->getCourse(1,1000,'%','%','%','%','','',$oneprogram['rowid'])['rows'];
            if(((float)$oneprogram['mcredits'])>((float)$oneprogram['gcredits'])||(count($course)>0&&$oneprogram['form']=='1'))
                $result=$fail;
            $detail.="<div class='program'>".$oneprogram['progname']."(".$oneprogram['programno']."),".$oneprogram['formname'].",总学分：".$oneprogram['credits'].",应获得：".$oneprogram['mcredits'].",已获得：".$oneprogram['gcredits'].$result."</div>";
            $nopass=''; //选了没有过的
            $noselect=''; //没有选课的
            $noend='';//未结束的
            foreach ($course as  $onecourse) {
                switch($onecourse['form']){
                    case 'A':
                        $nopass.="<div class='course'>".$fail.$onecourse['courseno']." ".$onecourse['coursename']." ".$onecourse['credits']."学分</div>";
                        break;
                    case 'B':
                        $noselect.="<div class='course'>".$unselect.$onecourse['courseno']." ".$onecourse['coursename']." ".$onecourse['credits']."学分</div>";
                        break;
                    case 'C':
                        $noend.="<div class='course'>".$process.$onecourse['courseno']." ".$onecourse['coursename']." ".$onecourse['credits']."学分</div>";
                        break;
                    default:break;
                }
            }
            $detail=$noend==''?$detail:$detail.'<div class="coursetitle">课程未结束</div>'.$noend;
            $detail=$nopass==''?$detail:$detail.'<div  class="coursetitle">已选但未通过</div>'.$nopass;
            $detail=$noselect==''?$detail:$detail.'<div class="coursetitle">未选修课程</div>'.$noselect;
        }
        $resultString.='<div class="detail">'.$detail.'</div>';
        return array("amount"=>1,"data"=>$resultString);
    }
} 