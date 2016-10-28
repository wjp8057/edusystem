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
use app\common\service\Majorplan;
use app\common\service\R12;

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

    public function majorplandetail($majorschool,$id=0){
        $operate="添加培养方案";
        $major=Item::getMajorItem($majorschool);
        $this->assign('major',$major);
        $majorplan=null;
        if($id!=0)
        {
            $operate="编辑详情";
            $obj=new Majorplan();
            $majorplan=$obj->getList(1,1,'','',$id)["rows"];
            $majorplan=$majorplan[0];
        }
        $this->assign('majorplan',$majorplan);
        $this->assign('operate',$operate);
        return $this->fetch();
    }


}