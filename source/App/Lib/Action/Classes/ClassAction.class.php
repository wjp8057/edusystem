<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 13-12-2
 * Time: 下午3:01
 **/
class ClassAction extends RightAction
{
    private $md;        //存放模型对象

    /**
     *  班级管理
     *
     **/
    public function __construct(){
  //      $this->model = M("SqlsrvModel:");
        parent::__construct();
        $this->md=new SqlsrvModel();
        $this->assign('quanxian',trim($_SESSION['S_USER_INFO']['ROLES']));
    }
    public function classesq()
    {
        if($_POST['auth_judge']){
            $sqlone="select SCHOOL from TEACHERS where TEACHERNO=:TEACHERNO";
            $school=$this->md->sqlFind($sqlone,array(':TEACHERNO'=>$_SESSION['S_USER_INFO']['TEACHERNO']));
            if(($school['SCHOOL']!='A1') && ($school['SCHOOL']!=$_POST['SCHOOL'])) {
                exit('false');
            }
        }
        if($this->_hasJson)
        {
            $model = M("SqlsrvModel:");
            // print_r($_POST);
            // exit;
            $bind = array(":CLASSNO"=>doWithBindStr($_POST['CLASSNO']), 
                ":CLASSNAME"=>doWithBindStr($_POST['CLASSNAME']),":SCHOOL"=>doWithBindStr($_POST['SCHOOL']),":YEAR"=>trim($_POST['YEAR']));
            // print_r($bind);
            // exit;
            //条数
        $sql = $model->getSqlMap("Class/classSelectCount.sql");
        trace($sql);
        $data = $model->sqlFind($sql,$bind);
        $arr['total'] = $data['ROWS'];
        if($arr['total']>0)
        {
            $sql = $model->getPageSql(null,"Class/classSelect.sql", $this->_pageDataIndex, $this->_pageSize);
            $arr['rows'] = $model->sqlQuery($sql,$bind);
            //trace($arr['rows']);
        }
        else
            $arr['rows']=array();
        $this->ajaxReturn($arr,"JSON");
        exit;

    }

        $shuju=M('schools');                                   // 学院数据

        $school=$shuju->select();
        $sjson=array();

        foreach($school as $val)
        {
            $sjson2['text']=trim($val['NAME']);
            $sjson2['value']=$val['SCHOOL'];                  // 把学院数据转成json格式给前台的combobox使用
            array_push($sjson,$sjson2);
        }
        $sjson=json_encode($sjson);

        $this->assign('school',$school);
        $this->assign('sjson',$sjson);
        $this->xiala('schools','schools');

        $this->display();
    }
    /*
     * 插入数据的方法
     */
    public function insertcl()
    {

        $int=$this->md->sqlFind("select * from CLASSES where CLASSNO='{$_POST['CLASSNO']}'");
        if($int){
            exit('班号有冲突');
        }
        $shuju=M('classes');
        if(trim($_POST["YEAR"])=="")
            $_POST["YEAR"]=date("Y-m-d");
        // $_POST["SCHOOL"]=$_POST["SCHOOLNAME"];
        $sql=$shuju->add($_POST);
        //$arr=$_POST['data'];

        if($sql) echo '创建成功';
        else echo 'false';
    }
    /*
    * 修改数据的方法
    */
    public function updatecl()
    {
        $teacherno = $_SESSION['S_USER_INFO']['USERNAME'];
        $schoolno = trim($_POST['SCHOOL']);
//        var_dump($teacherno,$schoolno);
        if(checkSelfSchool($teacherno,$schoolno) == 0){
            exit('无法修改其他学院的学生');
        }

        $this->quanxianpd2();
        $sstr='';
        $shuju=M('classes');
        if(is_numeric($_POST['SCHOOLNAME']))$sstr="SCHOOL='".$_POST['SCHOOLNAME']."',";

        $pd=$this->md->sqlExecute("update classes set {$sstr}CLASSNO=:classno,CLASSNAME=:classname,STUDENTS=:students,YEAR=:year where CLASSNO=:ctwo",
        array(':classno'=>$_POST['CLASSNO'],':classname'=>$_POST['CLASSNAME'],':students'=>$_POST['STUDENTS'],':year'=>$_POST['YEAR'],':ctwo'=>$_POST['CLASSNO']));


        if($pd)
            echo 'true';
        else
            echo 'false';
    }

