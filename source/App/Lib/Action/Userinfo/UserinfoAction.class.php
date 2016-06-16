<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 13-12-2
 * Time: 下午3:01
 **/
class UserinfoAction extends RightAction
{
    private $md;        //存放模型对象
    private $base;      //路径
    /**
     *  班级管理
     *
     **/
    public function __construct(){
        parent::__construct();
        $this->md=new SqlsrvModel();
        $this->base='Userinfo/';
        $this->assign('quanxian',trim($_SESSION['S_USER_INFO']['ROLES']));
    }

    /*
     *todo:基本信息的方法页面
     */
    public function basicinfo(){
        if($_POST['SQLPATH']){
            $jieguo=$this->md->sqlExecute($this->md->getSqlMap($_POST['SQLPATH']),$_POST['arr']);
            if($jieguo)
                echo '修改成功';
            else echo 'error';
            exit;
        }
        $info=$this->md->sqlFind($this->md->getSqlMap($this->base.'one_selectBasicinfo.SQL'),array(':username'=>$_SESSION['S_USER_INFO']['USERNAME']));
        $this->xiala('nationalitycode','nationalitycode');
        $this->xiala('partycode','partycode');
        $this->xiala('professioncode','professioncode');
        $this->xiala('edulevelcode','edulevelcode');
        $this->xiala('degreecode','degreecode');
        $this->assign('info',$info);
        $this->assign('teacherno',$_SESSION['S_USER_INFO']['TEACHERNO']);
        $this->display();
    }

    /*
     * todo:个人学习经历页面
     */
    public function xuexijingli(){
        if($_POST['SQLPATH']){
            $jieguo=$this->md->sqlExecute($this->md->getSqlMap($this->base.$_POST['SQLPATH']),$_POST['arr']);
            echo $jieguo;
            exit;
        }
        $info=$this->md->sqlQuery($this->md->getSqlMap($this->base.'Two_selectStudy.SQL'),array(':USERNAME'=>$_SESSION['S_USER_INFO']['USERNAME']));
        $this->assign('info',$info);

        $this->xiala('honorlevelcode','honorlevelcode');                //获奖级别
        $this->assign('teacherno',$_SESSION['S_USER_INFO']['TEACHERNO']);
        $this->display();
    }

    //todo:荣耀与获奖页面
    public function honor(){
        if($_POST['SQLPATH']){
            $jieguo=$this->md->sqlExecute($this->md->getSqlMap($this->base.$_POST['SQLPATH']),$_POST['arr']);
            echo $jieguo;
            exit;
        }
        $info=$this->md->sqlQuery($this->md->getSqlMap($this->base.'Three_selectHonor.SQL'),array(':USERNAME'=>$_SESSION['S_USER_INFO']['USERNAME']));
        $this->xiala('honorlevelcode','honorlevelcode');                //获奖级别
        $this->assign('info',$info);
        $this->assign('teacherno',$_SESSION['S_USER_INFO']['TEACHERNO']);
        $this->display();
    }

    //todo：论文页面
    public function thesis(){
        if($_POST['SQLPATH']){
            $jieguo=$this->md->sqlExecute($this->md->getSqlMap($this->base.$_POST['SQLPATH']),$_POST['arr']);
            echo $jieguo;
            exit;
        }
        $info=$this->md->sqlQuery($this->md->getSqlMap($this->base.'Four_selectThesis.SQL'),array(':USERNAME'=>$_SESSION['S_USER_INFO']['USERNAME']));
        $this->assign('info',$info);

        $this->assign('teacherno',$_SESSION['S_USER_INFO']['TEACHERNO']);
        $this->display();
    }

