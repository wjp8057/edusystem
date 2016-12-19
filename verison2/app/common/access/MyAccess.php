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
use app\common\vendor\MultiServer;
use think\Db;
use think\Exception;
use think\Log;
use think\Request;

class MyAccess {
    public static function clearSession(){
        $sql='delete from sessions where username=:username';
        $username=session('S_USER_NAME');
        Log::log($username);
        $bind=['username'=>$username];
        Db::execute($sql,$bind);
        session(null);
    }
    /**设置用户的session 信息
     * @param $data
     * @param $username
     * @param $guid
     * @return int
     */
    private static function  setSession($data,$username,$guid){
        $roles = trim($data['roles']);
        session(null);
        session('S_USER_SCHOOL', $data['school']);
        session('S_USER_SCHOOL_NAME', $data['schoolname']); //所在学院
        session("S_DEPART_NAME", $data['schoolname']); //分部门
        session("S_LOGIN_TYPE", 1); //注册用户为教师
        session("S_USER_NAME", $username); //注册用户
        session("S_TEACHERNO", $data['teacherno']); //注册用户
        session("S_TEACHER_NAME", $data['teachername']); //注册用户
        session("S_REAL_NAME", $data['teachername']); //注册用户
        session("S_GUID", $guid); //注册GUID
        session("S_ROLES", $roles); //注册角色信息
        session("S_LOGIN_COUNT", 0);
        session("S_MANAGE", $data['manage']);
        $user['TEACHERNO'] = $data['teacherno'];
        session("S_USER_INFO", $user);
        if ($roles == "B*" || $roles == "*B")
            return 2; //如果纯粹教师，返回状态2
        return 1; //管理员教师返回状态1
    }
    /**更新数据库session表
     * @param $username
     * @param $guid
     * @throws Exception
     */
    private static function updateSession($username,$guid){
        //记录当次ip 时间，以便下次登陆用
        $data = null;
        $condition = null;
        $condition['username'] = $username;
        $data['username'] = $username;
        $data['sessionid'] = $guid;
        $data['remoteip'] = get_client_ip();
        $data['logintime'] = date('Y-m-d H:i:s');
        $result = Db::table('sessions')->where($condition)->find();
        if (is_array($result)) {
            Db::table('sessions')->where($condition)->update($data);
        } else
            Db::table('sessions')->insert($data);
    }
    /**根据用户名写入登录信息
     * @param $username
     * @return int  0为找不到，1为管理员，2为普通教师
     */
    public static function signInAsUserName($username){
        $condition = null;
        $condition['username'] = $username;

        $data = Db::table('users')->join('teachers', 'teachers.teacherno=users.teacherno')
            ->join('schools', 'schools.school=teachers.school')
            ->field('schools.manage,teachers.teacherno,rtrim(teachers.name) as teachername,
            teachers.school,rtrim(schools.name) as schoolname,rtrim(users.roles) as roles')
            ->where($condition)->find();
        if (is_array($data) && count($data) > 0) {
            $guid = getGUID(session_id()); //获得GUID
            self::updateSession($username, $guid);
            return self::setSession($data,$username,$guid);
        }
        return 0;
    }
    /**根据教师号写入登录信息
     * @param $teacherno
     * @return int  0为找不到，1为管理员，2为普通教师
     */
    public static function signInAsTeacherNo($teacherno){
        $condition = null;
        $condition['teachers.teacherno'] = $teacherno;

        $data = Db::table('users')->join('teachers', 'teachers.teacherno=users.teacherno')
            ->join('schools', 'schools.school=teachers.school')
            ->field('users.username,schools.manage,teachers.teacherno,rtrim(teachers.name) as teachername,
            teachers.school,rtrim(schools.name) as schoolname,rtrim(users.roles) as roles')
            ->where($condition)->find();
        if (is_array($data) && count($data) > 0) {
            $guid = getGUID(session_id()); //获得GUID
            $username=trim($data['username']);
            self::updateSession($username, $guid);
            return self::setSession($data,$username,$guid);
        }
        return 0;

    }
    /**在用户表中检索信息
     * @param $username
     * @param $password
     * @return int 0为找不到，1为管理员，2为普通教师
     * @throws Exception
     */
    public static function loginAsUser($username,$password){
        $condition = null;
        $condition['username'] = $username;
        $data = Db::table('users')
            ->field('rtrim(users.password) as password')
            ->where($condition)->find();
        if (is_array($data) && count($data) > 0) {
            if (md5(md5($data['password']) . session('S_GUID')) == $password) {
                return self::signInAsUserName($username);
            }
        }
        return 0;
    }
    /**以学生身份登录信息
     * @param $studentno
     * @return int 3为学生成功，0为失败
     */
    public static function  signInAsStudent($studentno){
        $condition = null;
        $condition['studentno'] = $studentno;
        $data = Db::table('students')->join('classes', 'students.classno=classes.classno')
            ->join('schools','schools.school=classes.school')
            ->field('classes.school,students.studentno,rtrim(schools.name) schoolname,rtrim(students.name) studentname,rtrim(classes.classname) classname,students.classno')
            ->where($condition)->find();
        if (is_array($data) && count($data) > 0) {
            $guid = getGUID(session_id()); //获得GUID
            self::updateSession($studentno, $guid);

            session(null);
            session('S_USER_SCHOOL', $data['school']);
            session('S_USER_SCHOOL_NAME', $data['schoolname']); //所在学院
            session("S_DEPART_NAME", $data['classname']); //分部门
            session("S_DEPART_NO", $data['classno']); //分部门
            session("S_LOGIN_TYPE", 2); //注册用户为学生
            session("S_USER_NAME", $data['studentno']); //注册用户
            session("S_REAL_NAME", $data['studentname']); //注册用户
            session("S_GUID", $guid); //注册GUID
            session("S_ROLES", "S"); //注册角色信息
            session("S_LOGIN_COUNT", 0);
            session("S_USER_INFO", $data);
            return 3;
        }
        return 0;
    }
    /**以学生身份登录
     * @param $studentno
     * @param $password
     * @return int
     */
    public static  function loginAsStudent($studentno,$password){
        $condition = null;
        $condition['studentno'] = $studentno;
        $data = Db::table('students')
            ->field('rtrim(password) as password')
            ->where($condition)->find();
        if (is_array($data) && count($data) > 0) {
            if (md5(md5($data['password']) . session('S_GUID')) == $password) {
                return self::signInAsStudent($studentno);
            }
        }
        return 0;
    }
    /**验证用户的密码
     * @param $username string 用户名
     * @param $password string 密码
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public static function login($username,$password){
        $log=new MyLog();
        session("S_USER_NAME",$username);
        $log->write('R');
        $result=self::loginAsUser($username,$password);
        if($result==0){
            $result=self::loginAsStudent($username,$password);
        }
        return $result;
    }
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

    /**s数据库中读取操作的权限信息
     * @throws Exception
     */
    public static function  getAccess(){
        $request = Request::instance();
        $action= strtolower('/'.$request->module().'/'.$request->controller().'/'.$request->action());
        $usertype=session("S_LOGIN_TYPE");
        session('S_ACCESS', 0);
        if($usertype) {
            $condition=null;
            $condition["action.action"] = $action;
            $actionInfo=Db::table('action')->where($condition)->find();
            if(!is_array($actionInfo))
                throw new Exception($action . ' is not found!', MyException::WITH_OUT_PERMISSION);
            //写入session
            session('S_ACTIONID', $actionInfo['id']);
            session('S_ACTION', $action);
            //首先检查用户权限表
            if ($usertype == 1) {
                $condition=null;
                $condition['username']=session('S_USER_NAME');;
                $role =Db::table('users')->where($condition)->field('rtrim(roles) roles')->find();
                if(is_array($role)) {
                    $roles = str_split($role['roles']);
                    $condition = null;
                    $condition["action.action"] = $action;
                    $condition['actionrole.role'] = array('in', $roles);
                    $data = Db::table('action')->join('actionrole', 'actionrole.actionid=action.id')
                        ->where($condition)->field('access')->select();
                    $result = 0;
                    if (is_array($data) && count($data) > 0) {
                        foreach ($data as $one) {
                            $result = $result | $one['access'];
                        }
                    }
                    session('S_ACCESS', $result);
                    return ;
                }
            }
            //学生直接检查权限表
            else if($usertype==2){
                $condition = null;
                $condition["actionid"] =  $actionInfo['id'];
                $condition['role'] = 'S';
                $data = Db::table('actionrole')->where($condition)->field('access')->find();
                $result=is_array($data)?$data['access']:0;
                session('S_ACCESS', $result);
                return;
            }
        }
        throw new Exception('',MyException::NOT_LOGIN);
    }
    /**检查是否有读取权限
     * @param string $operate 操作类型：R读取、M修改、D删除、A增加、E执行，默认为读取
     * @return bool
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public static  function checkAccess($operate='R'){
        $result=session("S_ACCESS");
        if($result){
            //首先检查用户权限表
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
                    throw new Exception('undefined operation:'.$operate,MyException::WITH_OUT_PERMISSION);
                    break;
            }
         }
        throw new Exception(MyAccess::buildMessage(session("S_ACTIONID"),session("S_ACTION"),$operate), MyException::WITH_OUT_PERMISSION);
    }
    //检测登录IP地址是否与session表相同
    public static  function checkLoginIP(){
        $condition=null;
        $condition['username']=session('S_USER_NAME');
        $result=Db::table('sessions')->where($condition)->find();
        if(is_array($result)) {
            $remoteip=trim($result['remoteip']);
            if ($remoteip!= get_client_ip()) {
                session(null);
                throw new Exception('login by '.$remoteip.'!!', MyException::LOGIN_BY_OHTER);
            }
        }
        else {
            session(null);
            throw new Exception('', MyException::NOT_LOGIN);
        }
        return true;
    }
    /**抛出错误信息
     * @param $errorCode
     * @param $errorMessage
     */
    public static function throwException($errorCode,$errorMessage){
        if(MyAccess::getErrorMessage($errorCode)=='not defined error')
            $errorCode='700';
        header('Content-type: text/html; charset=utf-8');
        $errorMessage=str_replace(PHP_EOL, '', $errorMessage);
        header("HTTP/1.1 ".$errorCode." ".$errorMessage." ".MyAccess::getErrorMessage($errorCode)." ");

        $redirect='';
        if($errorCode=='701'||$errorCode=="702") {
            $serverip=MultiServer::selectServer();
            $redirect = '<script language="javascript">top.location="http://'.$serverip . Request::instance()->root() . '/home/index/login"</script>';
            echo '<html><head><meta charset="UTF-8">' . $redirect . '</head><body>' . $errorMessage . '<br/>' . MyAccess::getErrorChineseMessage($errorCode) . '</body></html>';
        }
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
            '702'=>'your account has been used on other computer!',
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
        $result=!isset($error[$errorCode])?'错误未定义！':$error[$errorCode];
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
    //检查课程所在学院是否有权限
    public static function checkCourseSchool($courseno=''){
        $condition=null;
        $condition['courseno']=substr($courseno,0,7);
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
    public static function checkTestPlanSchool($id){
        $condition=null;
        $condition['testplan.id']=$id;
        $data= Db::table('testplan')
            ->join('courses','substring(testplan.courseno,1,7)=courses.courseno')
            ->where($condition)->field('courses.school,testplan.studentschool')->select();
        if(!is_array($data)||count($data)!=1)
            throw new Exception('id:'.$id, MyException::PARAM_NOT_CORRECT);
        if($data[0]['school']==session('S_USER_SCHOOL')||session('S_MANAGE')==1||$data[0]['studentschool']==session('S_USER_SCHOOL')){
            return true;
        }
        else{
            return false;
        }
    }
    //检查开设专业权限
    public static function checkMajorSchool($majorschool=''){
        $condition=null;
        $condition['majorschool']=$majorschool;
        $data= Db::table('majors')->where($condition)->field('school')->select();
        if(!is_array($data)||count($data)!=1)
            throw new Exception('majorschool:'.$majorschool, MyException::PARAM_NOT_CORRECT);
        if($data[0]['school']==session('S_USER_SCHOOL')||session('S_MANAGE')==1){
            return true;
        }
        else{
            return false;
        }
    }
    public static function checkMajorPlanSchool($majorplanid=''){
        $condition=null;
        $condition['majorplan.rowid']=$majorplanid;
        $data= Db::table('majors')->join('majorplan','majorplan.majorschool=majors.majorschool')->where($condition)->field('school')->select();
        if(!is_array($data)||count($data)!=1)
            throw new Exception('majorplanid:'.$majorplanid, MyException::PARAM_NOT_CORRECT);
        if($data[0]['school']==session('S_USER_SCHOOL')||session('S_MANAGE')==1){
            return true;
        }
        else{
            return false;
        }
    }
    //检测教学计划的所属学院
    public static function checkProgramSchool($programno=''){
        $condition=null;
        $condition['programno']=$programno;

        $data= Db::table('programs')->where($condition)->field('school')->select();
        if(!is_array($data)||count($data)!=1)
            throw new Exception('programno:'.$programno, MyException::PARAM_NOT_CORRECT);
        if($data[0]['school']==session('S_USER_SCHOOL')||session('S_MANAGE')==1){
            return true;
        }
        else{
            return false;
        }
    }
    //检查创新技能认定申请的学院
    public static function checkCreditApplySchool($id=0){
        $condition=null;
        $condition['id']=$id;
        $data= Db::table('creditapply')->where($condition)->field('school')->select();
        if(!is_array($data)||count($data)!=1)
            throw new Exception('creditapply:'.$id, MyException::PARAM_NOT_CORRECT);
        if($data[0]['school']==session('S_USER_SCHOOL')||session('S_MANAGE')==1){
            return true;
        }
        else{
            return false;
        }
    }

    public static function checkQualityStudentSchool($id=0){
        $condition=null;
        $condition['id']=$id;
        $data= Db::table('qualitystudent')->where($condition)->field('school')->select();
        if(!is_array($data)||count($data)!=1)
            throw new Exception('creditapply:'.$id, MyException::PARAM_NOT_CORRECT);
        if($data[0]['school']==session('S_USER_SCHOOL')||session('S_MANAGE')==1){
            return true;
        }
        else{
            return false;
        }
    }

}