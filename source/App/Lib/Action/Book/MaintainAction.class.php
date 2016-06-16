<?php
/**
 * 教材管理--教材维护
 * @author shencp
 * Date: 14-04-23
 * Time: 上午09:32
 */
class MaintainAction extends RightAction {
	private $model;
	/**
	 * 构造
	 **/
	public function __construct(){
		parent::__construct();
		$this->model = M("SqlsrvModel:");
	}
	/**
	 * 教材信息
	 */
	public function bookList(){
		if($this->_hasJson){
			$bind = array(":isbn"=>doWithBindStr($_POST["isbn"]),
					":bookname"=>doWithBindStr($_POST["bookname"]),
					":status"=>doWithBindStr($_POST["status"]),
					":booknature"=>doWithBindStr($_POST["booknature"]));
			$arr = array("total"=>0, "rows"=>array());
				
			$sql = $this->model->getSqlMap("Book/Maintain/queryBookList.sql");
			$count = $this->model->sqlCount($sql,$bind,true);
			$arr["total"] = intval($count);
				
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql($sql,null,$this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		$this->display();
	}
	
	/**
	 * 更新教材
	 */
	public function updateBook(){
		if($this->_hasJson){
			$ary=array(":isbn"=>$_POST["isbn"],":bookname"=>$_POST["bookname"],":author"=>$_POST["author"],
					":press"=>$_POST["press"],":pubtime"=>trim($_POST["pubtime"]),":booknature"=>$_POST["booknature"],
					":dis_rate"=>$_POST["dis_rate"],":status"=>$_POST["status"]);
			$sql=$this->model->getSqlMap("Book/insertBook.sql");
			$row=$this->model->sqlExecute($sql,$ary);
			if($row) echo true;
			else echo false;
		}else{
			$dis_rate=floatval($_POST["dis_rate"]);
			$status=$_POST["status"];
			$press=$_POST["press"];
			
			$ary=array(":isbn"=>$_POST["isbn"],":bookname"=>$_POST["bookname"],":author"=>$_POST["author"],
					":pubtime"=>trim($_POST["pubtime"]),":booknature"=>$_POST["booknature"],
					":dis_rate"=>$dis_rate,":book_id"=>$_POST["book_id"]);
			
			$sql=$this->model->getSqlMap("Book/Maintain/updateBook.sql");
			
			if(is_numeric($press)){
				$sql.=",press=$press";
			}
			if(strlen($status) < 3){
				$sql.=",status=$status";
			}
			$sql.=" where book_id=:book_id";
			
			$bool=$this->model->sqlQuery($sql,$ary);
			if($bool===false) echo false;
			else echo true;
		}
	}
	
	/**
	 * 删除教材
	 */
	public function delBook(){
		$newids="";
		$i=0;$j=0;
		foreach($_POST["in"] as $val){
			$sql="select count(*) [count] from bookapply b where b.book_id=$val";
			$apply=$this->model->sqlFind($sql);
			if($apply["count"] > 0){
				$i++;
			}else{
				$j++;
				$newids.="'".$val."',";
			}
		}
		if($newids!=""){
			$newids=rtrim($newids,",");
            //删除教材
			$sql="delete from book where book_id in($newids);";
            //删除价格
			$sql.="delete from bookprice where book_id in($newids);";
            //删除征订记录教师信息
			$sql.="delete from bookteacher where book_id in($newids);";
            //开始删除
            $this->model->startTrans();
			$row=$this->model->sqlExecute($sql);
			if($row){
                $this->model->commit();
				$data["failure"]=$i;
				$data["succeed"]=$j;
				echo json_encode($data);
			}else{
                $this->model->rollback();
                echo false;
            }
		}else{
			$data["failure"]=-1;
			echo json_encode($data);
		}
	}
	
	/**
	 * 查询isbn是否存在
	 */
	public function getIsbnCount(){
		if($this->_hasJson){
			$bind=array(":book_id"=>$_POST["id"],":isbn"=>$_POST["isbn"]);
			$sql="select count(*) [count] from book where book_id != :book_id and isbn=:isbn";
			$data=$this->model->sqlFind($sql,$bind);
			echo $data["count"];
		}else{
			$bind=array(":isbn"=>$_POST["VALUE"]);
			$sql=$this->model->getSqlMap("Book/getIsbnCount.sql");
			$data=$this->model->sqlFind($sql,$bind);
			echo $data["count"];
		}
	}
	
	/**
	 * 教材出版社
	 */
	public function pressList(){
		if($this->_hasJson){
			$bind = array(":name"=>doWithBindStr($_POST["name"]),":status"=>doWithBindStr($_POST["status"]));
			$arr = array("total"=>0, "rows"=>array());
			
			$sql = $this->model->getSqlMap("Book/Maintain/queryPressList.sql");
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
	/**
	 * ajax 获取出版社
	 */
	public function ajaxPress(){
		$q=trim($_POST["q"]);
		$sql="select id,name from bookpress where name like :name and status='0'";
		$data=$this->model->sqlQuery($sql,array(":name"=>$q."%"));
		echo json_encode($data);
	}
	/**
	 * 更新出版社数据
	 */
	public function updatePress(){
		if($this->_hasJson){
            $bind=array(":name"=>trim($_POST["name"]));
			$sql="update bookpress set name=:name";
			$status=trim($_POST["status"]);
			if(strlen($status) < 3){
				$sql.=",status=:status";
                $bind[":status"]=$status;
			}
			$sql.=" where id=:id";
            $bind[":id"]=$_POST["id"];
			$row=$this->model->sqlExecute($sql,$bind);
			if($row) echo true;
			else echo false;
		}else{
			$ary=array(":name"=>$_POST["name"],":status"=>$_POST["status"]);
			$sql="insert into bookpress(name,status) values(:name,:status)";
			$row=$this->model->sqlExecute($sql,$ary);
			if($row) echo true;
			else echo false;
		}
	}
	/**
	 * 删除出版社
	 */
	public function delPress(){
		$newids="";
		$i=0;$j=0;
		foreach($_POST["ids"] as $val){
			$sql="select count(*) [count] from book b where b.press=$val";
			$apply=$this->model->sqlFind($sql);
			if($apply["count"] > 0){
				$i++;
			}else{
				$j++;
				$newids.="'".$val."',";
			}
		}
		if($newids!=""){
			$newids=rtrim($newids,",");
			$sql="delete from bookpress where id in($newids)";
			$row=$this->model->sqlExecute($sql);
			if(true){
				$data["failure"]=$i;
				$data["succeed"]=$j;
				echo json_encode($data);
			}else echo false;
		}else{
			$data["failure"]=-1;
			echo json_encode($data);
		}
	}
	/**
	 * 教材价格维护
	 */
	public function priceList(){
		if($this->_hasJson){
			$sql_="";
			$year=$_POST["year"];
			$term=$_POST["term"];
			$status=trim($_POST["status"]);
			$booknature=$_POST["booknature"];
			if($year!="" && $booknature!="自编") $sql_.=" and [year] = $year";
			if($term!=""  && $booknature!="自编") $sql_.=" and term = $term";
			if($status!="") $sql_.=$status=="0"?" and price is not null":" and price is null";
			if($booknature=="自编" && $year!="" && $term!=null){
				$zb="ZB";
				$bind = array(":year"=>$year,":term"=>$term,":n_year"=>$year,":n_term"=>$term,
						":isbn"=>doWithBindStr($_POST["isbn"]),":bookname"=>doWithBindStr($_POST["bookname"]),
						":booknature"=>doWithBindStr("$booknature"));
			}else{
				$zb="";
				$bind = array(":isbn"=>$_POST["isbn"],":bookname"=>$_POST["bookname"],
						":booknature"=>"$booknature");
			}
			
			$arr = array("total"=>0, "rows"=>array());
			$sql = $this->model->getSqlMap("Book/Maintain/query".$zb."PriceCount.sql");
			
			$count = $this->model->sqlCount($sql.$sql_,$bind);
			$arr["total"] = intval($count);
			
			if($arr["total"] > 0){
				$sql = $this->model->getSqlMap("Book/Maintain/query".$zb."PriceList.sql");
				$order=" order by [year] desc,term desc";
				$sql = $this->model->getPageSql($sql.$sql_.$order,null, $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		$this->display();
	}
	/**
	 * 更新教材价格信息
	 */
	public function updatePrice(){
		$id=trim($_POST["id"]);
		$price=floatval(sprintf("%.2f",$_POST["price"]));//价格
		$dis_rate=floatval($_POST["dis_rate"]);//折扣
		
		$sum=$price*$dis_rate;
		$dis_price=sprintf("%.2f",(string)$sum);//折扣价
		
		if($id!=""){
            $this->model->startTrans();
            //更新教材价格信息
            $sql="update bookprice set price=:price,dis_rate=:dis_rate,dis_price=:dis_price where id=:id";
			$bookprice=$this->model->sqlExecute($sql,array(":price"=>$price,":dis_rate"=>$dis_rate,":dis_price"=>$dis_price,":id"=>$id));
            //更新已发放学生价格信息
            $bp = $this->model->sqlFind("select * from bookprice  where id=:id",array(":id"=>$id));
            $p_sql="update bookpayment set price=:price,dis_rate=:dis_rate,dis_price=:dis_price where [year]=:year and term = :term and book_id=:book_id";
            $this->model->sqlExecute($p_sql,array(":price"=>$price,":dis_rate"=>$dis_rate,":dis_price"=>$dis_price,":year"=>$bp["year"],":term"=>$bp["term"],":book_id"=>$bp["book_id"]));
			if($bookprice){
                $this->model->commit();
                echo true;
            }else{
                $this->model->rollback();
                echo false;
            }
		}else{
			$year=trim($_POST["year"]);
			$term=trim($_POST["term"]);
			if($year=="" && $term==""){
				//获取当前学年学期
				$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
				$year=$yearTerm["YEAR"];
				$term=$yearTerm["TERM"];
			}
			
			$sql="insert into bookprice(year,term,price,dis_price,dis_rate,book_id) values(:year,:term,:price,:dis_price,:dis_rate,:book_id)";
			$row=$this->model->sqlExecute($sql,array(":year"=>$year,":term"=>$term,":price"=>$price,":dis_price"=>$dis_price,":dis_rate"=>$dis_rate,":book_id"=>$_POST["book_id"]));
			if($row) echo true;
			else echo false;
		}
	}
	/**
	 * 新增教材价格
	 */
	public function addPrice(){
		$id=trim($_POST["book_id"]);
		$year=trim($_POST["year"]);
		$term=trim($_POST["term"]);
		$price=floatval(sprintf("%.2f",$_POST["price"]));//价格
		$dis_rate=floatval($_POST["dis_rate"]);//折扣
		
		$sum=$price*$dis_rate;
		$dis_price=sprintf("%.2f",(string)$sum);//折扣价
		
		$count = $this->model->sqlCount("select count(*) from bookprice b where b.book_id=:book_id and [year]=:year and term =:term",array(":book_id"=>$id,":year"=>$year,":term"=>$term));
		if(intval($count) > 0){
			echo -1;
		}else{
			$sql="insert into bookprice(year,term,price,dis_price,dis_rate,book_id) values(:year,:term,:price,:dis_price,:dis_rate,:book_id)";
			$row=$this->model->sqlExecute($sql,array(":year"=>$year,":term"=>$term,":price"=>$price,":dis_price"=>$dis_price,":dis_rate"=>$dis_rate,":book_id"=>$id));
			if($row) echo 1;
			else echo -2;
		}
	}
	/**
	 * 获取教材信息
	 */
	public function ajaxGetBook(){
		$isbn=trim($_POST["isbn"]);
		$book = $this->model->sqlFind("select bookname,book_id,dis_rate from book where isbn =:isbn and status=0",array(":isbn"=>$isbn));
		if($book!=null){
			echo json_encode($book);
		}
	}
}