    //todo:工作量页面
    public function work(){
        if($this->_hasJson){
            $count=$this->md->sqlFind($this->md->getSqlMap($this->base.'Five_countwork.SQL'),array(':USERNAME'=>$_SESSION['S_USER_INFO']['USERNAME']));
            if($arr['total']=$count['']){
                $arr['rows']=$this->md->sqlQuery($this->md->getSqlMap($this->base.'Five_selectwork.SQL'),array(':USERNAME'=>$_SESSION['S_USER_INFO']['USERNAME'],':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize));
            }else{
                $arr['rows']=array();
            }
            $this->ajaxReturn($arr,'JSON');
            exit;
        }
        $this->display();
    }


    //todo:学期课程(teacherList)页面
    public function teacherList(){
        if($this->_hasJson){
            $one=$this->md->sqlFind("select USERS.username,TEACHERS.NAME,TEACHERS.SCHOOL from users LEFT JOIN TEACHERS on USERS.teacherno=TEACHERS.TEACHERNO where USERS.TEACHERNO='{$_SESSION['S_USER_INFO']['TEACHERNO']}'");
            $count=$this->md->sqlFind($this->md->getSqlMap($this->base.'Six_countTeacherList.SQL'),array(':TEACHERNAME'=>doWithBindStr($one['NAME']),':SCHOOL'=>doWithBindStr($one['SCHOOL']),':TEACHERNO'=>doWithBindStr($one['username'])));

            if($arr['total']=$count['ROWS']){

                $arr['rows']=$this->md->sqlQuery($this->md->getSqlMap($this->base.'Six_selectTeacherList.SQL'),array(':TEACHERNAME'=>doWithBindStr($one['NAME']),':SCHOOL'=>doWithBindStr($one['SCHOOL']),':TEACHERNO'=>doWithBindStr($one['username']),':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize));
            }else{
                $arr['rows']=array();
            }
            $this->ajaxReturn($arr,'JSON');
            exit;
        }
        //todo:查询当前的学年学期
        $yearterm=$this->md->sqlFind($this->md->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
        $this->assign('yearterm',$yearterm);
        $this->display();
    }

    /*
     * todo:教师课程
     */
    public function teachercourse(){
        if($this->_hasJson){
            $one=$this->md->sqlFind($this->md->getSqlMap($this->base.'Six_countTeacher_Course.SQL'),array(':YEAR'=>$_POST['year'],':TERM'=>$_POST['term'],':TEACHERNO'=>$_POST['teacherno']));
            if($arr['total']=$one['ROWS']){
                $arr['rows']=$this->md->sqlQuery($this->md->getSqlMap($this->base.'Six_selectTeacher_Course.SQL'),array(':YEAR'=>$_POST['year'],':TERM'=>$_POST['term'],':TEACHERNO'=>$_POST['teacherno'],':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize));
            }else{
                $arr['rows']=array();
            }
            $this->ajaxReturn($arr,'JSON');
            exit;
        }

        $this->assign('year',$_GET['year']);            //window.open传过来的学年
        $this->assign('term',$_GET['term']);            //window.open传过来的学期
        $this->assign('teacherno',$_GET['teacherno']);//window.open传过来的教师号
        $this->display();
    }

    //todo:课程情况的页面
    public function teachercourse2(){
        //todo:第二行的信息
        $one=$this->md->sqlFind($this->md->getSqlMap($this->base.'Seven_Mystudent_Top_two.SQL'),array(':year'=>$_POST['year'],':term'=>$_POST['term'],':courseno'=>$_POST['courseno']));
        //todo:第三行信息
        $two=$this->md->sqlFind($this->md->getSqlMap($this->base.'Seven_Mystudent_Top_js.SQL'),array(':year'=>$_POST['year'],':term'=>$_POST['term'],':courseno'=>$_POST['courseno']));
        $this->ajaxReturn(array_merge($one,$two),'JSON');
    }
    //成绩输入
    public function inputResults(){
        if($this->_hasJson){

            exit;
        }
        $this->assign('school',$this->md->sqlFind("select SCHOOL from TEACHERS where TEACHERNO='{$_SESSION['S_USER_INFO']['TEACHERNO']}'"));
        $this->assign('teacherno',$_SESSION['S_USER_INFO']['TEACHERNO']);
        $this->xiala('schools','schools');
        $this->display();
    }
    //todo:执行方法
    public function Sexecute(){
        foreach($_POST['order'] as $key=>$val){
            $_POST['order'][$key]=$_POST['bind'][$key];
        }
        $bool=$this->md->sqlExecute($this->md->getSqlMap($_POST['exe']),$_POST['order']);
        if($bool)echo 'true';
    }


    //todo:教学质量的页面
    public function teachingQuality(){
        $this->assign('username',$_SESSION['S_USER_INFO']['USERNAME']);
        $this->display();
    }

    //todo:教师周课表页面
    public function teacherWeekCourse(){
        $array=array();            //todo:保存排列后的信息
        $OEW = array("B"=>"","E"=>"（双周）","O"=>"（单周）");      //todo:单双周数组
        $courseList=$this->md->sqlQuery($this->md->getSqlMap($this->base.'Six_teacherWeekCourse.SQL'),array(':teacherno'=>$_GET['teacherno'],':year'=>$_GET['year'],':term'=>$_GET['term']));
        /*       echo '<pre>';
                print_r($courseList);
                echo '=========================';*/

        $teacherinfo=$this->md->sqlFind('select NAME from TEACHERS where TEACHERNO=:teacherno',array(':teacherno'=>$_GET['teacherno']));
        $User = A('Room/Room');
        $coursetype=$this->md->sqlQuery('select * from TIMESECTORS');        //todo:获取 所有课程类型信息

        $skarray=array();  //todo:要上课的课号列表
        foreach($courseList as $v){
            if($v['TIME'][1]=='0')continue;
            $skarray[$v['COURSENO'].$v['COURSEGROUP']]=$v['COURSENO'].$v['COURSEGROUP'];
        }
        $ar2=$User->getTime($coursetype);   //todo:节次数组        (以NAME为下标)
        $onejie=array_reduce($coursetype,'countOneDay');                               //todo:获得有1节课的数组



        $num=count($onejie);                                                             //todo:统计一节课的  有几节
        $ddstr='';
        // print_r($onejie);

        $daiding=$this->md->sqlQuery($this->md->getSqlMap($this->base.'Six_selectTeacher_Course.SQL'),array(':YEAR'=>$_GET['year'],':TERM'=>$_GET['term'],':TEACHERNO'=>$_GET['teacherno'],':start'=>0,':end'=>1000));
        /*    echo '<pre>';
                print_r($daiding);
                echo '-------------------------------';
                print_r($ar2);
                echo '+++++++++++++++++++++++++++++';
                print_r($onejie);*/
        /*        echo '<pre>';
                print_r($courseList);
                echo '<hr>';
                print_r($skarray);
                echo '<hr>';
                print_r($daiding);*/
        foreach($daiding as $v){
            if(!in_array($v['kh'],$skarray)){
                $ddstr.=$v['km']."({$v['kh']})&nbsp&nbsp";
            }
        }
        $array[0]=$ddstr;

        foreach($courseList as $key=>$val){
            $split = str_split($val["TIME"]);
            //   if($split[0]=='0') return $list[0] .= $times["COURSE"]."<br/>";

            if($val['WEEKS']!=262143){
                $weeks='周次'.str_pad(strrev(decbin($val['WEEKS'])),18,0);
            }else{
                $weeks='';
            }
            //todo:去查询课程名称
            $classname=$this->md->sqlQuery($this->md->getSqlMap($this->base.'Six_teacherWeekClassName.SQL'),array(':YEAR'=>$_GET['year'],':TERM'=>$_GET['term'],':COURSENO'=>$val['COURSENO'],':GROUP'=>$val['COURSEGROUP']));
            $classstr='';

            foreach($classname as $vall){
                $classstr.=$vall['CLASSNAME'];
            }

            for($i=1;$i<$num;$i+=2){

                for($j=0;$j<2;$j++){

                    if(($ar2[$val['TIME'][0]]['TIMEBITS'] & $ar2[$onejie[$i-1+$j]]['TIMEBITS'])>0){
                        //todo:ar2节次数组        (以NAME为下标)

                        if($ar2[$val['TIME'][0]]['UNIT']=="3"){
                            //todo:取最后一节课是第几节
                            $len=strlen(strrev(decbin($ar2[$val['TIME'][0]]['TIMEBITS'])));
                            //todo:表示到单节了
                            if(!($i+1<$len)){
                                $array[($i-1)/2+1][$val['TIME'][1]] .='(第'.$len.'节)'.$OEW[$val['TIME'][2]].$val["COURSE"]."{$weeks} {$classstr}{$val['TASK']}" ;
                            }else{
                                $array[($i-1)/2+1][$val['TIME'][1]] .=$OEW[$val['TIME'][2]].$val["COURSE"]."{$weeks} {$classstr}{$val['TASK']}<br/>";
                            }
                            break;
                        }
                        //todo:是一节课的就加上(第几节)  否则为空
                        $array[($i-1)/2+1][$val['TIME'][1]] .= ($ar2[$val['TIME'][0]]['UNIT']=="1" ? '('.trim($ar2[$val['TIME'][0]]['VALUE']).')' : '').$OEW[$val['TIME'][2]].$val["COURSE"]."{$weeks} {$classstr}{$val['TASK']}<br/>";
                        break;
                        /*   $array[($i-1)/2+1][$val['TIME'][1]] .= ($jieci[$val['TIME'][0]]['UNIT']=="1" ? '('.trim($jieci[$val['TIME'][0]]['VALUE']).')' : '').$OEW[$val['TIME'][2]].$val["COURSE"]."{$weeks}<br/>";
                           break;*/
                    }
                }
            }
        }

        /*      echo '<pre>';
          print_r($array);*/

        $str=$User->web($array,$teacherinfo['NAME'],date('Y-m-d H:i:s'),array('year'=>$_GET['year'],'term'=>$_GET['term']));

        $this->assign('str',$str);

        $this->display();










    }}

//todo:一天有几节课
function countOneDay($v1, $v2){
    if(!$v1) $v1 = array();
    if($v2['UNIT']=="1") $v1[]=$v2["NAME"];
    return $v1;
}