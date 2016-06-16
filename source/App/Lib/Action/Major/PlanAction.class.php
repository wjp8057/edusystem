<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 13-12-30
 * Time: 下午3:40
 */
class PlanAction extends RightAction{
    /*
     * 专业培养计划首页
     */
    private $mdl;
    private $sqlpath;
    private $arr;           //正常数组
    private $paixu;         //符合sqlMAP  sql的数组顺序

    public function __construct(){
        parent::__construct();
        $this->mdl=new SqlsrvModel();
        $this->sqlpath=$_POST['sqlpath'];
        $this->arr=$_POST['arr'];
        $this->paixu=$this->paixu($_POST['arr']);
        $this->assign('quanxian',trim($_SESSION['S_USER_INFO']['ROLES']));
    }




    public function selectmajors(){

        $str='<select name="type2">';
        $shuju=M('教学计划类别代码');
        $one=$shuju->select();

        foreach($one as $val){
            $str.="<option value={$val['name']}>{$val['value']}</option>";
        }
        $str.='</select>';
        $this->assign('codett',$str);
        $this->assign('codett2',$one);
        $this->xiala('degreeoptions','degreeoptions');
        $this->xiala('majorcode','majorcode');
        $this->xiala('教学计划类别代码','codetype');
        $this->xiala('schools','schools');              //学院
        $this->display();
    }


    //todo:==对数组进行顺序操作，便于SQLMAP能使用
    public function paixu($arr,$fuhao=false){
        foreach($_POST['paixu'] as $key=>$val){
            $fuhao?$key2=substr($key,1):$key2=$key;     //添加的时候需要用
            $_POST['paixu'][$key]=$arr[$key2];
        }
        return $_POST['paixu'];
    }


    public function index()
    {

        $this->display();
    }

