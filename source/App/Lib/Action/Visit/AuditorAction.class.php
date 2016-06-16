<?php
/**
 * 旁听管理
 * @author shencp
 * Date: 14-06-05
 * Time: 下午13:40
 */
class AuditorAction extends RightAction {
	private $model;
	/**
	 * 构造
	 **/
	public function __construct(){
		parent::__construct();
		$this->model = M("SqlsrvModel:");
	}
	
	/**
	 * 查询选课明细记录
	 */
	public function detail(){
		if($this->_hasJson){
			$bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
					":studentno"=>doWithBindStr($_POST["studentno"]));
				
			$arr = array("total"=>0, "rows"=>array());
			$sql = $this->model->getSqlMap("Visit/queryDetailCount.sql");
			$count = $this->model->sqlCount($sql,$bind);
			$arr["total"] = intval($count);
				
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Visit/queryDetailList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		$this->display();
	}
	
	/**
	 * 查询选课统计表
	 */
	public function statistical(){
		if($this->_hasJson){
			$bind = array(":YEAR"=>$_POST["year"],":TERM"=>$_POST["term"]);
				
			$arr = array("total"=>0, "rows"=>array());
			$sql = $this->model->getSqlMap("Visit/queryStatisticalCount.sql");
			$count = $this->model->sqlCount($sql,$bind);
			$arr["total"] = intval($count);
				
			if($arr["total"] > 0){
				$sql = $this->model->getPageSql(null,"Visit/queryStatisticalList.sql", $this->_pageDataIndex, $this->_pageSize);
				$arr["rows"] = $this->model->sqlQuery($sql,$bind);
			}
			$this->ajaxReturn($arr,"JSON");
			exit;
		}
		//获取当前学年学期
		$yearTerm = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
		$this->assign("yearTerm",$yearTerm);
		$this->display();
	}
}