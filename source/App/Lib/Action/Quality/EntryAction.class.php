<?php
/**
 * 教学质量评估
 * User: cwebs
 * Date: 14-2-10
 * Time: 下午4:17
 */
class EntryAction extends RightAction {
	private $model;
	/**
	 * 首页
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
			if (trim ( $_POST ['FLAG'] ) == "1") {

                $bind = array(':year'=>$_POST['YEAR'],':term'=>$_POST['TERM']);
                $sql = "declare @year char(4)
                        declare @term char(1)
                        set @year=:year
                        set @term=:term
                        insert into 教学质量评估综合(teacherno,courseno,task,year,term)
                        select distinct teacherno,courseno,task,year,term from (
                        select teacherplan.teacherno,scheduleplan.courseno+scheduleplan.[GROUP] courseno,case courses.type2
                        when 'A' then 'K'
                        when 'B' then 'S'
                        when 'C' then 'C'
                        else 'B' end as task,
                        scheduleplan.year,scheduleplan.term
                         from teacherplan inner join scheduleplan on scheduleplan.recno=teacherplan.map
                        inner join  courses on courses.courseno=scheduleplan.courseno
                        where scheduleplan.year=@year and scheduleplan.term=@term and teacherplan.teacherno!='000000') as temp
                        where not exists (select * from 教学质量评估综合 as temp2 where temp2.year=@year and temp2.term=@term and temp.teacherno=temp2.teacherno
                        and temp.courseno=temp2.courseno)";
//				$sql = "insert into 教学质量评估综合(teacherno,courseno,task,year,term) select teacherno,".
//				"courseno,task,year,term from (select distinct TEACHERPLAN.teacherno,".
//				"scheduleplan.courseno+scheduleplan.[group] AS COURSENO,".
//				"CASE [task] WHEN 'L' THEN 'K' WHEN 'N' THEN 'U' ELSE 'S' END AS TASK, ".
//				"teacherplan.year AS YEAR, teacherplan.term AS  TERM from teacherplan inner join".
//				" scheduleplan on teacherplan.map=scheduleplan.recno INNER JOIN TEACHERS ON ".
//				"TEACHERS.TEACHERNO=TEACHERPLAN.TEACHERNO where teacherplan.year='}' ".
//				"and teacherplan.term='{$_POST['TERM']}') as temp where not exists  ".
//				"(select * from 教学质量评估综合 where 教学质量评估综合.year='{$_POST['YEARS']}' AND ".
//				"教学质量评估综合.term='{$_POST['TERM']}' AND 教学质量评估综合.TEACHERNO=temp.teacherno and ".
//				"temp.courseno=教学质量评估综合.courseno and 教学质量评估综合.task=temp.task)";
				
				$boo = $this->model->sqlQuery ( $sql ,$bind);
				if ($boo)
					echo true;
				else
					echo false;
			} elseif (trim ( $_POST ['FLAG'] ) == "2") {
				$sql = $this->model->getSqlMap ( 'kaoping/kaoping_totaldelete.sql' );
				$bind = array (
						':YEAR' => $_POST ['YEAR'],
						':TERM' => $_POST ['TERM'] 
				);
				$one = $this->model->sqlExecute ( $sql, $bind );
				if ($one)
					echo true;
				else
					echo false;
			}
		}else{
			$data = $this->model->sqlFind ($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
			$this->assign("yearTerm",$data);
			$this->display();
		}
	}
}