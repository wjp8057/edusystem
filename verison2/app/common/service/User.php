<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/4
 * Time: 8:09
 */
namespace app\common\service;
use app\common\access\MyAccess;
use app\common\access\MyException;
use app\common\access\MyLog;
use app\common\access\MyService;
use think\db;
use think\Exception;

class User extends MyService{
    /**验证用户的密码
     * @param $username string 用户名
     * @param $password string 密码
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function login($username,$password){

        $condition=null;
        $condition['username'] = $username;
        $data=Db::table('users')->join('teachers','teachers.teacherno=users.teacherno')
            ->join('schools','schools.school=teachers.school')
            ->field('schools.manage,rtrim(users.password) as password,teachers.teacherno,rtrim(teachers.name) as teachername,
            teachers.school,rtrim(schools.name) as schoolname,rtrim(users.roles) as roles')
            ->where($condition)->find();

        if(is_array($data)&&count($data)>0) {
            //添加session信息
            if(md5(md5($data['password']) . session('S_GUID')) == $password) {
                $guid = getGUID(session_id()); //获得GUID
                session(null);
                session('S_USER_SCHOOL',$data['school']);
                session('S_USER_SCHOOL_NAME',$data['schoolname']); //所在学院
                session("S_LOGIN_TYPE",1); //注册用户为教师
                session("S_USER_NAME",$username); //注册用户
                session("S_TEACHERNO",$data['teacherno']); //注册用户
                session("S_TEACHER_NAME",$data['teachername']); //注册用户
                session("S_GUID",$guid); //注册GUID
                session("S_ROLES",$data['roles']); //注册角色信息
                session("S_LOGIN_COUNT", 0);
                session("S_MANAGE", $data['manage']);
                $user['TEACHERNO']=$data['teacherno'];
                session("S_USER_INFO", $user);
                $log=new MyLog();
                $log->write('R');
                //记录当次ip 时间，以便下次登陆用
                $data=null;
                $condition=null;

                $condition['username'] = $username;
                $data['username']=$username;
                $data['sessionid']=$guid;
                $data['remoteip']=get_client_ip();
                $data['logintime']=date('Y-m-d H:i:s');
                $result=Db::table('sessions')->where($condition)->find();
                if(is_array($result)){
                    Db::table('sessions')->where($condition)->update($data);
                }
                else
                    Db::table('sessions')->insert($data);
                return true;
            }
            else
                return false;
        }
        else
            return false;
    }

    /**获取用户列表
     * @param int $page
     * @param int $rows
     * @param string $username
     * @param string $name
     * @param string $school
     * @return array
     */
    public function getUserList($page=1,$rows=10,$username='%',$name='%',$school=''){
        $result=array();
        $condition=null;
        if($school!='') $condition['teachers.school']= $school;
        if($name!='%') $condition['teachers.name']=array('like',$name);
        if($username!='%')  $condition['users.username']=array('like',$username);
        $data=$this->query->table('users')->join('teachers','teachers.teacherno=users.teacherno')
            ->join('sessions','sessions.username=users.username','LEFT')
            ->join('schools ',' schools.school=teachers.school')
            ->join('sexcode','sexcode.code=teachers.sex')
            ->field('rtrim(users.roles) as role,rtrim(users.username) as username,teachers.sex,lock,
            users.teacherno,rtrim(teachers.name) as name,schools.school,schools.name as schoolname,
            remoteip,logintime,modifydate,enteryear,rtrim(sexcode.name) sexname ')
            ->where($condition)->page($page,$rows)->order('username')->select();
        $count=$this->query->table('users')->join('teachers ',' teachers.teacherno=users.teacherno')
            ->join('schools ','schools.school=teachers.school')->where($condition)->count();
        if(is_array($data)&&count($data)>0){
            $result=array('total'=>$count,'rows'=>$data);
        }
        return $result;
    }

    /**更新用户信息
     * @param $postData
     * @return array
     * @throws \Exception
     */
    public function  updateUser($postData)
    {
        $updateRow = 0;
        $deleteRow = 0;
        $insertRow = 0;
        //更新部分
        //开始事务
        $this->query->startTrans();
        try {
            if(isset($postData["updated"])){
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one){
                    $condition=null;
                    $data=null;
                    $condition['teacherno']=$one->teacherno;
                    $data['lock']=(int)$one->lock;
                    $data['username']=$one->username;
                    $data['roles']=$one->role;
                    $this->query->table('users')->where($condition)->update($data);
                    $data=null;
                    $data['school']=$one->school;
                    $data['name']=$one->name;
                    $data['sex']=$one->sex;
                    $data['enteryear']=$one->enteryear;
                    $this->query->table('teachers')->where($condition)->update($data);
                    $updateRow++;
                }
            }
            //删除部分
            if(isset($postData["deleted"])){
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one){
                    $condition=null;
                    $condition['teacherno']=$one->teacherno;
                    $deleteRow+=$this->query->table('users')->where($condition)->delete();
                    $this->query->table('teachers')->where($condition)->delete();
                }
            }
            if(isset($postData["inserted"])){
                $updated = $postData["inserted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one){
                    $data=null;
                    $data['lock']=(int)$one->lock;
                    $data['username']=$one->username;
                    $data['teacherno']=$one->teacherno;
                    $data['password']='123456';
                    $data['roles']=$one->role;
                    $data['modifydate']=date("Y-m-d H:i:s");
                    $this->query->table('users')->insert($data);
                    $data=null;
                    $data['name']=$one->name;
                    $data['sex']=$one->sex;
                    $data['teacherno']=$one->teacherno;
                    $data['enteryear']=$one->enteryear;
                    $data['school']=$one->school;

                    $this->query->table('teachers')->insert($data);
                    $insertRow++;
                }
            }
        } catch (\Exception $e) {
            $this->query->rollback();
            throw $e;
        }
        $this->query->commit();
        $info = '';
        if ($updateRow > 0) $info .= $updateRow . '条更新！</br>';
        if ($deleteRow > 0) $info .= $deleteRow . '条删除！</br>';
        if ($insertRow > 0) $info .= $insertRow . '条添加！</br>';
        $status = 1;
        if($info=='') {
            $info="没有数据被更新";
            $status=0;
        }
        $result = array('info' => $info, 'status' => $status);
        return $result;
    }

    /**根据教师号删除用户
     * @param $teacherno
     * @return bool
     * @throws \think\Exception
     */
    public function deleteUserByTeacherNo($teacherno)
    {
        if ($teacherno == '')
            throw new Exception('teacherno is empty', MyException::PARAM_NOT_CORRECT);
        else {
            $condition = null;
            $condition['teacherno'] = $teacherno;
            $this->query->table('users')->where($condition)->delete();
            $this->query->table('teachers')->where($condition)->delete();
            return true;
        }
    }

    /**获得指定用户的角色
     * @param string $username
     * @return array|null
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function getUserRole($username='')
    {
        $result=null;
        $condition['username']=$username;
        $data=$this->query->table('users')->where($condition)->field('rtrim(roles) roles')->find();
        if(!is_array($data)||count($data)==0){
            throw new Exception('username'.$username,MyException::USER_NOT_EXISTS);
        }
        $result=str_split($data['roles']);
        return $result;
    }

    /**更新用户角色
     * @param string $username
     * @param string $role
     * @return array
     * @throws \think\Exception
     */
    public  function  updateUserRole($username='',$role='*')
    {
        $condition['username']= $username;
        $data['roles']=$role;
        $this->query->table('users')->where($condition)->update($data);
        $result = array('info' => '保存成功', 'status' => 1);
        return $result;
    }

    /**修改指定教师号密码
     * @param $teacherno
     * @param $password
     * @return array
     * @throws \think\Exception
     */
    public function changeUserPassword($teacherno='',$password=''){
        if($teacherno==''||$password=='')
            throw new Exception('teacherno or password is empty', MyException::PARAM_NOT_CORRECT);

        //检查教师所在学院，
        if(MyAccess::checkTeacherSchool($teacherno)) {
            $condition['teacherno'] = $teacherno;
            $data['password'] = $password;
            $data['modifydate']=date("Y-m-d H:i:s");
            $this->query->table('users')->where($condition)->setField($data);
            $status = 1;
            $info = "密码修改成功";
        }
        else{
            $status = "错误";
            $info = "你无法修改其他学院教师的密码！";
        }
        $result = array('info' => $info, 'status' => $status);
        return $result;
    }

    /**修改本人密码
     * @param $oldPassword
     * @param $newPassword
     * @return bool
     */
    public function changeSelfPassword($oldPassword,$newPassword){
        $condition['username']= session('S_USER_NAME');
        $condition['password']= $oldPassword;
        $data['password']=$newPassword;
        $data['modifydate']=date("Y-m-d H:i:s");
        $result= $this->query->table('users')->where($condition)->select();

        if(is_array($result)&&count($result)==1)
        {
            $this->query->table('users')->where($condition)->setField($data);
            $status = 1;
            $info = "密码修改成功";
        }
        else{
            $status = "错误";
            $info = "旧密码错误！";
        }
        $result = array('info' => $info, 'status' => $status);
        return $result;
    }

    /**添加新用户
     * @param $teacherno
     * @param $username
     * @param $password
     * @return bool
     * @throws \think\Exception
     */
    public function addUser($teacherno,$username,$password){
        if ($teacherno == ''||$username==''||$password=='')
            throw new \think\Exception('teacherno username password is empty ', MyException::PARAM_NOT_CORRECT);

        $data['teacherno']=$teacherno;
        $data['username']=$username;
        $data['password']=$password;
        $data['modifydate']=date("Y-m-d H:i:s");
        $this->query->table('users')->insert($data);
        return true;
    }
}