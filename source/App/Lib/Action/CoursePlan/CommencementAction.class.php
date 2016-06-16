<?php
/**
 * 开课计划
 * User: cwebs
 * Date: 14-2-23
 * Time: 上午8:47
 */
class CommencementAction extends RightAction {
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
     * 新建开课计划
     */
    public function create(){
        $this->display();
    }

    /**
     * 新增开课计划
     */
    public function save(){

        //todo: 检测传入的参数
        if(VarIsIntval("YEAR,TERM")==false || VarIsNotEmpty("COURSENO,GROUP,CLASSNO,COURSETYPE,EXAMTYPE")==false){
            $this->message["type"] = "error";
            $this->message["message"] = "输入的参数有错误，非法提交数据！";
            $this->__done("create");
        }

        //课程检测
        $bind = $this->model->getBind("COURSENO", $_REQUEST);
        $data = $this->model->sqlFind("SELECT SCHOOL FROM COURSES WHERE COURSENO=:COURSENO",$bind);
        $sschool=$data["SCHOOL"];
        if($data==null){
                $this->message["type"] = "error";
                $this->message["message"] = $_REQUEST["COURSENO"]."这门课程在课程库里没有寻找到！你必须确保课程已经在课程库里！！";
                $this->__done("create");
        }elseif($this->theacher["SCHOOL"]!=$data["SCHOOL"]&&$this->theacher['SCHOOL']!='A1'){
                $this->message["type"] = "error";
                $this->message["message"] = "您无权为其它学院的课程添加班级！！";
                $this->__done("create");
        }

        // 班级检测
        $bind = $this->model->getBind("CLASSNO", $_REQUEST);
        $data = $this->model->sqlFind("SELECT CLASSNO FROM CLASSES WHERE CLASSNO=:CLASSNO",$bind);
        if($data==null){
            $this->message["type"] = "error";
            $this->message["message"] = $_REQUEST["CLASSNO"]."班号在班级库里没有找到！此班级并不存在！！";
            $this->__done("create");
        }

        //todo: 插入数据表
        $strWeeks = "";
        for($i=1;$i<19;$i++) $strWeeks .= $_REQUEST["Week"][$i]==$i ? 1 : 0;
        $_REQUEST["WEEKS"] = bindec(strrev($strWeeks));
        $_REQUEST["SCHOOL"] =$sschool;
        $_REQUEST["ATTENDENTS"] = intval($_REQUEST["ATTENDENTS"]);
        $bind = $this->model->getBind("YEAR,TERM,COURSENO,CLASSNO,SCHOOL,WEEKS,GROUP,COURSETYPE,EXAMTYPE,ATTENDENTS,REM", $_REQUEST);
        $data = $this->model->sqlExecute($this->model->getSqlMap("coursePlan/insert.sql"),$bind);
        if($data==null){
            $this->message["type"] = "error";
            if(strpos($this->model->getDbError(), "PRIMARY KEY 约束")){
                $this->message["message"] .= "<font color='red'>此开课计划已经存在！</font>";
            }else{
                $this->message["message"] .= "<font color='red'>添加开课计划时发生错误！</font>\n";
                $this->message["dbError"] .= $this->model->getDbError();
            }
        }else{
            $this->message["type"] = "info";
            $this->message["message"] = "开课计划添加成功！";
        }
        $this->__done("create");
    }

