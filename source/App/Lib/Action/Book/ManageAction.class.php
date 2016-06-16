<?php
/**
 * 教材管理 --征订管理
 * @author shencp
 * Date: 14-04-23
 * Time: 上午09:32
 */
class ManageAction extends RightAction {
	private $model;
	/**
	 * 构造
	 **/
	public function __construct(){
		parent::__construct();
		$this->model = M("SqlsrvModel:");
	}
	
	/**
	 * 征订单管理
	 */
	public function applyList(){
		if($this->_hasJson){
			$startTime =$_POST["startTime"];
			$endtime=$_POST["endtime"];
            $school=trim($_POST["school"]);
            $school=$school==""?"%":$school;

			$bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
					":approaches"=>doWithBindStr($_POST["approaches"]),":courseno"=>doWithBindStr($_POST["courseno"]),
					":coursename"=>doWithBindStr($_POST["coursename"]),":school"=>doWithBindStr($school)
					,":classno"=>doWithBindStr($_POST["classno"]),":status"=>doWithBindStr($_POST["status"]));
			
			$arr = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("Book/Manage/queryApplyList.sql");
			if($startTime!="" && $startTime!=null){
                $sql.=" and b.booktime >=cast(:startTime as datetime)";
                $bind[":startTime"]=$startTime;
			}
            if($endtime!="" && $endtime!=null){
                $sql.=" and b.booktime <=cast(:endtime as datetime)";
                $bind[":endtime"]=$endtime;
            }
			$count = $this->model->sqlCount($sql,$bind,true);
			$arr["total"] = intval($count);
			if($arr["total"] > 0){
                $sql.=" order by b.courseno";
				$sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//开课学院
		$this->assign("school",M("schools")->select());
		//修课方式
		$this->assign("approaches",M("courseapproaches")->select());
		//教材出版社
		$press = M("bookpress")->query("select id,name from __TABLE__ where status='0'");
		$this->assign("press",$press);
		//isbn列表
		$isbn = M("book")->query("select isbn from __TABLE__ where booknature='自编' and status=0");
		$this->assign("isbn",$isbn);
		$this->display();
	}
	/**
	 * 删除征订信息
	 */
	public function del(){
		$newids="";
		foreach($_POST["ids"] as $val){
			$newids.="'".$val."',";
		}
		$newids=rtrim($newids,",");
		$sql="delete from bookapply where apply_id in($newids);";
		$sql.="delete from bookpayment where apply_id in($newids);";
		
		$row=$this->model->sqlExecute($sql);
		if($row) echo true;
		else echo false;
	}
	/**
	 * 重置征订状态
	 */
	public function reset(){
		$row=false;
		foreach($_POST["ids"] as $val){
			$sql="";
			$id=$val["apply_id"];
			$status=intval($val["status"]);
			
			if($status==2){
				$sql="update bookapply set status='0' where apply_id='$id'";
			}else{
				$orderno=$this->getOrderNo(1);
				$sql="update bookapply set status='0',booktime=null,oderno='$orderno' where apply_id='$id'";
			}
			
			$row=$this->model->sqlExecute($sql);
		}
		if($row) echo true;
		else echo false;
	}
	
	/**
	 * 获取订单号
	 */
	private function getOrderNo($type){
		$data=$this->model->sqlFind("select orderno from bookorder where type=:type",array(":type"=>$type));
		$orderno=intval($data["orderno"])+1;
	
		$this->model->sqlQuery("update bookorder set orderno=:orderno where type=:type",array(":orderno"=>$orderno,":type"=>$type));
	
		return str_pad($orderno,4,'0',STR_PAD_LEFT);
	}
	
	/**
	 * 浏览征订单
	 */
	public function bookApplyList(){
		if($this->_hasJson){
            $school=trim($_POST["school"]);
            $school=$school==""?"%":$school;

			$bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],":school"=>doWithBindStr($school));
			$arr = array("total"=>0, "rows"=>array());
			
			$sql = $this->model->getSqlMap("Book/Manage/queryBookApplyList.sql");
			$count = $this->model->sqlCount($sql,$bind,true);
			$arr["total"] = intval($count);
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql($sql,null, $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		$this->assign("year",$_GET["year"]);
		$this->assign("term",$_GET["term"]);
		$this->assign("school",$_GET["school"]);
		$this->display();
	}
	/**
	 * 将状态改为暂不征订
	 */
	public function editStatus(){
		$ids=rtrim($_POST["ids"],",");
		$sql="update bookapply set status = '2' where apply_id in ($ids)";
		$row = $this->model->sqlExecute($sql);
		if($row) echo true;
		else echo false;
	}
}