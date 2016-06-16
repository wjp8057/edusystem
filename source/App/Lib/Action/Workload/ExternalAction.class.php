<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/5/14
 * Time: 9:57
 */
class ExternalAction extends RightAction {
    //外聘教师工作量及费用统计汇总表
    public function query($page=1,$rows=20,$year='2014',$term='2'){
        $Obj=D('works');
        $condition['works.year']=$year;
        $condition['works.term']=$term;
        $data=$Obj->join('schools on schools.school=works.school')
            ->join('workdetail on workdetail.map=works.id')
            ->join('workteacher on  workteacher.teacherno=workdetail.teacherno and workteacher.year=works.year')
            ->join('teachertype on teachertype.NAME=workteacher.type')
            ->join('positions on positions.name=workteacher.position')
            ->join('teacherlevel on teacherlevel.level=positions.level')
            ->join('workstand on workstand.level=positions.level and workstand.type=workteacher.type')
            ->field('schools.name schoolname,teachertype.value typename,teacherlevel.name levelname,teacherlevel.level as level,cast(sum(workdetail.work*workdetail.repeat) as decimal(8,2)) as work,
                  workstand.perwork,cast(sum(workdetail.work*workdetail.repeat)*workstand.perwork as decimal(8,2)) as charge')
            ->where($condition)->where("workteacher.type in ('C','D') and works.disable=0 and workteacher.disable=0")
            ->group('schools.school,schools.name,teachertype.value,teacherlevel.name,teacherlevel.level,workstand.perwork')->order('schoolname,typename,level')
            ->page($page,$rows)->select();
        $data2=$Obj->join('schools on schools.school=works.school')
            ->join('workdetail on workdetail.map=works.id')
            ->join('workteacher on  workteacher.teacherno=workdetail.teacherno  and workteacher.year=works.year')
            ->join('teachertype on teachertype.NAME=workteacher.type')
            ->join('positions on positions.name=workteacher.position')
            ->join('teacherlevel on teacherlevel.level=positions.level')
            ->join('workstand on workstand.level=positions.level and workstand.type=workteacher.type')
            ->where($condition)->where("workteacher.type in ('C','D') and works.disable=0 and workteacher.disable=0")
            ->field('schools.name schoolname,teachertype.value typename,teacherlevel.name levelname,cast(sum(workdetail.work*workdetail.repeat) as decimal(8,2)) as work,
                  workstand.perwork,cast(sum(workdetail.work*workdetail.repeat)*workstand.perwork as decimal(8,2)) as charge')
            ->group('schools.school,schools.name,teachertype.value,teacherlevel.name,workstand.perwork')->select();
        $count=count($data2);
        if($count>0){ //小于0的话就不返回内容，防止IE下无法解析rows为NULL时的错误。
            $result=array('total'=>$count,'rows'=>$data);
            $this->ajaxReturn($result,'JSON');
        }
    }
    public function query2($page=1,$rows=20,$year='2014',$term='2'){
        $Obj=D('works');
        $condition['works.year']=$year;
        $condition['works.term']=$term;
        $data=$Obj->join('schools on schools.school=works.school')
            ->join('workdetail on workdetail.map=works.id')
            ->join('workteacher on  workteacher.teacherno=workdetail.teacherno  and workteacher.year=works.year')
            ->join('teachertype on teachertype.NAME=workteacher.type')
            ->join('positions on positions.name=workteacher.position')
            ->join('teacherlevel on teacherlevel.level=positions.level')
            ->join('workstand on workstand.level=positions.level and workstand.type=workteacher.type')
            ->field('schools.name schoolname,teachertype.value typename,teacherlevel.name levelname,teacherlevel.level as level,cast(sum(workdetail.work*workdetail.repeat) as decimal(8,2)) as work,
                  workstand.exceedperwork,cast(sum(workdetail.work*workdetail.repeat)*workstand.exceedperwork as decimal(8,2)) as charge')
            ->where($condition)->where("workteacher.type in ('C','D') and works.disable=0 and workteacher.disable=0")
            ->group('schools.school,schools.name,teachertype.value,teacherlevel.name,teacherlevel.level,workstand.exceedperwork,teacherlevel.level')->order('schoolname,typename,level')
            ->page($page,$rows)->select();
        $data2=$Obj->join('schools on schools.school=works.school')
            ->join('workdetail on workdetail.map=works.id')
            ->join('workteacher on  workteacher.teacherno=workdetail.teacherno  and workteacher.year=works.year')
            ->join('teachertype on teachertype.NAME=workteacher.type')
            ->join('positions on positions.name=workteacher.position')
            ->join('teacherlevel on teacherlevel.level=positions.level')
            ->join('workstand on workstand.level=positions.level and workstand.type=workteacher.type')
            ->where($condition)->where("workteacher.type in ('C','D') and works.disable=0  and workteacher.disable=0")
            ->field('schools.name schoolname,teachertype.value typename,teacherlevel.name levelname,cast(sum(workdetail.work*workdetail.repeat) as decimal(8,2)) as work,
                  workstand.exceedperwork,cast(sum(workdetail.work*workdetail.repeat)*workstand.exceedperwork as decimal(8,2)) as charge')
            ->group('schools.school,schools.name,teachertype.value,teacherlevel.name,workstand.exceedperwork')->select();
        $count=count($data2);
        if($count>0){ //小于0的话就不返回内容，防止IE下无法解析rows为NULL时的错误。
            $result=array('total'=>$count,'rows'=>$data);
            $this->ajaxReturn($result,'JSON');
        }

    }


}