    /**
     * 修改开课计划
     */
    public function update(){
        //todo: 检测传入的参数
        if(VarIsIntval("YEAR,TERM")==false || VarIsNotEmpty("COURSENO,GROUP,CLASSNO,COURSETYPE,EXAMTYPE,NEWGROUP,NEWCLASSNO,WEEKS")==false){
            $this->message["type"] = "error";
            $this->message["message"] = "输入的参数有错误，非法提交数据！";
            $this->__done();
        }

        //todo: 检测课程存在，并且是否为本院开设课程
        $bind = $this->model->getBind("COURSENO", $_REQUEST);
        $data = $this->model->sqlFind("SELECT SCHOOL FROM COURSES WHERE COURSENO=:COURSENO",$bind);
        if($data==null){
            $this->message["type"] = "error";
            $this->message["message"] = $_REQUEST["COURSENO"]."这门课程在课程库里没有寻找到！你必须确保课程已经在课程库里！！";
            $this->__done("create");
        }elseif($this->theacher["SCHOOL"]!=$data["SCHOOL"]&&$this->theacher['SCHOOL']!='A1'){
            $this->message["type"] = "error";
            $this->message["message"] = "您无权为其它学院的课程修改班级！！";
            $this->__done();
        }

        //todo: 检测课程是否存
        $bind = $this->model->getBind("CLASSNO", $_REQUEST['NEWCLASSNO']);
        $data = $this->model->sqlFind("SELECT CLASSNO FROM CLASSES WHERE CLASSNO=:CLASSNO",$bind);
        if($data==null){
            $this->message["type"] = "error";
            $this->message["message"] = $_REQUEST["NEWCLASSNO"]."班号在班级库里没有找到！此班级并不存在！！";
            $this->__done();
        }

        //todo: 检测修改的记录是否存在
        $bind = $this->model->getBind("YEAR,TERM,COURSENO,GROUP,CLASSNO", $_REQUEST);
        $data = $this->model->sqlFind("SELECT * FROM COURSEPLAN WHERE YEAR=:YEAR AND TERM=:TERM AND COURSENO=:COURSENO AND [GROUP]=:GROUP and CLASSNO=:CLASSNO",$bind);
        if($data==null){
            $this->message["type"] = "error";
            $this->message["message"] = "修改的记录不存在，非法提交数据！！";
            $this->__done();
        }

        //todo 事物修改记录
        $weeks = bindec(strrev($_REQUEST['WEEKS']));
        $this->model->startTrans();
        $strBind  = "NGROUP1,NWEEKS1,YEAR1,TERM1,COURSENO1,GROUP1,";
        $strBind .= "NGROUP2,NWEEKS2,YEAR2,TERM2,COURSENO2,GROUP2,";
        $strBind .= "NGROUP3,YEAR3,TERM3,COURSENO3,GROUP3,";
        $strBind .= "NCLASSNO4,NATTENDENTS4,NREM4,NWEEKS4,NGROUP4,NCOURSETYPE4,NEXAMTYPE4,YEAR4,TERM4,COURSENO4,CLASSNO4,GROUP4";
        $bind = $this->model->getBind($strBind,
            array(
                $_REQUEST['NEWGROUP'], $weeks, $data["YEAR"],$data["TERM"],$data["COURSENO"],$data['GROUP'],
                $_REQUEST['NEWGROUP'], $weeks, $data["YEAR"],$data["TERM"],$data["COURSENO"],$data['GROUP'],
                $_REQUEST['NEWGROUP'], $data["YEAR"],$data["TERM"],$data["COURSENO"],$data['GROUP'],
                $_REQUEST['NEWCLASSNO'], intval($_REQUEST['ATTENDENTS']), $_REQUEST['REM'], $weeks, $_REQUEST['NEWGROUP'], $_REQUEST['COURSETYPE'],
                $_REQUEST['EXAMTYPE'], $data["YEAR"], $data["TERM"] ,$data["COURSENO"], $data["CLASSNO"], $data['GROUP']));
        $data = $this->model->sqlExecute($this->model->getSqlMap("coursePlan/update.sql"),$bind);
        if($data===false){
            $this->model->rollback();
            $this->message["type"] = "error";
            $this->message["message"] = "修改开课计划时发生错误！！";
            $this->__done();
        }
        $this->model->commit();
        $this->message["type"] = "info";
        $this->message["message"] = "开课计划修改成功！";
        $this->__done();
    }

