<?php
class CourseAction extends RightAction{
    /*
     * 新建课程的方法
     */
    public function index(){
        $this->display();
    }


    /*
     * 添加时候用到的 前台验证
     */
    function numberyz(){
            $shuju=M('courses');
        if(strlen($_POST['COURSENO'])!=7){
            exit('sev');
        }
            $one=$shuju->where($_POST)->find();
            if($one){
                echo 'false';
            }else{
                echo 'aa';
            }
    }

    /*
     * todo:新建课程的方法。
     */
    function newcourse(){
        //todo:课程Volist
        $this->xiala('schools','school');
        //todo:课程类别Volist
        $this->xiala('coursetypeoptions','coursetype');
        //todo:课程类型数据Volist    (纯理论-纯实践-理论实践)
        $this->xiala('coursetypeoptions2','coursetype2');
        //当前登录用户所在学院
        $mdl=new SqlsrvModel();
        $school = $mdl->sqlFind("SELECT * FROM SCHOOLS WHERE SCHOOL = (SELECT T.SCHOOL FROM TEACHERS T WHERE T.TEACHERNO=:TEACHERNO)",array(":TEACHERNO"=>$_SESSION["S_USER_INFO"]["TEACHERNO"]));
        $this->assign("userSchool",$school);
        $this->display();

    }

    /*
     * 对课程进行插入操作的方法
     */
    function courseyz(){
     $shuju=M('courses');
        foreach($_POST AS $key=>$value){        //对旁边的空格进行过滤用的
            $arr[$key]=trim($value);
        }
       $arr['课程介绍']=$arr['INTRODUCE'];
        unset($arr['INTRODUCE']);
         $num=$shuju->add($arr);
        if($num){
            echo 'true';
        }else{
            echo 'false';
        }

    }

