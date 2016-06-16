<?php
/**
 * 教学评估
 * User: educk
 * Date: 13-12-18
 * Time: 下午1:44
 */
class EvaluationAction extends RightAction {
    private $model;
    public function __construct(){
        parent::__construct();
        $this->model = M("SqlsrvModel:");
    }

    public function index(){
        $this->display();
    }

    public function qlist(){

      /*  echo '<pre>';
        $s=$this->model->sqlQuery($this->model->getSqlMap("kaoping/kaopingCourseList.sql"),array(":STUDENTNO"=>session("S_USER_NAME"),':year'=>$_GET['year'],':term'=>$_GET['term']));
        print_r($s);*/

   /*     $list = $this->model->sqlQuery($this->model->getSqlMap("kaoping/kaopingCourseList.sql"),
            array(':year'=>$_GET['year'],':term'=>$_GET['term'],":STUDENTNO"=>session("S_USER_NAME")));*/
//        $tab=D('教学质量评估综合');
//        $condition['year']=$_GET['year'];
//        $condition['term']=$_GET['term'];
//        $condition['studentno']=session("S_USER_NAME");
//        $condition['ENABLED']='1';
//        $list=$tab->join('教学质量评估详细 on 教学质量评估综合.recno=教学质量评估详细.map')
//            ->join('教学质量评估类型 on 教学质量评估类型.TYPE=教学质量评估综合.task')
//            ->join('courses on courses.courseno=substring(教学质量评估综合.courseno,1,7)')
//            ->join('teachers on 教学质量评估综合.teacherno=teachers.teacherno')
//            ->field('teachers.name,row_number() over(order by rank) as row,teachers.name as TEACHERNAME,教学质量评估详细.MAP,
//            教学质量评估详细.compelete,教学质量评估综合.courseno as COURSENO,courses.coursename as COURSENAME,rtrim(教学质量评估类型.NAME) AS TYPE,
//            教学质量评估综合.recno as RECNO,教学质量评估详细.recno as RECNO2,RTRIM(rank) as rank,
// 教学质量评估详细.TOTAL as FRACTION,教学质量评估综合.YEAR  YEAR,教学质量评估综合.TERM AS TERM')->where($condition)->select();


        $sql = 'SELECT  jdetail.studentno,
            teachers.name,row_number() over(order by rank) as row,teachers.name as TEACHERNAME,jdetail.MAP,
                        jdetail.compelete,jall.courseno as COURSENO,courses.coursename as COURSENAME,rtrim(jtype.NAME) AS TYPE,
                        jall.recno as RECNO,jdetail.recno as RECNO2,RTRIM(rank) as rank,
             jdetail.TOTAL as FRACTION,jall.YEAR  YEAR,jall.TERM AS TERM
             from 教学质量评估综合 as jall
            LEFT OUTER JOIN 教学质量评估详细 as jdetail on jall.recno=jdetail.map
            LEFT OUTER   JOIN 教学质量评估类型 as jtype on jtype.TYPE=jall.task
            LEFT OUTER  JOIN courses on courses.courseno=substring(jall.courseno,1,7)
            LEFT OUTER  JOIN teachers on jall.teacherno=teachers.teacherno
            WHERE [year] = :year and term = :term
             and studentno = :studentno and enabled = 1 ';
        $username = session("S_USER_NAME");
        $bind = array(':year'=>trim($_GET['year']),':term'=>trim($_GET['term']),':studentno'=>trim($username));
        $list = $this->model->sqlQuery($sql,$bind);


        $this->assign("year",$_GET['year']);
        $this->assign('term',$_GET['term']);
        $this->assign("list",$list);
        $this->display("qlist");
    }