    /**
     * 分班处理
     */
    public function split(){
        //todo: 检测传入的参数
        if(is_array($_REQUEST['ITEM'])==false || count($_REQUEST['ITEM'])==0){
            $this->message["type"] = "error";
            $this->message["message"] = "输入的参数有错误，非法提交数据！";
            $this->__done();
        }

        $coursearr=explode(',',$_REQUEST['ITEM'][0]);

       $zcourseno=trim($coursearr[2]).trim($coursearr[3]);


        $this->model->startTrans();                 //todo:开启事物.
        foreach($_REQUEST['ITEM'] as $row){
            $items = @explode(",",$row);
            if(count($items)!=5) continue;
            $year = intval($items[0]);
            $term = intval($items[1]);
            $courseno = trim($items[2]);
            $group = trim($items[3]);
            $classno = trim($items[4]);
            if($zcourseno!=trim($courseno).trim($group)){
           //     var_dump('123');
                $this->model->rollback();
                $this->message["message"] .= "<font color='red'>错误:组号不一样！</font><br />";
                $this->message["type"] = "info";
                $this->__done();
                exit;

            }
            //todo: 检测课程存在，并且是否为本院开设课程
            $bind = $this->model->getBind("COURSENO", $courseno);
            $data = $this->model->sqlFind("SELECT SCHOOL FROM COURSES WHERE COURSENO=:COURSENO",$bind);
            if($data==null || ($this->theacher["SCHOOL"]!=$data["SCHOOL"]&&$this->theacher['SCHOOL']!='A1')) {
                $this->message["message"] .= "<font color='red'>课号[$courseno][$group]不是本学院开设课程！</font><br />";
                continue;
            }

            //todo: 检测排课中是否存在
            $bind = $this->model->getBind("YEAR,TERM,COURSENO,GROUP", array($year,$term,$courseno,doWithBindStr($group)));
            $data = $this->model->sqlCount("SELECT count(*) FROM SCHEDULEPLAN WHERE YEAR=:YEAR AND TERM=:TERM AND COURSENO=RTRIM(:COURSENO) AND :GROUP LIKE [GROUP]",$bind);
            if($data===false || $data>0) {
                $this->message["message"] .= "<font color='red'>课号[$courseno][$group]已以送到排课计划！</font><br />";
                continue;
            }

            //todo: 检测开课计划存在
            $bind = $this->model->getBind("YEAR,TERM,COURSENO,CLASSNO,GROUP", array($year,$term,$courseno,$classno,$group));
            $data = $this->model->sqlFind("SELECT * FROM COURSEPLAN WHERE YEAR=:YEAR AND TERM=:TERM AND COURSENO=:COURSENO AND CLASSNO=:CLASSNO AND [GROUP]=:GROUP",$bind);
            if($data==null || !$data["YEAR"]) {
                $this->message["message"] .= "<font color='red'>课号[$courseno][$group]不存在！</font><br />";
                continue;
            }

            //todo: 取出最大组号
            $bind = $this->model->getBind("YEAR,TERM,COURSENO", array($year,$term,$courseno));
            $maxGroup = $this->model->sqlCount("SELECT MAX(dbo.fn_36to10([GROUP])) FROM COURSEPLAN WHERE YEAR=:YEAR AND TERM=:TERM AND COURSENO=:COURSENO",$bind);

 /*           var_dump($maxGroup);*/
            if($maxGroup===false) {
                $this->message["message"] .= "<font color='red'>课号[$courseno][$group]取出最大组号错误！</font><br />";
                continue;
            }

         /*   UPDATE COURSEPLAN SET [GROUP]=:GP
WHERE YEAR=:YEAR1 AND TERM=:TERM1 AND COURSENO=:COURSENO1 AND CLASSNO=:CLASSNO1 AND [GROUP]=:GROUP1;*/

            $boolean=$this->model->sqlExecute($this->model->getSqlmap('coursePlan/New_split.SQL'),
                array(':GP'=>$maxGroup+1,':YEAR1'=>$data["YEAR"],':TERM1'=>$data["TERM"],':COURSENO1'=>$data["COURSENO"],
                ':CLASSNO1'=>$data["CLASSNO"],':GROUP1'=>$data['GROUP']));

            if(!$boolean){
                exit('修改组号失败');
            }
           //     var_dump($data);
            if($data===false){
                $this->model->rollback();
                $this->message["message"] .= "<font color='red'>课号[$courseno][$group]，班号[$classno]分组失败！</font><br />";
            }
           // $this->model->commit();
            $this->message["message"] .= "课号[$courseno][$group]，班号[$classno]分组成功！<br />";

        }

     $this->model->commit();
        $this->message["type"] = "info";
        $this->__done();
    }

