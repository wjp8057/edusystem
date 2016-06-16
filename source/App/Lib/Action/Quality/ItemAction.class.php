<?php
/**
 * 考评条目筛选
 * User: cwebs
 * Date: 14-2-12
 * Time: 下午3:43
 */
class ItemAction extends RightAction
{
    private $md;        //存放模型对象
    /**
     *  考评条目管理
     *
     **/
    public function __construct()
    {
        parent::__construct();
        $this->model = M("SqlsrvModel:");
    }
    /**
     *  考评条目列表
     *
     **/
    public function itemq()
    {
        if($this->_hasJson)
        {
            if(trim($_POST['YEAR'])=="") $_POST['YEAR']=date("Y");
            if(trim($_POST['TERM'])=="") $_POST['TERM']=1;

            $bind = array(":YEAR"=>trim($_POST['YEAR']),":TERM"=>trim($_POST['TERM']),":COURSENO"=>doWithBindStr($_POST['COURSENO']),":COURSENAME"=>doWithBindStr($_POST['COURSENAME']),":TASK"=>doWithBindStr($_POST['TASK']),":TEACHERNAME"=>doWithBindStr($_POST['TEACHERNAME']),":ENABLED"=>doWithBindStr($_POST['ENABLED']));
            //条数
            $sql = $this->model->getSqlMap("kaoping/kaoping_queryresultcount.sql");
            //trace($sql);
            $data = $this->model->sqlFind($sql,$bind);

            $arr['total'] = $data['ROWS'];

            if($arr['total']>0)
            {
                $sql = $this->model->getPageSql(null,"kaoping/kaoping_queryresult.sql", $this->_pageDataIndex, $this->_pageSize);
                $arr['rows'] = $this->model->sqlQuery($sql,$bind);
            }
            else
                $arr['rows']=array();
            $this->ajaxReturn($arr,"JSON");
            exit;
        }
        $teachername=$this->model->sqlFind('select NAME from teachers where teacherno=:tno',array(':tno'=>$_SESSION['S_USER_INFO']['TEACHERNO']));
        $this->assign('name',$teachername['NAME']);
        $this->assign('quanxian',$_SESSION['S_USER_INFO']['ROLES']);
        $sjson=array();
        $sjson2['text']="课堂教学";
        $sjson2['value']="K";                    // 把类型数据转成json格式给前台的combobox使用
        array_push($sjson,$sjson2);
        $sjson2['text']="实践教学";
        $sjson2['value']="S";
        array_push($sjson,$sjson2);
        $sjson2['text']="毕业设计";
        $sjson2['value']="B";
        array_push($sjson,$sjson2);
        $sjson=json_encode($sjson);
        $this->assign('sjson',$sjson);

        $sjson3=array();
        $sjson2['text']="参评";
        $sjson2['value']="1";                    // 把是否参评数据转成json格式给前台的combobox使用
        array_push($sjson3,$sjson2);
        $sjson2['text']="不参评";
        $sjson2['value']="0";
        array_push($sjson3,$sjson2);
        $sjson3=json_encode($sjson3);
        $this->assign('sjson3',$sjson3);

        $data = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
     /*   echo '<pre>';
        print_r($_SESSION);*/
        $myschool=$this->model->sqlFind("select SCHOOL from teachers where teacherno=:teacherno",array(':teacherno'=>$_SESSION['S_USER_INFO']['TEACHERNO']));

        $this->assign('myschool',$myschool['SCHOOL']);
        $this->assign("yearTerm",$data);
      //  $this->assign("");

        $this->xiala('教学质量评估类型','coursetypeoptions2');
        $this->display();
    }


    public function updatelock(){
        $this->model->startTrans();
        foreach($_POST['bind'] as $val){
            $one=$this->model->sqlExecute("UPDATE 教学质量评估综合 SET lock=:lock  WHERE recno=:recno",
                array(':lock'=>$_POST['lock'],':recno'=>$val));
            if(!$one){
                exit('未知错误!');
                $this->model->rollback();
            }

    }
        $this->model->commit();
        exit('设置成功');
    }

    /*
     * 修改数据时候的方法
     */
    function updateit()
    {
        if($this->_hasJson){
            $this->model->startTrans();
            foreach($_POST['bind'] as $val){
                $one=$this->model->sqlExecute("UPDATE 教学质量评估综合 SET task=:task  WHERE recno=:recno",
                array(':task'=>$_POST['task'],':recno'=>$val));
                if(!$one){
                    exit('未知错误!');
                    $this->model->rollback();
                }
            }

            $this->model->commit();
            exit('设置成功');

        }

        $this->model->startTrans();
        foreach($_POST['bind'] as $val){
            $one=$this->model->sqlExecute("UPDATE 教学质量评估综合 SET enabled=:task  WHERE recno=:recno",
                array(':task'=>$_POST['task'],':recno'=>$val));
            if(!$one){
                exit('未知错误!');
                $this->model->rollback();
            }
        }

        $this->model->commit();
        exit('设置成功');
/*
        $task_sql=trim($_POST["TASK"]);
        $enabled_sql=trim($_POST["ENABLED"]);
        $recno_sql=trim($_POST["RECNO"]);

        $sql="UPDATE 教学质量评估综合 SET task='".$task_sql."',enabled='$enabled_sql' WHERE recno='$recno_sql'";
        $boo=$this->model->sqlQuery($sql);
        if($boo===false) echo false;
        else echo true;*/
    }

