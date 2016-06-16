<?php
/**
 * 最终数据处理
 * User: cwebs
 * Date: 14-2-14
 * Time: 下午4:17
 */
class EntryresultAction extends RightAction {
	private $model;
	/**
	 * 构造
	 */
	public function __construct() {
		parent::__construct ();
		$this->model = M ( "SqlsrvModel:" );
	}
	
	/**
	 * 条目整理
	 */
	public function entry() {
		if ($this->_hasJson) {
			
			$year=trim($_POST ['YEAR']);
			$term=trim($_POST ['TERM']);
			
			if (trim($_POST ['FLAG']) == "1") {
				$sql = $this->model->getSqlMap ( 'kaoping/kaoping_caculate.sql' );
				$bind = array (':YEAR'=>$year,':TERM'=>$term);
				
				$one = $this->model->sqlExecute ( $sql, $bind );
				if ($one)echo true;
				else echo false;
			}else{
				//成绩归档
				$sql = $this->model->getSqlMap('kaoping/kaoping_result_save.sql');
				$bind = array (':YEAR'=>$year,':TERM'=>$term);
				
				$one = $this->model->sqlExecute ( $sql, $bind );
				if ($one) echo true;
				else echo false;
			}
		} else {
			$data = $this->model->sqlFind ( $this->model->getSqlMap ( "course/getCourseYearTerm.sql" ), array (":TYPE" => "C" ));
			$this->assign( "yearTerm", $data );
			$this->display();
		}
	}
	
	/**
	 * 考评结果列表
	 */
	public function resultq() {
		if ($this->_hasJson) {
			$bind = array (
					':YEAR' => trim ( $_POST ['YEAR'] ),
					':TERM' => trim ( $_POST ['TERM'] ),
					":COURSENO" =>  doWithBindStr( $_POST ['COURSENO'] ) ,
					":TEACHERNAME" =>  doWithBindStr( $_POST ['TEACHERNAME'] ) ,
					":COURSENAME" =>  doWithBindStr( $_POST ['COURSENAME'] )
			);
			// 条数
			$sql = $this->model->getSqlMap ( "kaoping/kaoping_resultquery_resultcount.sql" );
			// trace($sql);
			$data = $this->model->sqlFind ( $sql, $bind );
			
			$arr ['total'] = $data ['ROWS'];
			
			if ($arr ['total'] > 0) {
				$sql = $this->model->getPageSql ( null, "kaoping/kaoping_resultquery_result.sql", $this->_pageDataIndex, $this->_pageSize );
				$arr ['rows'] = $this->model->sqlQuery ( $sql, $bind );
			} else
				$arr ['rows'] = array ();
			$this->ajaxReturn ( $arr, "JSON" );
			exit ();
		}
		
		$data = $this->model->sqlFind ( $this->model->getSqlMap ( "course/getCourseYearTerm.sql" ), array (
				":TYPE" => "C" 
		) );
		$this->assign ( "yearTerm", $data );
		
		$this->display ();
	}


    public  function jdquery(){
        $data = $this->model->sqlFind ( $this->model->getSqlMap ( "course/getCourseYearTerm.sql" ), array (
            ":TYPE" => "C"
        ) );
        $this->assign ( "yearTerm", $data );
        $this->display();
    }
	/**
	 * 考评名单结果列表
	 */
	public function entryq() {
		if ($this->_hasJson) {
			if (trim ( $_POST ['YEAR'] ) == "")
				$_POST ['YEAR'] = date ( "Y" );
			if (trim ( $_POST ['TERM'] ) == "")
				$_POST ['TERM'] = 1;
			$bind = array (
					':YEAR' => trim ( $_POST ['YEAR'] ),
					':TERM' => trim ( $_POST ['TERM'] ),
					":TEACHERNAME" =>  doWithBindStr( $_POST ['TEACHERNAME'] ) ,
					":COURSENO" =>  doWithBindStr( $_POST ['COURSENO'] ) ,
					":COURSENAME" =>  doWithBindStr( $_POST ['COURSENAME'] ) ,
					":TASK" =>  doWithBindStr( $_POST ['TASK'] )
			);
			// 条数
			$sql = $this->model->getSqlMap ( "kaoping/kaoping_studentresultcount.sql" );
			// trace($sql);
			$data = $this->model->sqlFind ( $sql, $bind );
			$arr ['total'] = $data ['ROWS'];
			if ($arr ['total'] > 0) {
				$sql = $this->model->getPageSql ( null, "kaoping/kaoping_studentresult.sql", $this->_pageDataIndex, $this->_pageSize );
				$arr ['rows'] = $this->model->sqlQuery ( $sql, $bind );
			} else
				$arr ['rows'] = array ();
			$this->ajaxReturn ( $arr, "JSON" );
			exit ();
		}
		
		$data = $this->model->sqlFind ( $this->model->getSqlMap ( "course/getCourseYearTerm.sql" ), array (
				":TYPE" => "C" 
		) );
		$this->assign ( "yearTerm", $data );

        $this->xiala('教学质量评估类型','coursetypeoptions2');
		$this->display ();
	}
	
	/**
	 * 参加教师教学质量评估学生列表
	 */
	public function querystudent() {
		if ($this->_hasJson) {
			$bind = array (
					':MAP' => trim ( $_POST ['Map'] ) 
			);
			// 条数
			$sql = $this->model->getSqlMap ( "kaoping/kaoping_studentlistcount.sql" );
			// trace($sql);
			$data = $this->model->sqlFind ( $sql, $bind );
			$arr ['total'] = $data ['ROWS'];
			if ($arr ['total'] > 0) {
				$sql = $this->model->getPageSql ( null, "kaoping/kaoping_studentlist.sql", $this->_pageDataIndex, $this->_pageSize );
				$arr ['rows'] = $this->model->sqlQuery ( $sql, $bind );
			} else
				$arr ['rows'] = array ();
			$this->ajaxReturn ( $arr, "JSON" );
			exit ();
		}
		
		$this->display ();
	}
	
