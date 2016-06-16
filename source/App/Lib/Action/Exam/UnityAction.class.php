<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 14-6-19
 * Time: 上午9:36
 */
class UnityAction extends RightAction {
    private $model;
    protected $message = array("type"=>"info","message"=>"","dbError"=>"");
    private $theacher;

    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");

        $bind = $this->model->getBind("SESSIONID", session("S_GUID"));
        $sql = $this->model->getSqlMap("user/teacher/getUserBySessionId.sql");
        $this->theacher = $this->model->sqlFind($sql, $bind);
        $this->assign("theacher", $this->theacher);
    }

    /**
     * 统考科目列表
     */
    public function qlist(){
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("exam/allExams.sql");
            $count = $this->model->sqlCount($sql,null,true);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $json["rows"] = $this->model->sqlQuery($this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize));
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $this->__done();
    }

    /**
     * 统考科目新建
     */
    public function save(){
        if(!$_REQUEST["ISADD"] || VarIsNotEmpty("COURSENO,EXAMNAME,TESTLEVEL")==false){
            $this->message["type"] = "error";
            $this->message["message"] = "参数错误，非法提交的数据！";
            $this->__done();
        }
        $bind = $this->model->getBind("COURSENO1,EXAMNAME,TESTLEVEL,TESTCODE,SCHOOLCODE,REM,COURSENO2,COURSENAME,CREDITS,SCHOOL",$_REQUEST);
        $sql  = "INSERT INTO STANDARDEXAMS (COURSENO,EXAMNAME,TESTLEVEL,TESTCODE,SCHOOLCODE,REM) VALUES (:COURSENO1,:EXAMNAME,:TESTLEVEL,:TESTCODE,:SCHOOLCODE,:REM);\n\r";
        $sql .= "INSERT INTO COURSES (COURSENO,COURSENAME,CREDITS,SCHOOL,TYPE) VALUES (:COURSENO2,:COURSENAME,:CREDITS,:SCHOOL,'T')";

        $bind[":COURSENO1"] = $bind[":COURSENO2"]= $_REQUEST["COURSENO"];
        $bind[":COURSENAME"]= $_REQUEST["EXAMNAME"];
        $bind[":SCHOOL"] = $this->theacher["SCHOOL"];

        $this->model->startTrans();
        $data = $this->model->sqlExecute($sql,$bind);
        if($data===false){
            $this->message["type"] = "error";
            $this->message["message"] = "添加统考科目时发生错误！";
            $this->model->rollback();
        }else {
            $this->model->commit();
            $this->message["message"] = "添加统考科目成功！";
        }
        $this->__done();
    }

    /**
     * 统考科目修改
     */
    public function update(){
        if(VarIsNotEmpty("COURSENO,EXAMNAME,TESTLEVEL")==false){
            $this->message["type"] = "error";
            $this->message["message"] = "参数错误，非法提交的数据！";
            $this->__done();
        }
        $bind = $this->model->getBind("EXAMNAME,TESTLEVEL,TESTCODE,SCHOOLCODE,REM,COURSENO1,CREDITS,COURSENO2",$_REQUEST);
        $sql  = "update STANDARDEXAMS set EXAMNAME=:EXAMNAME,TESTLEVEL=:TESTLEVEL,TESTCODE=:TESTCODE,SCHOOLCODE=:SCHOOLCODE,REM=:REM where COURSENO=:COURSENO1;\n\r";
        $sql .= "update COURSES set CREDITS=:CREDITS where COURSENO=:COURSENO2 ";

        $bind[":COURSENO1"] = $bind[":COURSENO2"]= $_REQUEST["COURSENO"];
  //     $bind[":COURSENAME"]= $_REQUEST["EXAMNAME"];

        $this->model->startTrans();
        $data = $this->model->sqlExecute($sql,$bind);
        if($data===false){
            $this->message["type"] = "error";
            $this->message["message"] = "更新统考科目时发生错误！";
            $this->model->rollback();
        }else {
            $this->message["message"] = "更新统考科目成功！";
            $this->model->commit();
        }
        $this->__done();
    }

    /**
     * 统考科目删除
     */
    public function delete(){
        if(!is_array($_REQUEST["COURSENO"]) || count($_REQUEST["COURSENO"])==0){
            $this->message["type"] = "error";
            $this->message["message"] = "参数错误，非法提交的数据！";
            $this->__done();
        }

        $sql = $this->model->getSqlMap("exam/unityDelete.sql");
        foreach($_REQUEST["COURSENO"] as $courseno){
            $bind = $this->model->getBind("COURSENO1,COURSENO2,COURSENO3,COURSENO4",array($courseno,$courseno,$courseno,$courseno));
            $this->model->startTrans();
            $data = $this->model->sqlExecute($sql,$bind);
            if($data===false){
                $this->message["message"] .= "<font color='red'>统考课程号[".$courseno."]删除时发生错误！</font>";
                $this->model->rollback();
            }else{
                $this->model->commit();
                $this->message["message"] .= "统考课程号[".$courseno."]删除成功！";
            }
        }

        $this->__done();
    }

    /**
     * 统考发布
     */
    public function pub(){
        if(VarIsIntval("YEAR,TERM,MAP,FEE") == false || VarIsNotEmpty("DEADLINE,DATEOFEXAM")==false){
            $this->message["type"] = "error";
            $this->message["message"] = "参数错误，非法提交的数据！";
            $this->__done();
        }

        $bind = $this->model->getBind("DEADLINE,YEAR,TERM,FEE,REM,DATEOFEXAM,MAP",$_REQUEST);
        $sql = "INSERT INTO EXAMNOTIFICATION (DEADLINE,YEAR,TERM,FEE,REM,DATEOFEXAM,MAP) VALUES (:DEADLINE,:YEAR,:TERM,:FEE,:REM,:DATEOFEXAM,:MAP)";
        $data = $this->model->sqlExecute($sql,$bind);
        if($data===false){
            $this->message["type"] = "error";
            $this->message["message"] .= "发布统考通知时发生错误！";
        }else{
            $this->message["type"] = "info";
            $this->message["message"] .= "统考通知已成功发布！";
        }
        $this->__done();
    }

    #########################################################
    ################# 统考报名 ##############################
    #########################################################

    /**
     * 统考报名列表
     */
    public function signList(){
        if($this->_hasJson){
            $bind = $this->model->getBind("YEAR,TERM",$_REQUEST);
            $json = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("exam/examNotesCount.sql");
            $count = $this->model->sqlCount($sql,$bind);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $sql = $this->model->getSqlMap("exam/examNotes.sql");
                $json["rows"] = $this->model->sqlQuery($this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize),$bind);
            }

            $this->ajaxReturn($json,"JSON");
            exit;
        }

        $one=$this->model->sqlfind("select TEACHERS.SCHOOL from TEACHERS where TEACHERS.TEACHERNO='{$_SESSION['S_USER_INFO']['TEACHERNO']}'");
        $this->assign('myschool',$one['SCHOOL']);
        $this->__done();
    }

    /**
     * 报名统考修改
     */
    public function signUpdate(){
        if(VarIsSet("DEADLINE,FEE,DATEOFEXAM,REM,RECNO")==false){
            $this->message["type"] = "error";
            $this->message["message"] = "参数错误，非法提交的数据！";
            $this->__done();
        }
        $one=$this->model->sqlfind("select TEACHERS.SCHOOL from TEACHERS where TEACHERS.TEACHERNO='{$_SESSION['S_USER_INFO']['TEACHERNO']}'");
        if(trim($one['SCHOOL']) != 'A1'){
            $this->message["message"] = "只有教务处的老师才能修改！";
            $this->__done();
        }
        $bind = $this->model->getBind("DEADLINE,FEE,DATEOFEXAM,REM,RECNO",$_REQUEST);
        $sql = "UPDATE EXAMNOTIFICATION SET DEADLINE=:DEADLINE,FEE=:FEE,DATEOFEXAM=:DATEOFEXAM,REM=:REM WHERE RECNO=:RECNO";
        $data = $this->model->sqlExecute($sql,$bind);
        if($data===false){
            $this->message["type"] = "error";
            $this->message["message"] = "统考课程修改时发生错误！";
        }else{
            $this->message["message"] = "统考课程已成功修改！";
        }
        $this->__done();
    }

    /**
     * 开启/关闭报名
     */
    public function signLock(){
        if(VarIsSet("LOCK,RECNO")==false){
            $this->message["type"] = "error";
            $this->message["message"] = "参数错误，非法提交的数据！";
            $this->__done();
        }
        $bind = $this->model->getBind("LOCK,RECNO",$_REQUEST);
        $sql = "UPDATE EXAMNOTIFICATION SET LOCK=:LOCK WHERE RECNO=:RECNO";
        $data = $this->model->sqlExecute($sql,$bind);
        if($data===false){
            $this->message["type"] = "error";
            $this->message["message"] = "统考报名开启/关闭时发生错误！";
        }else{
            $this->message["message"] = "统考报名已成功".($_REQUEST[LOCK]?'关闭':'开启')."！";
        }
        $this->__done();
    }

    public function signDelete(){
        if(!is_array($_REQUEST['RECNO']) || count($_REQUEST['RECNO'])==0){
            $this->message["type"] = "error";
            $this->message["message"] = "参数错误，非法提交的数据！";
            $this->__done();
        }


        $one=$this->model->sqlfind("select TEACHERS.SCHOOL from TEACHERS where TEACHERS.TEACHERNO='{$_SESSION['S_USER_INFO']['TEACHERNO']}'");
        if(trim($one) != 'A1'){
            $this->message["message"] = "只有教务处的老师才能删除！";
            $this->__done();
        }
        $sql  = "DELETE EXAMAPPLIES WHERE MAP=:MAP;\n";
        $sql .= "DELETE EXAMNOTIFICATION WHERE RECNO=:RECNO";
        foreach($_REQUEST['RECNO'] as $recno){
            $bind = $this->model->getBind("MAP,RECNO",array($recno,$recno));
            $this->model->startTrans();
            $data = $this->model->sqlExecute($sql,$bind);
            if($data===false) $this->model->rollback();
            else $this->model->commit();
        }
        $this->message["message"] = "指定的统考已成功删除！";
        $this->__done();
    }

    /**
     * 统考报名表
     */
    public function signForm(){
        if($this->_hasJson){
            if(isset($_REQUEST["CLASSNO"]) && $_REQUEST["CLASSNO"]){
                $bind = $this->model->getBind("FIELDSVALUE",array($_REQUEST["CLASSNO"]));
                $FIELDSNAME = "STUDENTS.CLASSNO";
            }else{
                $bind = $this->model->getBind("FIELDSVALUE",array($_REQUEST["STUDENTNO"]));
                $FIELDSNAME = "STUDENTNO";
            }

            $json = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("exam/examApplySearchClassCount.sql");
            $sql = str_replace(":FIELDSNAME",$FIELDSNAME,$sql);
            $count = $this->model->sqlCount($sql,$bind);
            $json["total"] = intval($count);
            trace($json);

            if($json["total"]>0){
                $sql = $this->model->getSqlMap("exam/examApplySearchClass.sql");
                $sql = str_replace(":FIELDSNAME",$FIELDSNAME,$sql);
                $json["rows"] = $this->model->sqlQuery($this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize),$bind);
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $bind=$this->model->getBind("RECNO",$_REQUEST);
        $data = $this->model->sqlFind($this->model->getSqlMap("exam/examNote.sql"),$bind);
        $this->assign("note",$data);
        $this->__done();
    }

    /**
     * 报名
     */
    public function signAdd(){
        if(!is_array($_REQUEST["STUDENTNO"]) || count($_REQUEST["STUDENTNO"])==0 || !$_REQUEST["RECNO"]){
            $this->message["type"] = "error";
            $this->message["message"] = "参数错误，非法提交的数据！";
            $this->__done();
        }
        $data = $this->model->sqlFind("SELECT convert(varchar,DEADLINE,120) as DEADLINE,LOCK FROM EXAMNOTIFICATION WHERE RECNO=:RECNO",array(":RECNO"=>$_REQUEST["RECNO"]));
        if($data === false || !$data || count($data)==0){
            $this->message["type"] = "error";
            $this->message["message"] = "指定的统考不存在！";
            $this->__done();
        }elseif($data["LOCK"]==1){
            $this->message["type"] = "error";
            $this->message["message"] = "指定的统考报名已终止！";
            $this->__done();
        }elseif(strtotime($data["DEADLINE"])<time()){
            $this->message["type"] = "error";
            $this->message["message"] = "指定的统考报名截止日期已到期！";
            $this->__done();
        }


        $sqlStudent = $this->model->getSqlMap("exam/examApplySearchClass.sql");
        $sqlStudent = str_replace(":FIELDSNAME","STUDENTNO",$sqlStudent);
        $indexOf = stripos($sqlStudent,"order by");
        $sqlStudent = substr($sqlStudent,0,$indexOf);

        $sql = "INSERT INTO EXAMAPPLIES (STUDENTNO,MAP,FEE) VALUES (:STUDENTNO,:MAP,:FEE)";

        foreach($_REQUEST["STUDENTNO"] as $studentNo){
            $data = $this->model->sqlFind($sqlStudent, array(":FIELDSVALUE"=>$studentNo));
            if(!$data) {
                $this->message["message"] .= "<font color='red'>指定学号[".$studentNo."]不存在！</font><br />";
            }elseif($data["SCHOOL"]!=$this->theacher["SCHOOL"]){
                $this->message["message"] .= "<font color='red'>指定学号[".$studentNo."]为别的学院学生！</font><br />";
            }else{
                $bind = $this->model->getBind("STUDENTNO,MAP,FEE",array($studentNo,$_REQUEST["RECNO"],1));
                $data = $this->model->sqlExecute($sql,$bind);
                if(!$data) $this->message["message"] .= "<font color='red'>指定学号[".$studentNo."]已在此统考列表中！</font><br />";
                else $this->message["message"] .= "指定学号[".$studentNo."]学生报名成功！<br />";
            }
        }
        $this->__done();
    }

    /**
     * 报名情况查看
     */
    public function signStudent(){
        if($this->_hasJson){
            $bind = $this->model->getBind("RECNO,STUDENTNO",$_REQUEST);
            $json = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("exam/examApplyListCount.sql");
            $count = $this->model->sqlCount($sql,$bind);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $sql = $this->model->getSqlMap("exam/examApplyList.sql");
                $json["rows"] = $this->model->sqlQuery($this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize),$bind);
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $bind=$this->model->getBind("RECNO",$_REQUEST);
        $data = $this->model->sqlFind($this->model->getSqlMap("exam/examNote.sql"),$bind);
        $this->assign("note",$data);
        $this->__done();
    }

    /**
     * 修改报名费
     */
    public function signFee(){
        if(VarIsSet("LOCK,RECNO")==false){
            $this->message["type"] = "error";
            $this->message["message"] = "参数错误，非法提交的数据！";
            $this->__done();
        }
        $bind = $this->model->getBind("LOCK,RECNO",$_REQUEST);
        $sql = "UPDATE EXAMAPPLIES SET FEE=:LOCK WHERE RECNO=:RECNO";
        $data = $this->model->sqlExecute($sql,$bind);
        if($data===false){
            $this->message["type"] = "error";
            $this->message["message"] = "修改报名费是否交讫时发生错误！";
        }else{
            $this->message["message"] = "指定学生统考报名已成功设定为：".($_REQUEST[LOCK]?'交讫':'未交讫')."！";
        }
        $this->__done();
    }

    /**
     * 删除报名学生
     */
    public function signStudentDelete(){
        if(!is_array($_REQUEST["RECNO"]) || count($_REQUEST["RECNO"])==0 || !$_REQUEST["MAP"]){
            $this->message["type"] = "error";
            $this->message["message"] = "参数错误，非法提交的数据！";
            $this->__done();
        }
        //判断是否是本学院的学生
        $sqlone="select SCHOOLS.NAME from TEACHERS inner join SCHOOLS on SCHOOLS.SCHOOL = TEACHERS.SCHOOL  where TEACHERS.TEACHERNO=:TEACHERNO";
        $myschool=$this->model->sqlFind($sqlone,array(':TEACHERNO'=>$_SESSION['S_USER_INFO']['TEACHERNO']));
      // exit(trim($myschool['NAME']));  //得到"商贸学院‘
       /* if($school['NAME']=='A1'||$school['NAME']==$_POST['SCHOOL'])    //todo:改变教学计划的权限是  本学院的老师    或者是  教务处级别的
            exit('true');           //todo:是教务处的是有权限的
        */

        $data = $this->model->sqlFind("SELECT DEADLINE,LOCK FROM EXAMNOTIFICATION WHERE RECNO=:RECNO",array(":RECNO"=>$_REQUEST["MAP"]));
        if($data === false || !$data){
            $this->message["type"] = "error";
            $this->message["message"] = "指定的统考不存在！";
            $this->__done();
        }elseif($data["LOCK"]==1){
            $this->message["type"] = "error";
            $this->message["message"] = "指定的统考报名已终止，不能删除报名！";
            $this->__done();
        }

        //$this->message["message"]  .= "  $".trim($myschool['NAME'])."$  ";
        $one=$this->model->sqlfind("select TEACHERS.SCHOOL from TEACHERS where TEACHERS.TEACHERNO='{$_SESSION['S_USER_INFO']['TEACHERNO']}'");
        if(trim($one) != 'A1'){
            foreach($_REQUEST['SCHOOLNAME'] as $school){
                //$this->message["message"]  .= "  $".trim($school)."$  ";
                if(trim($myschool['NAME']) != trim($school)){
                    $this->message["message"] .= "不能删除非本学院的学生！";
                    $this->__done();
                }
            }
        }


        foreach($_REQUEST["RECNO"] as $recno){
            $this->model->sqlExecute("delete from EXAMAPPLIES where RECNO=:RECNO and MAP=:MAP",array(":RECNO"=>$recno,":MAP"=>$_REQUEST["MAP"]));
        }
        $this->message["message"] = "指定的报名已成功删除！";
        $this->__done();
    }

    /**
     * 报名汇总
     */
    public function signCount(){
        if($this->_hasJson){
            $bind = $this->model->getBind("RECNO,SCHOOL",$_REQUEST);
            $json = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("exam/examApplyList_FeeClear_Count.sql");
            $count = $this->model->sqlCount($sql,$bind);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $sql = $this->model->getSqlMap("exam/examApplyList_FeeClear.sql");
                $json["rows"] = $this->model->sqlQuery($this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize),$bind);
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $bind=$this->model->getBind("RECNO",$_REQUEST);
        $data = $this->model->sqlFind($this->model->getSqlMap("exam/examNote.sql"),$bind);
        $this->assign("note",$data);

        $bind=$this->model->getBind("RECNO,SCHOOL",$_REQUEST);
        $data = $this->model->sqlFind($this->model->getSqlMap("exam/examApplyRoundUp.sql"),$bind);
        $this->assign("roundup",$data);
        $this->__done();
    }

    /**
     * 汇总导出
     */
    public function signExp(){
        vendor("PHPExcel.PHPExcel");
        $PHPExcel = new PHPExcel();
        set_time_limit(0);

        $template1 = array("STUDENTNO"=>"学号","STUDENTNAME"=>"姓名","SEX"=>"性别",
            'NATIONALITY'=>'民族',
            "BIRTHDAY"=>"出生年月","ID"=>"身份证号","SCHOOLNAME"=>"学院","CLASSNAME"=>"班级",
            "ENTERYEAR"=>"入学年份","YEARS"=>"学制","SCORE"=>"cet4成绩","SCORE2"=>"cet3成绩","SCORE3"=>"A级成绩","SCORE4"=>"B级成绩");

        $bind=$this->model->getBind("RECNO",$_REQUEST);
        $data = $this->model->sqlFind($this->model->getSqlMap("exam/examNote.sql"),$bind);
        //合并单元格
        $PHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(30);
        $PHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(30);
        $PHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(30);

        $PHPExcel->getActiveSheet(0)->mergeCellsByColumnAndRow(0,1,count($template1)-1,1);
        $PHPExcel->getActiveSheet(0)->mergeCellsByColumnAndRow(0,2,count($template1)-1,2);
        //设置标题
        $PHPExcel->getActiveSheet(0)->setCellValueByColumnAndRow(0,1,"统 考 报 名 汇 总 表");
        $PHPExcel->getActiveSheet(0)->setCellValueByColumnAndRow(0,2,"考试名称：".trim($data["EXAMNAME"])." 考试代码：".trim($data["TESTCODE"])." 考试日期：".$data["DATEOFEXAM"]
            ." 报名截止日期：".$data["DEADLINE"]." 考试说明：".trim($data["EXAMREM"])." 本次考试说明：".trim($data["NOTIFICATIONNOTE"]));
        $EXAMNAME = trim($data["EXAMNAME"]);


        $bind=$this->model->getBind("RECNO,SCHOOL",$_REQUEST);
        $data = $this->model->sqlFind($this->model->getSqlMap("exam/examApplyRoundUp.sql"),$bind);
        //合并单元格
        $PHPExcel->getActiveSheet(0)->mergeCellsByColumnAndRow(0,3,count($template1)-1,3);
        //设置标题
        $PHPExcel->getActiveSheet(0)->setCellValueByColumnAndRow(0,3,"交费报名人数：".$data["APPLIERS"]."，共计收费：".$data["MONEY"]."元。");


        $i=0;
        foreach($template1 as $v){
            $PHPExcel->getActiveSheet(0)->setCellValueByColumnAndRow($i,4,$v);
            $i++;
        }
        //设置窗口冻结
        $PHPExcel->getActiveSheet(0)->freezePane("A4");
        $PHPExcel->getActiveSheet(0)->freezePane("A5");

        $bind = $this->model->getBind("RECNO,SCHOOL",$_REQUEST);
        $sql = $this->model->getSqlMap("exam/examApplyList_FeeClear.sql");
        $data = $this->model->sqlQuery($this->model->getPageSql($sql,null, $this->_pageDataIndex, 5000),$bind);

        if($data!==false || count($data)>0){
            $index = 5;
            foreach($data as $row){
                $template1 = array("STUDENTNO"=>"学号","STUDENTNAME"=>"姓名","SEX"=>"性别",
                    'NATIONALITY'=>'民族',"BIRTHDAY"=>"出生年月","ID"=>"身份证号","SCHOOLNAME"=>"学院","CLASSNAME"=>"班级",
                    "ENTERYEAR"=>"入学年份","YEARS"=>"学制","SCORE"=>"cet4成绩","SCORE2"=>"cet3成绩","SCORE3"=>"A级成绩","SCORE4"=>"B级成绩");
                $PHPExcel->getActiveSheet()->setCellValueExplicit("A$index",trim($row['STUDENTNO']),PHPExcel_Cell_DataType::TYPE_STRING);
                $PHPExcel->getActiveSheet()->setCellValue("B$index",trim($row['STUDENTNAME']));
                $PHPExcel->getActiveSheet()->setCellValue("C$index",trim($row['SNAME']));
                $PHPExcel->getActiveSheet()->setCellValue("D$index",trim($row['NATIONALITY']));
                $PHPExcel->getActiveSheet()->setCellValue("E$index",trim($row['BIRTHDAY']));
                $PHPExcel->getActiveSheet()->setCellValueExplicit("F$index",trim($row['ID']),PHPExcel_Cell_DataType::TYPE_STRING);
                $PHPExcel->getActiveSheet()->setCellValue("G$index",trim($row['SCHOOLNAME']));
                $PHPExcel->getActiveSheet()->setCellValue("H$index",trim($row['CLASSNAME']));
                $PHPExcel->getActiveSheet()->setCellValue("I$index",trim($row['ENTERYEAR']));
                $PHPExcel->getActiveSheet()->setCellValue("J$index",trim($row['YEARS']));
                $PHPExcel->getActiveSheet()->setCellValue("K$index",trim($row['SCORE']));
                $PHPExcel->getActiveSheet()->setCellValue("L$index",trim($row['SCORE2']));
                $PHPExcel->getActiveSheet()->setCellValue("M$index",trim($row['SCORE3']));
                $PHPExcel->getActiveSheet()->setCellValue("N$index",trim($row['SCORE4']));
              /*  $j=0;
                foreach($template1 as $k=>$v){
                    $PHPExcel->getActiveSheet(0)->setCellValueByColumnAndRow($j,$index,$row[$k]);
                    $j++;
                }*/

                $index++;
            }
        }

        //设置font
        $styleArray = array(
            'font' => array('name' => '隶书','size' => '14','bold' => true),
            'alignment' => array('horizontal' =>  PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
        );
        $PHPExcel->getActiveSheet(0)->getStyle('A1')->applyFromArray($styleArray);

        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.iconv("UTF-8","GB2312",$EXAMNAME."统考报名汇总表.xlsx").'"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
}