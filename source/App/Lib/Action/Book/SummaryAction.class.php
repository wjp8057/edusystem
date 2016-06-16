<?php
/**
 * 教材管理 --教材结算
 * @author shencp
 * Date: 14-05-27
 * Time: 上午09:32
 */
class SummaryAction extends RightAction {
	private $model;
	/**
	 * 构造
	 **/
	public function __construct(){
		parent::__construct();
		$this->model = M("SqlsrvModel:");
	}
    /************************************************班级教材总汇**********************************************/
    //班级教材总汇-列表
    public function sumClass(){
        if($this->_hasJson){
            $bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],
            ":classno"=>doWithBindStr($_POST["classno"]),":classname"=>doWithBindStr($_POST["classname"]),
            ":school"=>doWithBindStr($_POST["school"]) );

            $arr = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("Book/Summary/Q_summaryByClass.sql");
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
    //班级学生教材发放查看
    public function studentByClassNo(){
        if($this->_hasJson){
            $bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],":studentno"=>doWithBindStr($_POST["studentno"])
            ,":name"=>doWithBindStr($_POST["name"]),":classno"=>doWithBindStr($_POST["classno"]));
            $arr = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("Book/Summary/Q_studentByClassNo.sql");
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
        $class = $this->model->sqlFind("select classno,classname from classes where classno like '{$_GET["classno"]}'");
        $this->assign("class",$class);
        $this->assign("year",$_GET["year"]);
        $this->assign("term",$_GET["term"]);
        $this->display();
    }
    /************************************************学生发放总汇**********************************************/
    //学生发放-列表
    public function sumStudent(){
        if($this->_hasJson){
            $bind = array(":year"=>$_POST["year"],":term"=>$_POST["term"],":studentno"=>doWithBindStr($_POST["studentno"])
            ,":name"=>doWithBindStr($_POST["name"]),":school"=>doWithBindStr($_POST["school"]));

            $arr = array("total"=>0, "rows"=>array());
            $sql = $this->model->getSqlMap("Book/Summary/Q_summaryByStudent.sql");
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
}