    /**
     * 合班
     */
    public function  merge(){
        if(is_array($_REQUEST['ITEM'])==false || count($_REQUEST['ITEM'])==0){
            $this->message["type"] = "error";
            $this->message["message"] = "输入的参数有错误，非法提交数据！";
            $this->__done();
        }

        $arrItems = array("ATTENDENTS"=>0,"GROUPS"=>"");
        $len = count($_REQUEST['ITEM']);
        for($i=0; $i<$len; $i++){
            //每个item分解成数组
            $items = @explode(",",$_REQUEST['ITEM'][$i]);
            if(count($items)!=5){
                $this->message["type"] = "error";
                $this->message["message"] = "输入的参数有错误，非法提交数据0！";
                $this->__done();
            }
            $arrItems[$i] = array("YEAR"=>intval($items[0]),"TERM"=>intval($items[1]),"COURSENO"=>trim($items[2]),"GROUP"=>trim($items[3]),"CLASSNO"=>trim($items[4]));
            //todo: 取得课程计划
            $bind = $this->model->getBind("YEAR,TERM,COURSENO,CLASSNO,GROUP", $arrItems[$i]);
            $data = $this->model->sqlFind("SELECT * FROM COURSEPLAN WHERE YEAR=:YEAR AND TERM=:TERM AND COURSENO=:COURSENO AND CLASSNO=:CLASSNO AND [GROUP]=:GROUP",$bind);
            if($data==null) {
                $this->message["type"] = "error";
                $this->message["message"] .= "输入的参数有错误，非法提交数据1！";
                $this->__done();
            }elseif(count($data)==0){
                $arrItems[$i]["DATA"] = $data;
                if($this->theacher["SCHOOL"]!=$data["SCHOOL"]&&$this->theacher["SCHOOL"]!='A1'){
                    $this->message["type"] = "error";
                    $this->message["message"] .= "输入的参数有错误，非法提交数据2！";
                    $this->__done();
                }
            }elseif(/*$data["SCHOOL"]!=$arrItems[0]["DATA"]["SCHOOL"] ||*/ $data["COURSENO"]!=$arrItems[0]["COURSENO"]
              /*  || $data["WEEKS"]!=$arrItems[0]["DATA"]["WEEKS"] || $data["COURSETYPE"]!=$arrItems[0]["DATA"]["COURSETYPE"]*/){

                $this->message["type"] = "error";
                $this->message["message"] .= "输入的参数有错误，非法提交数据3！";
                $this->__done();
            }
            $arrItems["ATTENDENTS"] += intval($data["ATTENDENTS"]);
            $arrItems["GROUPS"] .= $data["GROUP"].'&';
            //todo: 检测排课中是否存在
            $bind = $this->model->getBind("YEAR,TERM,COURSENO", $arrItems[0]);


            $bind[":GROUPS"] = $data["GROUP"];
            $data = $this->model->sqlCount("SELECT count(*) FROM SCHEDULEPLAN WHERE YEAR=:YEAR AND TERM=:TERM AND COURSENO=RTRIM(:COURSENO) AND :GROUPS LIKE [GROUP]",$bind);

            if($data===false || $data>0) {
                $this->message["type"] = "error";
                $this->message["message"] = "开课计划已以送到排课计划！";
                $this->__done();
            }
        }



        //todo: 取出最大组号
        $bind = $this->model->getBind("YEAR,TERM,COURSENO", $arrItems[0]);
        $maxGroup = $this->model->sqlCount("SELECT MAX(dbo.fn_36to10([GROUP])) FROM COURSEPLAN WHERE YEAR=:YEAR AND TERM=:TERM AND COURSENO=:COURSENO",$bind);
        if($maxGroup===false) {
            $this->message["type"] = "error";
            $this->message["message"] = "取出最大组号发生错误！";
            $this->__done();
        }

        $this->model->startTrans();

        for($i=0;$i<$len;$i++){
            $data=$this->model->sqlExecute("update courseplan set [group]=dbo.fn_10to36(:GP) where YEAR=:YEAR AND TERM=:TERM AND COURSENO=:COURSENO AND CLASSNO=:CLASSNO AND [GROUP]=:GROUP",
                array(':GP'=>$maxGroup+1,':YEAR'=>$arrItems[$i]['YEAR'],':TERM'=>$arrItems[$i]['TERM'],':COURSENO'=>$arrItems[$i]['COURSENO'],':CLASSNO'=>$arrItems[$i]['CLASSNO'],
                    ':GROUP'=>$arrItems[$i]['GROUP']));

            if($data===false){
                $this->model->rollback();
                $this->message["type"] = "error";
                $this->message["message"] = "更新原有课程时发生错误！";
                $this->__done();
            }
        }


        $this->model->commit();
        $this->message["type"] = "info";
        $this->message["message"] = "开课计划合班成功！";
        $this->__done();
    }