	/**
	 * 删除学生操作的方法
	 */
	public function deletequerystudent() {
		$shuju = new SqlsrvModel ();
		$data = array ();
		$newids = '';
		foreach ( $_POST ['in'] as $val ) {
			$newids .= "'" . $val . "',";
		}
		$newids = rtrim ( $newids, ',' );
		$sql = "delete  from 教学质量评估详细 where recno in ($newids)";
		$row = $this->model->sqlExecute ( $sql );
		if ($row)
			echo true;
		else
			echo false;
	}
	
	/*
	 * 对学生进行插入操作的方法
	 */
	public function addquerystudent() {
		if ($this->_hasJson) {
			$sql = "insert into 教学质量评估详细(Map,Studentno)values('".$_POST['MAP']."','".$_POST['STUDENTNO']."')";
			$row = $this->model->sqlExecute ($sql);
			if ($row) echo true;
			else echo false;
		}else{
			$map=$_POST['MAP'];
			$i=0;
			foreach($_POST['STUDENTNO'] as $val){
				$sql="SELECT COUNT(*) [COUNT] FROM 教学质量评估详细 J WHERE J.MAP='$map' AND J.STUDENTNO='$val'";
				$data=$this->model->sqlFind($sql);
				if($data["COUNT"] == 0){
					$sql = "insert into 教学质量评估详细(Map,Studentno)values('$map','$val')";
					$row = $this->model->sqlExecute ($sql);
					if($row) $i++;
				}
			}
			echo $i;
		}
	}
	/**
	 * 添加学生验证
	 */
	public function checkstudentid() {
		if ($this->_hasJson) {
			$data=$this->model->sqlFind("SELECT COUNT(*) [COUNT] FROM STUDENTS WHERE STUDENTNO = :STUDENTNO",array(":STUDENTNO"=>$_POST["VALUE"]));
			if($data["COUNT"]==1){
				$sql="SELECT COUNT(*) [COUNT] FROM 教学质量评估详细 J WHERE J.MAP='".$_POST["MAP"]."' AND J.STUDENTNO='".$_POST["VALUE"]."'";
				$data=$this->model->sqlFind($sql);
				if($data["COUNT"] > 0){
					echo -1;
				}else{
					echo 1;
				}
			}else echo 0;
		}else{
			$data=$this->model->sqlFind("SELECT COUNT(*) [COUNT] FROM CLASSES C WHERE C.CLASSNO = :CLASSNO",array(":CLASSNO"=>$_POST["VALUE"]));
			echo $data["COUNT"];
		}
	}
	function queryStudentByNo(){
		$sql = $this->model->getSqlMap ( "kaoping/kaoping_studentByNoCount.sql" );
		$data = $this->model->sqlFind ($sql,array(":CLASSNO"=>$_POST["CLASSNO"]));
		$arr['total'] = $data['COUNT'];
		if ($arr['total'] > 0) {
			$sql = $this->model->getPageSql (null,"kaoping/kaoping_studentByNoList.sql", $this->_pageDataIndex, $this->_pageSize );
			$arr['rows'] = $this->model->sqlQuery ($sql,array(":CLASSNO"=>$_POST["CLASSNO"]));
		}else $arr ['rows'] = array ();
		$this->ajaxReturn ($arr,"JSON");
	}


    //TODO:按课号检索
  public function CourseStudent(){

        $zh=$this->model->sqlFind('select recno from 教学质量评估综合 where courseno=:courseno and year=:year and term=:term',

        array(':courseno'=>$_POST['courseno'],':year'=>$_POST['year'],':term'=>$_POST['term']));

        if(!$zh){
            exit('没有找到该课号');
        }
      $studentList=array();
      $count=$this->model->sqlFind("select count(*) as ROWS from(SELECT row_number() over(order by S.studentno) as row,S.STUDENTNO,S.NAME,C.CLASSNAME,S2.NAME SCHOOLNAME,S3.[VALUE] from 教学质量评估详细
        inner join STUDENTS S on 教学质量评估详细.studentno=S.studentno
INNER JOIN CLASSES C ON S.CLASSNO=C.CLASSNO
INNER JOIN SCHOOLS S2 ON S2.SCHOOL=S.SCHOOL
INNER JOIN STATUSOPTIONS S3 ON S3.NAME=S.STATUS WHERE 教学质量评估详细.map=:map) as b ",array(':map'=>$zh['recno']));

      if($studentList['total']=$count['ROWS']){
          $studentList['rows']=$this->model->sqlQuery("select * from(SELECT row_number() over(order by S.studentno) as row,S.STUDENTNO,S.NAME,C.CLASSNAME,S2.NAME SCHOOLNAME,S3.[VALUE] from 教学质量评估详细
        inner join STUDENTS S on 教学质量评估详细.studentno=S.studentno
INNER JOIN CLASSES C ON S.CLASSNO=C.CLASSNO
INNER JOIN SCHOOLS S2 ON S2.SCHOOL=S.SCHOOL
INNER JOIN STATUSOPTIONS S3 ON S3.NAME=S.STATUS WHERE 教学质量评估详细.map=:map) as b where b.row between :start and :end",array_merge(array(':map'=>$zh['recno']),array(':start'=>$this->_pageDataIndex+1,':end'=>$this->_pageDataIndex+$this->_pageSize)));
      }else{
          $studentList['total']=0;
          $studentList['rows']=array();
      }
      echo json_encode($studentList);
  }
}