<?php
/**
 * 教材管理 --征订申请
 * @author shencp
 * Date: 14-04-23
 * Time: 上午09:32
 */
class ApplyAction extends RightAction {
	private $model;
	private $user_school;
	/**
	 * 构造
	 **/
	public function __construct(){
		parent::__construct();
		$this->model = M("SqlsrvModel:");
		
		//当前登录用户所在学院
		$teacherNo=$_SESSION["S_USER_INFO"]["TEACHERNO"];
		$school = $this->model->sqlFind("SELECT T.SCHOOL FROM TEACHERS T WHERE T.TEACHERNO=:TEACHERNO",array(":TEACHERNO"=>$teacherNo));
		$this->user_school=$school["SCHOOL"];
	}
	
	/**
	 * 征订申请
	 */
	public function applyList(){
		if($this->_hasJson){
			$status=trim($_POST["STATUS"]);
			$school=$this->user_school=="A1"?$_POST["SCHOOL"]:$this->user_school;
			
			$bind = array(":YEAR"=>$_POST["YEAR"],":TERM"=>$_POST["TERM"],
					":COURSETYPE"=>doWithBindStr($_POST["COURSETYPE"]),":COURSENO"=>doWithBindStr($_POST["COURSENO"]),
					":COURSENAME"=>doWithBindStr($_POST["COURSENAME"]),":SCHOOL"=>doWithBindStr("$school"),":CLASSNO"=>doWithBindStr($_POST["CLASSNO"]));
			$arr = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("Book/Apply/queryCourseList.sql");

            if(trim($_POST["TEACHERNAME"])!="" && trim($_POST["TEACHERNAME"])!="%"){
                $sql.=" AND S2.RECNO IN (SELECT T.MAP FROM TEACHERPLAN T LEFT JOIN TEACHERS T2 ON T.TEACHERNO=T2.TEACHERNO WHERE T2.NAME LIKE :TEACHERNAME)";
                $bind[":TEACHERNAME"]=$_POST["TEACHERNAME"];
            }
			if($status!="" && $status!="%"){
                $sql.=" AND (SELECT COUNT(*) FROM BOOKAPPLY B WHERE B.COURSENO=C.COURSENO AND B.[GROUP]=C.[GROUP] AND B.[YEAR]=C.[YEAR] AND B.TERM=C.TERM AND B.SCHOOL=C.SCHOOL AND B.CLASSNO=C.CLASSNO)";
                $sql.=$status=="0"?" = 0 ":" > 0 ";
			}

			$count = $this->model->sqlCount($sql,$bind,true);
			$arr["total"] = intval($count);
			if($arr["total"] > 0){
                $sql.=" ORDER BY C.COURSENO";
				$sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//修课方式
		$this->assign("approaches",M("courseapproaches")->select());
		//所有学院
		$this->assign("school",M("schools")->select());
		//当前用户所属学院
		$this->assign("user_school",$this->user_school);
		//教材出版社
		$press = M("bookpress")->query("select id,name from __TABLE__ where status='0'");
		$this->assign("press",$press);
		//isbn列表
		$isbn = M("book")->query("select isbn from __TABLE__ where booknature='自编' and status=0");
		$this->assign("isbn",$isbn);
		
		$this->display();
	}
	/**
	 * 获取任课教师
	 */
	public function getTeacherList(){
		$arr = array("total"=>0, "rows"=>array());
		$recno=	trim($_POST["RECNO"]);
		if($recno!="" && $recno!=null){
			$arr["rows"] = $this->model->sqlQuery("SELECT DISTINCT T.TEACHERNO,T2.NAME FROM TEACHERPLAN T LEFT JOIN TEACHERS T2 ON T.TEACHERNO=T2.TEACHERNO WHERE T.MAP=:RECNO",array(":RECNO"=>$recno));
		}
		$this->ajaxReturn($arr,"JSON");
	}
	/**
	 * 获取Isbn列表
	 */
	public function getIsbnList(){
		$book = M("book")->query("select isbn from __TABLE__ where booknature='自编' and status=0");
		echo json_encode($book);
	}
	/**
	 * 获取教材信息 ,用于申请征订教材信息获取
	 */
	public function getBook(){
		if($this->_hasJson){
			$book = $this->model->sqlFind("select b.book_id,b.booknature,b.isbn,b.bookname,b.author,b.pubtime,".
					"p.name press,b2.remarks,b2.apply_id from book b left join bookapply b2 on b.book_id=b2.book_id left join bookpress p on b.press=p.id".
					" where b2.apply_id=:apply_id",array(":apply_id"=>trim($_POST["apply_id"])));
			if($book!=null){
				echo json_encode($book);
			}
		}else{
			$isbn=trim($_POST["isbn"]);
			$data=$this->model->sqlFind("select count(isbn) count from book where isbn=:isbn and status=1",array(":isbn"=>$isbn));
			if($data["count"] > 0){
				echo json_encode("-1");
			}else{
				$book =$this->model->sqlFind("select b.book_id,b.isbn,b.bookname,b.author,b.press,p.name pressname,b.pubtime from book b left join bookpress p on b.press=p.id where b.isbn=:isbn and b.status=0",array(":isbn"=>$isbn));
				if($book!=null){
					echo json_encode($book);
				}
			}
		}		
	}
	/**
	 * 保存征订信息
	 */
	public function addApply(){
		$bookList=$_POST["book"];//教材
		$courseList=$_POST["course"];//课程
		$bookId=array();
		//存储教材信息并返回ID
		if($bookList!=null && $bookList!=""){
			$i = 0;
			foreach($bookList as $val){
				if($val["book_id"]==null || $val["book_id"]==""){
					$ary=array(":isbn"=>$val["isbn"],":bookname"=>$val["bookname"],":author"=>$val["author"],
							":press"=>$val["press"],":pubtime"=>trim($val["pubtime"]),":booknature"=>$val["booknature"],
							":dis_rate"=>0.795,":status"=>0);
					
					$row=$this->model->sqlExecute($this->model->getSqlMap("Book/insertBook.sql"),$ary);
					if($row){
						$Id=$this->model->sqlFind("select max(book_id) id from book");
						$bookId[$i]=array("book_id"=>$Id["id"],"remarks"=>$val["remarks"]);
					}
				}else{
					$bookId[$i]=array("book_id"=>$val["book_id"],"remarks"=>$val["remarks"]);
				}
				$i++;
			}
		}
		
		//生成征订信息
		if($courseList!=null && $courseList!="" && $bookId != null){
			$i=0;
			foreach($courseList as $val){
				$j=0;
				//学生征订人数
				$studentData=$this->model->sqlFind("SELECT STUDENTS FROM CLASSES WHERE CLASSNO=:CLASSNO",array(":CLASSNO"=>$val["CLASSNO"]));
				$studentNum=$studentData["STUDENTS"];
				//预计人数
				$count=intval($val["ATTENDENTS"]);
				//设置公共课人数为0
				$str=substr($val["COURSENO"],0,3);
				if($str=="085"){
					$studentNum=0;
					$count=0;
				}
				$studentNum=$studentNum!=null?$studentNum:0;
				//计算教师实际数量
				$teacherNum=intval($val["TEACHERNUM"]);//教师数量
				$tea_issue_nym=$teacherNum;//记录教师实际发放数
				//查询该课程教师号
				$teacherList=$this->model->sqlQuery("SELECT DISTINCT TEACHERNO FROM TEACHERPLAN T WHERE T.MAP =:MAP",array(":MAP"=>$val["MAP"]));
				
				foreach($bookId as $b){
					//计算教师实际数量
					if($teacherNum > 0){
						foreach($teacherList as $t){
							$arr=array(":TEACHERNO"=>$t["TEACHERNO"],":COURSENO"=>$val["COURSENO"],":YEAR"=>$val["YEAR"],
									":TERM"=>$val["TERM"],":BOOK_ID"=>$b["book_id"]);
							$num=$this->model->sqlFind("select count(*) [count] from bookteacher where teacherno =:TEACHERNO and courseno=:COURSENO"
									." and [year]=:YEAR and term=:TERM and book_id=:BOOK_ID",$arr);
							if($num["count"] > 0){
								$tea_issue_nym--;
							}else{
								$data=array(":year"=>$val["YEAR"],":term"=>$val["TERM"],":courseno"=>$val["COURSENO"],
										":teacherno"=>$t["TEACHERNO"],":status"=>"0",":book_id"=>$b["book_id"]);
								$this->model->sqlExecute($this->model->getSqlMap("Book/Apply/insertBookTeacher.sql"),$data);
							}
						}
					}
					
					//生成订单号
					$orderno=$this->getOrderNo($val["YEAR"],$val["TERM"],$val["COURSENO"],$val["SCHOOL"],$b["book_id"],1);
					//保存征订信息
					$data=array(":book_id"=>$b["book_id"],":year"=>$val["YEAR"],":term"=>$val["TERM"],
							":courseno"=>$val["COURSENO"],":group"=>$val["GROUP"],":approaches"=>$val["COURSETYPE"],
							":remarks"=>$b["remarks"],
							":attendents"=>$count,":stu_quantity"=>$studentNum,":tea_quantity"=>$val["TEACHERNUM"],
							":oderno"=>$orderno,":status"=>0,":school"=>$val["SCHOOL"],":classno"=>$val["CLASSNO"],
							":map"=>$val["MAP"],":tea_issue_nym"=>$tea_issue_nym);
					
					$sql=$this->model->getSqlMap("Book/Apply/insertApply.sql");
					$row=$this->model->sqlExecute($sql,$data);
					if($row)$j++;
				}
				if($j==0)break;
				else $i++;
			}
			if($i > 0) echo true;
			else echo false;
		}
	}
	//验证
	public function validation(){
        $count = $this->model->sqlCount("select count(*) from students where studentno = :studentno",array(":studentno"=>$_POST["studentno"]));
        echo intval($count);
	}
	/**
	 * 征订检索
	 */
	public function query(){
		if($this->_hasJson){
			$school=$this->user_school=="A1"?$_POST["SCHOOL"]:$this->user_school;
			$bind = array(":YEAR"=>$_POST["YEAR"],":TERM"=>$_POST["TERM"],
					":COURSETYPE"=>doWithBindStr($_POST["COURSETYPE"]),":COURSENO"=>doWithBindStr($_POST["COURSENO"]),
					":COURSENAME"=>doWithBindStr($_POST["COURSENAME"]),":CLASSNO"=>doWithBindStr($_POST["CLASSNO"]),
					":SCHOOL"=>doWithBindStr("$school"),":STATUS"=>doWithBindStr($_POST["STATUS"]),":TEACHERNAME"=>doWithBindStr($_POST["TEACHERNAME"]));
			
			$arr = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("Book/Apply/queryApplyList.sql");

			$count = $this->model->sqlCount($sql,$bind,true);
			$arr["total"] = intval($count);
		
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		//修课方式
		$this->assign("approaches",M("courseapproaches")->select());
		//所有学院
		$this->assign("school",M("schools")->select());
		//当前用户所属学院
		$this->assign("user_school",$this->user_school);
		//教材出版社
		$press = M("bookpress")->query("select id,name from __TABLE__ where status='0'");
		$this->assign("press",$press);		
		//isbn列表
		$isbn = M("book")->query("select isbn from __TABLE__ where booknature='自编' and status=0");
		$this->assign("isbn",$isbn);
		
		$this->display();
	}
	
	/**
	 * 编辑征订信息
	 */
	public function editApply(){
		$bookList=$_POST["book"];//教材
		$delId=trim($_POST["delId"]);//教材删除ID
		$apply_id=trim($_POST["apply_id"]);//订单号
		$array=array();
		$bookId=array();
		
		//存储教材信息并返回ID
		if($bookList!=null && $bookList!=""){
			$i = 0;
			foreach($bookList as $val){
				if($val["book_id"]==null || $val["book_id"]==""){
					$ary=array(":isbn"=>$val["isbn"],":bookname"=>$val["bookname"],":author"=>$val["author"],
							":press"=>$val["press"],":pubtime"=>trim($val["pubtime"]),":booknature"=>$val["booknature"],
							":dis_rate"=>0.795,":status"=>0);
					
					$row=$this->model->sqlExecute($this->model->getSqlMap("Book/insertBook.sql"),$ary);
					if($row){
						$Id=$this->model->sqlFind("select max(book_id) id from book");
						$bookId[$i]=array("book_id"=>$Id["id"],"remarks"=>$val["remarks"],"apply_id"=>trim($val["apply_id"]));
					}
				}else{
					$bookId[$i]=array("book_id"=>$val["book_id"],"remarks"=>$val["remarks"],"apply_id"=>trim($val["apply_id"]));
				}
				$i++;
			}
		}
		
		$j=0;//记录操作成功数量
		//删除教材
		if($delId!=""){
			$delId=rtrim($delId,",");
			$sql="delete from bookapply where apply_id in($delId)";
			$delrow=$this->model->sqlExecute($sql);
			if($delrow) $j++;
		}
		/*
		 * 生成或编辑征订信息
		 */
		$course=$_POST["course"];//课程信息
		if($course!=null && $course!=""){
			//学生征订人数
			$studentData=$this->model->sqlFind("SELECT STUDENTS FROM CLASSES WHERE CLASSNO=:CLASSNO",array(":CLASSNO"=>$course["CLASSNO"]));
			$studentNum=$studentData["STUDENTS"];
			//预计人数
			$count=intval($course["ATTENDENTS"]);
			//设置公共课人数为0
			$str=substr($course["COURSENO"],0,3);
			if($str=="085"){
				$studentNum=0;
				$count=0;
			}
			$studentNum=$studentNum!=null?$studentNum:0;
			$array=array("year"=>$course["YEAR"],"term"=>$course["TERM"],
					"courseno"=>$course["COURSENO"],"group"=>$course["GROUP"],"approaches"=>$course["COURSETYPE"],
					"attendents"=>$count,"stu_quantity"=>$studentNum,"tea_quantity"=>intval($course["TEACHERNUM"]),
					"school"=>$course["SCHOOL"],"classno"=>$course["CLASSNO"],"map"=>$course["MAP"]);
		}else if($apply_id != ""){
			$bookApply=$this->model->sqlFind("select * from bookapply where apply_id=:apply_id",array(":apply_id"=>$apply_id));
			if($bookApply!=null){
				$array=array("year"=>$bookApply["year"],"term"=>$bookApply["term"],
						"courseno"=>$bookApply["courseno"],"group"=>$bookApply["group"],"approaches"=>$bookApply["approaches"],
						"attendents"=>$bookApply["attendents"],"stu_quantity"=>$bookApply["stu_quantity"],"tea_quantity"=>$bookApply["tea_quantity"],
						"school"=>$bookApply["school"],"classno"=>$bookApply["classno"],"map"=>$bookApply["map"]);
			}
		}
		if($array!=null && $bookId != null){
			foreach($bookId as $b){
				if($b["apply_id"]!=""){
					$row=$this->model->sqlExecute("update bookapply set book_id=:book_id,remarks =:remarks where apply_id=:apply_id",
							array(":book_id"=>$b["book_id"],":remarks"=>$b["remarks"],":apply_id"=>$b["apply_id"]));
					if($row) $j++;
					continue;
				}
				//计算教师实际数量
				$tea_issue_nym=$array["tea_quantity"];
				if($array["tea_quantity"] > 0){
					//查询该课程教师号
					$teacherList=$this->model->sqlQuery("SELECT DISTINCT TEACHERNO FROM TEACHERPLAN T WHERE T.MAP =:MAP",array(":MAP"=>$array["map"]));
					foreach($teacherList as $t){
						$arr=array(":TEACHERNO"=>$t["TEACHERNO"],":COURSENO"=>$array["courseno"],":YEAR"=>$array["year"],
								":TERM"=>$array["term"],":BOOK_ID"=>$b["book_id"]);
						$num=$this->model->sqlFind("select count(*) [count] from bookteacher where teacherno =:TEACHERNO and courseno=:COURSENO"
								." and [year]=:YEAR and term=:TERM and book_id=:BOOK_ID",$arr);
						if($num["count"] > 0){
							$tea_issue_nym--;
						}else{
							$data=array(":year"=>$array["year"],":term"=>$array["term"],":courseno"=>$array["courseno"],
									":teacherno"=>$t["TEACHERNO"],":status"=>"0",":book_id"=>$b["book_id"]);
							$this->model->sqlExecute($this->model->getSqlMap("Book/Apply/insertBookTeacher.sql"),$data);
						}
					}
				}
				//生成订单号
				$orderno=$this->getOrderNo($array["year"],$array["term"],$array["courseno"],$array["school"],$b["book_id"],1);
				
				//保存征订信息
				$data=array(":book_id"=>$b["book_id"],":year"=>$array["year"],":term"=>$array["term"],
						":courseno"=>$array["courseno"],":group"=>$array["group"],":approaches"=>$array["approaches"],
						":remarks"=>$b["remarks"],":attendents"=>$array["attendents"],
						":stu_quantity"=>$array["stu_quantity"],":tea_quantity"=>$array["tea_quantity"],
						":oderno"=>$orderno,":status"=>0,":school"=>$array["school"],":classno"=>$array["classno"],
						":map"=>$array["map"],":tea_issue_nym"=>$tea_issue_nym);
				$sql=$this->model->getSqlMap("Book/Apply/insertApply.sql");
				$row=$this->model->sqlExecute($sql,$data);
				if($row) $j++;
			}
			if($j > 0) echo true; else echo false;
		}
	}
	//删除征订信息
	public function del(){
		$newids="";
		foreach($_POST["ids"] as $val){
			$newids.="'".$val."',";
		}
		$newids=rtrim($newids,",");
		$sql="delete from bookapply where apply_id in($newids)";
		$row=$this->model->sqlExecute($sql);
		if($row) echo true;
		else echo false;
	}
	//查看征订记录
	public function queryBook(){
		if($this->_hasJson){
			$bind = array(":COURSENO"=>$_POST["COURSENO"],":GROUP"=>$_POST["GROUP"],
					":YEAR"=>$_POST["YEAR"],":TERM"=>$_POST["TERM"],
					":SCHOOL"=>$_POST["SCHOOL"],":CLASSNO"=>$_POST["CLASSNO"]);
			$sql="select b.*,a.apply_id,a.status state,a.remarks,b.pubtime time from bookapply a left join book b on a.book_id=b.book_id ".
			"where a.courseno=:COURSENO and a.[group]=:GROUP and a.[year]=:YEAR and a.term=:TERM and ".
			"a.school=:SCHOOL and a.classno=:CLASSNO";
			$data=$this->model->sqlQuery($sql,$bind);
			if($data!=null) echo json_encode($data);
		}
	}
	//获取订单号
	private function getOrderNo($year,$term,$courseno,$school,$book_id,$type){
		$arr=array(":year"=>$year,":term"=>$term,":courseno"=>$courseno,":school"=>$school,":book_id"=>$book_id);
		$sql="select top 1.oderno from bookapply b where b.[year]=:year and b.term=:term and b.courseno=:courseno and b.school=:school and b.book_id =:book_id";
		$data=$this->model->sqlFind($sql,$arr);
		if($data["oderno"]!="" && $data["oderno"]!=null){
			return $data["oderno"];
		}
		$data=$this->model->sqlFind("select orderno from bookorder where type=:type",array(":type"=>$type));
		$orderno=intval($data["orderno"])+1;
		
		$this->model->sqlExecute("update bookorder set orderno=:orderno where type=:type",array(":orderno"=>$orderno,":type"=>$type));
		
		return str_pad($orderno,4,'0',STR_PAD_LEFT);
	}
}