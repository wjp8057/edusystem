<?php
/**
 * Created by PhpStorm.
 * User: cwebs
 * Date: 14-4-21
 * Time: 下午1:35
 */
class TeachingPlanAction extends RightAction {
    private $model;
    private $message = array("type"=>"info","message"=>"","dbError"=>"");
    private $theacher;

    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");

        $user = session("S_USER_INFO");
        $bind = $this->model->getBind("TEACHERNO", $user["TEACHERNO"]);
        $sql = $this->model->getSqlMap("user/teacher/editUser.SQL");
        $this->theacher = $this->model->sqlFind($sql, $bind);
        $this->assign("theacher", $this->theacher);
    }

    /**
     * 检索教学计划
     */
    public function search(){
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());
            $bind = $this->model->getBind("PROGRAMNO,PROGRAMNAME,SCHOOL",$_REQUEST);
            $sql = $this->model->getSqlMap("programs/QueryProgramRows.sql");
            $count = $this->model->sqlCount($sql, $bind);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $sql = $this->model->getSqlMap("programs/QueryProgramsByProgramNo.sql");
                $sort = $_REQUEST['sort'] ? $_REQUEST['sort'] : 'PROGRAMNO';
                $order = $_REQUEST['order'] ? $_REQUEST['order'] : 'asc';
                $sql = str_replace("ORDER BY PROGRAMNO","ORDER BY ".$sort." ".$order, $sql);

                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql, $bind);
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $this->display("search");
    }
    /**
     * 教学计划删除
     */
    public function delete(){
        if(!$_REQUEST["PROGRAMNO"]){
            $this->message["type"] = "error";
            $this->message["message"] = "没有指定要删除的教学计划！";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }
        $bind = $this->model->getBind("PROGRAMNO",$_REQUEST);
        $data = $this->model->sqlFind("select * from PROGRAMS where PROGRAMNO=:PROGRAMNO", $bind);
        if($data==null || $data["SCHOOL"]!=$this->theacher["SCHOOL"]){
            $this->message["type"] = "error";
            $this->message["message"] = "对不起，指定的教学计划不存在或者该教学计划不是本学校的教学计划不能删除！";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }

        //todo、1检测是否有课程
        $count = $this->model->sqlCount("select count(*) from R12 where ProgramNo=:PROGRAMNO", $bind);
        if($count>0){
            $this->message["type"] = "error";
            $this->message["message"] = "对不起，该教学计划已经加入课程列表，要删除该教学计划，请先删除教学计划内的课程列表！";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }
        //todo、2检测是否有班级
        $count = $this->model->sqlCount("select count(*) from R16 where ProgramNo=:PROGRAMNO",$bind);
        if($count>0){
            $this->message["type"] = "error";
            $this->message["message"] = "对不起，该教学计划已经绑定到班级修课计划。要删除该教学计划，请先删除教学计划内的班级列表！";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }
        //todo、3检测是否有学生
        $count = $this->model->sqlCount("select count(*) from R28 where ProgramNo=:PROGRAMNO",$bind);
        if($count>0){
            $this->message["type"] = "error";
            $this->message["message"] = "对不起，该教学计划已经绑定到个人修课计划，要删除该教学计划，请先删除教学计划内的个人选修列表！";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }
        //todo、4检测是否有辅修班
        $count = $this->model->sqlCount("select count(*) from R7 where ProgramNo=:PROGRAMNO",$bind);
        if($count>0){
            $this->message["type"] = "error";
            $this->message["message"] = "对不起，该教学计划已经绑定到辅修班修课计划，要删除该教学计划，请先删除教学计划内的辅修班列表！";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }
        //todo、5检测是否有R30
        $count = $this->model->sqlCount("select count(*) from R30 where PROGNO=:PROGRAMNO",$bind);
        if($count>0){
            $this->message["type"] = "error";
            $this->message["message"] = "对不起，该教学计划已经绑定到R30修课计划，要删除该教学计划，请先删除教学计划内的R30列表！";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }
        $data = $this->model->sqlExecute("DELETE PROGRAMS WHERE PROGRAMNO=:PROGRAMNO",$bind);
        if($data===false){
            $this->message["type"] = "error";
            $this->message["message"] .= "<font color='red'>[".$_REQUEST["PROGRAMNO"]."]该教学计划删除时发生错误！</font>\n";
            $this->message["dbError"] .= $this->model->getDbError();
        }else $this->message["message"] .= "[".$_REQUEST["PROGRAMNO"]."]该教学计划已经被安全的删除！\n";
    }
    public function update(){
        if(!$_REQUEST["PROGRAMNO"]){
            $this->message["type"] = "error";
            $this->message["message"] = "没有指定要修改的教学计划！";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }
        $bind = $this->model->getBind("PROGRAMNO",$_REQUEST);
        $data = $this->model->sqlFind("select * from PROGRAMS where PROGRAMNO=:PROGRAMNO", $bind);
        if($data==null || $data["SCHOOL"]!=$this->theacher["SCHOOL"]){
            $this->message["type"] = "error";
            $this->message["message"] = "对不起，指定的教学计划不存在或者该教学计划不是本学校的教学计划不能修改！";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }
        $bind = $this->model->getBind("PROGNAME,DATE,REMS,URL,VALID,TYPE,MAJOR,PROGRAMNO",$_REQUEST);
        $data = $this->model->sqlExecute("Update Programs Set ProgName=:PROGNAME,Date=:DATE,Rem=:REMS,Url=:URL,Valid=:VALID,Type=:TYPE,major=:MAJOR Where ProgramNo=:PROGRAMNO",$bind);
        if($data===false){
            $this->message["type"] = "error";
            $this->message["message"] .= "<font color='red'>[".$_REQUEST["PROGRAMNO"]."]该教学计划更新时发生错误！</font>\n";
            $this->message["dbError"] .= $this->model->getDbError();
        }else $this->message["message"] .= "[".$_REQUEST["PROGRAMNO"]."]该教学计划已经被更新！\n";
        $this->ajaxReturn($this->message,"JSON");
        exit;
    }
    public function copy(){
        if(!$_REQUEST["PROGRAMNO"] || !$_REQUEST["NEWPROGRAMNO"]){
            $this->message["type"] = "error";
            $this->message["message"] = "没有指定要复制的教学计划！";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }
        $bind = $this->model->getBind("PROGRAMNO",$_REQUEST);
        $programs = $this->model->sqlFind("select * from PROGRAMS where PROGRAMNO=:PROGRAMNO", $bind);
        if($programs==null || $programs["SCHOOL"]!=$this->theacher["SCHOOL"]){
            $this->message["type"] = "error";
            $this->message["message"] = "对不起，指定的教学计划不存在或者该教学计划不是本学校的教学计划不能复制！";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }

        //todo: 启动事务机制
        $this->model->startTrans();

        //todo: 1、启动事务机制插入教学计划
        $bind = $this->model->getBind("PROGRAMNO,PROGNAME,SCHOOL,TYPE",$_REQUEST["NEWPROGRAMNO"].",".$_REQUEST["PROGNAME"].",".$programs["SCHOOL"].",".$programs["TYPE"]);
        $data = $this->model->sqlExecute("INSERT INTO PROGRAMS (PROGRAMNO,PROGNAME,DATE,VALID,SCHOOL,TYPE) VALUES(:PROGRAMNO,:PROGNAME,GETDATE(),1,:SCHOOL,:TYPE)",$bind);
        if($data===false){
            $this->message["type"] = "error";
            $this->message["message"] .= "<font color='red'>插入教学计划发生错误！</font>\n";
            $this->message["dbError"] .= $this->model->getDbError();
            $this->model->rollback();
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }

        //todo: 2、复制教学计划修读课程
        $bind = $this->model->getBind("PROGRAMNO",$programs["PROGRAMNO"]);
        $count = $this->model->sqlCount("SELECT count(*) FROM R12 WHERE PROGRAMNO=:PROGRAMNO", $bind);
        trace($count,"ccccccccccccccccccccccccccccccc");
        if($count>0){
            $sql  = "Insert into R12 (PROGRAMNO,COURSENO,COURSETYPE,EXAMTYPE,TEST,CATEGORY,YEAR,TERM,WEEKS) ";
            $sql .= "SELECT '".$_REQUEST["NEWPROGRAMNO"]."',COURSENO,COURSETYPE,EXAMTYPE,TEST,CATEGORY,YEAR,TERM,WEEKS FROM R12 WHERE PROGRAMNO=:PROGRAMNO";
            $data = $this->model->sqlExecute($sql,$bind);
            if($data===false){
                $this->message["type"] = "error";
                $this->message["message"] .= "<font color='red'>复制教学计划修读课程时发生错误！</font>\n";
                $this->message["dbError"] .= $this->model->getDbError();
                $this->model->rollback();
                $this->ajaxReturn($this->message,"JSON");
                exit;
            }
        }
        //todo: 3、提交事务
        $this->model->commit();
        $this->message["type"] = "info";
        $this->message["message"] = "[".$programs["PROGRAMNO"]."]教学计划已成功复制成[".$_REQUEST["NEWPROGRAMNO"]."]教学计划！！";

        $this->ajaxReturn($this->message,"JSON");
        exit;
    }

    #################################################################################
    /**
     * 修课班级
     */
    public function classListTemplate(){
        $bind = $this->model->getBind("programno",$_REQUEST);
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("programs/QueryGetR16COUNT.sql");
            $count = $this->model->sqlCount($sql, $bind);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $sql = $this->model->getSqlMap("programs/QueryGetR16.sql");
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql, $bind);
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $sql = $this->model->getSqlMap("programs/QueryProgram.sql");
        $this->assign("programs", $this->model->sqlFind($sql, $bind));

        $this->display("classlisttemplate");
    }
    /**
     * 修读班级添加
     */
    public function classAdd(){
        $bind = $this->model->getBind("CLASSNO,CLASSNAME,SCHOOL",$_REQUEST);
        $json = array("total"=>0, "rows"=>array());
        $sql = $this->model->getSqlMap("Class/QueryClassSelectCount.sql");
        $count = $this->model->sqlCount($sql, $bind);
        $json["total"] = intval($count);

        if($json["total"]>0){
            $sql = $this->model->getSqlMap("Class/QueryClassSelect.sql");
            $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
            $json["rows"] = $this->model->sqlQuery($sql, $bind);
        }
        $this->ajaxReturn($json,"JSON");
    }
    /**
     * 修读班级保存
     */
    public function classSave(){
        if(!is_array($_REQUEST["CLASSNO"]) || count($_REQUEST["CLASSNO"])==0){
            $this->message["message"] = "没有提交任一数据\n";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }
        foreach($_REQUEST["CLASSNO"] as $CLASSNO){
            if($CLASSNO){
                $bind = $this->model->getBind("PROGRAMNO,CLASSNO",$_REQUEST["PROGRAMNO"].",".$CLASSNO);
                $data = $this->model->sqlExecute("INSERT INTO R16 (PROGRAMNO,CLASSNO) VALUES (:PROGRAMNO,:CLASSNO)",$bind);
                if($data===false){
                    $this->message["type"] = "error";
                    if(strpos($this->model->getDbError(), "PRIMARY KEY 约束")){
                        $this->message["message"] .= "<font color='red'>班号[".$CLASSNO."]已存在！</font>\n";
                    }else{
                        $this->message["dbError"] .= $this->model->getDbError();
                    }
                }else $this->message["message"] .= "班号[".$CLASSNO."]添加成功！\n";
            }
        }
        $this->message["message"] = nl2br($this->message["message"]);
        $this->ajaxReturn($this->message,"JSON");
        exit;
    }
    /**
     * 绑定到学生
     */
    public function  classBind(){
        if(!is_array($_REQUEST["CLASSNO"]) || count($_REQUEST["CLASSNO"])==0){
            $this->message["message"] = "没有提交任一数据\n";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }

        foreach($_REQUEST["CLASSNO"] as $CLASSNO){
            if($CLASSNO){
                $bind = $this->model->getBind("D_PROGRAMNO,D_CLASSNO,I_PROGRAMNO,I_CLASSNO",$_REQUEST["PROGRAMNO"].",".$CLASSNO.",".$_REQUEST["PROGRAMNO"].",".$CLASSNO);
                $data = $this->model->sqlExecute($this->model->getSqlMap("programs/classBind.sql"),$bind);
                if($data===false){
                    $this->message["type"] = "error";
                    $this->message["message"] .= "<font color='red'>班号[".$CLASSNO."]绑定时发生错误！</font>\n";
                    $this->message["dbError"] .= $this->model->getDbError();
                }else $this->message["message"] .= "班号[".$CLASSNO."]绑定成功！\n";
            }
        }

        $this->message["message"] = nl2br($this->message["message"]);
        $this->ajaxReturn($this->message,"JSON");
        exit;
    }
    /**
     * 删除修读班级
     */
    public function classDelete(){
        if(!is_array($_REQUEST["CLASSNO"]) || count($_REQUEST["CLASSNO"])==0){
            $this->message["message"] = "没有提交任一数据\n";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }

        foreach($_REQUEST["CLASSNO"] as $CLASSNO){
            if($CLASSNO){
                $bind = $this->model->getBind("PROGRAMNO,CLASSNO",$_REQUEST["PROGRAMNO"].",".$CLASSNO);
                $data = $this->model->sqlExecute("DELETE R16 WHERE PROGRAMNO=:PROGRAMNO AND CLASSNO=:CLASSNO",$bind);
                if($data===false){
                    $this->message["type"] = "error";
                    $this->message["message"] .= "<font color='red'>班号[".$CLASSNO."]删除时发生错误！</font>\n";
                    $this->message["dbError"] .= $this->model->getDbError();
                }else $this->message["message"] .= "班号[".$CLASSNO."]删除成功！\n";
            }
        }

        $this->message["message"] = nl2br($this->message["message"]);
        $this->ajaxReturn($this->message,"JSON");
        exit;
    }
    #################################################################################



    #################################################################################
    /**
     * 辅修班级
     */
    public function subsidListTemplate(){
        $bind = $this->model->getBind("programno",$_REQUEST);
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("programs/QueryGetR7Count.sql");
            $count = $this->model->sqlCount($sql, $bind);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $sql = $this->model->getSqlMap("programs/QueryGetR7.sql");
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql, $bind);
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $sql = $this->model->getSqlMap("programs/QueryProgram.sql");
        $this->assign("programs", $this->model->sqlFind($sql, $bind));

        $this->display("subsidlisttemplate");
    }
    /**
     * 辅修班级添加
     */
    public function subsidAdd(){
        $bind = $this->model->getBind("CLASSNO,CLASSNAME,SCHOOL",$_REQUEST);
        $json = array("total"=>0, "rows"=>array());
        $sql = $this->model->getSqlMap("Class/QuerySubsidSelectCount.sql");
        $count = $this->model->sqlCount($sql, $bind);
        $json["total"] = intval($count);

        if($json["total"]>0){
            $sql = $this->model->getSqlMap("Class/QuerySubsidSelect.sql");
            $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
            $json["rows"] = $this->model->sqlQuery($sql, $bind);
        }
        $this->ajaxReturn($json,"JSON");
    }
    /**
     * 辅修班级保存
     */
    public function subsidSave(){
        if(!is_array($_REQUEST["CLASSNO"]) || count($_REQUEST["CLASSNO"])==0){
            $this->message["message"] = "没有提交任一数据\n";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }
        foreach($_REQUEST["CLASSNO"] as $CLASSNO){
            if($CLASSNO){
                $bind = $this->model->getBind("PROGRAMNO,CLASSNO",$_REQUEST["PROGRAMNO"].",".$CLASSNO);
                $data = $this->model->sqlExecute("INSERT INTO R7 (PROGRAMNO,CLASSNO) VALUES (:PROGRAMNO,:CLASSNO)",$bind);
                if($data===false){
                    $this->message["type"] = "error";
                    if(strpos($this->model->getDbError(), "PRIMARY KEY 约束")){
                        $this->message["message"] .= "<font color='red'>辅修班号[".$CLASSNO."]已存在！</font>\n";
                    }else{
                        $this->message["dbError"] .= $this->model->getDbError();
                    }
                }else $this->message["message"] .= "辅修班号[".$CLASSNO."]添加成功！\n";
            }
        }
        $this->message["message"] = nl2br($this->message["message"]);
        $this->ajaxReturn($this->message,"JSON");
        exit;
    }
    /**
     * 删除辅修班级
     */
    public function subsidDelete(){
        if(!is_array($_REQUEST["CLASSNO"]) || count($_REQUEST["CLASSNO"])==0){
            $this->message["message"] = "没有提交任一数据\n";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }

        foreach($_REQUEST["CLASSNO"] as $CLASSNO){
            if($CLASSNO){
                $bind = $this->model->getBind("PROGRAMNO,CLASSNO",$_REQUEST["PROGRAMNO"].",".$CLASSNO);
                $data = $this->model->sqlExecute("DELETE R7 WHERE PROGRAMNO=:PROGRAMNO AND CLASSNO=:CLASSNO",$bind);
                if($data===false){
                    $this->message["type"] = "error";
                    $this->message["message"] .= "<font color='red'>辅修班号[".$CLASSNO."]删除时发生错误！</font>\n";
                    $this->message["dbError"] .= $this->model->getDbError();
                }else $this->message["message"] .= "辅修班号[".$CLASSNO."]删除成功！\n";
            }
        }

        $this->message["message"] = nl2br($this->message["message"]);
        $this->ajaxReturn($this->message,"JSON");
        exit;
    }
    /**
     * 绑定到学生
     */
    public function  subsidBind(){
        if(!is_array($_REQUEST["CLASSNO"]) || count($_REQUEST["CLASSNO"])==0){
            $this->message["message"] = "没有提交任一数据\n";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }

        foreach($_REQUEST["CLASSNO"] as $CLASSNO){
            if($CLASSNO){
                $bind = $this->model->getBind("PROGRAMNO,CLASSNO",$_REQUEST["PROGRAMNO"].",".$CLASSNO);
                $data = $this->model->sqlExecute($this->model->getSqlMap("programs/subsidBind.sql"),$bind);
                if($data===false){
                    $this->message["type"] = "error";
                    $this->message["message"] .= "<font color='red'>辅修班号[".$CLASSNO."]绑定时发生错误！</font>\n";
                    $this->message["dbError"] .= $this->model->getDbError();
                }else $this->message["message"] .= "辅修班号[".$CLASSNO."]绑定成功！\n";
            }
        }

        $this->message["message"] = nl2br($this->message["message"]);
        $this->ajaxReturn($this->message,"JSON");
        exit;
    }



    #################################################################################
    /**
     * 修读学生
     */
    public function studentsListTemplate(){
        $bind = $this->model->getBind("programno",$_REQUEST);
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("programs/QueryGetR28Count.sql");
            $count = $this->model->sqlCount($sql, $bind);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $sql = $this->model->getSqlMap("programs/QueryGetR28.sql");
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql, $bind);
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $sql = $this->model->getSqlMap("programs/QueryProgram.sql");
        $this->assign("programs", $this->model->sqlFind($sql, $bind));

        $this->display("studentslisttemplate");
    }
    /**
     * 修读学生添加
     */
    public function studentsAdd(){
        $bind = $this->model->getBind("STUDENTNO,NAME,CLASSNO,CLASSNAME,SCHOOL",$_REQUEST);
        $json = array("total"=>0, "rows"=>array());
        $sql = $this->model->getSqlMap("programs/QueryStudentSelectCount.sql");
        $count = $this->model->sqlCount($sql, $bind);
        $json["total"] = intval($count);

        if($json["total"]>0){
            $sql = $this->model->getSqlMap("programs/QueryStudentSelect.sql");
            $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
            $json["rows"] = $this->model->sqlQuery($sql, $bind);
        }
        $this->ajaxReturn($json,"JSON");
    }
    /**
     * 修读学生保存
     */
    public function studentsSave(){
        if(!is_array($_REQUEST["STUDENTNO"]) || count($_REQUEST["STUDENTNO"])==0){
            $this->message["message"] = "没有提交任一数据\n";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }
        foreach($_REQUEST["STUDENTNO"] as $STUDENTNO){
            if($STUDENTNO){
                $bind = $this->model->getBind("STUDENTNO,PROGRAMNO",$STUDENTNO.",".$_REQUEST["PROGRAMNO"]);
                $data = $this->model->sqlExecute("INSERT INTO R28 (STUDENTNO,PROGRAMNO) VALUES (:STUDENTNO,:PROGRAMNO)",$bind);
                if($data===false){
                    $this->message["type"] = "error";
                    if(strpos($this->model->getDbError(), "PRIMARY KEY 约束")){
                        $this->message["message"] .= "<font color='red'>学号[".$STUDENTNO."]学生已存在！</font>\n";
                    }else{
                        $this->message["dbError"] .= $this->model->getDbError();
                    }
                }else $this->message["message"] .= "学号[".$STUDENTNO."]学生添加成功！\n";
            }
        }
        $this->message["message"] = nl2br($this->message["message"]);
        $this->ajaxReturn($this->message,"JSON");
        exit;
    }
    /**
     * 删除修读学生
     */
    public function studentsDelete(){
        if(!is_array($_REQUEST["STUDENTNO"]) || count($_REQUEST["STUDENTNO"])==0){
            $this->message["message"] = "没有提交任一数据\n";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }

        foreach($_REQUEST["STUDENTNO"] as $STUDENTNO){
            if($STUDENTNO){
                $bind = $this->model->getBind("STUDENTNO,PROGRAMNO",$STUDENTNO.",".$_REQUEST["PROGRAMNO"]);
                $data = $this->model->sqlExecute("delete from R28 where StudentNo=:STUDENTNO and ProgramNo=:PROGRAMNO",$bind);
                if($data===false){
                    $this->message["type"] = "error";
                    $this->message["message"] .= "<font color='red'>学号[".$STUDENTNO."]学生删除时发生错误！</font>\n";
                    $this->message["dbError"] .= $this->model->getDbError();
                }else $this->message["message"] .= "学号[".$STUDENTNO."]学生删除成功！\n";
            }
        }

        $this->message["message"] = nl2br($this->message["message"]);
        $this->ajaxReturn($this->message,"JSON");
        exit;
    }




    #################################################################################
    /**
     * 修课课程
     */
    public function courselistTemplate(){
        if(isset($_GET['godetail']) && ($_GET['godetail'] == 1)){
            $bind = array(':courseno'=>$_GET['courseno']);
            $sql = $this->model->getSqlMap("programs/queryCourseDetail.SQL");
            $data = $this->model->sqlFind($sql, $bind);
            if(isset($_GET['refleshData']) && ($_GET['refleshData'] == 1)){
            	$this->ajaxReturn($data,"JSON");
            	exit;
            }
        	//todo:课程Volist
        	$this->xiala('schools','school');
        	//todo:课程类别Volist
        	$this->xiala('coursetypeoptions','coursetype');
        	//todo:课程类型数据Volist    (纯理论-纯实践-理论实践)
        	$this->xiala('coursetypeoptions2','coursetype2');
        	 
            //var_dump(str_replace("\"",'\'',json_encode($data)));
            $this->assign('data',str_replace("\"",'\'',json_encode($data)));
            $this->display('coursedetail');
            exit;
        }
        $bind = $this->model->getBind("programno",$_REQUEST);
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());

            $sql = $this->model->getSqlMap("programs/QueryProgramDetail.sql");
            $json["rows"] = $this->model->sqlQuery($sql, $bind);
            $json["total"] = count($json["rows"]);
            if($json["total"]>0){
                foreach($json["rows"] as $k=>$row){
                    $json["rows"][$k]["Weeks"] = strrev(sprintf("%018s", decbin($row["Weeks"])));
                }
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $sql = $this->model->getSqlMap("programs/QueryProgram.sql");
        $this->assign("programs", $this->model->sqlFind($sql, $bind));
        $this->assign('examtype',$this->combobox('examoptions','NAME','VALUE'));
        $this->assign('xktype',$this->combobox('courseapproaches','NAME','VALUE'));
        $this->assign('kaoshijibie',$this->combobox('testlevel','NAME','VALUE'));
        $this->assign('kctype',$this->combobox('COURSECAT','NAME','VALUE'));
        $this->display("courselisttemplate");
    }

    private function combobox($tablename,$value,$text){
        $arr=$this->model->sqlQuery("select * from $tablename");
        $sjson=array();
        foreach($arr as $val){
            $sjson2['text']=trim($val[$text]);
            $sjson2['value']=trim($val[$value]);                  // 把学院数据转成json格式给前台的combobox使用
            array_push($sjson,$sjson2);
        }
        $sjson=json_encode($sjson);
        return $sjson;
    }


    public function updateP(){
        $this->model->startTrans();
        foreach($_POST['bind'] as $val){
            $EXAMTYPE='';
            $COURSETYPE='';
            $TEST='';
            $CATEGORY='';
           // echo strlen(trim($_POST['ExamType']));
            if(strlen(trim($val['ExamType']))==1)$EXAMTYPE="EXAMTYPE='{$val['ExamType']}',";
            if(strlen(trim($val['CourseType']))==1)$COURSETYPE="COURSETYPE='{$val['CourseType']}',";
            if(strlen(trim($val['TESTVALUE']))==1)$TEST="TEST='{$val['TESTVALUE']}',";
            if(strlen(trim($val['CATEGORYVALUE']))==1)$CATEGORY="CATEGORY='{$val['CATEGORYVALUE']}',";
            $data = $this->model->sqlExecute("UPDATE R12 SET {$COURSETYPE}{$EXAMTYPE}{$TEST}{$CATEGORY}YEAR=:YEAR,TERM=:TERM WHERE COURSENO=:COURSENO AND PROGRAMNO=:PROGRAMNO",
            array(':YEAR'=>trim($val['Year']),':TERM'=>trim($val['Term']),':COURSENO'=>$val['CourseNo'],':PROGRAMNO'=>$val['PROGRAMNO']));
            if(!$data){
                $this->model->rollback();
                exit('系统错误');
            }
        }
        $this->model->commit();
        exit('修改成功');
    }


    /**
     * 修读课程添加
     */
    public function courseAdd(){
        $bind = $this->model->getBind("COURSENO,COURSENAME,SCHOOL,COURSETYPE",$_REQUEST);
        $json = array("total"=>0, "rows"=>array());
        $sql = $this->model->getSqlMap("programs/QueryCourseSelectCount.sql");
        $count = $this->model->sqlCount($sql, $bind);
        $json["total"] = intval($count);

        if($json["total"]>0){
            $sql = $this->model->getSqlMap("programs/QueryCourseSelect.sql");
            $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
            $json["rows"] = $this->model->sqlQuery($sql, $bind);
        }
        $this->ajaxReturn($json,"JSON");
    }
    /**
     * 修读课程保存
     */
    public function courseSave(){
        if(!is_array($_REQUEST["COURSENO"]) || count($_REQUEST["COURSENO"])==0){
            $this->message["message"] = "没有提交任一数据\n";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }
        foreach($_REQUEST["COURSENO"] as $COURSENO){
            if($COURSENO){
                $bind = $this->model->getBind("PROGRAMNO,COURSENO",$_REQUEST["PROGRAMNO"].",".$COURSENO);
                $data = $this->model->sqlExecute("insert INTO R12(ProgramNo,CourseNo) values(:PROGRAMNO,:COURSENO)",$bind);
                if($data===false){
                    $this->message["type"] = "error";
                    if(strpos($this->model->getDbError(), "PRIMARY KEY 约束")){
                        $this->message["message"] .= "<font color='red'>课程号[".$COURSENO."]已存在！</font>\n";
                    }else{
                        $this->message["dbError"] .= $this->model->getDbError();
                    }
                }else $this->message["message"] .= "课程号[".$COURSENO."]添加成功！\n";
            }
        }
        $this->message["message"] = nl2br($this->message["message"]);
        $this->ajaxReturn($this->message,"JSON");
        exit;
    }
    /**
     * 删除修读课程
     */
    public function courseDelete(){
        if(!is_array($_REQUEST["COURSENO"]) || count($_REQUEST["COURSENO"])==0){
            $this->message["message"] = "没有提交任一数据\n";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }

        foreach($_REQUEST["COURSENO"] as $COURSENO){
            if($COURSENO){
                $bind = $this->model->getBind("PROGRAMNO,COURSENO",$_REQUEST["PROGRAMNO"].",".$COURSENO);
                $data = $this->model->sqlExecute("DELETE R12 WHERE PROGRAMNO=:PROGRAMNO AND COURSENO=:COURSENO",$bind);
                if($data===false){
                    $this->message["type"] = "error";
                    $this->message["message"] .= "<font color='red'>课程号[".$COURSENO."]删除时发生错误！</font>\n";
                    $this->message["dbError"] .= $this->model->getDbError();
                }else $this->message["message"] .= "课程号[".$COURSENO."]学生删除成功！\n";
            }
        }

        $this->message["message"] = nl2br($this->message["message"]);
        $this->ajaxReturn($this->message,"JSON");
        exit;
    }
    /**
     * 更新修读课程
     */
    public function courseUpdate(){
        if(!$_REQUEST["COURSENO"] || !isset($_REQUEST["YEAR"]) || !isset($_REQUEST["TERM"]) || !isset($_REQUEST["EXAMTYPECODE"])
            || !isset($_REQUEST["TESTCODE"]) || !isset($_REQUEST["CATEGORYCODE"]) || !isset($_REQUEST["WEEKS"])){
            $this->message["message"] = "提交的数据非法\n";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }

        $_REQUEST["WEEKS"] = bindec(strrev($_REQUEST["WEEKS"]));
        $bind = $this->model->getBind("COURSETYPECODE,EXAMTYPECODE,TESTCODE,CATEGORYCODE,YEAR,TERM,WEEKS,COURSENO,PROGRAMNO",$_REQUEST);
        $data = $this->model->sqlExecute("UPDATE R12 SET COURSETYPE=:COURSETYPECODE,EXAMTYPE=:EXAMTYPECODE,TEST=:TESTCODE,CATEGORY=:CATEGORYCODE,YEAR=:YEAR,TERM=:TERM,WEEKS=:WEEKS WHERE COURSENO=:COURSENO AND PROGRAMNO=:PROGRAMNO", $bind);
        if($data===false){
            $this->message["type"] = "error";
            $this->message["message"] .= "<font color='red'>课程号[".$_REQUEST["COURSENO"]."]更新时发生错误！</font>\n";
            $this->message["dbError"] .= $this->model->getDbError();
        }else $this->message["message"] .= "课程号[".$_REQUEST["COURSENO"]."]更新成功！\n";

        $this->message["message"] = nl2br($this->message["message"]);
        $this->ajaxReturn($this->message,"JSON");
        exit;
    }
}