    /**
     * 删除开课计划
     */
    public function delete(){
        if(!is_array($_REQUEST["ITEM"]) || count($_REQUEST["ITEM"])==0){
            $this->message["message"] = "没有提交任一数据";
            $this->__done();
        }
        foreach($_REQUEST["ITEM"] as $item){
            $items = @explode(",",$item);
            if(count($items)!=4) continue;
            $this->model->startTrans();
            $bind = $this->model->getBind("YEAR1,TERM1,COURSENO1,GROUP1,YEAR2,TERM2,COURSENO2,GROUP2,YEAR3,TERM3,COURSENO3,GROUP3,YEAR4,TERM4,COURSENO4,GROUP4",
                array(
                    $items[0], $items[1], $items[2],$items[3],
                    $items[0], $items[1], $items[2],$items[3],
                    $items[0], $items[1], $items[2],$items[3],
                    $items[0], $items[1], $items[2],$items[3]));
            $data = $this->model->sqlExecute($this->model->getSqlMap("coursePlan/delete.sql"),$bind);
            if($data===false){
                $this->model->rollback();
            }
            $this->model->commit();
        }
        $this->message["message"] = "选择的开课计划已成功删除！";
        $this->__done();
    }

    /**
     * 浏览开课计划　
     */
    public function qlist(){
        if($this->_hasJson){
            $json = array("total"=>0, "rows"=>array());
            $bind = $this->model->getBind("YEAR,TERM,COURSENO,GROUP,SCHOOL,COURSETYPE,CLASSNO,EXAMTYPE",$_REQUEST,"%");
            $sql = $this->model->getSqlMap("coursePlan/QueryCoursePlanCount.sql");
            $count = $this->model->sqlCount($sql, $bind);
            $json["total"] = intval($count);

            if($json["total"]>0){
                $sql = $this->model->getSqlMap("coursePlan/QueryCoursePlan.sql");
                $sort = $_REQUEST['sort'] ? $_REQUEST['sort'] : 'COURSENO';
                $order = $_REQUEST['order'] ? $_REQUEST['order'] : 'asc';
                if($sort=="CLASSNAME") $sql .= "ORDER BY CLASSNO ".$order.",COURSENO ".$order.",[GROUP] ".$order.",SCHOOL ".$order;
                else if($sort=="SCHOOLNAME") $sql .= "ORDER BY SCHOOL ".$order.",COURSENO ".$order.",[GROUP] ".$order.",CLASSNO ".$order;
                else $sql .= "ORDER BY COURSENO ".$order.",[GROUP] ".$order.",CLASSNO ".$order.",SCHOOL ".$order;

                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $json["rows"] = $this->model->sqlQuery($sql, $bind);
                foreach($json["rows"] as $k=>$row){
                    $json["rows"][$k]["WEEKS"] = strrev(sprintf("%018s", decbin($row["WEEKS"])));
                }
            }
            $this->ajaxReturn($json,"JSON");
            exit;
        }
        $this->display("qlist");
    }

