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
use app\common\access\MyAccess;
use app\common\service\Classroom;
use think\Controller;
use think\Db;
use think\Log;

/**各种下拉菜单
 * Class Option
 * @package app\all\controller
 */
class Option {
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

    /**是否
     * @param int $only
     * @return \think\response\Json
     */
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

    /**读取一周第几天列表
     * @param int $only
     * @return \think\response\Json
     */
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
    public function coursetype(){
        $result=null;
        try {
            $result =Db::table('coursetypeoptions')->field('rtrim(name) as type,rtrim(value) as name')->order('type')->select();
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }

    public function courseform(){
        $result=null;
        try {
            $result =Db::table('courseform')->field('rtrim(name) as form,rtrim(value) as name')->order('form')->select();
        } catch (\Exception $e) {
            MyAccess::throwException($e->getCode(),$e->getMessage());
        }
        return json($result);
    }
    public function test(){
        $condition['courseno']='080A01A';
        $result =Db::table('courses')->field('convert(varchar(10),khours) lhours')->where($condition)->select();
        dump($result);


    }
}