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


use app\common\access\Item;
use app\common\access\MyAccess;
use app\common\access\MyException;
use app\common\access\MyService;
use think\Exception;

/**学生信息
 * Class Student
 * @package app\common\service
 */
class Student extends MyService {
    /**获取学生基本数据信息
     * @param int $page
     * @param int $rows
     * @param string $studentno
     * @param string $name
     * @param string $classno
     * @param string $school
     * @param string $status
     * @param string $free
     * @return array|null
     */
    function getList($page=1,$rows=20,$studentno='%',$name='%',$classno='%',$school='',$status='',$free=''){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        if($studentno!='%') $condition['students.studentno']=array('like',$studentno);
        if($name!='%') $condition['students.name']=array('like',$name);
        if($classno!='%') $condition['students.classno']=array('like',$classno);
        if($school!='') $condition['classes.school']= $school;
        if($status!='') $condition['students.status']= $status;
        if($free!='') $condition['students.free']= $free;
        $data=$this->query->table('students')->join('classes' , 'classes.classno=students.classno')
            ->join('schools ','schools.school=classes.school')
            ->join('sexcode  ',' sexcode.code=students.sex')
            ->join('statusoptions  ',' statusoptions.name=students.status')
            ->join('personal  ',' personal.studentno=students.studentno')
            ->join('nationalitycode  ',' nationalitycode.code=personal.nationality','LEFT')
            ->join('partycode  ',' partycode.code=personal.party','LEFT')
            ->join('studentplan'," students.studentno=studentplan.studentno and studentplan.type='M'",'LEFT')
            ->join('majorplan','majorplan.rowid=studentplan.majorplanid','LEFT')
            ->join('majors','majors.majorschool=majorplan.majorschool','LEFT')
            ->join('majorcode ',' majorcode.code=majors.majorno','LEFT')
            ->join('majordirection ','majordirection.direction=majors.direction','LEFT')
            ->page($page,$rows)
            ->field("students.studentno,rtrim(students.name) name,students.sex,rtrim(sexcode.name) sexname,
            students.classno,rtrim(classes.classname) as classname,classes.school,rtrim(schools.name) schoolname,
            personal.party,rtrim(partycode.name) partyname,personal.nationality,rtrim(nationalitycode.name) nationalityname,
            personal.major,rtrim(majorcode.name) majorname,rtrim(majordirection.name) directionname,students.status,rtrim(statusoptions.value) statusname,'' rem,students.free,score,students.years")
            ->where($condition)->order('studentno')->select();
        $count= $this->query->table('students')->join('classes  ',' classes.classno=students.classno')->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
    /**获取学生详细数据信息，包含联系电话，身份证等信息
     * @param int $page 页码
     * @param int $rows 每页记录数
     * @param string $studentno 学号
     * @param string $name 姓名
     * @param string $classno 班号
     * @param string $school 学院
     * @param string $status 学籍状态
     * @return array|null
     */
    function getDetailList($page=1,$rows=20,$studentno='%',$name='%',$classno='%',$school='',$status=''){
        $result=null;
        $condition=null;
        if($studentno!='%') $condition['students.studentno']=array('like',$studentno);
        if($name!='%') $condition['students.name']=array('like',$name);
        if($classno!='%') $condition['students.classno']=array('like',$classno);
        if($school!='') $condition['classes.school']= $school;
        if($status!='') $condition['students.status']= $status;
        $data=$this->query->table('students')->join('classes  ',' classes.classno=students.classno')
            ->join('schools  ',' schools.school=classes.school')
            ->join('sexcode  ',' sexcode.code=students.sex')
            ->join('statusoptions  ',' statusoptions.name=students.status')
            ->join('personal  ',' personal.studentno=students.studentno')
            ->join('nationalitycode  ',' nationalitycode.code=personal.nationality','LEFT')
            ->join('partycode  ',' partycode.code=personal.party','LEFT')
            ->join('provincecode  ',' provincecode.code=personal.province','LEFT')
            ->join('classcode  ',' classcode.code=personal.class','LEFT')
            ->join('studentplan'," students.studentno=studentplan.studentno and studentplan.type='M'",'LEFT')
            ->join('majorplan','majorplan.rowid=studentplan.majorplanid','LEFT')
            ->join('majors','majors.majorschool=majorplan.majorschool','LEFT')
            ->join('majorcode ',' majorcode.code=majors.majorno','LEFT')
            ->join('majordirection ','majordirection.direction=majors.direction','LEFT')
            ->page($page,$rows)
            ->field("students.studentno,rtrim(students.name) name,students.sex,rtrim(sexcode.name) sexname,
            students.classno,rtrim(classes.classname) as classname,classes.school,rtrim(schools.name) schoolname,
            personal.party,rtrim(partycode.name) partyname,personal.nationality,rtrim(nationalitycode.name) nationalityname,
            personal.major,rtrim(majorcode.name) majorname,students.status,rtrim(statusoptions.value) statusname,
            students.years,personal.id,personal.examno,personal.province,rtrim(provincecode.name) provincename,
            rtrim(personal.midschool) midschool,rtrim(personal.address) address,rtrim(tel) tel,rtrim(origin) as origin,
            majorplan.year as grade,students.enterdate,personal.class classcode,
            rtrim(classcode.name) classcodename,rtrim(personal.photo) photo,majordirection.direction,rtrim(majordirection.name) directionname,CONVERT(varchar(100),birthday, 112) birthday")
            ->where($condition)->order('studentno')->select();
        $count= $this->query->table('students')->join('classes  ',' classes.classno=students.classno')->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }
    //读取毕业信息
    public function getGraduate($studentno){
        $condition['studentno']=$studentno;
        $data=$this->query->table('students')
            ->field('rtrim(degree) degree,rtrim(majorname) majorname,rtrim(thesis) thesis,rtrim(mentor) mentor,rtrim(graduateno) graduateno,rtrim(verdict) verdict')
            ->where($condition)->find();
        if(!is_array($data)) {
                throw new Exception('studentno' . $studentno, MyException::ITEM_NOT_EXISTS);
        }
        return $data;
    }
    /**修改学生密码(管理员更改）
     * @param string $studentno 学号
     * @param string $password 新密码
     * @return array
     * @throws \think\Exception
     */
    function changePassword($studentno='',$password=''){
        if($studentno==''||$password=='')
            throw new Exception('studentno or password is empty ', MyException::PARAM_NOT_CORRECT);

        if(MyAccess::checkStudentSchool($studentno)) {
            $condition['studentno'] = $studentno;
            $data['password'] = $password;
            $this->query->table('students')->where($condition)->setField($data);
            $status = 1;
            $info = "密码修改成功";
        }
        else{
            $status = "错误";
            $info = "你无法修改其他学院学生的密码！";
        }
        $result = array('info' => $info, 'status' => $status);
        return $result;
    }

    public function changeSelfPassword($oldPassword,$newPassword){
        $condition['studentno']= session('S_USER_NAME');
        $condition['password']= $oldPassword;
        $result= $this->query->table('students')->where($condition)->select();

        if(is_array($result)&&count($result)==1)
        {
            $data['password']=$newPassword;
            $data['enterdate']=date("Y-m-d H:i:s");
            $this->query->table('students')->where($condition)->setField($data);
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
    /**获取单个学生详细信息
     * @param $studentno
     * @throws \think\Exception
     */
    function getStudentDetail($studentno){
        $condition=null;
        $condition['students.studentno']=$studentno;
        $data=$this->query->table('students')->join('classes  ',' classes.classno=students.classno')
            ->join('schools  ',' schools.school=classes.school')
            ->join('sexcode  ',' sexcode.code=students.sex')
            ->join('statusoptions  ',' statusoptions.name=students.status')
            ->join('personal  ',' personal.studentno=students.studentno')
            ->join('nationalitycode  ',' nationalitycode.code=personal.nationality','LEFT')
            ->join('partycode  ',' partycode.code=personal.party','LEFT')
            ->join('majorcode  ',' majorcode.code=personal.major')
            ->join('provincecode  ',' provincecode.code=personal.province','LEFT')
            ->join('classcode  ',' classcode.code=personal.class','LEFT')
            ->field("students.studentno,rtrim(students.name) name,students.sex,rtrim(sexcode.name) sexname,
            students.classno,rtrim(classes.classname) as classname,classes.school,rtrim(schools.name) schoolname,
            personal.party,rtrim(partycode.name) partyname,personal.nationality,rtrim(nationalitycode.name) nationalityname,
            personal.major,rtrim(majorcode.name) majorname,students.status,rtrim(statusoptions.value) statusname,
            students.years,rtrim(personal.id) id,rtrim(personal.examno) examno,personal.province,
            rtrim(provincecode.name) provincename,personal.birthday,
            rtrim(personal.midschool) midschool,rtrim(personal.address) address,rtrim(tel) tel,rtrim(origin) as origin,
            cast('20'+substring(classes.classno,1,2) as char(4)) as grade,students.enterdate,personal.class classcode,
            rtrim(classcode.name) classcodename,personal.postcode,
            cast('/photo/'+rtrim(students.studentno)+'.jpg' as char(40)) as photo")
            ->where($condition)->select();
        if(!is_array($data)||count($data)!=1)
            throw new Exception('studentno'.$studentno, MyException::PARAM_NOT_CORRECT);
        return $data[0];
    }

    /**更新学生详细信息
     * @param $postData
     * @return array
     * @throws \Exception
     * @throws \think\Exception
     */
    function updateDetail($postData){
        $studentno=$postData['studentno'];
        $classno=$postData['classno'];
        $school=Item::getClassItem($classno)['school'];
        if(MyAccess::checkStudentSchool($studentno)) {
            $this->query->startTrans(); //用事务保证两个表同时修改了。
            try {
                $condition=null;
                $data=null;
                $condition['studentno'] = $studentno;
                $data['name'] = $postData['name'];
                $data['sex'] = $postData['sex'];
                $data['years'] = $postData['years'];
                $data['enterdate'] = $postData['enterdate'];
                $data['classno'] = $classno;
                $data['school'] = $school;
                $data['status'] = $postData['status'];
                $this->query->table('students')->where($condition)->setField($data);

                $condition=null;
                $data=null;
                $condition['studentno'] = $studentno;
                $data['name'] = $postData['name'];
                $data['sex'] = $postData['sex'];
                $data['birthday'] = $postData['birthday'];
                $data['nationality'] = $postData['nationality'];
                $data['party'] = $postData['party'];
                $data['major'] = $postData['major'];
                $data['class'] = $postData['classcode'];
                $data['midschool'] = $postData['midschool'];
                $data['postcode'] = $postData['postcode'];
                $data['address'] = $postData['address'];
                $data['examno'] = $postData['examno'];
                $data['id'] = $postData['id'];
                $data['origin'] = $postData['origin'];
                $data['province'] = $postData['province'];
                $data['tel'] = $postData['tel'];
                $this->query->table('personal')->where($condition)->setField($data);
            }
            catch(\Exception $e){
                $this->query->rollback();
                throw $e;
            }
            $this->query->commit();
            $status = 1;
            $info = "数据更新成功";
        }
        else{
            $status = 0;
            $info = "你无法修改其他学院学生的详细信息！";
        }
        $result = array('info' => $info, 'status' => $status);
        return $result;
    }

    /**添加单个学生
     * @param $postData
     * @return array
     * @throws \Exception
     * @throws \think\Exception
     */
    function addStudent($postData)
    {
        $classno = $postData['classno'];
        $school=Item::getClassItem($classno)['school'];
        if (MyAccess::checkClassSchool($classno)) {
            $this->query->startTrans(); //用事务保证两个表同时修改了。
            try {
                $data = null;

                $data['studentno'] = $postData['studentno'];
                $data['name'] = $postData['name'];
                $data['sex'] = $postData['sex'];
                $data['years'] = $postData['years'];
                $data['enterdate'] = $postData['enterdate'];
                $data['classno'] = $postData['classno'];
                $data['school'] = $school;
                $data['status'] = $postData['status'];
                $data['grade'] = 1;
                $data['password'] = $postData['studentno'];
                $this->query->table('students')->insert($data);

                $data = null;
                $data['studentno'] = $postData['studentno'];
                $data['name'] = $postData['name'];
                $data['sex'] = $postData['sex'];
                $data['years'] = $postData['years'];
                $data['school'] = $school;
                $data['birthday'] = $postData['birthday'];
                $data['years'] = $postData['years'];
                $data['nationality'] = $postData['nationality'];
                $data['party'] = $postData['party'];
                $data['major'] = $postData['major'];
                $data['class'] = $postData['classcode'];
                $data['midschool'] = $postData['midschool'];
                $data['postcode'] = $postData['postcode'];
                $data['address'] = $postData['address'];
                $data['examno'] = $postData['examno'];
                $data['id'] = $postData['id'];
                $data['origin'] = $postData['origin'];
                $data['province'] = $postData['province'];
                $data['tel'] = $postData['tel'];
                $this->query->table('personal')->insert($data);
            } catch (\Exception $e) {
                $this->query->rollback();
                throw $e;
            }
            $this->query->commit();
            $status = 1;
            $info = "学生添加成功";
        } else {
            $status = 0;
            $info = "你无法为其它学院的班级添加学生！";
        }
        $result = array('info' => $info, 'status' => $status);
        return $result;
    }

    /**更新锁定状态
     * @param $postData
     * @return array
     * @throws \Exception
     */
    function updateStatus($postData){
        $updateRow=0;
        //更新部分
        //开始事务
        $this->query->startTrans();
        try {
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['studentno'] = $one->studentno;
                    $data['free'] = $one->free;
                    $updateRow += $this->query->table('students')->where($condition)->update($data);
                }
            }
        }
        catch(\Exception $e){
            $this->query->rollback();
            throw $e;
        }
        $this->query->commit();
        $info='';
        if($updateRow>0) $info.=$updateRow.'条更新！</br>';
        $status=1;
        if($info=='') {
            $info="没有数据被更新";
            $status=0;
        }
        $result=array('info'=>$info,'status'=>$status);
        return $result;
    }
} 