    /**
     * 检索课程
     */
    function scourse(){
        if($this->_hasJson){
            $shuju=new SqlsrvModel();
            $count=$shuju->getSqlMap('course/teacher/coursecount.SQL');              //统计条数的sql
             $sql=$shuju->getSqlMap('course/teacher/courseselect.SQL');               //查询课程的sql语句
           $bind=array(':SCHOOL'=>doWithBindStr($_POST['SCHOOL']),':COURSENO'=>doWithBindStr($_POST['COURSENO']) ,':OLDCOURSENO'=>  doWithBindStr($_POST['OLDCOURSENO']) ,':COURSENAME'=>  doWithBindStr($_POST['COURSENAME']) ,':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize);
           $bind2=array(':SCHOOL'=>  doWithBindStr($_POST['SCHOOL']) ,':COURSENO'=>  doWithBindStr($_POST['COURSENO']) ,':OLDCOURSENO'=>  doWithBindStr($_POST['OLDCOURSENO']) ,':COURSENAME'=>  doWithBindStr($_POST['COURSENAME']) );
            $one=$shuju->sqlQuery($count,$bind2);
            if($arr['total']=$one[0]['']){
                   $arr['rows']=$shuju->sqlQuery($sql,$bind);
            }else{
                   $arr['rows']=array();
            }
            $this->ajaxReturn($arr,'JSON');
            exit;
        }
        //todo:课程Volist
        $this->xiala('schools','school');
        //todo:课程类别Volist
        $this->xiala('coursetypeoptions','coursetype');
        //todo:课程类型数据Volist    (纯理论-纯实践-理论实践)
        $this->xiala('coursetypeoptions2','coursetype2');
        $this->display();
    }

    /*
     * 删除课程的方法
     */
    function decourse(){
        $shuju=new SqlsrvModel();
       // $sql=$shuju->getSqlMap('./courseDelete.SQL');                //删除课程的方法
        if(count($_POST['in'])==1&&$_POST['in'][0]==""){
            exit('false');
        }else{
            $v="";
            foreach($_POST['in'] as $val){
                $val=str_replace($val,"'$val'",$val);
                $v.=$val.',';                                         //拼接条件
            }
            $v=rtrim($v,',');
         //   $bind=array(':jihe'=>"(003J03A)");
         $sql=  "delete from COURSES where COURSENO in ($v)";
            $row=$shuju->sqlExecute($sql);
                if($row){
                    echo 'yes';
                }else{

                    echo 'false';
                }
        }

    }

    /*
     * 查看详细信息时候的方法
     */
    function infoaa(){
        //COURSETYPEOPTIONS2.[VALUE] AS TYPE2NAME,COURSETYPEOPTIONS.[VALUE] AS TYPENAME,,isnull(temp.amount,0) as sycs
        $shuju=new SqlsrvModel();
        $sql=$shuju->getSqlMap('course/teacher/infoaa.SQL');
        $bind=array(':idone'=>$_POST['id'],':idtwo'=>$_POST['id']);

    $one=$shuju->sqlQuery($sql,$bind);

        echo json_encode($one[0]);
    }

    /*
     * 修改数据时候的方法
     */
   function courseup(){
        $shuju=new SqlsrvModel();

       $user_teacherno=$_SESSION["S_USER_INFO"]["TEACHERNO"];
       $user_school = $shuju->sqlFind("SELECT T.SCHOOL FROM TEACHERS T WHERE T.TEACHERNO='$user_teacherno'");
       $course_school = $shuju->sqlFind("select SCHOOL from COURSES where COURSENO = :courseno;",array(":courseno"=>$_POST['COURSENO']));
       if(($course_school["SCHOOL"] != $user_school["SCHOOL"]) && ($user_school["SCHOOL"]!="A1")){
           exit('nopermission');
       }

       //-- 查询之前是什么课程 --//
       $sql = 'SELECT SCHOOL from COURSES WHERE COURSENO = :courseno';
       $rtn = $shuju->sqlFind($sql,array(':courseno'=>trim($_POST['COURSENO'])));
       if($rtn){
           if(trim($rtn['SCHOOL']) != trim($_POST['SCHOOL'])){
               //正试图修改学院
               if($user_school['SCHOOL']!='A1'){
                   exit('npeditschool');
               }
           }
       }else{
           exit('qsfailed'.$shuju->getDbError());
       }

       $bind = array(
            ':total' => $_POST['TOTAL'],
            ':coursename' => $_POST['COURSENAME'],
            ':credits' => $_POST['CREDITS'],
            ':hours' => $_POST['HOURS'],
            ':experiments' => $_POST['EXPERIMENTS'],
            ':computing' => $_POST['COMPUTING'],
            ':khours' => $_POST['KHOURS'],
            ':shours' => $_POST['SHOURS'],
            ':zhours' => $_POST['ZHOURS'],
            ':lhours' => $_POST['LHOURS'],
            ':type' => $_POST['TYPE'],
            ':syllabus' => $_POST['SYLLABUS'],
            ':rem' => $_POST['REM'],
            ':introduce' => $_POST['INTRODUCE'],
            ':school'=>$_POST['SCHOOL'],
            ':quarter' => $_POST['QUARTER'],
            ':tp' => $_POST['TYPE2'],
            ':limit' => $_POST['Limit'],
            ':courseno' => $_POST['COURSENO'],
       );
       //,SCHOOL='{$_POST['SCHOOL']}'  开课学院不可以更改
       $sql = "UPDATE COURSES
SET TOTAL=:total,COURSENAME=:coursename,CREDITS=:credits,
HOURS=:hours,EXPERIMENTS=:experiments,COMPUTING=:computing,KHOURS=:khours,
SHOURS=:shours,ZHOURS=:zhours,LHOURS=:lhours,[TYPE]=:type,SYLLABUS=:syllabus,
REM=:rem,OLDCOURSENO='无',[课程介绍]=:introduce,SCHOOL=:school,quarter=:quarter,TYPE2=:tp,Limit=:limit
WHERE COURSENO=:courseno";
//            $sql="UPDATE COURSES
//	            SET TOTAL={$_POST['TOTAL']},COURSENAME='{$_POST['COURSENAME']}',CREDITS='{$_POST['CREDITS']}',
//	            HOURS='{$_POST['HOURS']}',EXPERIMENTS='{$_POST['EXPERIMENTS']}',COMPUTING='{$_POST['COMPUTING']}',KHOURS='{$_POST['KHOURS']}',
//	            SHOURS='{$_POST['SHOURS']}',ZHOURS='{$_POST['ZHOURS']}',LHOURS='{$_POST['LHOURS']}',TYPE='{$_POST['TYPE']}',SYLLABUS='{$_POST['SYLLABUS']}',
//	            REM='{$_POST['REM']}',OLDCOURSENO='无',课程介绍='{$_POST['INTRODUCE']}',quarter='{$_POST['QUARTER']}',TYPE2='{$_POST['TYPE2']}',Limit={$_POST['Limit']}
//	            WHERE COURSENO='{$_POST['COURSENO']}'";
            $boo=$shuju->sqlExecute($sql,$bind);
//       echo '<pre>';
//       var_dump($boo ,$sql,$bind,$shuju->getDbError(),$shuju->getLastSql());
            echo $boo?'true':'Error:'.$shuju->getDbError();
           /*
             function courseup(){
               $shuju=new SqlsrvModel();
               $sql=$shuju->getSqlMap('./courseupdate.SQL');
               $bind=array(':COURSENO'=>'0013K11',':TOTAL'=>$_POST['TOTAL'],':SCHOOL'=>$_POST['SCHOOL'],':COURSENAME'=>$_POST['COURSENAME'],':CREDITS'=>$_POST['CREDITS'],':HOURS'=>$_POST['HOURS'],':EXPERIMENTS'=>$_POST['EXPERIMENTS'],':COMPUTING'=>$_POST['COMPUTING'],':KHOURS'=>$_POST['KHOURS'],':SHOURS'=>$_POST['SHOURS'],':ZHOURS'=>$_POST['ZHOURS'],':LHOURS'=>$_POST['LHOURS'],':TYPE'=>$_POST['TYPE'],':SYLLABUS'=>$_POST['SYLLABUS'],':REM'=>$_POST['REM'],':OLDCOURSENO'=>'无',':quarter'=>$_POST['QUARTER'],':ff3'=>$_POST['TYPE2'],':Limit'=>$_POST['Limit']);

               $boo=$shuju->sqlQuery($sql,$bind);
               var_dump($boo);
               echo true;
           }*/

    }

    /*
     * 等价课程
     */
    public function eqcourse(){
        if($this->_hasJson){
            $shuju=new SqlsrvModel();
           $sql=$shuju->getSqlMap('course/teacher/eqcourseselect.SQL');                 //查询
            $count=$shuju->getSqlMap('course/teacher/eqcoursecount.SQL');                //统计
            $bind=array(':COURSENO'=>doWithBindStr($_POST['COURSENO']),':EQNO'=>doWithBindStr($_POST['EQNO']),':PROGRAMNO'=>doWithBindStr($_POST['PROGRAMNO']),':strat'=>$this->_pageDataIndex,':end2'=>$this->_pageDataIndex+$this->_pageSize);
            $bind2=array(':COURSENO'=>doWithBindStr($_POST['COURSENO']),':EQNO'=>doWithBindStr($_POST['EQNO']),':PROGRAMNO'=>doWithBindStr($_POST['PROGRAMNO']));
            $ct=$shuju->sqlQuery($count,$bind2);
            if($arr['total']=$ct[0]['']){
                $arr['rows']=$shuju->sqlQuery($sql,$bind);
            }else{
                $arr['rows']=array();
            }
            $this->ajaxReturn($arr,'JSON');
            exit;
        }
        $this->display();

    }

    /*
     * 等价课程的添加
     */
    public function eqcourseadd(){
        $shuju=M('r33');                //等价关系表
        $shuju2=M('courses');
        $shuju3=M('programs');         //教学计划表
        $courseno['COURSENO']=$_POST['COURSENO'];                                //判断课号
        $one=$shuju2->where($courseno)->select();
        $eqno['EQNO']=$_POST['EQNO'];                                             //判断等价课号
        $two=$shuju2->where($eqno)->find();
        $programno['PROGRAMNO']=$_POST['PROGRAMNO'];                            //判断教学计划号
        $three=$shuju3->where($programno)->find();


        if($one==null){
            exit('1');               //      课程号不存在
        }

        if($two==null){
            exit('2');              //等价课程号不存在
        }

        if($three==null){
            exit('3');              //教学计划号不存在
        }

       $pd=$shuju->add($_POST);
            if($pd){
                echo 'yes';
            }else{
                echo 'no';
            }
    }

    /*
     * 等价课程的删除
     */
    public function eqcoursede(){
       $shuju=new SqlsrvModel();
       $sql= $shuju->getSqlMap("course/teacher/eqcoursedelete.SQL");
       $bind=array(":COURSENO"=>$_POST['COURSENO'],':EQNO'=>$_POST['EQNO'],':PROGRAMNO'=>$_POST['PROGRAMNO']);
        $boo=$shuju->sqlQuery($sql,$bind);
        echo 'yes';
    }
}
?>