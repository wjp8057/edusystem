<?php
/**
 * 院校评教
 * User: cwebs
 * Date: 14-2-20
 * Time: 上午9:03
 */
class EntryschoolAction extends RightAction
{
    private $model;
    private $base;
    /**
     * 构造函数
     **/
    public function __construct()
    {
        parent::__construct();
        $this->model = M("SqlsrvModel:");
        $this->base='kaoping/';
    }

    /**
     * 同步名单
     **/  
    public function entry()
    {
        if($this->_hasJson)
        {
             $sql=$this->model->getSqlMap('kaoping/kaoping_schoolscore_listupdate.sql');
             $bind=array(':YEAR'=>$_POST['YEAR']);
             $one=$this->model->sqlExecute($sql,$bind);
             if($one) echo true;
             else echo false;
        }
        else
        {
            $shuju=M('schools');                                   // 学院数据
            $school=$shuju->select();
            $sjson=array();
            $this->assign('school',$school);

            $data = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
            $this->assign("yearTerm",$data);

            $this->display();
        }
    }
    public function Daying(){
        $schoolno=$this->model->sqlFind('select NAME from SCHOOLS where SCHOOL=:schoolno',array(':schoolno'=>$_GET['SCHOOL']));
        $this->assign('schoolname',$schoolno['NAME']);
        $name=$this->model->sqlFind('SELECT TEACHERS.NAME AS NAME FROM USERS INNER JOIN TEACHERS ON TEACHERS.TEACHERNO=USERS.TEACHERNO WHERE USERS.USERNAME=:TEACHERNAME',array(':TEACHERNAME'=>$_SESSION['S_USER_INFO']['USERNAME']));
        $jiaoshi=$_GET['TEACHERNO'];
        $this->assign('jiaoshi',$jiaoshi);
      echo '<pre>';
        var_dump($this->view);
      echo '<pre>';
       var_dump($_GET);
        $this->assign('name',$name['NAME']);
        $this->assign('date',Date('Y-m-d'));
        $this->assign('SCHOOL',$_GET['SCHOOL']);
        $this->assign('YEAR',$_GET['YEAR']);
        $this->display();
    }

    /*
     * 显示主页FORM的方法
     */
    public function schoollist()
    {
            //$_POST['YEAR']="2013";
            //$_POST['SCHOOLNO']="01";

            $bind = array(":YEAR"=>trim($_POST['YEAR']),":SCHOOLNO"=>trim($_POST['SCHOOLNO']));
            $sql = $this->model->getSqlMap("kaoping/kaoping_schoollistcount.sql");
            $data = $this->model->sqlFind($sql,$bind);

            $arr['total'] = $data['ROWS'];

            if($arr['total']>0)
            {
                $sql = $this->model->getPageSql(null,"kaoping/kaoping_schoollist.sql", $this->_pageDataIndex, $this->_pageSize);
                $arr['rows'] = $this->model->sqlQuery($sql,$bind);
            }
            else
                $arr['rows']=array();
            $this->ajaxReturn($arr,"JSON");
            exit;
    }

    /*
    *  修改数据时候的方法
    */
    public function updateentry()
    {
        $tscore_sql=$_POST["TSCORE"]?$_POST["TSCORE"]:0;
        $xscore_sql=$_POST["XSCORE"]?$_POST["XSCORE"]:0;
        $addscore_sql=$_POST["ADDSCORE"]?$_POST["ADDSCORE"]:0;
        if($tscore_sql>100.00 || $tscore_sql<0)
            exit('a1');                           //      同行评估分不符合要求
        if($xscore_sql>20.00 || $xscore_sql<0)
            exit('a2');                           //      学院评估分不符合要求
        if($addscore_sql>10.00 || $addscore_sql<-10.00)
            exit('a3');                           //      其他得分不符合要求
        $rem_sql=trim($_POST["REM"]);
        $TEACHERNO=trim($_POST["TEACHERNO"]);
        $YEAR=trim($_POST["YEAR"]);
        $sql="UPDATE 教学质量评估院校评估 SET TSCORE=".$tscore_sql.",XSCORE=".$xscore_sql.",ADDSCORE=".$addscore_sql.",REM='".$rem_sql."' WHERE TEACHERNO='$TEACHERNO' AND YEAR='$YEAR'";
        $boo=$this->model->sqlQuery($sql);
        if($boo===false) echo false;
        else echo true;
    }
    
    public function selectList(){
    	if($this->_hasJson){
    		$arr = array("total"=>0, "rows"=>array());
    		
    		$sql = $this->model->getSqlMap($this->base.$_POST['count']);
    		$count = $this->model->sqlCount($sql,$_POST['arr']);
    		$arr["total"] = intval($count);
            if($arr["total"] > 0){
               $sql = $this->model->getPageSql(null,$this->base.$_POST['select'], $this->_pageDataIndex, $this->_pageSize);
               $value = $this->model->sqlQuery($sql,$_POST['arr']);
                foreach($value as $key=>$val){
                    //总评=学评教成绩*学评教比例+学院评分*学院评分比例*100/20+同行评分*同行评分比例+其他评分
                    if($val['xspf']=='.00'){
                        $value[$key]['xspf']=0;
                        $val['xspf']=0;
                    }
                   $value[$key]['total']=round($val['xspf']*$_POST['xspfb']/100+($val['xypf']*$_POST['xypfb']/20)/100+$val['thpf']*$_POST['qtpfb']/100+$val['qtpf'],2);
               }
               $arr['rows']=$value;
            }
            
            $this->ajaxReturn($arr,'JSON');
            exit;
       }
        $schoolno=$this->model->sqlFind('select NAME from SCHOOLS where SCHOOL=:schoolno',array(':schoolno'=>$_POST['schoolno']));
        echo $schoolno['NAME'];
    }
}