    /*
    * 专业培养计划
    */
    public function plans()
    {
        if($this->_hasJson)
        {
            $shuju = M("SqlsrvModel:");
            $sql=$shuju->getSqlMap('major/majorplanqueryresult.sql');
            $count=$shuju->getSqlMap('major/majorplancount.sql');
            $_POST['MAJOR']=trim($_POST['MAJOR']);
            $_POST['YEARS']=trim($_POST['YEARS']);
            $_POST['GRADE']=trim($_POST['GRADE']);
            $_POST['SCHOOL']=trim($_POST['SCHOOL']);
            $bind=array(':SCHOOL'=>doWithBindStr($_POST['SCHOOL']),':GRADE'=>$_POST['GRADE'],':YEARS'=>doWithBindStr($_POST['YEARS']),':MAJOR'=>doWithBindStr($_POST['MAJOR']),':start'=>$this->_pageDataIndex+1,':end'=>$this->_pageDataIndex+$this->_pageSize);
            $bind2=array(':SCHOOL'=>doWithBindStr($_POST['SCHOOL']),':GRADE'=>$_POST['GRADE'],':YEARS'=>doWithBindStr($_POST['YEARS']),':MAJOR'=>doWithBindStr($_POST['MAJOR']));
            $one=$shuju->sqlQuery($count,$bind2);
            if($arr['total']=$one[0]['ROWSID'])
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
        $this->xiala('majorcode','majorcode');
        $this->xiala('教学计划类别代码','codetype');
        $this->xiala('schools','schools');              //学院


        //var_dump($sjson);

        $str='<select name="type2">';
        $shuju=M('教学计划类别代码');
        $one=$shuju->select();

        foreach($one as $val){
            $str.="<option value={$val['name']}>{$val['value']}</option>";
        }
        $str.='</select>';
        $this->assign('codett',$str);
        $this->assign('codett2',$one);
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
//TODO:---------------------------------------CONMOBOBOX---------------------------------------------------------
        $this->combobox('schools','school','NAME','SCHOOL');
        $this->combobox('majorcode','sjson3','NAME','CODE');
        $this->combobox('degreeoptions','sjson4','NAME','CODE');
       $this->combobox('教学计划类别代码','codetype','value','name');
        $this->display();
    }


    public function combobox($tablename,$bianliangname,$TEXT,$VALUE){
        $shuju=M($tablename);
        $data=$shuju->select();                                // 专业列表
        $sjson3=array();
        foreach($data as $val){
            $sjson2['text']=trim($val[$TEXT]);
            $sjson2['value']=$val[$VALUE];                    // 把专业数据转成json格式给前台的combobox使用
            array_push($sjson3,$sjson2);
        }
        $sjson3=json_encode($sjson3);
        $this->assign($bianliangname,$sjson3);
       // $this->assign('major',$major);
    }
    /*
     * 对专业培养计划进行插入操作的方法
     */
    function insertpl()
    {
        $shuju=new SqlsrvModel();
        foreach($_POST AS $key=>$value)
        {   //对旁边的空格进行过滤用的
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
    function updatepl()
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
        $boo=$shuju->sqlQuery($sql);
        if($boo===false) echo 'false';
        else echo true;
    }

    /*
     * 删除数据时候的方法
    */
    public function deletepl()
    {
        $shuju=new SqlsrvModel();
        $data=array();
        $newids='';
        foreach($_POST['in'] as $val){
            $newids.="'".$val."',";
        }
        $newids=rtrim($newids,',');
        $sql="DELETE FROM MAJORPLAN WHERE ROWID in ($newids)";
        $row=$shuju->sqlExecute($sql);
        if($row) echo true;
        else echo false;
    }



    //todo:先修页面
    public function xianxiu(){
        $this->assign('courseno',$_GET['courseno']);
        $this->display();
    }

   public function newplans(){

       $teacherschool= $this->mdl->sqlFind("select TEACHERS.SCHOOL from TEACHERS,USERS where USERS.TEACHERNO=TEACHERS.TEACHERNO AND USERS.USERNAME='{$_SESSION['S_USER_NAME']}'");
       $CT=$this->mdl->sqlFind($this->mdl->getSqlMap('major/newplans.SQL'),array(':ROWID'=>$_GET['rowid']));
           $this->assign('content',$CT);

           $this->assign('teacherno',$teacherschool['SCHOOL']);
       $this->display();
   }


    /**
     * 毕业审核
     */
    public function shenhe(){
        if(isset($_GET['tag']) && $_GET['tag'] == 'getlist') {

            $jsonret = array('data' => array());
            $programstype = array(1 => '全选全通过', '全选部分通过', '部分选部分通过', '公共选修课');
            $studentno =  session('studentno');
            $classno = session('classno');

            /*-- 获取学生列表 存入session --*/
//            $students = session('students');
//            if (!isset($students)) {
                $students = $this->mdl->sqlQuery($this->mdl->getSqlMap('major/shenhe_studentInfo.SQL'),
                    array(':studentno' => $studentno . '%',':classno' => $classno.'%' ));
//                session('students', $students);
//            }

            /*-- 获取教学计划 列表 --*/
//            $programs = session('programs');
//            if (!isset($programs)) {
//                //第一次请求
                $programs = $this->mdl->sqlQuery($this->mdl->getSqlMap('major/shenhe_selectR30.SQL'),
                    array(':rowid' => session('rowid')));

                if (empty($programs)) {
                    //查询失败  或者 教学计划为空  ==>  返回总数为空
                    $jsonret['total'] = (count($programs) == 0)?1:count($programs);
                    exit(json_encode($jsonret));
                }
//                session('programs', $programs);
//            }


            /*-- 首次查询学生 获取教学计划相关信息和需要查询的次数信息 后返回 --*/
            if (isset($_POST['tag']) && $_POST['tag'] == 'gettotal') {
                //第一次请求 标记学生数目用于查找用于持续查找
                $jsonret['total'] = (count($students) == 0 || count($programs) == 0)?1:count($students);
                //返回第一次请求
                exit(json_encode($jsonret));
            } else if (isset($_POST['tag']) && $_POST['tag'] == 'getdata'){

                if(count($programs) == 0){
                    $jsonret['data'][0] = array(
                        '_tips'=>array(
                            '_tipmsg'=>'未查询到计划列表，请检查专业、专业方向信息,或关闭重试！'
                        )
                    );
                    session('studentno',null);
                    session('classno',null);
                    session('rowid',null);
                    exit(json_encode($jsonret));
                }
                if(count($students) == 0){
                    $jsonret['data'][0] = array(
                        '_tips'=>array(
                            '_tipmsg'=>'未查询到学生列表，请检查班级学号信息,或关闭重试！'
                        )
                    );
                    session('studentno',null);
                    session('classno',null);
                    session('rowid',null);
                    exit(json_encode($jsonret));
                }

                //接下来的请求  未指明请求哪一个时，指向大的数以中断退出
                $studentsindex = isset($_POST['curindex']) ? $_POST['curindex'] : 1000;
                //第1个学生之前输出班级和学分信息信息
                if ($studentsindex == 0) {
                    //第一个学生之前输出 增加教学计划信息
                    $jsonret['data'][] = array(
                        '_bigtitle' => array(
                            '_grade' => $programs[0]['nj'],
                            '_majorname' => $programs[0]['zymc'],
                            '_majordirection' => $programs[0]['zyfx'],
                            '_creditrequesttotal' => $programs[0]['zxf'],
                            '_creditmusttotal' => $programs[0]['zbx'],
                            '_checktime' => date('Y-m-d H:i:s'),
                        )
                    );
                }

                /*-- 获取当前学生的教学计划相关信息 --*/
                $sql01 = $this->mdl->getSqlMap('major/shenhe_selectcourseList.SQL');
                $sql02 = $this->mdl->getSqlMap('major/shenhe_tongguoCredit.SQL');
                $sql03 = $this->mdl->getSqlMap('major/shenhe_gongxuan.SQL');
                //获取该学生的创新学分
                $skillcreditsql = 'select isnull(sum(addcredit.credit),0) as cxxf from students left outer join addcredit on students.studentno=addcredit.studentno  where students.studentno=:studentno';
                if ($studentsindex < count($students)) {
                    //还在当前计划列表内部
                    $studentno = $students[$studentsindex]['xh'];
                    //创新学分
                    $skillcredit = $this->mdl->sqlfind($skillcreditsql, array(':studentno' => $studentno));
                    if (empty($skillcredit)) {
                        $skillcredit = 0;
                    } else {
                        $skillcredit = $skillcredit['cxxf'];
                    }

                    $passed = floatval($students[$studentsindex]['zxf'] + $skillcredit) >= floatval($programs[0]['zxf']);
                    // 学生的信息
                    $jsonret['data'][] = array(
                        '_studentofplan' => array(
                            '_studentindex' =>'',// $studentsindex,
                            '_studentno' => $students[$studentsindex]['xh'],
                            '_studentname' => $students[$studentsindex]['xm'],
                            '_classname' => $students[$studentsindex]['bj'],
                            '_credittotalofplan' => $students[$studentsindex]['zxf'] + $skillcredit,
                            '_creditskill' => $skillcredit,
                            '_passmarkok' =>  $passed? '√' : '' ,
                            '_passmarkno' =>  $passed? '' : '×',
                        )
                    );

                    //学生教学计划的信息  需要按照顺序排列，为了同步前端读取顺序
                    foreach ($programs as $key => $prog) {
                        $programno = $prog['jhh'];
                        //教学计划标题名称
                        $jsonret['data'][] = array(
                            '_scheduleplantitle' => array(
                                '_teachingplanno' => $programno,
                                '_teachingplanname' => $prog['jhm'],
                                '_teachingplantype' => $programstype[$prog['jhlb']],
                            )
                        );
                        $alltoall = $parttopart = $publicpart = false;
                        $bind01 = array(':programno' => $programno, ':stone' => $studentno, ':sttwo' => $studentno, ':stthree' => $studentno);
                        switch ($prog['jhlb']) {
                            case 1://全选全通过
                                $alltoall = $this->mdl->sqlQuery($sql01, $bind01);
                                break;
                            case 2://全选部分通过(部分选部分通过 + 全选全通过)
                                $alltoall = $this->mdl->sqlQuery($sql01, $bind01);
                                $parttopart = $this->mdl->sqlfind($sql02, $bind01);
                                break;
                            case 3://部分选部分通过
                                $parttopart = $this->mdl->sqlfind($sql02, $bind01);
                                break;
                            case 4://公共选修课
                                $publicpart = $this->mdl->sqlfind($sql03, array(':studentno' => $studentno));
                                break;
                        }
                        //部分选部分通过  允许存在不通过的课程，因此不需要列出不通过的课程
                        if ($parttopart !== false) {
                            $passed = floatval($parttopart['xf']) >= floatval($prog['jhbx']);
                            $jsonret['data'][] = array(
                                '_parttopart' => array(
                                    '_ptprequest' => $prog['jhbx'],
                                    '_ptppassed' => $parttopart['xf'],
                                    '_ptpmarkok' =>  $passed? '√' : '',
                                    '_ptpmarkno' =>  $passed? '' : '×',
                                ),
                            );
                        }
                        //公共选修课
                        if ($publicpart !== false) {
                            $passed = floatval($skillcredit + $publicpart['xf']) >= floatval($prog['jhbx']);
                            $jsonret['data'][] = array(
                                '_publicpart' => array(
                                    '_pprequest' => $prog['jhbx'],
                                    '_pppasswed' => $publicpart['xf'] + $skillcredit,
                                    '_ppskillcredit' => $skillcredit,
                                    '_ppmarkok' =>  $passed? '√' : '',
                                    '_ppmarkno' =>  $passed? '' : '×',
                                ),
                            );
                        }
                        //全选全通过（额外列出未通过的课程）
                        if ($alltoall !== false) {
                            if (count($alltoall) == 0) {
                            	//没有不通过的课 要求和通过都是要求学分
                                $jsonret['data'][] = array(
                                    '_alltoallok' => array(
                                        '_atarequest' => $prog['jhbx'],
                                        '_atapassed' => $prog['jhbx'],
                                        '_ataomark' => '√',
                                    ),
                                );
                            } else {
                            	//有不通过的课程  列出要求和不通过的课程
                                $jsonret['data'][] = array(
                                    '_alltoallnot' => array(
                                        '_atanrequest' => $prog['jhbx'],
                                        '_atanmark' => '×',
                                    ),
                                );
                                //列出尚未通过的课程
                                foreach ($alltoall as  $val) {
                                    $jsonret['data'][] = array(
                                        '_alltoallcourses' => array(
                                            '_ataccourseno' => $val['courseno'],
                                            '_ataccredit' => $val['credit'],
                                            '_ataccoursename' => $val['coursename'],
                                        ),
                                    );
                                }
                            }
                        }

                    }
                    //指向下一个学生
                    if (++$studentsindex == count($students)) {
                        $jsonret['data'][] = array(
                            '_countstudents'=>array(
                                '_countstudentnum'=>$studentsindex
                            )
                        );
                        //清楚session信息
                        session('students', null);
                        session('programs', null);
                        session('classno', null);
                        session('studentno', null);
                        session('rowid',null);
                    }
                    exit(json_encode($jsonret));
                }
            }
        }
        session('studentno',str_replace('_', '', $_GET['studentno']));
        session('classno',$_GET['classno']);
        session('rowid',$_GET['rowid']);
        $this->display();
    }



    //todo:专门用于查询的方法
    public function chaxun(){
            $count=$this->mdl->sqlFind($this->mdl->getSqlMap($this->sqlpath['count']),$this->arr);
            if($arr['total']=$count['']){
                $pag=array(':start'=>$this->_pageDataIndex,':end'=>$this->_pageDataIndex+$this->_pageSize);
                $arr['rows']=$this->mdl->sqlQuery($this->mdl->getSqlMap($this->sqlpath['select']),array_merge($this->arr,$pag));}
            else{
                $arr['total']=0;
                $arr['rows']=array();
                }
            $this->ajaxReturn($arr,'JSON');
    }


    //_________修改教学计划
    public function programEdit(){
        $one=$this->mdl->sqlFind($this->mdl->getSqlMap('major/programEdit.SQL'),array(':PROGNO'=>$_POST['PROGNO'],':ROWID'=>$_POST['ROWID']));
        echo json_encode($one);
       // $this->ajaxReturn(,'JSON');
    }
   //_________删除教学计划
    public function programdelete(){
        $boo=$this->mdl->sqlFind($this->mdl->getSqlMap('major/programEdit.SQL'),array(':PROGNO'=>$_POST['PROGNO'],':ROWID'=>$_POST['ROWID']));
        if(!$boo)
            echo '<b>删除成功</b>';
        else
            echo '<font color="red">????????????????</font>';
    }

    //todo:专门用于修改的方法
    public function xiugai(){
        $bool=$this->mdl->sqlExecute($this->mdl->getSqlMap($this->sqlpath['edit']),$this->paixu);
        if(!$bool)
            echo 'false';
        else
            echo '<b>修改成功</b>';
    }

    public function jihuaSHENHE(){
        $bool=$this->mdl->sqlExecute($this->mdl->getSqlMap($this->sqlpath['edit']),$this->paixu);

         echo date('Y-m-d H:i:s');
    }


    //todo:添加计划的方法
    public function jihuaADD(){
        $pd=true;
        $programno="";
            $this->mdl->startTrans();
        foreach($_POST['arr'] as $key=>$val){
            $bool=$this->mdl->sqlExecute($this->mdl->getSqlMap($this->sqlpath['insert']),$this->paixu($val));
            if(!$bool){
                $pd=false;
                $programno.=$val[':PROGRAMNO'].' ';
            }
        }
        if($pd){
            $this->mdl->commit();
            echo '<b>数据插入成功</b>';
        }else{
            $this->mdl->rollback();
            echo "<font color='red'><b>{$programno}</b>记录已经存在,不要重复添加</font>";
        }
    }

    //todo:删除培养计划的方法
    public function delete_plan(){
        $this->mdl->startTrans();       //todo:开启事物
        $bool=$this->mdl->sqlExecute($this->mdl->getSqlMap('major/delete_peiyang_R30.SQL'),array(':rowid'=>$_POST['rowid']));
        $bool2=$this->mdl->sqlExecute($this->mdl->getSqlMap('major/delete_peiyang_plan.SQL'),array(':rowid'=>$_POST['rowid']));

        if($bool2){
          $this->mdl->commit();
            echo '<b>删除成功</b>';
        }else{
            $this->mdl->rollback();     //todo:回滚
            echo '<b>删除失败</b>';
        }
    }

    //:TODO:查询出要修改的培养计划的信息
    public function editInfo(){
            $bool=$this->mdl->sqlFind($this->mdl->getSqlMap($this->sqlpath['edit2']),$this->paixu);
            echo json_encode($bool);
    }

    public function editplans(){
       $this->assign('info',$_GET['rowid']);
        $this->display();
    }

    public function look_edit_program(){
        $this->xiala('教学计划类别代码','codett2');
        $this->xiala('degreeoptions','degreeoptions');
        $this->xiala('majorcode','majorcode');

        $this->assign('info',$_GET['rowid']);
        $this->assign('school',$_GET['schoolno']);
        $this->display();
    }


    //todo:添加教学计划
    public function add_program(){
        $this->assign('info',$_GET['rowid']);
        $this->xiala('schools','schools');              //学院
        $this->xiala('教学计划类别代码','codett2');
        $this->xiala('degreeoptions','degreeoptions');
        $this->xiala('majorcode','majorcode');
        $shuju=M('教学计划类别代码');
        $one=$shuju->select();
        $str='<select name="type2">';
        foreach($one as $val){
            $str.="<option value={$val['name']}>{$val['value']}</option>";
        }
        $str.='</select>';
        $this->assign('codett',$str);
        $this->display();
    }



}

