<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 13-12-13
 * Time: 下午3:20
 */
class MajorAction extends RightAction{
    /*
     * 专业管理首页
     */
    private $mdl;

    public function index()
    {
        $this->display();
    }

    public function __construct(){
        parent::__construct();
        $this->mdl=new SqlsrvModel();
        $this->assign('quanxian',trim($_SESSION['S_USER_INFO']['ROLES']));
    }
    /*
     *检索专业的页面
     */


    /*
    * 专业条目
    */
    public function majors()
    {
        if($this->_hasJson)
        {
            $shuju = M("SqlsrvModel:");
            $sql=$shuju->getSqlMap('major/majorselect.SQL');
            $count=$shuju->getSqlMap('major/majorcount.SQL');
            $_POST['MAJORNO']=trim($_POST['MAJORNO']);
            $_POST['YEARS']=trim($_POST['YEARS']);
            $_POST['DEGREE']=trim($_POST['DEGREE']);
            $_POST['SCHOOL']=trim($_POST['SCHOOL']);
            $bind=array(':MAJORNO'=>doWithBindStr($_POST['MAJORNO']),':YEARS'=>$_POST['YEARS'],':DEGREE'=>doWithBindStr($_POST['DEGREE']),':SCHOOL'=>doWithBindStr($_POST['SCHOOL']),':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize);
            $bind2=array(':MAJORNO'=>doWithBindStr($_POST['MAJORNO']),':YEARS'=>$_POST['YEARS'],':DEGREE'=>doWithBindStr($_POST['DEGREE']),':SCHOOL'=>doWithBindStr($_POST['SCHOOL']));

            $one=$shuju->sqlQuery($count,$bind2);
            if($arr['total']=$one[0][''])
            {
                $arr['rows']=$shuju->sqlQuery($sql,$bind);
            }
            else
            {
                $arr['rows']=array();
            }
            $this->ajaxReturn($arr,'JSON');
            exit;
        }

        $teacherschool= $this->mdl->sqlFind("select TEACHERS.SCHOOL from TEACHERS,USERS where USERS.TEACHERNO=TEACHERS.TEACHERNO AND USERS.USERNAME='{$_SESSION['S_USER_NAME']}'");

        $this->assign('teacherschool',$teacherschool['SCHOOL']);
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

        //var_dump($sjson);

        $shuju=M('majorcode');
        $major=$shuju->select();                                // 专业列表
        $sjson3=array();
        foreach($major as $val){
            $sjson2['text']=trim($val['NAME']);
            $sjson2['value']=$val['CODE'];                    // 把专业数据转成json格式给前台的combobox使用
            array_push($sjson3,$sjson2);
        }
        $sjson3=json_encode($sjson3);
        $this->assign('sjson3',$sjson3);
        $this->assign('major',$major);
        //var_dump($major);

        $shuju=M('degreeoptions');                            // 学位信息
        $degree=$shuju->select();
        $sjson4=array();
        foreach($degree as $val)
        {
            $sjson2['text']=trim($val['NAME']);
            $sjson2['value']=$val['CODE'];                    // 把学位数据转成json格式给前台的combobox使用
            array_push($sjson4,$sjson2);
        }
        $sjson4=json_encode($sjson4);
        $this->assign('sjson4',$sjson4);
        $this->assign('degree',$degree);


        $sjson5=array();
        $sjson2['text']="二年制";
        $sjson2['value']="2";                    // 把学位数据转成json格式给前台的combobox使用
        array_push($sjson5,$sjson2);
        $sjson2['text']="三年制";
        $sjson2['value']="3";
        array_push($sjson5,$sjson2);
        $sjson2['text']="四年制";
        $sjson2['value']="4";
        array_push($sjson5,$sjson2);
        $sjson2['text']="五年制";
        $sjson2['value']="5";
        array_push($sjson5,$sjson2);
        $sjson5=json_encode($sjson5);
        $this->assign('sjson5',$sjson5);
        $this->display();
    }

    /*
     * 对专业进行插入操作的方法
     */
    function insertma(){
        $shuju=new SqlsrvModel();
        foreach($_POST AS $key=>$value)
        {        //对旁边的空格进行过滤用的
            $arr[$key]=trim($value);
        }
        $arr['BRANCHNAME']=substr($arr['MAJORNAME'],0,2);
        $sql="INSERT INTO MAJORS(MAJORNO,YEARS,BRANCH,SCHOOL,DEGREE,REM) VALUES('".$arr['MAJORNAME']."','".$arr['YEARS']."','".$arr['BRANCHNAME']."','".$arr['SCHOOLNAME']."','".$arr['DEGREENAME']."','".$arr['REM']."')";
        $row=$shuju->sqlExecute($sql);
        if($row) echo 'true';
        else echo 'false';
    }



    /*
 * 修改数据时候的方法
 */
    function updatema()
    {
        $shuju=new SqlsrvModel();
        if(is_numeric($_POST['YEARS'])) $_POST['YEARS']=$_POST['YEARS'];
        else $_POST['YEARS']="";
        if(is_numeric($_POST['SCHOOLNAME'])) $_POST['SCHOOLNAME']=$_POST['SCHOOLNAME'];
        else $_POST['SCHOOLNAME']="";
        if(strlen($_POST['DEGREENAME'])<2) $_POST['DEGREENAME']=$_POST['DEGREENAME'];
        else $_POST['DEGREENAME']="";
        foreach($_POST AS $key=>$value)
        {        //对旁边的空格进行过滤用的
            $arr[$key]=trim($value);
        }
        $sql="UPDATE MAJORS SET REM='".$arr['REM']."'";
        if(trim($_POST['YEARS'])!="") $sql.=",YEARS='".$arr['YEARS']."'";
        if(trim($_POST['SCHOOLNAME'])!="") $sql.=",SCHOOL='".$arr['SCHOOLNAME']."'";
        if(trim($_POST['DEGREENAME'])!="") $sql.=",DEGREE='".$arr['DEGREENAME']."'";
        $sql.=" WHERE ROWID='".$arr['ROWID']."'";
        //exit;
        $boo=$shuju->sqlExecute($sql);
        if($boo===false) echo 'false';
        else echo 'true';
    }

    public function deletema()
    {
        $shuju=new SqlsrvModel();
        $data=array();
        $newids='';
        foreach($_POST['in'] as $val){
            $newids.="'".$val."',";
        }
        $newids=rtrim($newids,',');
        $sql="DELETE FROM MAJORS WHERE ROWID in ($newids)";
        $row=$shuju->sqlExecute($sql);
        if($row) echo 'true';
        else echo false;
    }

}