    /**
     * 自动产生开课计划
     */
    public function auto(){
        if($this->_hasJson){
            $bind = $this->model->getBind("YEAR,TERM", $_REQUEST);
            $data = $this->model->sqlExecute("DELETE FROM COURSEPLAN WHERE YEAR=:YEAR AND TERM=:TERM", $bind);
            if($data===false){
                $this->message["type"] = "error";
                $this->message["message"] = "<font color='red'>清空已有开课计划发生错误，自动创建开课计划失败！</font>";
                $this->ajaxReturn($this->message,"JSON");
                exit;
            }
            $data = $this->model->sqlExecute("UPDATE CLASSES set GRADE=".intval($_REQUEST['YEAR'])."-DATENAME(YEAR,[YEAR])+1");
            if($data===false){
                $this->message["type"] = "error";
                $this->message["message"] = "<font color='red'>计算班级开课等级时发生错误，自动创建开课计划失败！</font>";
                $this->ajaxReturn($this->message,"JSON");
                exit;
            }
            $bind = $this->model->getBind("TERM", $_REQUEST);
            $sql = $this->model->getSqlMap("coursePlan/AutoBySchoolCount.sql");
            $total = $this->model->sqlCount($sql, $bind);
            if($total>0){
                $sql = $this->model->getSqlMap("coursePlan/AutoBySchoolImp.sql");
                $sql = str_replace('{$YEAR}',intval($_REQUEST['YEAR']),$sql);
                $sql = str_replace('{$TERM}',intval($_REQUEST['TERM']),$sql);
                $data = $this->model->sqlExecute($sql, $bind);
                if($data===false){
                    $this->message["type"] = "error";
                    $this->message["message"] = "<font color='red'>自动创建开课计划失败！</font>";
                }else $this->message["message"] = $data."条开课计划已成功导入！";
            }else $this->message["message"] = "没有任何教学计划可以导入成开课计划！";
            $this->ajaxReturn($this->message,"JSON");
            exit;
        }
        $this->display("auto");
    }

    /**
     * 导出到排课计划
     */
    public function toSchedulePlan(){
        if(!is_array($_REQUEST["ITEM"]) || count($_REQUEST["ITEM"])==0){
            $this->message["message"] = "没有提交任一数据";
            $this->__done();
        }
        $count = count($_REQUEST["ITEM"]);
        foreach($_REQUEST["ITEM"] as $item){
            $items = @explode(",",$item);
            if(count($items)!=4) {
                $count--;
                continue;
            }
            $this->model->startTrans();
            $bind = $this->model->getBind("YEAR1,TERM1,COURSENO1,GROUP1,YEAR2,TERM2,COURSENO2,GROUP2",
                array(
                    $items[0], $items[1], $items[2],$items[3],
                    $items[0], $items[1], $items[2],$items[3]));
            $data = $this->model->sqlExecute($this->model->getSqlMap("coursePlan/toSchedulePlan.sql"),$bind);
           /* $str=$this->model->getDbError();
            echo $str;*/
            if($data===false){
                $this->model->rollback();
                $count--;
            }
            $this->model->commit();
        }
        $this->message["message"] = $count."条的开课计划已导出到排课计划！";
        $this->__done();
    }


    public function Courseinfo(){
        $this->assign('courseno',$_GET['courseno']);
        $this->xiala('schools','schools');
        //todo:课程类别Volist
        $this->xiala('coursetypeoptions','coursetype');
        //todo:课程类型数据Volist    (纯理论-纯实践-理论实践)
        $this->xiala('coursetypeoptions2','coursetype2');
        $this->display();
    }

    public function Courseinfo2(){
        $this->assign('courseno',$_GET['courseno']);
        $this->xiala('schools','schools');
        //todo:课程类别Volist
        $this->xiala('coursetypeoptions','coursetype');
        //todo:课程类型数据Volist    (纯理论-纯实践-理论实践)
        $this->xiala('coursetypeoptions2','coursetype2');
        $this->display();
    }


}