    /**
     *  删除考评条目操作的方法
     **/
    public function deleteit()
    {
        $shuju=new SqlsrvModel();
        $data=array();
        $newids='';
        foreach($_POST['in'] as $val){
            $newids.="'".$val."',";
        }
        $newids=rtrim($newids,',');
        $sql="delete  from 教学质量评估综合 where recno in ($newids);delete from 教学质量评估详细 where map in ($newids);";
        $row=$this->model->sqlExecute($sql);
        if($row) echo true;
        else echo false;
    }

    /*
     * 显示主页FORM的方法
     */
    public function additem(){
    	if($this->_hasJson){
    		$teacher =$this->model->sqlQuery("select rtrim(teacherno) code,rtrim(name) name from  teachers where name!='' order by name"); 
        	echo json_encode($teacher);exit;
    	}
        $data = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
        $this->assign("yearTerm",$data);
        $this->xiala('教学质量评估类型','coursetypeoptions2');
        $this->xiala('schools','schools');
        $this->display();
    }

    /*
     * 对考评条目进行插入操作的方法
     */
    function addit(){
        foreach($_POST AS $key=>$value){        //对旁边的空格进行过滤用的
            $arr[$key]=trim($value);
        }
      //  $sql="insert into 教学质量评估综合 (teacherno,courseno,task,year,term) values('".$arr['TEACHERNO']."','".$arr['COURSENO']."','".$arr['TYPE']."','".$arr['YEAR']."','".$arr['TERM']."')";
        $row=$this->model->sqlExecute("insert into 教学质量评估综合 (teacherno,courseno,task,year,term,lock) values(:teacherno,:courseno,:task,:year,:term,0)",
        array(':teacherno'=>$arr['TEACHERNO'],':courseno'=>$arr['COURSENO'],':task'=>$arr['TYPE'],':year'=>$arr['YEAR'],':term'=>$arr['TERM']));
        $str=$this->model->getDbError();
        if($row) echo 'true';
        else if(strpos($str,'[ SQL语句 ]')){
            echo '重复插入了'.$str;
        }else{
            echo '未知错误'.$str;
        }
    }
    
    /**
     * 课号验证
     */
    public function validation(){
        if(isset($_POST['teacherno'])){
            if($this->_hasJson){
                $ct=$this->model->sqlFind('select NAME from teachers where teacherno=:teacherno',array(':teacherno'=>$_POST['teacherno']));
                exit(json_encode($ct));
            }
        }elseif(isset($_POST["VALUE"])){
            //查询课号是否存在
            $courseno=$this->model->sqlFind("SELECT COUNT(*) [COUNT] FROM courses S WHERE S.COURSENO= '".substr(trim($_POST["VALUE"]),0,7)."'");
            echo $courseno["COUNT"];
        }

    }

    //todo:查看自己的课程
    public function mycreate(){
        $arr=array();
        $array=array(':school'=>$_POST['school'],
            ':yone'=>$_POST['year'],':tone'=>$_POST['term'],':ytwo'=>$_POST['year'],':ttwo'=>$_POST['term']);
        $count=$this->model->sqlFind(" select count(*) as ROWS from (SELECT row_number() over(order by TEACHERS.teacherno) as row,TEACHERS.NAME AS TEACHERNAME,temp.TEACHERNO AS TEACHERNO,
temp.COURSENO AS COURSENO,COURSES.COURSENAME AS COURSENAME,temp.TASK ,YEAR,TERM,RECNO,TEMP.ENABLED
FROM 教学质量评估综合 as temp INNER JOIN TEACHERS ON TEACHERS.TEACHERNO=temp.TEACHERNO
INNER JOIN COURSES ON COURSES.COURSENO=SUBSTRING(temp.COURSENO,1,7)
where courses.SCHOOL=:school  and temp.year=:yone and temp.term=:tone and temp.courseno not in(
	select rtrim(courseno)+rtrim([group]) from SCHEDULEPLAN where year=:ytwo and term=:ttwo
))as b ",$array);

        if($arr['total']=$count['ROWS']){
          $arr['rows']=$this->model->sqlQuery(" select *  from (SELECT row_number() over(order by TEACHERS.teacherno) as row,TEACHERS.NAME AS TEACHERNAME,temp.TEACHERNO AS TEACHERNO,
temp.COURSENO AS COURSENO,COURSES.COURSENAME AS COURSENAME,temp.TASK ,YEAR,TERM,RECNO,TEMP.ENABLED
FROM 教学质量评估综合 as temp INNER JOIN TEACHERS ON TEACHERS.TEACHERNO=temp.TEACHERNO
INNER JOIN COURSES ON COURSES.COURSENO=SUBSTRING(temp.COURSENO,1,7)
where courses.SCHOOL=:school  and temp.year=:yone and temp.term=:tone and temp.courseno not in(
	select rtrim(courseno)+rtrim([group]) from SCHEDULEPLAN where year=:ytwo and term=:ttwo
))as b",array_merge($array,array(':start'=>$this->_pageDataIndex+1,':end'=>$this->_pageDataIndex+$this->_pageSize)));
        }else{
            $arr['total']=0;
            $arr['rows']=array();
        }
        echo json_encode($arr);

    }
}