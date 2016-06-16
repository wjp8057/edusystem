<?php
/**
 * 学生评教
 * User: cwebs
 * Date: 14-2-14
 * Time: 下午4:17
 */
class EntrystudAction extends RightAction{
    private $model;
    /**
     * 首页
     **/
    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");
    }
    /**
     * 条目整理
     *
     **/
    public function entry()
    {
        if($this->_hasJson)
        {
            if(trim($_POST['FLAG'])=="1")
            {
                    $sql="INSERT INTO 教学质量评估详细 (MAP,STUDENTNO) SELECT RECNO,R32.STUDENTNO
                     FROM 教学质量评估综合 INNER JOIN R32 ON 教学质量评估综合.COURSENO=R32.COURSENO+R32.[GROUP] AND
                     教学质量评估综合.YEAR=R32.YEAR AND 教学质量评估综合.TERM=R32.TERM WHERE 教学质量评估综合.YEAR={$_POST['YEAR']} AND
                     教学质量评估综合.TERM={$_POST['TERM']} and
                     not exists (select * from 教学质量评估详细 as temp where temp.studentno=r32.studentno and 教学质量评估综合.recno=temp.map)";
                    $boo=$this->model->sqlExecute($sql);
                    if($boo){
                        var_dump($boo);
                    }else echo false;

            }
            elseif(trim($_POST['FLAG'])=="2")
            {
                    $sql=$this->model->getSqlMap('kaoping/kaoping_deleteallstudent.sql');
                    $bind=array(':YEAR'=>$_POST['YEAR'],':TERM'=>$_POST['TERM']);
                    $one=$this->model->sqlExecute($sql,$bind);

                    if($one) echo true;
                    else echo false;
            }
        }
        else
        {
            $data = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
            $this->assign("yearTerm",$data);
            $this->display();
        }
    }
}