    public function deletecl()
    {
        $this->quanxianpd2();
        $shuju=M('classes');
        $data=array();
        $data['CLASSNO']=array('in',$_POST['in']);

        $arr=$shuju->where($data)->delete();
        if($arr)
            echo 'true';
        else
            echo 'false';
    }

    //todo:查询出  班级学生所用到的方法
    public function ClassQueryStudent(){
        //查询出这个班级的信息
        $count=$this->md->sqlFind($this->md->getSqlMap('Class/classQueryStudentCount.SQL'),array(':ClassNo'=>$_POST['CLASSNO']));
        if($one['total']=$count[''])
            $one['rows']=$this->md->sqlQuery($this->md->getSqlMap('Class/classQueryStudent.SQL'),array(':ClassNo'=>$_POST['CLASSNO'],':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize));
        else
            $one['rows']=array();
        $this->ajaxReturn($one,'JSON');
    }

    //todo:查询出  教学计划的 方法
    public function Classjiaoxuejihua(){
        $count=$this->md->sqlFind($this->md->getSqlMap('Class/ClassxiuxueCount.SQL'),array(':Classno'=>doWithBindStr($_POST['CLASSNO'])));
        if($one['total']=$count[''])
            $one['rows']=$this->md->sqlQuery($this->md->getSqlMap('Class/Classxiuxuejihua.SQL'),array(':Classno'=>doWithBindStr($_POST['CLASSNO'])));
        else
            $one['rows']=array();
        $this->ajaxReturn($one,'JSON');
    }


    //todo:判断用户是否有权限删除 或添加 教学计划
    public function quanxianpd(){
        //查出教师的所在学院
        $sqlone="select SCHOOL from TEACHERS where TEACHERNO=:TEACHERNO";
        $school=$this->md->sqlFind($sqlone,array(':TEACHERNO'=>$_SESSION['S_USER_INFO']['TEACHERNO']));
        if($school['SCHOOL']=='A1'||$school['SCHOOL']==$_POST['SCHOOL'])    //todo:改变教学计划的权限是  本学院的老师    或者是  教务处级别的
            exit('true');           //todo:是教务处的是有权限的
        echo '<font color="red">不能修改其他班级的信息！</font>';

    }
    //todo:判断用户是否有权限删除 或添加 教学计划
    public function quanxianpd2(){
        //查出教师的所在学院
//        $sqlone="select SCHOOL from TEACHERS where TEACHERNO=:TEACHERNO";
//        $school=$this->md->sqlFind($sqlone,array(':TEACHERNO'=>$_SESSION['S_USER_INFO']['TEACHERNO']));
        $school = trim($_SESSION['S_USER_INFO']['SCHOOL']);
        if($school['SCHOOL']!='A1'&& $school['SCHOOL']!=$_POST['SCHOOL'])    //todo:改变教学计划的权限是  本学院的老师    或者是  教务处级别的
            exit('false');           //todo:是教务处的是有权限的
    }


    /*
     * 删除 班级学生列表的方法
     */
    public function deleteClassStudent(){
        $pd=true;
        foreach($_POST['STUDENT'] as $val){
            $one=$this->md->sqlExecute("update students set classno='' where RTRIM(studentno)=:STUDENTNO",array(':STUDENTNO'=>trim($val['StudentNo'])));
            if(!$one)
                $pd=false;
        }
        if($pd)
            echo '<b>删除成功</b>';
        else
            echo '<font color="red">删除的过程中出现异常，请测试</font>';
    }

    /*
     * 删除 班级教学计划的方法
     */
    public function deleteClassprogram(){

        $bool=$this->md->sqlExecute($this->md->getSqlMap('Class/deleteClassProgra.SQL'),array(':PROGRAMNO'=>$_POST['programno'],':CLASSNO'=>$_POST['classno']));
        if($bool)
            echo '删除成功！';
        else
            echo '数据库异常';

    }

    /*
     *todo:查询出教学计划的方法
     */

    public function selectjiaoxuejihua(){
        $count=$this->md->sqlFind($this->md->getSqlMap('Class/countjiaoxuejihua.SQL'),array(':PROGRAMNO'=>doWithBindStr($_POST['PROGRAMNO']),':PROGRAMNAME'=>doWithBindStr($_POST['PROGRAMNAME']),':SCHOOL'=>doWithBindStr($_POST['SCHOOL'])));
        if($arr['total']=$count['ROWS'])
            $arr['rows']=$this->md->sqlQuery($this->md->getSqlMap('Class/selectjiaoxuejihua.SQL'),array(':PROGRAMNO'=>doWithBindStr($_POST['PROGRAMNO']),':PROGRAMNAME'=>doWithBindStr($_POST['PROGRAMNAME']),':SCHOOL'=>doWithBindStr($_POST['SCHOOL']),':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize));
        else
            $arr['rows']=array();
        echo json_encode($arr);
    }

    //todo:查询出学生的方法

    public function selectxuesheng(){
        $count=$this->md->sqlFind($this->md->getSqlMap('Class/ClassCountStudent.SQL'),array(':StudentNo'=>doWithBindStr($_POST['STUDENTNO']),':Name'=>doWithBindStr($_POST['NAME']),':School'=>doWithBindStr($_POST['SCHOOL']),':ClassNo'=>doWithBindStr($_POST['CLASSNO'])));
        if($arr['total']=$count['ROWS'])
            $arr['rows']=$this->md->sqlQuery($this->md->getSqlMap('Class/ClassSelectStudent.SQL'),array(':StudentNo'=>doWithBindStr($_POST['STUDENTNO']),':Name'=>doWithBindStr($_POST['NAME']),':School'=>doWithBindStr($_POST['SCHOOL']),':ClassNo'=>doWithBindStr($_POST['CLASSNO']),':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize));
        else
            $arr['rows']=array();
        echo json_encode($arr);
    }

    /*
     * todo:添加教学计划号的方法
     */
    public function addprogram(){

            $pd=true;
        foreach($_POST['data'] as $key=>$val){
            $bool=$this->md->sqlExecute('INSERT INTO R16(PROGRAMNO,CLASSNO) VALUES(:PROGRAMNO1,:CLASSNO1)',array(':PROGRAMNO1'=>$val['PROGRAMNO'],':CLASSNO1'=>$_POST['CLASSNO']));
            if(!$bool){
                $pd=false;
            }
        }
        if($pd)
            echo '<b>数据插入成功</b>';
        else
            echo '<font color="red">有重复提交的数据,请检查班级里是否已经有该教学计划</font>';
    }

    /*
     * todo:给班级添加学生的方法
     */
    public function addStudent(){

        $classno=$this->md->sqlFind("select CLASSNO,SCHOOL from CLASSES where CLASSNO=:CLASSNO",array(':CLASSNO'=>$_POST['CLASSNO']));//todo:查询出指定班级的 所在学院

        $pd=true;

        foreach($_POST['data'] as $key=>$val){
            $bool=$this->md->sqlExecute('update students set classno=:CLASSNO,school=:SCHOOL where studentno=:STUDENTNO',array(':CLASSNO'=>$classno['CLASSNO'],':SCHOOL'=>$classno['SCHOOL'],':STUDENTNO'=>$val['StudentNo']));
            if(!$bool){
                $pd=false;
            }
        }
        if($pd)
            echo '<b>数据插入成功</b>';
        else
            echo '<font color="red">待添加,这是添加学生失败时候的</font>';

    }


    //todo:将选中的教学计划统一绑定到班级学生 的 方法
    public function addProgramToStudent(){
        $this->md->startTrans();//启动事物
        $pd=true;
        foreach($_POST['P'] as $val){
            //todo:第一步：删除R28中已经绑定了该教学计划的学生（因为重复插入会报错（存在主键））
            $bool=$this->md->sqlExecute($this->md->getSqlMap('Class/deleteProgramToStudent.SQL'),array(':PROGRAMNO'=>$val['PROGRAMNO'],':CLASSNO'=>$_POST['CLASSNO']));
            //todo:第二步：插入R28中 这个班级里的所有学生 绑定该教学计划
            $bool2=$this->md->sqlExecute($this->md->getSqlMap('Class/insertProgramToStudent.SQL'),array(':PROGRAMNO'=>$val['PROGRAMNO'],':CLASSNO'=>$_POST['CLASSNO']));
            if($bool2){
               $this->md->commit();    //提交
            }else{
                $pd=false;
                $this->md->rollback();  //回滚
            }
        }
        if($pd)
            echo '<b>教学计划绑定成功</b>';
        else
            echo '<font color="red">多条插入过程中有异常，请检查</font>';

    }

    //todo:查询出  学生个人教学计划的方法
    public function studentProgram(){
        $count=$this->chaxun('Class/studentprogramCount.SQL',array(':STUDENTNO'=>$_POST['STUDENTNO']),'Find');
        if($arr['total']=$count[''])
            $arr['rows']=$this->chaxun('Class/studentprogram.SQL',array(':STUDENTNO'=>$_POST['STUDENTNO']));
        else
            $arr['rows']=array();
        $this->ajaxReturn($arr,'JSON');
    }

    //todo:查询出某个教学计划 所有课程的方法
    public function programcourse(){
        $count=$this->chaxun('Class/countprogramcourse.SQL',array(':PROGRAMNO'=>$_POST['PROGRAMNO']),'Find');
        if($arr['total']=$count['']){
            $User = A('Room/Room');     //todo:转换进制用的函数
            $arr['rows']=$User->jinzhi($this->chaxun('Class/selectprogramcourse.SQL',array(':PROGRAMNO'=>$_POST['PROGRAMNO'],':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize)));
        }else{
            $arr['rows']=array();
         }
           $this->ajaxReturn($arr,'JSON');
    }


    //todo:查询出某个专业的教学计划
    public function zhuanyeprogram(){
        $count=$this->chaxun('Class/countzhuanyeprogram.SQL',array(':SCHOOL'=>$_POST['SCHOOL']),'Find');
        if($arr['total']=$count['']){
            $arr['rows']=$this->chaxun('Class/zhuanyeprogram.SQL',array(':SCHOOL'=>$_POST['SCHOOL'],':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize));
        }else{
            $arr['rows']=array();
        }
        $this->ajaxReturn($arr,'JSON');
    }

    //专门用于查询的方法
    public function chaxun($sqlPath,$arr,$type='Query'){
        switch($type){
            case 'Query':
                return $this->md->sqlQuery($this->md->getSqlMap($sqlPath),$arr);
            case 'Find':
                return $this->md->sqlFind($this->md->getSqlMap($sqlPath),$arr);
            case 'Execute':
                return $this->md->sqlExecute($this->md->getSqlMap($sqlPath),$arr);
        }
    }



    //todo:班级导出excel功能
    public function excel(){
        $data=$this->md->sqlQuery($this->md->getSqlMap('Class/classExcel.SQL'),
            array());
    }


    public function studentlist(){
        $this->assign('info',$_GET);
        $this->display();
    }

    public function studentinfo(){
        $this->assign('info',$_GET);
        $this->display();
    }

    public function program_one(){
        $shuju=M('schools');                                   // 学院数据

        $school=$shuju->select();
        $this->assign('school',$school);
        $this->assign('info',$_GET);
        $this->display();
    }

    public function program_course(){
        $this->assign('info',$_GET);
        $this->display();
    }

    //添加学生的页面
    public function add_student(){
        $shuju=M('schools');                                   // 学院数据
        $school=$shuju->select();
        $this->assign('school',$school);
        $this->assign('info',$_GET);
        $this->display();
    }


    //todo:教学计划列表
    public function programlist(){
        $this->assign('info',$_GET);
        $this->display();
    }

    public function add_program(){
        $shuju=M('schools');                                   // 学院数据
        $school=$shuju->select();
        $this->assign('school',$school);
        $this->assign('info',$_GET);
        $this->display();
    }

}