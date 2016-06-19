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

namespace app\common\access;
use think\Db;
use think\Exception;
use think\Request;

class MyAccess {

    /**
     * @param $id
     * @param $action
     * @param $operate
     * @return string
     */
    private static  function buildMessage($id,$action,$operate){
        $operation=array(
            'R'=>'read',
            'M'=>'modify',
            'D'=>'delete',
            'E'=>'batch execute',
            'A'=>'add'
        );
        return 'id:'.$id.'<br/>action:'.$action . '<br/>'.$operation[$operate];
    }

    /**检查是否有读取权限
     * @param string $operate 操作类型：R读取、M修改、D删除、A增加、E执行，默认为读取
     * @return bool
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public static  function checkAccess($operate='R'){
        $request = Request::instance();
        $action='/'.$request->module().'/'.$request->controller().'/'.$request->action();
        $session=session("S_LOGIN_TYPE");
        if($session){
            //首先检查用户权限表
            if($session==1){
                $condition=null;
                $condition['username']=session('S_USER_NAME');;
                $role =Db::table('users')->where($condition)->field('rtrim(roles) roles')->find(); //获取用户角色
                $condition=null;
                $condition["action.action"] = $action;
                $actionInfo=Db::table('action')->where($condition)->find();
                if(is_array($role)&&is_array($actionInfo)) {
                    $roles = str_split($role['roles']);
                    $condition=null;
                    $condition["action.action"] = $action;
                    $condition['actionrole.role'] = array('in', $roles);
                    $data = Db::table('action')->join('actionrole','actionrole.actionid=action.id')
                        ->where($condition)->field('access')->select();
                    $result = 0;
                    if (is_array($data) && count($data) > 0) {
                        foreach ($data as $one) {
                            $result = $result | $one['access'];
                        }
                        switch ($operate) {
                            case 'R':
                                if (($result & 1) == 1)
                                    return true;
                                break;
                            case 'M':
                                if (($result & 2) == 2)
                                    return true;
                                break;
                            case 'D':
                                if (($result & 4) == 4)
                                    return true;
                                break;
                            case 'A':
                                if (($result & 8) == 8)
                                    return true;
                                break;
                            case 'E':
                                if (($result & 16) == 16)
                                    return true;
                                break;
                            default:
                                throw new Exception($action . ' undefined operation',MyException::WITH_OUT_PERMISSION);
                                break;
                        }
                    }
                    throw new Exception(MyAccess::buildMessage($actionInfo['id'],$action,$operate), MyException::WITH_OUT_PERMISSION);

                }
                else{
                    throw new Exception($action . ' is not found!', MyException::WITH_OUT_PERMISSION);
                }
            }

        }
        else
            throw new Exception('',MyException::NOT_LOGIN);
        return false;
    }

    /**抛出错误信息
     * @param $errorCode
     * @param $errorMessage
     */
    public static function throwException($errorCode,$errorMessage){
        if(MyAccess::getErrorMessage($errorCode)=='not defined error')
            $errorCode='700';
        header('Content-type: text/html; charset=utf-8');
        header("HTTP/1.1 ".$errorCode." ".$errorMessage." ".MyAccess::getErrorMessage($errorCode)." ");

        $redirect='';
        if($errorCode=='701'||$errorCode=="702")
            $redirect='<script language="javascript">top.location="'.Request::instance()->root().'/home/index/login"</script>';
        echo '<html><head><meta charset="UTF-8">'.$redirect.'</head><body>'.$errorMessage.'<br/>'.MyAccess::getErrorChineseMessage($errorCode).'</body></html>';
        die();
    }
    /**获取错误代码消息
     * @param $errorCode
     * @return string
     */
    public static function getErrorMessage($errorCode){
        $error=array(
            '200'=>'ok',
            '701'=>'you have not login system!',
            '702'=>'your account has been used on another computer!',
            '703'=>'without permission!',
            '704'=>'user is not exist!',
            '705'=>'parameter is not correct!',
            '706'=>'item is not exist!'
        );
        $result=!isset($error[$errorCode])?'not defined error':$error[$errorCode];
        return $result;
    }
    /**获取错中文错误误代码消息
     * @param $errorCode
     * @return string
     */
    public static function getErrorChineseMessage($errorCode){
        $error=array(
            '200'=>'正常',
            '701'=>'您你尚未登录系统!',
            '702'=>'您的账户已在其他电脑登录!',
            '703'=>'无权访问！',
            '704'=>'用户不存在!',
            '705'=>'访问参数错误!',
            '706'=>'数据不存在！'
        );
        $result=!isset($error[$errorCode])?'未定义错误！':$error[$errorCode];
        return $result;
    }

    /**检查是否为监考老师
     * @param string $year
     * @param string $term
     * @param string $courseno
     * @return bool
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public static function  checkCourseTeacher($year='',$term='',$courseno=''){
        if($year==''||$term==''||$courseno=='')
            throw new Exception('year or term or courseno is empty', MyException::PARAM_NOT_CORRECT);
        $condition['year']=$year;
        $condition['term']=$term;
        $condition['courseno']=$courseno;
        $condition['teacherno']=session('S_TEACHERNO');
        $data= Db::table('viewteachercourse')->where($condition)->find();
        if(is_array($data))
            return true;
        else
            return false;
    }

    /*
     * 检查教师所在学院是否与登录账户一致。
     */
    public static function checkTeacherSchool($teacherno=''){

        $condition['teacherno']=$teacherno;
        $data= Db::table('teachers')->where($condition)->field('school')->select();
        if(!is_array($data)||count($data)!=1)
            throw new Exception('teacher'.$teacherno, MyException::PARAM_NOT_CORRECT);

        if($data[0]['school']==session('S_USER_SCHOOL')||session('S_MANAGE')==1){
            return true;
        }
        else{
            return false;
        }

    }
    /**检查学生所在学院是不是和操作员一致
     * @param string $studentno 学号
     * @return bool true一致/false 不一致
     * @throws \think\Exception
     */
    public static function checkStudentSchool($studentno=''){
        $condition=null;
        $condition['students.studentno']=$studentno;
        $data= Db::table('students')->join('classes','classes.classno=students.classno') //取班级中的学院
        ->where($condition)->field('classes.school')->select();
        if(!is_array($data)||count($data)!=1)
            throw new Exception('studentno'.$studentno, MyException::PARAM_NOT_CORRECT);
        if($data[0]['school']==session('S_USER_SCHOOL')||session('S_MANAGE')==1){
            return true;
        }
        else{
            return false;
        }
    }

    /**检查班级的学院是否与操作员一致
     * @param string $classno
     * @return bool
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public static function checkClassSchool($classno=''){
        $condition=null;
        $condition['classno']=$classno;

        $data= Db::table('classes')->where($condition)->field('classes.school')->select();
        if(!is_array($data)||count($data)!=1)
            throw new Exception('classno:'.$classno, MyException::PARAM_NOT_CORRECT);
        if($data[0]['school']==session('S_USER_SCHOOL')||session('S_MANAGE')==1){
            return true;
        }
        else{
            return false;
        }
    }

    public static function checkCourseSchool($courseno=''){
        $condition=null;
        $condition['courseno']=$courseno;

        $data= Db::table('courses')->where($condition)->field('school')->select();
        if(!is_array($data)||count($data)!=1)
            throw new Exception('courseno:'.$courseno, MyException::PARAM_NOT_CORRECT);
        if($data[0]['school']==session('S_USER_SCHOOL')||session('S_MANAGE')==1){
            return true;
        }
        else{
            return false;
        }
    }
}