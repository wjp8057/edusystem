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

namespace app\all\controller;
use app\common\service\R32;
use app\common\vendor\Captcha;
use app\common\access\MyAccess;
use think\Controller;
use think\Db;
use think\Log;

/**各种下拉菜单
 * Class Option
 * @package app\all\controller
 */
class Option {
    public function verify(){
        return Captcha::validimg();
    }
    //角色
    public function role(){
        $result=null;
        try {
            $result=Db::table('roles')->field('role,rtrim(description) as name')->select();
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //获取学院列表 only=0时添加全部选项
    public function school($only=1,$active=0){
        $result=null;
        $condition=null;
        try {
            if($active==1)
                $condition['active']=1;
            $result =Db::table('schools')->order('school')->where($condition)->field('school,rtrim(name) as name')->select();
            if($only==0) {
                $all[] = array('school' => '', 'name' => '全部');
                $result = array_merge($all, $result);
            }
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //获取小图标
    public function icon(){
        $result=null;
        try {
            $result =Db::table('icon')->select();
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //获取教师职称的级别
    public function teacherlevel(){
        $result=null;
        try {
            $result =Db::table('teacherlevel')->field('level,rtrim(name) as name')->order('level')->select();
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //获取职称列表
    public function position(){
        $result=null;
        try {
            $result =Db::table('positions')->field('name as position,rtrim(value) as name')->order('position')->select();
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //获取教师岗位
    public function teacherjob(){
        $result=null;
        try {
            $result =Db::table('teacherjob')->field('job,rtrim(name) as name')->order('job')->select();
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //获取教师岗位
    public function teachertype(){
        $result=null;
        try {
            $result =Db::table('teachertype')->field('name type,rtrim(value) as name')->order('type')->select();
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //性别
    public function sex(){
        $result=null;
        try {
            $result =Db::table('sexcode')->field('rtrim(code) as sex,rtrim(name) as name')->order('sex')->select();
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //注册代码
    public function regcode(){
        $result=null;
        try {
            $result =Db::table('regcodeoptions')->field('rtrim(name) as code,rtrim(value) as name')->order('code')->select();

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //获取学院列表 only=0时添加全部选项
    public function studentstatus($only=1){
        $result=null;
        try {
            $result = Db::table('statusoptions')->order('status')->field('name status,rtrim(value) as name')->select();
            if($only==0) {
                $all[] = array('status' => '', 'name' => '全部');
                $result = array_merge($all, $result);
            }

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //民族
    public function nationality(){
        $result=null;
        try {
            $result =Db::table('nationalitycode')->field('rtrim(code) as nationality,rtrim(name) as name')->order('nationality')->select();

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //政治面貌
    public function party(){
        $result=null;
        try {
            $result =Db::table('partycode')->field('rtrim(code) as party,rtrim(name) as name')->order('party')->select();

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //专业
    public function major(){
        $result=null;
        try {
            $result =Db::table('majorcode')->field('rtrim(code) as major,rtrim(name) as name')->order('major')->select();

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //生源类型
    public function classcode(){
        $result=null;
        try {
            $result =Db::table('classcode')->field('rtrim(code) as class,rtrim(name) as name')->order('class')->select();

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //省份
    public function province(){
        $result=null;
        try {
            $result =Db::table('provincecode')->field('rtrim(code) as province,rtrim(name) as name')->order('province')->select();

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //学籍异动类型
    public function infotype($only=1){
        $result=null;
        try {
            $result =Db::table('infotype')->field('rtrim(code) as infotype,rtrim(name) as name')->order('infotype')->select();
            if($only==0) {
                $all[] = array('infotype' => '', 'name' => '全部');
                $result = array_merge($all, $result);
            }

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //校区
    public function area($only=1){
        $result=null;
        try {
            $result =Db::table('areas')->field('rtrim(name) as area,rtrim(value) as name')->order('area')->select();
            if($only==0) {
                $all[] = array('area' => '', 'name' => '全部');
                $result = array_merge($all, $result);
            }

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //设施
    public function equipment($only=1){
        $result=null;
        try {
            $result =Db::table('roomoptions')->field('rtrim(name) as equipment,rtrim(value) as name')->order('equipment')->select();
            if($only==0) {
                $all[] = array('equipment' => '', 'name' => '全部');
                $result = array_merge($all, $result);
            }

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
//   排课约束
    public function usage($only=1){
        $result=null;
        try {
            $result =Db::table('roomusages')->field('rtrim(name) as usage,rtrim(value) as name')->order('usage')->select();
            if($only==0) {
                $all[] = array('usage' => '', 'name' => '全部');
                $result = array_merge($all, $result);
            }

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //是否
    public function zo($only=1){
        $result=null;
        try {
            $result =Db::table('zo')->field('rtrim(name) as zo,rtrim(value) as name')->order('zo')->select();
            if($only==0) {
                $all[] = array('zo' => '', 'name' => '全部');
                $result = array_merge($all, $result);
            }

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //第几天
    public function  weekday($only=1){
        $result=null;
        try {
            $result =Db::table('weekday')->field('day,rtrim(name) as weekday,rtrim(value) as name')->order('day')->select();
            if($only==0) {
                $all[] = array('day' => '','weekday'=>'', 'name' => '全部');
                $result = array_merge($all, $result);
            }

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //单双周
    public function oew($only=1){
        $result=null;
        try {
            $result =Db::table('oewoptions')->field('rtrim(code) as oew,rtrim(name) as name')->order('oew')->select();
            if($only==0) {
                $all[] = array('oew' => '', 'name' => '全部');
                $result = array_merge($all, $result);
            }

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //节次
    public function timesection($only=1){
        $result=null;
        try {
            $result =Db::table('timesections')->field('rtrim(name) as time,rtrim(value) as name')->order('time')->select();
            if($only==0) {
                $all[] = array('time' => '', 'name' => '全部');
                $result = array_merge($all, $result);
            }

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //专业方向
    public function majordirection($major=''){
        $result=null;
        try {
            $condition['major']=$major;
            $result =Db::table('majordirection')->field('rtrim(direction) as direction,rtrim(name) as name')->where($condition)->order('direction')->select();
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //课程类型
    public function coursetype(){
        $result=null;
        try {
            $result =Db::table('coursetypeoptions')->field('rtrim(name) as type,rtrim(value) as name')->order('type')->select();
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //课程形式
    public function courseform(){
        $result=null;
        try {
            $result =Db::table('courseform')->field('rtrim(name) as form,rtrim(value) as name')->order('form')->select();
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //专业代码
    public function majorcode(){
        $result=null;
        try {
            $result =Db::table('majorcode')->field('rtrim(code) as code,rtrim(name) as name')->order('name')->select();
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
     //学科列表
    public function branch(){
        $result=null;
        try {
            $result =Db::table('branchcode')->field('rtrim(code) as code,rtrim(name) as name')->order('name')->select();
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
     //学位
    public function degree(){
        $result=null;
        try {
            $result =Db::table('degreeoptions')->field('rtrim(code) as code,rtrim(name) as name')->order('name')->select();
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //教学计划类型
    public function programtype(){
        $result=null;
        try {
            $result =Db::table('programtype')->field('rtrim(name) as type,rtrim(value) as name')->order('type')->select();
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //必修、模块、选修
    public function courseapproach(){
        $result=null;
        try {
            $result =Db::table('courseapproaches')->field('rtrim(name) as approach,rtrim(value) as name')->order('approach')->select();
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //考核方式 考试、考查、考核
    public function examoption(){
        $result=null;
        try {
            $result =Db::table('examoptions')->field('rtrim(name) as exam,rtrim(value) as name')->order('exam')->select();
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //课程类别
    public function coursecat(){
        $result=null;
        try {
            $result =Db::table('coursecat')->field('rtrim(name) as category,rtrim(value) as name')->order('category')->select();
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //考试级别 国家统考，教师自考
    public function testlevel(){
        $result=null;
        try {
            $result =Db::table('testlevel')->field('rtrim(name) as level,rtrim(value) as name')->order('level')->select();
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //教学计划类型 全选全通过，部分选部分通过，全选部分通过，公共选修课
    public function programform(){
        $result=null;
        try {
            $result =Db::table('programform')->field('rtrim(name) as form,rtrim(value) as name')->order('form')->select();
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //毕业审核详细表中的课程未通过原因，为选修，
    public function graduateform($only=1){
        $result=null;
        try {
            $result =Db::table('graduateform')->field('rtrim(name) as form,rtrim(value) as name')->order('form')->select();
            if($only==0) {
                $all[] = array('form' => '', 'name' => '全部');
                $result = array_merge($all, $result);
            }

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //学评教课程类型
    public function qualitytype($only=1){
        $result=null;
        try {
            $result =Db::table('qualitystudenttype')->field('rtrim(type) as type,rtrim(name) as name')->order('type')->select();
            if($only==0) {
                $all[] = array('type' => '', 'name' => '全部');
                $result = array_merge($all, $result);
            }

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
//   认定学分类型
    public function credittype($only=1){
        $result=null;
        try {
            $result =Db::table('credittype')->field('rtrim(type) as type,rtrim(name) as name')->order('type')->select();
            if($only==0) {
                $all[] = array('type' => '', 'name' => '全部');
                $result = array_merge($all, $result);
            }
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //场次
    public function testbatch($year,$term,$type,$only=1){
        $result=null;
        try {
            $condition=null;
            $condition['year']=$year;
            $condition['term']=$term;
            $condition['type']=$type;
            $result =Db::table('testbatch')->field('rtrim(testtime) as name,flag')
                ->where($condition)->order('name')->select();
            if($only==0) {
                $all[] = array('flag' => '', 'name' => '全部');
                $result = array_merge($all, $result);
            }
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //修课方式 正常修课、免听、免修、毕业前补考、部分免听、
    public function approachcode(){
        $result=null;
        try {
            $result =Db::table('approachcode')->field('rtrim(code) as approach,rtrim(name) as name')->where("code!='D'")->order('approach')->select();
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //考试备注
    public function examrem($only=1){
        $result=null;
        try {
            $result =Db::table('examremoptions')->field('rtrim(code) as examrem,rtrim(name) as name')
               ->order('examrem')->select();
            if($only==0) {
                $all[] = array('examrem' => '', 'name' => '全部');
                $result = array_merge($all, $result);
            }

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    //缓考原因
    public function delay($only=1){
        $result=null;
        try {
            $result =Db::table('delaycode')->field('delay,rtrim(name) as name')
                ->order('delay')->select();
            if($only==0) {
                $all[] = array('delay' => '', 'name' => '全部缓考');
                $result = array_merge($all, $result);
            }

        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    /**
     * 测试专用
     */
    public function test(){
       $obj=new R32();
        echo $obj->selectedTable('2016','1','1530104');

    }
}