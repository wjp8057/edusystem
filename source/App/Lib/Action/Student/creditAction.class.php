<?php
/**
 * 选课、退课模块
 * User: educk
 * Date: 13-12-25
 * Time: 下午2:59
 */
class creditAction extends RightAction {
    private $md;
    private $_array = array(1=>"MON","TUE","WES","THU","FRI","SAT","SUN");

    public function __construct(){
        parent::__construct();
        $this->md = M("SqlsrvModel:");
    }

    public function skill(){
        if(isset($_GET['tag']) && ($_GET['tag'] == 'xuliehao')){
            $sql = 'select top 1 xuliehao from Creditxuliehao where year=:year and term=:term order by xuliehao desc';
            $bind = array(':year'=>$_POST['year'],':term'=>$_POST['term']);
            $res = $this->md->sqlFind($sql,$bind);
            exit(json_encode($res));
        }
        if(isset($_GET['tag']) && ($_GET['tag'] == 'checkrepeat')){
            $sql = 'select count(*) as ROWS from CreditSinglefirm where Studentno=:studentno and certficatetime=:certficatetime and projectname=:projectname and credittype = :credittype';
            $bind = array(':studentno'=>$_POST['studentno'],':certficatetime'=>$_POST['certficatetime'],':projectname'=>$_POST['projectname'],':credittype'=>$_POST['credittype']);
            $res = $this->md->sqlFind($sql,$bind);
            exit(json_encode($res));
        }
        if(isset($_GET['tag']) && ($_GET['tag'] == 'add')){
            $this->md->startTrans();
            $sql1 = 'insert into CreditSinglefirm ([year],term,firmno,Studentno,projectname,credittype,certficatetime,credit,description,submitter,schoolname,recordmark,schoolview,deanview)
            			values(:year,:term,:firmno,:Studentno,:projectname,:credittype,:certficatetime,:credit,:description,:submitter,:schoolname,:recordmark,:schoolview,:deanview);';
            $bind1 = array(':year'=>$_POST['year'],
            ':term'=>$_POST['term'],
            ':firmno'=>$_POST['firmno'],
            ':Studentno'=>$_POST['Studentno'],
            ':projectname'=>$_POST['projectname'],
            ':credittype'=>$_POST['credittype'],
            ':certficatetime'=>$_POST['certficatetime'],
            ':credit'=>$_POST['credit'],
            ':description'=>$_POST['description'],
            ':submitter'=>$_POST['submitter'],
            ':schoolname'=>$_POST['schoolname'],
            ':recordmark'=>$_POST['recordmark'],
            ':schoolview'=>$_POST['schoolview'],
            ':deanview'=>$_POST['deanview']);
            $res1 = $this->md->sqlExecute($sql1,$bind1);
           /* $sql2 = 'insert into Creditxuliehao([year],[term],xuliehao) values(:y2,:t2,:xuliehao);';
            $bind2 = array(  ':y2'=>$_POST['y2'],
                ':t2'=>$_POST['t2'],
                ':xuliehao'=>$_POST['xuliehao']);
           $res2 = $this->md->sqlExecute($sql2,$bind2);*/
            if($res1>0){
                $this->md->commit();
                exit("success");
            }else{
                $this->md->rollback();
//                var_dump($this->md->getDbError(),$bind1);
                exit('failure');
            }
        }

     
        $this->assign('yearterm',$this->md->sqlFind($this->md->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"R")));
        //todo:判断是否在时间范围内
        $this->assign('is_time',$this->is_time());
        //todo:查询证书列表
        $this->assign('Project',$this->md->sqlQuery("select cert_id,credit,cert_name from CreditCertficate where status=1 "));
        $this->assign('username',$_SESSION['S_USER_NAME']);     //todo:用户账号
        $this->assign('name',$_SESSION['S_USER_INFO']['NAME']); //todo:用户姓名
        $this->assign('schoolname',$_SESSION['S_USER_INFO']['SCHOOL']); //todo:用户所在学院
        $this->assign('creditYearTerm',$this->getCreditTime());

        $this->display();
    }

  /*  public function quality(){
        $this->assign('yearterm',$this->md->sqlFind($this->md->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"R")));
        //todo:判断是否在时间范围内
//        $this->assign('is_time',$this->is_time());
        $this->assign('is_time','true');

        //todo:查询证书列表
        $this->assign('Project',$this->md->sqlQuery("select cert_id,credit,cert_name from Credit_Certficate where status=1 and type='S'"));


        $this->assign('username',$_SESSION['S_USER_NAME']);     //todo:用户账号
        $this->assign('name',$_SESSION['S_USER_INFO']['NAME']); //todo:用户姓名
        $this->assign('schoolname',$_SESSION['S_USER_INFO']['SCHOOL']); //todo:用户所在学院
        $this->display();
    }*/

    public function status(){
        $this->assign('username',$_SESSION['S_USER_NAME']);     //todo:用户账号
        $this->assign('name',$_SESSION['S_USER_INFO']['NAME']); //todo:用户姓名
        $this->assign('schoolname',$_SESSION['S_USER_INFO']['SCHOOL']); //todo:用户所在学院
        $this->display();
    }

    public function lookprocess(){
        if($this->_hasJson){
            $sql = 'select STUDENTS.NAME,STUDENTS.STUDENTNO,CLASSES.CLASSNAME,c.description,c.applydate_id,c.projectname,c.projecttype,c.credittype,c.credit,
convert(varchar(10),c.certficatetime,20)as certficatetime,c.description,c.schoolview,c.deanview,convert(varchar(10),c.schoolsubtime,20)as schoolsubtime,
convert(varchar(10),c.deansubtime,20) as deansubtime,c.year,c.term from STUDENTS LEFT OUTER JOIN CLASSES on STUDENTS.CLASSNO=CLASSES.CLASSNO
inner JOIN CreditSinglefirm AS c on STUDENTS.STUDENTNO=c.Studentno where STUDENTS.STUDENTNO = :studentno';
            $bind = array(':studentno'=>$_POST['studentno']);
            $res = $this->md->sqlQuery($sql,$bind);
            $this->ajaxReturn($res,'JSON');
            exit;
        }
        $this->assign('username',$_SESSION['S_USER_NAME']);
        $this->display();
    }

    //todo:判断是否在时间里
    public function is_time(){
        $timearr=$this->md->sqlQuery('select year,term,convert(varchar(20),begintime,20) as begintime,convert(varchar(20),endtime,20) as endtime from Creditapplydate where status=1');
        if(count($timearr)==1){
            $start=strtotime($timearr[0]['begintime']);
            $end=strtotime($timearr[0]['endtime']);
           $this->assign('begintime',date('Y/m/d',$start));
            $this->assign('endtime',date('Y/m/d',$end));
            if(time()<$start||time()>$end)
                return 'false';// exit('false');
        }
        return 'true';    // exit('true');

    }

    public function getCreditTime(){
        return $this->md->sqlFind('select year,term,convert(varchar(20),begintime,20) as begintime,convert(varchar(20),endtime,20) as endtime from Creditapplydate where status=1');
    }
}