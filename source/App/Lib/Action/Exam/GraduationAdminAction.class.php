<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-1
 * Time: 下午3:07
 */
class GraduationAdminAction extends RightAction {
    private $md;
    protected $message = array("type"=>"info","message"=>"","dbError"=>"");

    public function __construct(){
        parent::__construct();
        $this->md = M("SqlsrvModel:");
    }


    //todo:毕业免听报名
    public function GraduationMtbm(){
        if($this->_hasJson){
            //todo:查询标题
            //学校NO  和学校名称
            $SchoolInfo=$this->md->sqlFind($this->md->getSqlMap('exam/GraduationMtbm_title.SQL'),array(':STUDENTNO'=>$_POST['STUDENTNO']));
            //班级名称
            $classname=$this->md->sqlFind($this->md->getSqlMap('exam/GraduationMtbm_title_two.SQL'),array(':STUDENTNO'=>$_POST['STUDENTNO']));

            //学生姓名
            $username=$this->md->sqlFind("SELECT NAME AS STUDENTNAME FROM STUDENTS WHERE STUDENTNO='{$_POST['STUDENTNO']}'");

            //todo:最外层检索
            if(!isset($_POST['COURSENO'])){
                $arr=array_merge($SchoolInfo,$classname,$username);
                echo json_encode($arr);
                exit;
            }else if(isset($_POST['COURSENO'])&&($SchoolInfo&&$classname&&$username)){
                $courseinfo=$this->md->sqlFind("SELECT COURSENAME, CREDITS FROM COURSES WHERE  COURSENO ='{$_POST['COURSENO']}'");
                if(!$courseinfo){
                       exit('false');
                }
                $arr=array_merge($SchoolInfo,$classname,$username,$courseinfo);
                echo json_encode($arr);
                exit;
            }else{
                     exit('false');
            }
        }

        //todo:找出教师学院
        $teacherSCHOOL=$this->md->sqlfind('select SCHOOL from TEACHERS where RTRIM(TEACHERNO)=:TEACHERNO',array(':TEACHERNO'=>$_SESSION['S_USER_INFO']['TEACHERNO']));
       $this->assign('teacherSCHOOL',$teacherSCHOOL);

        $this->display();
    }

    //todo:毕业免听报名--->提交到数据库
    public function GraduationMtbm_insert(){

        foreach($_POST['ct'] as $val){
            $bool=$this->md->sqlExecute($this->md->getSqlMap('exam/GraduationMtbm_insert.SQL'),
                array(':Q_STUDENTNO'=>$_POST['STUDENTNO'],':Q_YEAR'=>$_POST['YEAR'],':Q_TERM'=>$_POST['TERM'],':Q_COURSENO'=>$val['kh']
                ,':COURSENO'=>$val['kh'],':STUDENTNO'=>$_POST['STUDENTNO'],':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':PLANTYPE'=>$val['PLANTYPE'],':TESTTYPE'=>$val['TESTTYPE']));
            if(!$bool){
                exit('failed');
            }
        }
        exit('success');
    }

    //todo:毕业免听查询
    public function GraduationMtQuery(){
        $this->xiala('schools','schools');

        $user_teacherno=$_SESSION["S_USER_INFO"]["TEACHERNO"];
        $user_school = $this->md->sqlFind("SELECT T.SCHOOL FROM TEACHERS T WHERE T.TEACHERNO='$user_teacherno'");
        $this->assign('userschool', $user_school["SCHOOL"]);

        $this->display();
    }

    public function GraduationMtpaikao(){
        if($this->_hasJson){
            $bool=$this->md->sqlFind("select COURSENO FROM TestCOURSE where COURSENO='{$_POST['bind'][':COURSENO']}'");
            if(!$bool){
                exit('false');
            }
            //todo:修改TestCourse 的等价课程
            $one=$this->md->sqlExecute("update TestCourse set CourseNo2=:EQCOURSENO WHERE CourseNo=:COURSENO",array(':EQCOURSENO'=>$_POST['bind'][':EQCOURSENO'],':COURSENO'=>$_POST['bind'][':COURSENO']));
            exit('true');
            exit;
        }
        $teacherSCHOOL=$this->md->sqlfind('select SCHOOL from TEACHERS where RTRIM(TEACHERNO)=:TEACHERNO',array(':TEACHERNO'=>$_SESSION['S_USER_INFO']['TEACHERNO']));
        $this->assign('teacherSCHOOL',$teacherSCHOOL);
        $this->display();
    }


    //todo:毕业免听排考初始化
    public function chushihua(){

        $this->md->startTrans();
        //todo:初始化指定学年学期的课程
        $this->md->sqlExecute('delete from TestCourse');
        $course=$this->md->sqlExecute($this->md->getSqlMap('exam/GraduationMtpaikao_CSH_Course.SQL'),array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM']));

        //todo:初始化学生
        $this->md->sqlExecute('delete from TestStudent');
        $student=$this->md->sqlExecute($this->md->getSqlMap('exam/GraduationMtpaikao_CSH_Student.SQL'),array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM']));

        //todo:删除Testbatch
        $this->md->sqlExecute('delete from Testbatch');

        //todo:找出选课最大的
        $max=$this->md->sqlFind($this->md->getSqlMap('exam/dataInit_max.SQL'));

        //todo:插入
        for($i=0;$i<$max['row'];$i++){
            $this->md->sqlExecute($this->md->getSqlMap('exam/dataInit_insert_Testbatch.SQL'),array(':FLAG'=>$i+1,':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM']));
        }
        $this->md->commit();
    }

    public function kaowuquery(){
        $this->display();
    }



}
?>