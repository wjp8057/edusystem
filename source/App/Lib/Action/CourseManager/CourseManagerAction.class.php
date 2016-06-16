<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 13-12-2
 * Time: 下午3:01
 **/
class CourseManagerAction extends RightAction
{
    private $md;        //存放模型对象
    private $base;      //路径
    /**
     *   学期课表
     *
     **/
    public function __construct(){
        parent::__construct();
        $this->md=new SqlsrvModel();
        $this->base='CourseManager/';
    }

    //todo:选课的查询和管理
    public function one_one(){
        if($this->_hasJson){
            $this->md->startTrans();
            foreach($_POST['bind']['obj'] as $key=>$val){
                $panduan=$this->md->sqlExecute($this->md->getSqlMap($this->base.'One_one_updateStatus.SQL'),
                    array(':TINGKE_TYPE'=>$val['tkfs'],':REPEAT'=>$val['cx'],':FEE'=>$val['cxf'],':YEAR'=>$_POST['bind'][':YEAR'],':TERM'=>$_POST['bind'][':TERM'],':COURSENO'=>$_POST['bind'][':COURSENO'],':STUDENTNO'=>$val['xh']));
                if(!$panduan){
                    $this->md->rollback();
                    exit('error');
                }
            }
            $this->md->commit();
            exit('成功');
        }
        $this->assign('myschool',$this->getTeacherSchoolno($_SESSION['S_USER_INFO']['TEACHERNO']));
        $this->xiala('coursetypeoptions','coursetype');     //课程类型
        $this->xiala('schools','school');                  //todo:学院
        $this->xiala('approachcode','approachcode');        //todo:听课方式
        $this->display();
    }

    private function getTeacherSchoolno($nm){
        $bind = array(':teacherno'=>trim($nm));
        $rst = $this->md->sqlFind('select SCHOOL from TEACHERS WHERE TEACHERNO=:teacherno',$bind);
        return $rst?$rst['SCHOOL']:null;
    }


    //todo:列出所有选课人数超过教室座位数的课程
    public function one_two(){
        $this->display();
    }


    //todo:所有有空的公选课
    public function one_three(){
        $this->xiala('schools','schools');//todo: 学院
        $this->display();
    }


    //todo:列出少于(条件)的课程
    public function one_four(){
        $this->display();
    }


    public function one_five(){

        $this->xiala('coursetypeoptions','coursetypeoptions');
        $this->display();
    }




    public function one_seven(){
        $this->display();
    }

    //todo:回收站恢复
    public function huifu(){
        if($this->_hasJson){
            foreach($_POST['bind'] as $val){
                $this->md->startTrans();
                //todo:查出学生的信息                                       1
                $zero=$this->md->sqlFind($this->md->getSqlMap($this->base.'One_seven_huifu_one.SQL'),array(':STUDENTNO'=>doWithBindStr($val['xh'])));
                //todo:查出 这个课号 这个学生  在R32DUMP中的信息             2
                $one=$this->md->SqlFind($this->md->getSqlMap($this->base.'One_seven_huifu_two.SQL'),array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':COURSENO'=>$val['kh'],':STUDENTNO'=>$val['xh']));
                //todo:往R32表中插入数据                                    3
                $two=$this->md->sqlExecute($this->md->getSqlMap($this->base.'One_seven_huifu_three.SQL'), array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':COURSENO'=>substr($val['kh'],0,7),':GP'=>substr($val['kh'],7),
                    ':STUDENTNO'=>$val['xh'],':INPROGRAM'=>$one['INPROGRAM'],':CONFLICTS'=>$one['CONFLICTS'],':CF'=>$one['CONFIRM'],':APPROACH'=>$one['APPROACH'],':REPEAT'=>$one['REPEAT'],
                    ':FEE'=>$one['FEE'],':COURSETYPE'=>$one['COURSETYPE'],':EXAMTYPE'=>$one['EXAMTYPE'],':SELECTTIME'=>$one['TIME'] ));
                //todo:删除R32DUMP中的数据                                  4
                $three=$this->md->sqlExecute($this->md->getSqlMap($this->base.'One_seven_huifu_four.SQL'),array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':COURSENO'=>$val['kh'],':STUDENTNO'=>$val['xh']));

