<?php
/**
 * 教材管理 --教材发放
 * @author shencp
 * Date: 14-04-23
 * Time: 上午09:32
 */
class IssueAction extends RightAction {
	private $model;
	//构造
	public function __construct(){
		parent::__construct();
		$this->model = M("SqlsrvModel:");
	}
	/***************************************按征订单发放******************************************/
	//按征订单发放-列表
	public function orderIssue(){
		if($this->_hasJson){
			$bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
					":courseno"=>doWithBindStr($_POST["courseno"]),":coursename"=>doWithBindStr($_POST["coursename"]),
					":school"=>doWithBindStr($_POST["school"]),":classno"=>doWithBindStr($_POST["classno"]));
				
			$arr = array("total"=>0, "rows"=>array());
			$sql = $this->model->getSqlMap("Book/Issue/Q_applyList.sql");
            if($_POST["status"]!="0"){
                $sql.=" and b.status = :status";
                $bind[":status"]=$_POST["status"];
            }else{
                $sql.=" and (b.status='1' or b.status='3') ";
            }

			$count = $this->model->sqlCount($sql,$bind,true);
			$arr["total"] = intval($count);
			if($arr["total"] > 0){
                $sql.=" order by courseno";
				$sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//开课学院
		$this->assign("school",M("schools")->select());
		$this->display();
	}
    //统一发放
    public function issue(){
        $row=false;
        foreach($_POST["ary"] as $val){
            $count=0;
            $sql= $this->model->getSqlMap("Book/Issue/stedentIssueQuery.sql");
            $data = $this->model->sqlQuery($sql,array(":year"=>$val["year"],":term"=>$val["term"],":apply_id"=>$val["apply_id"]));
            if($data!=null){
                foreach($data as $s){
                    $bind = array(":book_id"=>$val["book_id"],":year"=>$val["year"],
                        ":term"=>$val["term"],":no"=>$s["studentno"],
                        ":name"=>$s["name"],":price"=>$val["price"],
                        ":dis_rate"=>$val["dis_rate"],":dis_price"=>$val["dis_price"],":type"=>'0',":apply_id"=>$val["apply_id"]);

                    $sql = $this->model->getSqlMap("Book/Issue/insertPayment.sql");
                    $bool=$this->model->sqlExecute($sql,$bind);
                    if($bool) $count++;
                }
                if($count > 0){
                    $sql="update bookapply set issue_nym =:issue_nym,status=:status where apply_id=:apply_id";
                    $row=$this->model->sqlExecute($sql,array(":issue_nym"=>$count,":status"=>3,":apply_id"=>$val["apply_id"]));
                }
            }
        }
        echo $row;
    }
    //初始化发放
    public function initIssue(){
        $row=false; $year=trim($_POST["year"]);$term=trim($_POST["term"]);
        $apply=$this->model->sqlQuery("select a.*,p.price,p.dis_price,p.dis_rate from bookapply a left join bookprice p on a.book_id=p.book_id and a.[year]=p.[year] and a.term=p.term where a.year=:year and a.term =:term and a.status=1 and p.price is not null",array(":year"=>$year,":term"=>$term));
        if($apply != null){
            set_time_limit(0);
            foreach($apply as $a){
                $count=0;
                $sql = $this->model->getSqlMap("Book/Issue/stedentIssueQuery.sql");
                $data = $this->model->sqlQuery($sql,array(":year"=>$a["year"],":term"=>$a["term"],":apply_id"=>$a["apply_id"]));
                if($data!=null){
                    foreach($data as $d){
                        $bind = array(":book_id"=>$a["book_id"],":year"=>$a["year"],
                            ":term"=>$a["term"],":no"=>$d["studentno"],
                            ":name"=>$d["name"],":price"=>$a["price"],
                            ":dis_rate"=>$a["dis_rate"],":dis_price"=>$a["dis_price"],":type"=>'0',":apply_id"=>$a["apply_id"]);
                        $sql = $this->model->getSqlMap("Book/Issue/insertPayment.sql");
                        $bool=$this->model->sqlExecute($sql,$bind);
                        if($bool) $count++;
                    }
                    if($count > 0){
                        $sql="update bookapply set issue_nym =:issue_nym,status=:status where apply_id=:apply_id";
                        $row=$this->model->sqlExecute($sql,array(":issue_nym"=>$count,":status"=>3,":apply_id"=>$a["apply_id"]));
                    }
                }
            }
        }
        echo $row;
    }
    //更新教材价格
    public function updatePeice(){
        $price=floatval(sprintf("%.2f",$_POST["price"]));
        $dis_rate=floatval($_POST["dis_rate"]);
        $sum=$price*$dis_rate;
        $dis_price=sprintf("%.2f",(string)$sum);
        $book_id=$_POST["book_id"];$year=$_POST["year"];$term=$_POST["term"];

        $sql="update bookprice set price=:price,dis_rate=:dis_rate,dis_price=:dis_price".
            " where book_id=:book_id and [year]=:year and term=:term";

        $row=$this->model->sqlExecute($sql,array(":price"=>$price,":dis_rate"=>$dis_rate,":dis_price"=>$dis_price,":book_id"=>$book_id,":year"=>$year,":term"=>$term));
        if($row) echo true;
        else echo false;
    }
    //学生发放列表页
    public function issueByApplyId(){
        if($this->_hasJson){
            $bind = array(":applyId"=>$_POST["apply_id"],":name"=>doWithBindStr($_POST["name"]),":studentno"=>doWithBindStr($_POST["studentno"]));
            $arr = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("Book/Issue/Q_studentByApplyId.sql");
            $count = $this->model->sqlCount($sql,$bind,true);
            $arr["total"] = intval($count);

            if($arr["total"] > 0){
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $arr["rows"] = $this->model->sqlQuery($sql,$bind);
            }
            $this->ajaxReturn($arr,"JSON");
            exit;
        }
        $this->assign("apply_id",$_GET["apply_id"]);
        $courseno=substr($_GET["courseno"],0,7);
        $this->assign("course",$this->model->sqlFind("select courseno,coursename from courses where courseno =:courseno",array(":courseno"=>$courseno)));
        $this->display();
    }
    /***************************************按教材号发放******************************************/
	//按教材号发放-列表页
	public function bookIssue(){
		if($this->_hasJson){
			$bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
					":isbn"=>doWithBindStr($_POST["isbn"]),":bookname"=>doWithBindStr($_POST["bookname"]) );
				
			$arr = array("total"=>0, "rows"=>array());
			$sql = $this->model->getSqlMap("Book/Issue/Q_bookList.sql");
            $count = $this->model->sqlCount($sql,$bind,true);
            $arr["total"] = intval($count);
				
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		$this->display();
	}
    //按教材号发放type=0:按班级号发放，否则按学号发放
    public function bookNoIssue(){
        $value=trim($_POST["value"]);
        $type=intval(trim($_POST["type"]));
        $isbn=trim($_POST["isbn"]);
        $year=trim($_POST["year"]);
        $term=trim($_POST["term"]);

        $count = 0;
        if($type==0){
            $value.="%";
            $count = $this->model->sqlCount("select count(*) from classes where classno like :value",array(":value"=>$value));
        }else{
            $count = $this->model->sqlCount("select count(*) from students where studentno=:value",array(":value"=>$value));
        }
        if(intval($count) == 0){
            echo "-1";
        }else{
            $book = $this->model->sqlFind("select b.book_id,p.dis_price,p.dis_rate,p.price from book b left join bookprice p on b.book_id=p.book_id  where b.isbn =:isbn order by p.year desc,p.term desc",array(":isbn"=>$isbn));
            if($book["price"]==null){
                echo "-3";
                return;
            }
            if($type==0){
                $sql="select rtrim(s.studentno) studentno,s.name from students s where s.classno like :classno and not exists(select * from bookpayment p where p.year=:year and p.term=:term and p.book_id=:book_id and s.studentno=p.no and p.type=0)";
                $data = $this->model->sqlQuery($sql,array(":classno"=>$value,":year"=>$year,":term"=>$term,":book_id"=>$book["book_id"]));
                if($data!=null){
                    $num=0;
                    foreach($data as $s){
                        $bind = array(":book_id"=>$book["book_id"],":year"=>$year,
                            ":term"=>$term,":no"=>$s["studentno"],
                            ":name"=>$s["name"],":price"=>$book["price"],
                            ":dis_rate"=>$book["dis_rate"],":dis_price"=>$book["dis_price"],":type"=>'0',":apply_id"=>null);

                        $sql = $this->model->getSqlMap("Book/Issue/insertPayment.sql");
                        $bool=$this->model->sqlExecute($sql,$bind);
                        if($bool) $num++;
                    }
                    if($num>0) echo "1"; else echo "-2";
                }
            }else{
                $count = $this->model->sqlCount("select count(*) from studentbook where book_id=:book_id and [year]=:year and term=:term and studentno=:studentno",
                    array(":book_id"=>$book["book_id"],":year"=>$year,":term"=>$term,":studentno"=>$value));
                if(intval($count) > 0){
                    echo "-2";
                }else{
                    $student=$this->model->sqlFind("select name from students where studentno=:studentno",array(":studentno"=>$value));
                    if($student!=null){
                        $bind = array(":book_id"=>$book["book_id"],":year"=>$year,
                            ":term"=>$term,":no"=>$value,
                            ":name"=>$student["name"],":price"=>$book["price"],
                            ":dis_rate"=>$book["dis_rate"],":dis_price"=>$book["dis_price"],":type"=>'0',":apply_id"=>null);

                        $sql = $this->model->getSqlMap("Book/Issue/insertPayment.sql");
                        $bool=$this->model->sqlExecute($sql,$bind);
                        if($bool) echo "1";
                    }
                }
            }
        }
    }
	//获取教材信息
	public function getBook(){
		$isbn=trim($_POST["isbn"]);
		$book = $this->model->sqlFind("select b.bookname,p.price from book b left join bookprice p on p.book_id=b.book_id where isbn =:isbn and status=0",array(":isbn"=>$isbn));
		if($book!=null){
			echo json_encode($book);
		}
	}
    //按教材ID查询发放信息
    public function issueByBookId(){
        if($this->_hasJson){
            $classno=trim($_POST["classno"]);
            $classno=$classno==""?"%":$classno;
            $bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],":book_id"=>$_POST["book_id"],
                ":name"=>doWithBindStr($_POST["name"]),":studentno"=>doWithBindStr($_POST["studentno"]),":classno"=>$classno);
            $arr = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("Book/Issue/Q_studentByBookId.sql");
            $count = $this->model->sqlCount($sql,$bind,true);
            $arr["total"] = intval($count);

            if($arr["total"] > 0){
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $arr["rows"] = $this->model->sqlQuery($sql,$bind);
            }
            $this->ajaxReturn($arr,"JSON");
            exit;
        }
        $this->assign("classno",trim($_GET["classno"]));
        $this->assign("year",$_GET["year"]);
        $this->assign("term",$_GET["term"]);
        $this->assign("book",$this->model->sqlFind("select * from book where book_id=:book_id",array(":book_id"=>$_GET["book_id"])));
        $this->display();
    }
    /***************************************学生教材发放******************************************/
    //学生教材发放-列表
    public function studentIssue(){
        if($this->_hasJson){
            $bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],":studentno"=>doWithBindStr($_POST["studentno"]),
                ":classno"=>doWithBindStr($_POST["classno"]),":school"=>doWithBindStr($_POST["school"]) );
            $arr = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("Book/Issue/Q_studentIssue.sql");
            $count = $this->model->sqlCount($sql,$bind,true);
            $arr["total"] = intval($count);

            if($arr["total"] > 0){
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $arr["rows"] = $this->model->sqlQuery($sql,$bind);
            }
            $this->ajaxReturn($arr,"JSON");
            exit;
        }
        //开课学院
        $this->assign("school",M("schools")->select());
        $this->display();
    }
    //学生教材查看
    public function issueByStudent(){
        if($this->_hasJson){
            $bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],":studentno"=>doWithBindStr($_POST["studentno"]));
            $arr = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("Book/Issue/Q_bookByStudent.sql");
            $count = $this->model->sqlCount($sql,$bind,true);
            $arr["total"] = intval($count);

            if($arr["total"] > 0){
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $arr["rows"] = $this->model->sqlQuery($sql,$bind);
            }
            $this->ajaxReturn($arr,"JSON");
            exit;
        }
        $year=$_GET["year"];$term=$_GET["term"];$studentno=$_GET["studentno"];$add=trim($_GET["add"]);
        //学生教材价格信息
        $sql="select count(*) number,CAST(sum(price) as decimal(38, 2)) price,CAST(sum(dis_price) as decimal(38, 2)) dis_price from studentbook where studentno=:studentno and [year]=:year and term=:term";
        $data = $this->model->sqlFind($sql,array(":studentno"=>$studentno,":year"=>$year,":term"=>$term));
        $this->assign("data",$data);
        //学生个人信息
        $sql="select s.studentno,s.name,c.classname,c.classno,sch.name schoolname from students s left join classes c on c.classno=s.classno left join schools sch on sch.school=s.school where studentno=:studentno";
        $stu = $this->model->sqlFind($sql,array(":studentno"=>$studentno));
        $this->assign("stu",$stu);
        //学生购买教材ID列表
        $sql="select book_id from studentbook where studentno=:studentno and [year]=:year and term=:term group by book_id";
        $list = $this->model->sqlQuery($sql,array(":studentno"=>$studentno,":year"=>$year,":term"=>$term));
        $this->assign("list",json_encode($list));
        $this->assign("year",$year);
        $this->assign("term",$term);
        $this->assign("studentno",$studentno);
        $this->assign("add",$add);
        $this->display();
    }
    //检索出所有有价格信息的教材或班级征订单
    public function getBookList(){
        $type=$_POST["type"];
        $key=doWithBindStr($_POST["key"]);
        if($type=="isbn"){
            $bind = array(":isbn"=>$key);
            $sql = $this->model->getSqlMap("Book/Issue/Q_bookByIsbn.sql");
        }else{
            $bind = array(":classno"=>$key,":year"=>$_POST["year"],":term"=>$_POST["term"]);
            $sql = $this->model->getSqlMap("Book/Issue/Q_applyByStudent.sql");
        }
        $arr = array("total"=>0, "rows"=>array());
        $count = $this->model->sqlCount($sql,$bind,true);
        $arr["total"] = intval($count);
        if($arr["total"] > 0){
            $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
            $arr["rows"] = $this->model->sqlQuery($sql,$bind);
        }
        $this->ajaxReturn($arr,"JSON");
    }
    //通过学号获取学生个人教材列表
    public function getStuBook(){
        $bind = array(":studentno"=>$_POST["studentno"],":year"=>$_POST["year"],":term"=>$_POST["term"]);
        $arr = array("total"=>0, "rows"=>array());
        $sql = $this->model->getSqlMap("Book/Issue/Q_stuBookList.sql");
        $count = $this->model->sqlCount($sql,$bind,true);
        $arr["total"] = intval($count);
        if($arr["total"] > 0){
            $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
            $arr["rows"] = $this->model->sqlQuery($sql,$bind);
        }
        $this->ajaxReturn($arr,"JSON");
    }
    //保存学生、教师---教材列表
    public function savebooklist(){
        if($this->_hasJson){
            $num="";
            $ids=$_POST["ids"];
            $delId=trim($_POST["delId"]);
            $year=$_POST["year"];$term=$_POST["term"];
            $studentno=trim($_POST["studentno"]);
            $teacherno=trim($_POST["teacherno"]);
            $name=$_POST["name"];
            $type=0;$no=$studentno;
            if($teacherno!=null && $teacherno!=""){
                $type=1;$no=$teacherno;
            }
            if(!is_array($ids) || count($ids)==0){
                $num="-1";
            }else{
                $i=0;
                foreach($ids as $id){
                    $bind = array(":isbn"=>"%",":book_id"=>$id);
                    $sql = $this->model->getSqlMap("Book/Issue/Q_bookByIsbn.sql");
                    $data = $this->model->sqlFind($sql." where b.book_id=:book_id",$bind);
                    $bind = array(":book_id"=>$data["book_id"],":year"=>$year,
                        ":term"=>$term,":no"=>$no,
                        ":name"=>$name,":price"=>$data["price"],
                        ":dis_rate"=>$data["dis_rate"],":dis_price"=>$data["dis_price"],":type"=>$type,":apply_id"=>null);
                    $sql = $this->model->getSqlMap("Book/Issue/insertPayment.sql");
                    $bool=$this->model->sqlExecute($sql,$bind);
                    if($bool) $i++;
                }
                if($i > 0) $num="1";
                else $num="-1";
            }
            if($delId!=""){
                $delId=rtrim($delId,",");
                $sql="delete from bookpayment where [year]=:year and term=:term and type=:type and [no]=:no and  book_id in ($delId)";
                $row=$this->model->sqlExecute($sql,array(":year"=>$year,":term"=>$term,":type"=>$type,":no"=>$no));
                if($row) $num= "1";
                else $num= "-1";
            }
            echo $num;
        }
    }
    /***************************************教师教材发放******************************************/
    //教师教材发放-列表
    public function teacherIssue(){
        if($this->_hasJson){
            $bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],":p_year"=>$_POST["year"],":p_term"=>$_POST["term"],
                ":school"=>doWithBindStr($_POST["school"]),
                ":teacherno"=>doWithBindStr($_POST["teacherno"]),":name"=>doWithBindStr($_POST["name"]) );

            $arr = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("Book/Issue/Q_teacherIssue.sql");
            $count = $this->model->sqlCount($sql,$bind,true);
            $arr["total"] = intval($count);

            if($arr["total"] > 0){
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $arr["rows"] = $this->model->sqlQuery($sql,$bind);
            }
            $this->ajaxReturn($arr,"JSON");
            exit;
        }
        //开课学院
        $this->assign("school",M("schools")->select());
        $this->display();
    }
    //教师教材查看
    public function issueByTeacher(){
        if($this->_hasJson){
            $bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],":teacherno"=>doWithBindStr($_POST["teacherno"]));
            $arr = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("Book/Issue/Q_bookByTeacher.sql");
            $count = $this->model->sqlCount($sql,$bind,true);
            $arr["total"] = intval($count);
            if($arr["total"] > 0){
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $arr["rows"] = $this->model->sqlQuery($sql,$bind);
            }
            $this->ajaxReturn($arr,"JSON");
            exit;
        }
        $year=$_GET["year"];$term=$_GET["term"];$teacherno=$_GET["teacherno"];
        //教师教材价格信息
        $sql="select count(*) number,sum(price) price,sum(dis_price) dis_price from teacherbook where teacherno=:teacherno and [year]=:year and term=:term";
        $data = $this->model->sqlFind($sql,array(":teacherno"=>$teacherno,":year"=>$year,":term"=>$term));
        $this->assign("data",$data);
        //教师个人信息
        $sql="select t.teacherno,t.name,t.school,s.name schoolname from teachers t left join schools s on s.school=t.school where t.teacherno = :teacherno";
        $teacher = $this->model->sqlFind($sql,array(":teacherno"=>$teacherno));
        $this->assign("teacher",$teacher);
        //教师购买教材ID列表
        $sql="select book_id from teacherbook  where teacherno=:teacherno and [year]=:year and term=:term group by book_id";
        $list = $this->model->sqlQuery($sql,array(":teacherno"=>$teacherno,":year"=>$year,":term"=>$term));
        $this->assign("list",json_encode($list));
        $this->assign("year",$year);
        $this->assign("term",$term);
        $this->assign("teacherno",$teacherno);
        $this->display();
    }
    //检索出所有有价格信息的教材或征订单
    public function getBookListByTeacher(){
        $type=trim($_POST["type"]);
        $isbn=doWithBindStr($_POST["isbn"]);
        if($type=="teacher"){
            $bind = array(":isbn"=>$isbn,":teacherno"=>$_POST["teacherno"],":year"=>$_POST["year"],":term"=>$_POST["term"]);
            $sql = $this->model->getSqlMap("Book/Issue/Q_bookByApply.sql");
        }else{
            $bind = array(":isbn"=>$isbn);
            $sql = $this->model->getSqlMap("Book/Issue/Q_bookByIsbn.sql");
        }
        $arr = array("total"=>0, "rows"=>array());
        $count = $this->model->sqlCount($sql,$bind,true);
        $arr["total"] = intval($count);
        if($arr["total"] > 0){
            $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
            $arr["rows"] = $this->model->sqlQuery($sql,$bind);
        }
        $this->ajaxReturn($arr,"JSON");
    }
    //通过教师号获取教师个人教材列表
    public function getTeacherBook(){
        $bind = array(":teacherno"=>$_POST["teacherno"],":year"=>$_POST["year"],":term"=>$_POST["term"]);
        $arr = array("total"=>0, "rows"=>array());
        $sql = $this->model->getSqlMap("Book/Issue/Q_teacherBookList.sql");
        $count = $this->model->sqlCount($sql,$bind,true);
        $arr["total"] = intval($count);
        if($arr["total"] > 0){
            $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
            $arr["rows"] = $this->model->sqlQuery($sql,$bind);
        }
        $this->ajaxReturn($arr,"JSON");
    }
    /***************************************班级教材发放******************************************/
    //班级教材发放-列表
    public function classIssue(){
        if($this->_hasJson){
            $bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
                ":classname"=>doWithBindStr($_POST["classname"]),":classno"=>doWithBindStr($_POST["classno"]) );

            $arr = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("Book/Issue/Q_classIssue.sql");
            $count = $this->model->sqlCount($sql,$bind,true);
            $arr["total"] = intval($count);

            if($arr["total"] > 0){
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $arr["rows"] = $this->model->sqlQuery($sql,$bind);
            }
            $this->ajaxReturn($arr,"JSON");
            exit;
        }
        $this->display();
    }
    //班级教材查看
    public function issueByClass(){
        if($this->_hasJson){
            $bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],":classno"=>doWithBindStr($_POST["classno"]));
            $arr = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("Book/Issue/Q_bookByClass.sql");
            $count = $this->model->sqlCount($sql,$bind,true);
            $arr["total"] = intval($count);
            if($arr["total"] > 0){
                $sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
                $arr["rows"] = $this->model->sqlQuery($sql,$bind);
            }
            $this->ajaxReturn($arr,"JSON");
            exit;
        }
        //班级
        $class = $this->model->sqlFind("select classno,classname from classes where classno like :classno",array(":classno"=>$_GET["classno"]));
        $this->assign("class",$class);
        $this->assign("year",$_GET["year"]);
        $this->assign("term",$_GET["term"]);
        $this->display();
    }
    /***************************************公用函数******************************************/
   //更新学生发放教材折扣率
    public function updateDiscount(){
        $price=floatval(sprintf("%.2f",$_POST["price"]));
        $dis_rate=floatval($_POST["dis_rate"]);

        $sum=$price*$dis_rate;
        $dis_price=sprintf("%.2f",(string)$sum);

        $payment_id=$_POST["payment_id"];
        $sql="update bookpayment set dis_rate=:dis_rate,dis_price=:dis_price where payment_id=:payment_id";
        $row=$this->model->sqlExecute($sql,array(":dis_rate"=>$dis_rate,":dis_price"=>$dis_price,":payment_id"=>$payment_id));
        if($row) echo true;
        else echo false;
    }
    //不发放
    public function delPayment(){
        $newids="";
        $ids=$_POST["ids"];
        $classno=trim($_POST["classno"]);
        if($this->_hasJson){
            $bind=array(":year"=>$_POST["year"],":term"=>$_POST["term"]);
            //撤销教材发放
            if(is_array($ids) && count($ids) > 0){
                foreach($ids as $val){
                    $newids.=$val.",";
                }
                $newids=rtrim($newids,",");
                if($classno==""){
                    $sql="delete from bookpayment where type=0 and year=:year and term=:term and book_id in ($newids)";
                }else{
                    $sql="delete from bookpayment where type=0 and year=:year and term=:term and book_id in ($newids) and no in ".
                    "(select studentno from students where classno=:classno)";
                    $bind[":classno"]=$classno;
                }
                $row=$this->model->sqlExecute($sql,$bind);
                echo $row;
            }else{
                //撤销班级教材发放
                $cids=$_POST["cids"];
                if(!is_array($cids) || count($cids) == 0){
                    echo false;
                    return;
                }
                foreach($cids as $val){
                    $newids.="'".$val."',";
                }
                $newids=rtrim($newids,",");
                $sql="delete from bookpayment where type=0 and year=:year and term=:term and no in ".
                    "(select studentno from students where classno in($newids))";
                $row=$this->model->sqlExecute($sql,$bind);
                echo $row;
            }
        }else{
            //不发放
            foreach($ids as $val){
                $newids.=$val.",";
            }
            $newids=rtrim($newids,",");
            $row=$this->model->sqlExecute("delete from bookpayment where payment_id in ($newids)");
            echo $row;
        }
    }
    //新增发放
    public function addPayment(){
        if($this->_hasJson){
            //按教材ID根据学号
            $studentno=$_POST["studentno"];
            $bookId=trim($_POST["book_id"]);
            $count = $this->model->sqlCount("select count(*) from students where studentno=:studentno",array(":studentno"=>$studentno));
            if(intval($count) < 1){
                echo "-1";
            }else{
                if($bookId!=null){
                    $count = $this->model->sqlCount("select count(*) from bookpayment b where book_id=:book_id and b.[no]=:studentno and type=0 and year=:year and term=:term",
                        array(":book_id"=>$bookId,":studentno"=>$studentno,":year"=>$_POST["year"],":term"=>$_POST["term"]));
                    if(intval($count) > 0){
                        echo "-2";
                    }else{
                        $student=$this->model->sqlFind("select name from students where studentno=:studentno",array(":studentno"=>$studentno));
                        $data=$this->model->sqlFind("select price,dis_rate,dis_price from bookprice where book_id=:book_id and year=:year and term=:term",
                            array(":book_id"=>$bookId,":year"=>$_POST["year"],":term"=>$_POST["term"]));
                        if($data!=null){
                            $bind = array(":book_id"=>$bookId,":year"=>$_POST["year"],
                                ":term"=>$_POST["term"],":no"=>$studentno,
                                ":name"=>$student["name"],":price"=>$data["price"],
                                ":dis_rate"=>$data["dis_rate"],":dis_price"=>$data["dis_price"],":type"=>'0',":apply_id"=>null);

                            $sql = $this->model->getSqlMap("Book/Issue/insertPayment.sql");
                            $bool=$this->model->sqlExecute($sql,$bind);
                            if($bool)echo "1";
                            else echo "0";
                        }
                    }
                }else{
                    echo "0";
                }
            }
        }else{
            //按征订单根据学号
            $studentno=$_POST["studentno"];
            $applyId=trim($_POST["apply_id"]);
            $count = $this->model->sqlCount("select count(*) from students where studentno=:studentno",array(":studentno"=>$studentno));
            if(intval($count) < 1){
                echo "-1";
            }else{
                if($applyId!=null){
                    $count = $this->model->sqlCount("select count(*) from bookpayment b where apply_id=:apply_id and b.[no]=:studentno",array(":apply_id"=>$applyId,":studentno"=>$studentno));
                    if(intval($count) > 0){
                        echo "-2";
                    }else{
                        $student=$this->model->sqlFind("select name from students where studentno=:studentno",array(":studentno"=>$studentno));
                        $data=$this->model->sqlFind("select b.[year],b.term,b.book_id,p.price,p.dis_rate,p.dis_price from bookapply b ".
                            "left join bookprice p on p.book_id = b.book_id and p.[year]=b.[year] and p.term =b.term  where b.apply_id=:apply_id",array(":apply_id"=>$applyId));
                        if($data!=null){
                            $bind = array(":book_id"=>$data["book_id"],":year"=>$data["year"],
                                ":term"=>$data["term"],":no"=>$studentno,
                                ":name"=>$student["name"],":price"=>$data["price"],
                                ":dis_rate"=>$data["dis_rate"],":dis_price"=>$data["dis_price"],":type"=>'0',":apply_id"=>$applyId);

                            $sql = $this->model->getSqlMap("Book/Issue/insertPayment.sql");
                            $bool=$this->model->sqlExecute($sql,$bind);
                            if($bool)echo "1";
                            else echo "0";
                        }
                    }
                }else{
                    echo "0";
                }
            }
        }
    }

}