    public function qlist2(){

        /*  select row_number() over(order by rank) as row,teachers.name as TEACHERNAME,
教学质量评估详细.MAP,
教学质量评估详细.compelete,
教学质量评估综合.courseno as COURSENO,courses.coursename as COURSENAME,
教学质量评估类型.NAME AS TYPE,教学质量评估综合.recno as RECNO,
      教学质量评估详细.recno as RECNO2,
      RTRIM(rank) as rank,
case [compelete]
      WHEN 1 THEN RTRIM(教学质量评估详细.TOTAL)+'分'
      ELSE '未评'
      END AS FRACTION,教学质量评估综合.YEAR  YEAR,教学质量评估综合.TERM AS TERM
from 教学质量评估综合 inner join teachers on 教学质量评估综合.teacherno=teachers.teacherno
inner join courses on courses.courseno=substring(教学质量评估综合.courseno,1,7)
inner join 教学质量评估详细 on 教学质量评估综合.recno=教学质量评估详细.map
inner join 教学质量评估类型 on 教学质量评估类型.TYPE=教学质量评估综合.[task]
where year=:year and term=:term AND 教学质量评估综合.ENABLED='1'and studentno=:STUDENTNO

*/
       $tab=D('教学质量评估综合');
        $condition['year']='2014';
        $condition['term']='1';
        $condition['studentno']='133280309';

        $arr=$tab->join('教学质量评估详细 on 教学质量评估综合.recno=教学质量评估详细.map')
            ->join('教学质量评估类型 on 教学质量评估类型.TYPE=教学质量评估综合.task')
            ->join('courses on courses.courseno=substring(教学质量评估综合.courseno,1,7)')
            ->join('teachers on 教学质量评估综合.teacherno=teachers.teacherno')
            ->field('teachers.name,row_number() over(order by rank) as row,teachers.name as TEACHERNAME,教学质量评估详细.MAP,
            教学质量评估详细.compelete,教学质量评估综合.courseno as COURSENO,courses.coursename as COURSENAME,rtrim(教学质量评估类型.NAME) AS TYPE,
            教学质量评估综合.recno as RECNO,教学质量评估详细.recno as RECNO2,RTRIM(rank) as rank,
教学质量评估详细.TOTAL as FRACTION,教学质量评估综合.YEAR  YEAR,教学质量评估综合.TERM AS TERM')->where($condition)->select();


        echo $tab->getLastSql();

        echo '<pre>';
        var_dump($arr);
        exit;

    }

    public function save_rank(){
        if($this->_hasJson){

            $studentno=session("S_USER_NAME");
            $isnull= $this->model->sqlfind("select count(*) as ROWS
from 教学质量评估综合 inner join teachers on 教学质量评估综合.teacherno=teachers.teacherno
inner join courses on courses.courseno=substring(教学质量评估综合.courseno,1,7)
inner join 教学质量评估详细 on 教学质量评估综合.recno=教学质量评估详细.map
where studentno='{$studentno}' AND 教学质量评估综合.ENABLED='1'
and year=:year and term=:term
and ISNULL(rank,0)=0",array(':year'=>$_POST['year'],':term'=>$_POST['term']));

            if($isnull['ROWS']==0)exit('false');
            exit('true');


        }

        $count=array();
        foreach($_POST['bind'] as $val){
            if(!trim($val['rank'])==''){
                array_push($count,trim($val['rank']));
            }
        }

        if(count(array_unique($count))<count($count)){
            exit('设置的排名中不能有重复的值,请检查');
        }

        $studentno=session("S_USER_NAME");
        $maxrank = $this->model->sqlQuery("select max(rank) as rank
from 教学质量评估综合 inner join teachers on 教学质量评估综合.teacherno=teachers.teacherno
inner join courses on courses.courseno=substring(教学质量评估综合.courseno,1,7)
inner join 教学质量评估详细 on 教学质量评估综合.recno=教学质量评估详细.map
where studentno='{$studentno}' AND 教学质量评估综合.ENABLED='1'
and year=:year and term=:term",array(':year'=>$_POST['YEAR'],':term'=>$_POST['TERM']));

            $this->model->starttrans();
        foreach($_POST['bind'] as $val){
            if(trim($val['rank'])==''){
                continue;
            }
            if($val['rank']<$maxrank['rank']){
                exit('排名保存异常,请核对是否有重复');
            }
            $int=$this->model->sqlExecute('update 教学质量评估详细 set rank=:rank where recno=:recno',array(':rank'=>trim($val['rank']),':recno'=>$val['RECNO2']));

            if(!$int){
                $this->model->rollback();
                exit('保存失败！');
            }
        }
                $this->model->commit();
                exit('保存成功');


    }

    /**
     * ACT课堂教学
     */
    public function ketangList(){

       // "&teachername="+obj.TEACHERNAME+"&courseno="+obj.COURSENO+"&Recno="+obj.RECNO+"&coursename="+obj.COURSENAME+'&RECNO2='+obj.RECNO2;


        $number=$this->model->sqlFind("select ([1th]+[2th]+[3th]+[4th]) as total,[1th],[2th],[3th],[4th],recno from 教学质量评估详细 where recno=:recno"
        ,array(':recno'=>$_GET['RECNO2']));

        $this->assign('recno',$_GET['Recno']);
        $this->assign('rank',$_GET['rank']);
        $this->assign('recno2',$_GET['RECNO2']);
        //$this->assign('')
        $this->assign("year",$_GET['year']);
        $this->assign("term",$_GET['term']);
        $this->assign('number',$number);
        $this->display();
    }

    /**
     * ACT实践教学
     */
    public function shijianList(){
        $number=$this->model->sqlFind("select ([1th]+[2th]+[3th]+[4th]) as total,[1th],[2th],[3th],[4th],recno from 教学质量评估详细 where recno=:recno"
            ,array(':recno'=>$_GET['RECNO2']));

        $this->assign('recno',$_GET['Recno']);
        $this->assign('rank',$_GET['rank']);
        $this->assign('recno2',$_GET['RECNO2']);
        //$this->assign('')

        $this->assign("year",$_GET['year']);
        $this->assign("term",$_GET['term']);
        $this->assign('number',$number);
        $this->display();
    }

    //todo:理实一体
    public function lishi(){

        $number=$this->model->sqlFind("select ([1th]+[2th]+[3th]+[4th]) as total,[1th],[2th],[3th],[4th],recno from 教学质量评估详细 where recno=:recno"
            ,array(':recno'=>$_GET['RECNO2']));

        $this->assign('recno',$_GET['Recno']);
        $this->assign('rank',$_GET['rank']);
        $this->assign('recno2',$_GET['RECNO2']);
        //$this->assign('')

        $this->assign("year",$_GET['year']);
        $this->assign("term",$_GET['term']);
        $this->assign('number',$number);
        $this->display();
    }

    public function biyeshijian(){
        $number=$this->model->sqlFind("select ([1th]+[2th]+[3th]+[4th]) as total,[1th],[2th],[3th],[4th],recno from 教学质量评估详细 where recno=:recno"
            ,array(':recno'=>$_GET['RECNO2']));

        $this->assign('recno',$_GET['Recno']);
        $this->assign('rank',$_GET['rank']);
        $this->assign('recno2',$_GET['RECNO2']);
        //$this->assign('')

        $this->assign("year",$_GET['year']);
        $this->assign("term",$_GET['term']);
        $this->assign('number',$number);
        $this->display();

    }


    /**
     * ACT毕业论文
     */
    public function lunwenList(){
        $this->display();
    }

    /**
     * ACT保存
     */
    public function saveKetang(){


        $studentno=session("S_USER_NAME");
        $t=$this->model->sqlFind("select * from(
select row_number() over(order by 教学质量评估详细.rank) as row,教学质量评估详细.total,教学质量评估详细.recno from 教学质量评估综合 inner join teachers on 教学质量评估综合.teacherno=teachers.teacherno
inner join courses on courses.courseno=substring(教学质量评估综合.courseno,1,7) inner join 教学质量评估详细 on 教学质量评估综合.recno=教学质量评估详细.map
where studentno='{$studentno}' AND 教学质量评估综合.ENABLED='1' and year=:year and term=:term and compelete=1 ) b where b.row=:rank
",array(':year'=>$_POST['year'],':term'=>$_POST['term'],':rank'=>$_POST['rank']+1));

        $total=($_POST['one']+$_POST['two']+$_POST['three']+$_POST['four']);

        if($t){
            if($total<=$t['total']){
                exit('本教师的总分不能小于或等于排名后一位的教师的得分：<span style="color:red">'.$t['total'].'分</span>');
            }}

//todo:===============================================================================上面所和下一位判断,下面是和上一位判断
        $t=$this->model->sqlFind("select * from(select row_number() over(order by 教学质量评估详细.rank) as row,教学质量评估详细.total,教学质量评估详细.recno from 教学质量评估综合
inner join teachers on 教学质量评估综合.teacherno=teachers.teacherno inner join courses on courses.courseno=substring(教学质量评估综合.courseno,1,7)
inner join 教学质量评估详细 on 教学质量评估综合.recno=教学质量评估详细.map where studentno='{$studentno}' AND 教学质量评估综合.ENABLED='1'
and year=:year and term=:term and compelete=1 ) b where b.row=:rank
",array(':year'=>$_POST['year'],':term'=>$_POST['term'],':rank'=>$_POST['rank']-1));



        $total=($_POST['one']+$_POST['two']+$_POST['three']+$_POST['four']);
        if($t){
            if($total>=$t['total']){
                exit('本教师的总分不能大于或等于排名前一位的教师的得分：<span style="color:red">'.$t['total'].'分</span>');
            }}


        /*        $t=$this->model->sqlFind("select 教学质量评估详细.total,教学质量评估详细.recno from 教学质量评估综合 inner join teachers on 教学质量评估综合.teacherno=teachers.teacherno inner join courses on courses.courseno=substring(教学质量评估综合.courseno,1,7) inner join 教学质量评估详细 on 教学质量评估综合.recno=教学质量评估详细.map where studentno=:STUDENTNO AND 教学质量评估综合.ENABLED='1' and year=:year and term=:term and compelete=1 and rank=:rank
        ",array(":STUDENTNO"=>session("S_USER_NAME"),':year'=>$_POST['year'],':term'=>$_POST['term'],':rank'=>$_POST['rank']-1));



        $total=($_POST['one']+$_POST['two']+$_POST['three']+$_POST['four']);
            if($t){
                if($total>=$t['total']){
                    exit('本教师的总分不能大于或等于排名前一位的教师的得分：'.$t['total'].'分');
            }}*/
        $this->model->starttrans();
        $int=$this->model->sqlexecute("update 教学质量评估详细 set [1th]=:one,[2th]=:two,[3th]=:three,[4th]=:four,Total=:total where recno=:recno",
            array(":one"=>$_POST['one'],':two'=>$_POST['two'],':three'=>$_POST['three'],':four'=>$_POST['four'],
                ':total'=>$total,':recno'=>$_POST['recno']));
        $int2=$this->model->sqlexecute('update 教学质量评估详细 set compelete=1 where recno=:recno',
            array(':recno'=>$_POST['recno']));
        if($int&&$int2){
            $this->model->commit();
            exit('保存成功');
        }else{
            $this->model->rollback();
            exit('保存失败');
        }

        exit;
        $bind = $this->model->getBind("one,two,three,four,five,six,seven,eight,nine,ten,total,map,studentno",$_REQUEST);
        $bind[":studentno"] = session("S_USER_NAME");

        $data = $this->model->sqlExecute($this->model->getSqlMap("kaoping/kaoping_update.sql"),$bind);
        if($data===false) $this->assign("message","<font style='color: #ff0000'>更新评价时发生错误，评价失败！</font>");
        else $this->assign("message","你已成功评价！");
        $this->qlist();
    }


    //todo:查询出上一条的信息
    public function is_last(){
        //   $data = $this->model->sqlFind($this->model->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
        $studentno=session("S_USER_NAME");
        $content=$this->model->sqlFind("select * from (select row_number() over(order by 教学质量评估详细.rank) as row,teachers.name as TEACHERNAME,
教学质量评估详细.MAP,
教学质量评估详细.compelete,
教学质量评估综合.courseno as COURSENO,courses.coursename as COURSENAME,
CASE [task]
      WHEN 'B' THEN '毕业实践'
      WHEN 'K' THEN '理论课'
      WHEN 'C' THEN '理实一体'
      WHEN 'S' THEN '实践课'
      END AS TYPE,教学质量评估综合.recno as RECNO,
      教学质量评估详细.recno as RECNO2,
      RTRIM(rank) as rank,
case [compelete]
      WHEN 1 THEN RTRIM(教学质量评估详细.TOTAL)+'分'
      ELSE '未评'
      END AS FRACTION,教学质量评估综合.YEAR  YEAR,教学质量评估综合.TERM AS TERM
from 教学质量评估综合 inner join teachers on 教学质量评估综合.teacherno=teachers.teacherno
inner join courses on courses.courseno=substring(教学质量评估综合.courseno,1,7)
inner join 教学质量评估详细 on 教学质量评估综合.recno=教学质量评估详细.map
where studentno='{$studentno}' AND 教学质量评估综合.ENABLED='1'
and year=:year and term=:term
) as b where b.row=:rank
",array(':year'=>$_POST['year'],':term'=>$_POST['term'],':rank'=>$_POST['rank']-1));
        /*     echo '<pre>';
             print_r($content);*/
        echo json_encode($content);


    }



}