                //todo:统计出最新的人数                                     5
                $four=$this->md->sqlFind("SELECT COUNT(*) AS NUMBER FROM R32 WHERE YEAR={$_POST['YEAR']} AND TERM={$_POST['TERM']} AND COURSENO+[GROUP]='{$val['kh']}'");

                //todo:设置排课计划表的选课人数                              6
                $five=$this->md->sqlExecute("UPDATE SCHEDULEPLAN SET ATTENDENTS={$four['NUMBER']} WHERE YEAR={$_POST['YEAR']} AND TERM={$_POST['TERM']} AND COURSENO+[GROUP]='{$_POST['COURSENO']}'");
                if($zero&$one&$two&$three&$four){
                    $this->md->commit();
                    $boo=true;
                }else{
                    dump($zero);
                    dump($one.'=========one');
                    dump($two.'==========two');
                    dump($three.'==========three');
                    dump($four.'=========four');
                    dump($five.'==========five');
                    $this->md->rollback();
                    exit('失败');
                }

            }
            if($boo){
                exit('成功');
            }

        }
    }

    //todo:选课人数+新生预计人数大于教室座位数的课程
    public function one_eight(){

        $this->display();
    }


    //todo:将课程从开课计划删除
    public function delete_course(){
        //todo:判断是否有权限
       $teachershool= $this->md->sqlFind("select TEACHERS.SCHOOL from TEACHERS,USERS where USERS.TEACHERNO=TEACHERS.TEACHERNO AND USERS.USERNAME=''{$_SESSION['S_USER_NAME']}");
       $classschool=$this->md->sqlFind("SELECT SCHOOL FROM CLASSES  WHERE CLASSNO='{$_POST['CLASSNO']}");
       if($teachershool['SCHOOL']!=$classschool['SCHOOL']&&($teachershool['SCHOOL']!='A1')){
            exit('您不能删除其他学院的课程');
       }
      $courseno=substr($_POST['bind']['COURSENO'],0,7);
      $group=substr($_POST['bind']['COURSENO'],7);
        $this->md->startTrans();
 //       '2014',2,'008A04A','1331906','01'
        //todo:删除COURSEPLAN表中的数据
       $one=$this->md->sqlExecute($this->md->getSqlMap($this->base.'One_four_delete_one.SQL'),
           array(':YEAR'=>$_POST['bind']['YEAR'],':TERM'=>$_POST['bind']['TERM'],':COURSENO'=>$courseno,':CLASSNO'=>$_POST['bind']['CLASSNO'],':GROUP'=>$group));

        //todo:查询courseplan
       $two=$this->md->sqlFind("SELECT RECNO FROM COURSEPLAN WHERE (YEAR={$_POST['bind']['YEAR']}) AND (TERM={$_POST['bind']['TERM']}) AND (COURSENO='$courseno') AND ([GROUP]='$group')");

        //todo:删除SCHEDULE表中的数据
        $three=$this->md->sqlExecute($this->md->getSqlMap($this->base.'One_four_delete_two.SQL'),
            array(':YEAR'=>$_POST['bind']['YEAR'],':TERM'=>$_POST['bind']['TERM'],':COURSENO'=>$courseno,':GROUP'=>$group));

        //todo:删除R32表中的数据
        $four=$this->md->sqlExecute($this->md->getSqlMap($this->base.'One_four_delete_three.SQL'),
            array(':YEAR'=>$_POST['bind']['YEAR'],':TERM'=>$_POST['bind']['TERM'],':COURSENO'=>$courseno,':GROUP'=>$group));

        //todo:查询SCHEDULEPLAN的数据
       $five=$this->md->sqlFind("SELECT RECNO FROM SCHEDULEPLAN WHERE (YEAR={$_POST['bind']['YEAR']}) AND (TERM={$_POST['bind']['TERM']}) AND (COURSENO='$courseno') AND ([GROUP]='$group')");//2014',2,'008A04A','04'

        //todo:删除SCHEDULE数据

        $six=$this->md->sqlExecute("DELETE SCHEDULE WHERE (YEAR={$_POST['bind']['YEAR']}) AND (TERM={$_POST['bind']['TERM']}) AND (COURSENO='$courseno') AND ([GROUP]='$group')");


        //todo:删除SCHEDULEPLAN表中的数据
        $seven=$this->md->sqlExecute($this->md->getSqlMap($this->base.'One_four_delete_four.SQL'),
            array(':YEAR'=>$_POST['bind']['YEAR'],':TERM'=>$_POST['bind']['TERM'],':COURSENO'=>$courseno,':GROUP'=>$group));



        $this->md->commit();

        exit;
    }

    //todo:新窗口打开的页面
    public function StudentList(){
        if($this->_hasJson){
        }
        $arr=$this->md->sqlFind("select SCHOOL from TEACHERS WHERE TEACHERNO='{$_SESSION['S_USER_INFO']['TEACHERNO']}'");
        $this->assign('myschool',$this->md->sqlFind("select SCHOOL from TEACHERS WHERE TEACHERNO='{$_SESSION['S_USER_INFO']['TEACHERNO']}'"));
        $this->xiala('approachcode','approachcode');        //todo:听课方式
        $this->assign('shuju',$_GET);
        $this->display();

    }


    //todo:删除选课学生的方法
    public function delete_student(){
        $this->md->startTrans();
      //  ini_set("display_errors","On");
        //todo:这个是什么  设置confirm =1
        $four=$this->md->sqlExecute("UPDATE R32 SET [CONFIRM]=1 WHERE YEAR={$_POST['bind'][':YEAR']} AND TERM={$_POST['bind'][':TERM']} AND COURSENO+[GROUP]='{$_POST['bind'][':COURSENO']}'");

        $courseno=substr($_POST['bind'][':COURSENO'],0,7);
        $gp=substr($_POST['bind'][':COURSENO'],7);

        foreach($_POST['bind']['obj'] as $v){
            $val=array();
            $val['xh']=trim($v);

            $one=$this->md->sqlFind("select CONVERT(varchar(10),b.SELECTTIME,20) as TIME,b.* from(SELECT * FROM R32 WHERE YEAR={$_POST['bind'][':YEAR']} AND TERM={$_POST['bind'][':TERM']} AND COURSENO+[GROUP]='{$_POST['bind'][':COURSENO']}' AND STUDENTNO='{$val['xh']}')as b");

            //todo:往RE2 DUMP 插入数据
            $bind =  array(':YEAR'=>$_POST['bind'][':YEAR'],':TERM'=>$_POST['bind'][':TERM'],':COURSENO'=>$courseno,':GP'=>$gp,
                ':STUDENTNO'=>$val['xh'],':INPROGRAM'=>$one['INPROGRAM'],':CONFLICTS'=>$one['CONFLICTS'],':CF'=>$one['CONFIRM'],':APPROACH'=>$one['APPROACH'],':REPEAT'=>$one['REPEAT'],
                ':FEE'=>$one['FEE'],':COURSETYPE'=>$one['COURSETYPE'],':EXAMTYPE'=>$one['EXAMTYPE'],':SELECTTIME'=>$one['TIME']
                );
            $two=$this->md->sqlExecute($this->md->getSqlMap($this->base.'One_one_deleteStudent_two.SQL'),$bind);

            //todo:删除R32的数据
            $three=$this->md->sqlExecute("DELETE R32 WHERE YEAR={$_POST['bind'][':YEAR']} AND TERM={$_POST['bind'][':TERM']} AND COURSENO+[GROUP]='{$_POST['bind'][':COURSENO']}' AND STUDENTNO='{$val['xh']}'");

            //todo:统计出最新的人数
            $five=$this->md->sqlFind("SELECT COUNT(*) AS NUMBER FROM R32 WHERE YEAR={$_POST['bind'][':YEAR']} AND TERM={$_POST['bind'][':TERM']} AND COURSENO+[GROUP]='{$_POST['bind'][':COURSENO']}'");

            //todo:设置排课计划表的选课人数
            $six=$this->md->sqlExecute("UPDATE SCHEDULEPLAN SET ATTENDENTS={$five['NUMBER']} WHERE YEAR={$_POST['bind'][':YEAR']} AND TERM={$_POST['bind'][':TERM']} AND COURSENO+[GROUP]='{$_POST['bind'][':COURSENO']}'");

            if(!($one&&$two&&$three&&$four&&$five&&$six)){
                $this->md->rollback();
                exit($val['xh'].'删除失败');
            }else{
                $this->md->commit();
            }
        }

        exit('删除成功');
    }


    //todo:删除 one_six的内容的方法
    public function delete_six(){

        foreach($_POST['bind'] as $val){
            $this->md->startTrans();
            //todo:查询R32的数据
            $one=$this->md->sqlFind("select CONVERT(varchar(10),b.SELECTTIME,20) as TIME,b.* from(SELECT * FROM R32 WHERE YEAR={$_POST['YEAR']} AND TERM={$_POST['TERM']} AND COURSENO+[GROUP]='{$val['kh']}' AND STUDENTNO='{$val['xh']}')as b");

            //todo:插入到 R32DUMP
            $two=$this->md->sqlExecute($this->md->getSqlMap($this->base.'One_six_One_insertR32dump.SQL')
                ,array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':COURSENO'=>substr($val['kh'],0,7),':GP'=>substr($val['kh'],7),':STUDENTNO'=>$val['xh'],
                ':INPROGRAM'=>$one['INPROGRAM'],':CONFLICTS'=>$one['CONFLICTS'],':CF'=>$one['CONFIRM'],':APPROACH'=>$one['APPROACH'],':REPEAT'=>$one['REPEAT'],
                ':FEE'=>$one['FEE'],':COURSETYPE'=>$one['COURSETYPE'],':EXAMTYPE'=>$one['EXAMTYPE'],':SELECTTIME'=>$one['TIME'],':REASON'=>'违反选课规则，过多选择此类课程！'));
            //todo:删除R32
            $three=$this->md->sqlExecute("DELETE R32 WHERE YEAR={$_POST['YEAR']} AND TERM={$_POST['TERM']} AND COURSENO+[GROUP]='{$val['kh']}' AND STUDENTNO='{$val['xh']}'");
            //todo:查询出多少人
            $four=$this->md->sqlFind("SELECT COUNT(*) AS NUMBER FROM R32 WHERE YEAR={$_POST['YEAR']} AND TERM={$_POST['TERM']} AND COURSENO+[GROUP]='{$val['kh']}'");

            //todo:设置排课计划表的选课人数
            $six=$this->md->sqlExecute("UPDATE SCHEDULEPLAN SET ATTENDENTS={$four['NUMBER']} WHERE YEAR={$_POST['YEAR']} AND TERM={$_POST['TERM']} AND COURSENO+[GROUP]='{$val['kh']}'");
            if($one&&$two&$three&&$four&&$six){
                $this->md->commit();

            }else{
                dump($one);
                dump($two);
                dump($three);
                dump($four);

                dump($six);
                $this->md->rollback();
                exit('失败');
            }

        }
    }

    public function one_six(){
        $this->display();
    }



    //todo:同步课程总表
    public function tongbu(){
        $this->md->startTrans();
       $one= $this->md->sqlExecute($this->md->getSqlMap($this->base.'One_tongbu_one.SQL'),array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM']));
        $two=$this->md->sqlExecute($this->md->getSqlMap($this->base.'One_tongbu_two.SQL'),array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM']));
        $three=$this->md->sqlExecute($this->md->getSqlMap($this->base.'One_tongbu_three.SQL'),array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM']));
        if($one&&$three){
            $this->md->commit();
            exit('同步成功');
        }else{
            dump($one);
            dump($two);
            dump($three);
            exit('同步失败');
        }
    }

    
    //todo:添加学生提交的方法
    public function addStudent_add(){

        $str='';
        $panduan=true;
        $courseno=substr($_POST['COURSENO'],0,7);
        $group=substr($_POST['COURSENO'],7);

        //todo:课的锁定情况  实际人数  预计人数？
        $two=$this->md->sqlFind("SELECT LOCK,HALFLOCK,ATTENDENTS,ESTIMATE FROM SCHEDULEPLAN WHERE YEAR={$_POST['YEAR']} AND TERM={$_POST['TERM']} AND COURSENO='{$courseno}' AND [GROUP]='{$group}'");     //$this->md->sqFind("");
        if ($two){
	        if($two['ATTENDENTS']>=$two['ESTIMATE']){
	            exit("{$_POST['COURSENO']}选课人数超过人数限制，不能再选！");
	        }
        }else {
        	exit("查不到课程号为'{$_POST['COURSENO']}'的课程信息！");
        }
        
        foreach($_POST['bind'] as $val){
            $this->md->startTrans();

            $xuehao=trim($val['xh'],'%');

            //todo:查询课程的考试类型？ 必修课
            $one=$this->md->sqlFind("SELECT EXAMTYPE FROM COURSEPLAN WHERE YEAR={$_POST['YEAR']} AND TERM={$_POST['TERM']} AND COURSENO+[GROUP]='{$_POST['COURSENO']}'");
            //varDebug($one);// T

            //todo:课程的TIMELIST  
            $three=$this->md->sqlFind("SELECT MON,TUE,WES,THU,FRI,SAT,SUN FROM TIMELIST WHERE (YEAR={$_POST['YEAR']}) AND(TERM={$_POST['TERM']}) AND(WHO='{$courseno}') AND(TYPE='P') AND (PARA='{$group}')");

                //todo:查询学生号存在不存在？
            $four=$this->md->sqlFind("SELECT STUDENTNO FROM STUDENTS WHERE  STUDENTNO='$xuehao'");
            if(!$four){
                $this->md->rollback();
                exit('学生不存在');
            }

                //todo:找出学生的学院
            $five=$this->md->sqlFind("SELECT SCHOOL FROM STUDENTS WHERE STUDENTNO='{$four['STUDENTNO']}'");
			if (!$five){
			                $this->md->rollback();
			                exit('未找到该学生所在学院！');
			}

            //todo:找出学生的TIMELIST
            $six=$this->md->sqlFind("SELECT MON,TUE,WES,THU,FRI,SAT,SUN FROM TIMELIST WHERE (YEAR={$_POST['YEAR']}) AND(TERM={$_POST['TERM']}) AND(WHO='$xuehao') AND(TYPE='S') AND (PARA='')");
// 			var_dump($this->md->getLastSql());     
// 			varDebug($six);//2003 1/2 或2004 1/2

            $Timearr=array();           //todo:插入到TimeList 用到的数组
            foreach($three as $key=>$val){
                if($val&$six[$key]){
                    $str.="{$xuehao}所选的课程：{$_POST['CORUSENO']}和他已选的课程上课时间有冲突，建议他进入退选程序，适当退选课程或者办理相关免听手续";
                }
                $Timearr[$key]=$val|$three[$key];
            }


            
            //todo:去VIEWSTUDENTPLANCOURSE 找？
            $seven=$this->md->sqlFind("SELECT COURSENO,COURSETYPE,EXAMTYPE FROM VIEWSTUDENTPLANCOURSE WHERE STUDENTNO='{$four['STUDENTNO']}' AND COURSENO LIKE '{$courseno}%'");
            if(!$seven){
                //查不到记录就认为是选修课程
                $seven['COURSETYPE'] = 'E';
            }
            //todo:往R32中插入数据
            $bind1 = array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':COURSENO'=>$courseno,':GP'=>$group,':STUDENTNO'=>$four['STUDENTNO'],':INPROGRAM'=>0,':CONFLICTS'=>0,':REPEAT'=>0,
                ':FEE'=>1,':COURSETYPE'=>$seven['COURSETYPE'],':EXAMTYPE'=>$one['EXAMTYPE']);
            $eight=$this->md->sqlExecute($this->md->getSqlMap($this->base."One_one_addStudent_insertR32.SQL"),$bind1 );

            //todo:更新 SCHEDULEPLAN 表中的数据(更新选课人数)
            $nine=$this->md->sqlExecute("UPDATE SCHEDULEPLAN SET ATTENDENTS=ATTENDENTS+1 WHERE YEAR={$_POST['YEAR']} AND TERM={$_POST['TERM']} AND COURSENO='$courseno' AND [GROUP]='$group'");

            $ten=$this->md->sqlExecute("DELETE TIMELIST WHERE YEAR={$_POST['YEAR']} AND TERM={$_POST['TERM']} AND WHO='{$four['STUDENTNO']}' AND TYPE='S'");
            
            $bind2 = array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM'],':WHO'=>$four['STUDENTNO'],':TYPE'=>'S',':PARA'=>'',':MON'=>$this->nullToZero($Timearr['MON']),
                ':TUE'=>$this->nullToZero($Timearr['TUE']),':WES'=>$this->nullToZero($Timearr['WES']),':THU'=>$this->nullToZero($Timearr['THU']),':FRI'=>$this->nullToZero($Timearr['FRI']),
            		':SAT'=>$this->nullToZero($Timearr['SAT']),':SUN'=>$this->nullToZero($Timearr['SUN']));
            $eleven=$this->md->sqlExecute($this->md->getSqlMap($this->base.'One_one_addStudent_insertTime.SQL'),$bind2 );

            if(!$eight){
            	$str.=$val['xh']."在向数据库提交课程:{$_POST['COURSENO']}时发生异常，这门课程可能已经在他的选课单中了！<br />".$this->md->getDbError();
            }
            if(!$nine){
                $str .= '更新选课人数操作失败!<br />';
            }
            if(!$eleven){
                $str.= '插入资源时间表出错<br />';
            }

            if($eight&&$nine&&$eleven){
                $this->md->commit();
                continue;
            }

            $panduan=false;
            $this->md->rollback();

        }
        if($panduan){
            exit('学生选课添加成功,请刷新页面');
        }

        if(trim($str) == ''){
            $str = '学生选课添加失败！';
        }

        echo $str;


    }

	private function nullToZero($param){
		return isset($param)?$param:0;
	}
    

    //todo:选课终止
    public function Two_ElectiveStop(){
          $this->display();
    }




    //todo:选课开放
    public function Three_ElectiveStart(){
        if($this->_hasJson){
//            $this->md->startTrans();
//            $courseList=$this->md->sqlQuery($this->md->getSqlMap($this->base.'Three_two_select.SQL'),array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM']));

//            $num=count($courseList);

            //不去更新预计人数
//            for($i=0;$i<$num;$i++){
//               $bool=$this->md->sqlExecute("UPDATE SCHEDULEPLAN SET ESTIMATE={$courseList[$i]['SEATS']} WHERE YEAR={$_POST['YEAR']} AND TERM={$_POST['TERM']} AND COURSENO+[GROUP]='{$courseList[$i]['COURSENOGROUP']}' AND SEATSLOCK=0 AND ESTIMATE>0");
//            }
            $rst= $this->md->sqlExecute("UPDATE SCHEDULEPLAN SET HALFLOCK=0 WHERE YEAR=:year AND TERM=:term and lock = 1;",array(':year'=>$_POST['YEAR'],':term'=>$_POST['TERM']));
            exit($rst?'success':'failure');
        }
        $this->assign('quanxian',trim($_SESSION['S_USER_INFO']['ROLES']));
        $this->display();
    }

    //todo:选课初始化
    public function Three_chushihua(){
        //todo:删除R32的数据
        $bool=$this->md->sqlExecute("DELETE R32 WHERE YEAR={$_POST['YEAR']} AND TERM={$_POST['TERM']}");
        //todo:修改SCHEDULEPLAN的状态
        $bool2=$this->md->sqlExecute("UPDATE SCHEDULEPLAN SET ATTENDENTS=0 WHERE YEAR={$_POST['YEAR']} AND TERM={$_POST['TERM']}");

        dump($bool);
        dump($bool2);
    }













    public function getTime($arr){
        $ar2=array();
        foreach($arr as $val){
            $ar2[$val['NAME']]=$val;
        }
        return $ar2;
    }

    //todo:选课统计
    public function Five_count(){
       $this->xiala('schools','schools');
        $this->display();
    }


    //todo:添加学生的列表
    public function liebiao(){

     //   'CourseManager/One_one_title_Info.SQL','bind':{':YEAR':year,':TERM':term,':COURSENO':COURSENO}
       $countent=$this->md->Sqlfind($this->md->getSqlMap('CourseManager/One_one_title_Info.SQL'),array(':YEAR'=>$_GET['year'],':TERM'=>$_GET['term'],':COURSENO'=>$_GET['courseno']));
        $this->assign('info',$countent);
        $this->assign('year',$_GET['year']);
        $this->assign('term',$_GET['term']);
        $this->assign('coursetype',$_GET['coursetype']);
        $this->assign('courseno',$_GET['courseno']);
        $this->display();
    }

    public function liebiao2(){
        $classno=$_GET['classno']?$_GET['classno']:'';
        $countent=$this->md->Sqlfind($this->md->getSqlMap('CourseManager/One_one_title_Info.SQL'),array(':YEAR'=>$_GET['year'],':TERM'=>$_GET['term'],':COURSENO'=>$_GET['courseno']));
        $this->assign('info',$countent);
        $this->assign('year',$_GET['year']);
        $this->assign('term',$_GET['term']);
        $this->assign('coursetype',$_GET['coursetype']);
        $this->assign('courseno',$_GET['courseno']);

        $this->assign('classno',$classno);
        $this->display();

    }

    //todo:给学生选必修课和模块课
    public function bixiu(){

        $int=$this->md->sqlExecute("insert into r32(year,term,courseno,[group],studentno,coursetype,examtype,selecttime)
select distinct courseplan.year,courseplan.term,courseplan.courseno,courseplan.[group],students.studentno,courseplan.coursetype,courseplan.examtype ,getdate() s from students inner join courseplan
on courseplan.classno=students.classno
where courseplan.coursetype in ('M','T') and  courseplan.year=:ytwo and courseplan.term=:ttwo  and students.status='A'
and not exists (select * from r32 where r32.year=:year and r32.term=:term and r32.courseno=courseplan.courseno and r32.[group]=courseplan.[group] and students.studentno=r32.studentno)",
        array(':ytwo'=>$_POST['year'],':ttwo'=>$_POST['term'],':year'=>$_POST['year'],':term'=>$_POST['term']));
        if($int){
            var_dump($int);
            exit('同步成功');
        }else{
            var_dump($int);
            exit('没有查询到数据');
        }

    }














}

//todo:一天有几节课
function countOneDay($v1, $v2){
    if(!$v1) $v1 = array();
    if($v2['UNIT']=="1") $v1[]=$v2["NAME"];
    return $v1;
}

