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
namespace app\major\controller;
use app\common\access\Item;
use app\common\access\Template;
use app\common\service\Action;
use app\common\service\Graduate;
use app\common\service\Majorplan;
use app\common\service\R12;
use app\common\service\Studentplan;

class Index extends Template{
    /**首页
     * @return mixed
     * @throws \think\Exception
     */
    public function index(){
        $obj=new Action();
        $menuJson=array('menus'=>$obj->getUserAccessMenu(session('S_USER_NAME'),1417));
        $this->assign('menu',json_encode($menuJson));
        return $this->fetch();
    }
    //编辑教学计划课程详细信息
    public function programcoursedetail($programno,$courseno=''){
        $operate="添加课程";
        $program=Item::getProgramItem($programno);
        $this->assign('program',$program);
        $course=null;
        if($courseno!='')
        {
            $operate="编辑详情";
            $obj=new R12();
            $course=$obj->getList(1,1,$programno,$courseno)["rows"];
            $course=$course[0];
        }
        $this->assign('course',$course);
        $this->assign('operate',$operate);
        return $this->fetch();
    }
    //培养方案详细页面
    public function majorplandetail($majorschool,$rowid=''){
        $operate="添加培养方案";
        $major=Item::getMajorItem($majorschool);
        $this->assign('major',$major);
        $majorplan=null;
        if($rowid!='')
        {
            $operate="编辑详情";
            $obj=new Majorplan();
            $majorplan=$obj->getList(1,1,'','',$rowid)["rows"];
            $majorplan=$majorplan[0];
        }
        $this->assign('majorplan',$majorplan);
        $this->assign('operate',$operate);
        return $this->fetch();
    }

    public function graduatedetail($studentno,$majorplanid){
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
        $this->assign('base',$data);

        $detail='';
        $graduate=new Graduate();
        $program=$graduate->getProgram($majorplanid,$studentno)['rows'];
        foreach ($program as  $oneprogram) {
            $result=$ok;
            if(((float)$oneprogram['mcredits'])>((float)$oneprogram['gcredits']))
                $result=$fail;
            $detail.="<div class='program'>".$oneprogram['progname'].",".$oneprogram['formname'].",总学分：".$oneprogram['credits'].",应获得：".$oneprogram['mcredits'].",已获得：".$oneprogram['gcredits'].$result."</div>";
            $course=$graduate->getCourse(1,1000,'%','%','%','','',$oneprogram['rowid'])['rows'];
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
        $this->assign('detail',$detail);


        return $this->fetch();
    }
}