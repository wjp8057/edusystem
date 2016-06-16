<?php
/**
 * Created 系统管理Action类
 * User: cwebs
 * Date: 13-11-26
 * Time: 下午1:14
 */
class ManageAction extends RightAction {

    private $md;             //数据库对象实例


    public function __construct(){
        parent::__construct();
        $this->md=new SqlsrvModel();
        $this->assign('quanxian',trim($_SESSION['S_USER_INFO']['ROLES']));
    }
	public function methods() {

        if(isset($_GET['tag']) && ($_GET['tag'] == 'insertme')){
            $model = M ( 'methods' );
            $sql = $model->add ( $_POST );
            varDebug($sql);
            exit($sql>0?'true':'false');
        }
        if(isset($_GET['tag']) && ($_GET['tag'] == 'deleteme')){
            $model = M ( 'methods' );
            $data = array ();
            $data ['METHODID'] = array (
                'in',
                $_POST ['in']
            );
            $arr = $model->where ( $data )->delete ();
            if ($arr)
                echo true;
            else
                echo false;
            return;
        }

		if ($this->_hasJson) {
			/*$xy = M ( 'methods' );
			$arr ['total'] = $xy->where ( $this->mewhere () )->count ();
			if ($arr ['total'] > 0)
				$arr ['rows'] = $xy->where ( $this->mewhere () )->limit ( $this->_pageDataIndex, $this->_pageSize )->select (); // $this->_pageSize 请求多少行(该语句返回查询到的数组)
			else
				$arr ['rows'] = array ();
			$this->ajaxReturn ( $arr, "JSON" );
			exit ();*/
            $arr=array();
            $count=$this->md->sqlfind('select count(*) as ROWS from METHODS WHERE METHODID LIKE :methodid and ROLES like :roles and DESCRIPTION like :sm',
                array(':methodid'=>doWithBindStr($_POST['METHODIDS']),':roles'=>doWithBindStr($_POST['ROLES']),':sm'=>doWithBindStr($_POST['DESCRIPTION'])));
            if($arr['total']=$count['ROWS']){
                $arr['rows']=$count=$this->md->sqlQuery('select * from(select row_number() over(order by METHODID) as row,
                rtrim(METHODID) as METHODID,rtrim(ROLES) as ROLES,rtrim(DESCRIPTION) as DESCRIPTION,rtrim(ACTION_PATH) as ACTION_PATH from METHODS WHERE METHODID LIKE :methodid and ROLES like :roles and DESCRIPTION like :sm)as b where b.row between :start and :end',
                    array(':methodid'=>doWithBindStr($_POST['METHODIDS']),':roles'=>doWithBindStr($_POST['ROLES']),':sm'=>doWithBindStr($_POST['DESCRIPTION']),':start'=>$this->_pageDataIndex+1,':end'=>$this->_pageDataIndex+$this->_pageSize));

            }else{
                $arr['total']=0;
                $arr['rows']=array();
            }

            $this->ajaxReturn ( $arr, "JSON" );
            exit ();
		}
		$this->display ();
	}

    public function methods_edit(){
        $arr=$this->md->sqlFind("select * from METHODS where METHODID='{$_POST['bind']['METHODID']}'");
        echo json_encode($arr);
    }

    public function e_method(){
        $this->assign('MID',$_GET['mid']);
        $this->display();
    }

	// 组合where条件
	public function mewhere() {
		// 判断where条件
		$data = array ();
		$_POST ['METHODIDS'] = trim ( $_POST ['METHODIDS'] );
		if (trim ( $_POST ['METHODIDS'] ) != '') {
			$data ['METHODID'] = array (
					'like',
					array (
							"{$_POST['METHODIDS']}" 
					)
			);
		}
		$pd = count ( $data ) == 0 ? "" : $data;
		return $pd;
	}
	
	/*
	 * 插入数据的方法
	 */
	public function insertme() {
		$model = M ( 'methods' );
		
		$sql = $model->add ( $_POST );
		// $arr=$_POST['data'];
		if ($sql)
			echo true;
		else
			echo false;
	}
	
	/*
	 * 修改数据的方法
	 */
	public function updateme() {
		$model = M ( 'methods' );
		$data = array ();
		$data ['METHODID'] = $_POST ['METHODID'];
		$pd = $model->where ( $data )->save ( $_POST );
		if ($pd)
			echo true;
		else
			echo false;
	}
	public function deleteme() {
		$model = M ( 'methods' );
		$data = array ();
		$data ['METHODID'] = array (
				'in',
				$_POST ['in'] 
		);
		$arr = $model->where ( $data )->delete ();
		if ($arr)
			echo true;
		else
			echo false;
	}
	
	/**
	 * 系统日志
	 */
	public function logs(){
		if($this->_hasJson){
            $sql="SELECT TOP :COUNT RECNO,USERNAME,REMOTEIP,COOKIEROLES,SCRIPTNAME,PATHINFO,METHOD,CONVERT(VARCHAR(100),".
                "REQUESTTIME,20) REQUESTTIME,CASE SUCCESS WHEN '1' THEN '成功' ELSE '失败' END SUCCESS,QUERY FROM LOGS ".
                "WHERE USERNAME LIKE :USERNAME AND METHOD LIKE :METHOD AND PATHINFO LIKE :PATHINFO AND REMOTEIP LIKE ".
                " :REMOTEIP AND SCRIPTNAME LIKE :SCRIPTNAME AND DATEDIFF(DAY,REQUESTTIME,GETDATE()) < :REQUESTTIME AND ".
                "(SUCCESS= :SUCCESS OR SUCCESS= :FAIL) ORDER BY REQUESTTIME DESC";

            $bind=array(':COUNT'=>$_POST['COUNT'],
                ':USERNAME'=>doWithBindStr($_POST['USERNAME']),
                ':METHOD'=>doWithBindStr($_POST['METHOD']),
                ':PATHINFO'=>doWithBindStr($_POST['PATHINFO']),
                ':REMOTEIP'=>doWithBindStr($_POST['REMOTEIP']),
                ':SCRIPTNAME'=>doWithBindStr($_POST['SCRIPTNAME']),
                ':REQUESTTIME'=>$_POST['REQUESTTIME'],
                ':SUCCESS'=>$_POST['SUCCESS'],
                ':FAIL'=>$_POST['FAIL']);

            $model = M("SqlsrvModel:");
            $arr["rows"] = $model->sqlQuery($sql,$bind);
            $this->ajaxReturn($arr,"JSON");
            exit;
		}
		$this->display();
	}
	/**
	 * 删除系统日志
	 */
	public function delLogs(){
		$newids="";
		foreach($_POST["ids"] as $val){
			$newids.="'".$val."',";
		}
		$newids=rtrim($newids,",");
		$sql="delete from logs where recno in($newids)";
		$model = M("SqlsrvModel:");
		$row=$model->sqlExecute($sql);
		if($row) echo true;
		else echo false;
	}
}