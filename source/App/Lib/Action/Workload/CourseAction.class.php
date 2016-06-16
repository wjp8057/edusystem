<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/5/14
 * Time: 9:57
 */
class CourseAction extends RightAction {
    public function query($page=1,$rows=20,$year='2014',$term='2',$coursename='',$courseno='',$school='',$checked='',$worktype=''){
        $Obj=D('courses');
        $condition['scheduleplan.year']=$year;
        $condition['scheduleplan.term']=$term;
        if($coursename!='')  $condition['courses.coursename']=array('like',$coursename);
        if($courseno!='')  $condition['courses.courseno']=array('like',$courseno);
        if($school!='')  $condition['courses.school']=array('like',$school);
        if($worktype!='')  $condition['courses.worktype']=$worktype;
        if($checked!='')  $condition['courses.checked']=(int)$checked;
        $data=$Obj->join('scheduleplan on scheduleplan.courseno=courses.courseno')
            ->join('schools on schools.school=courses.school')
            ->join('worktype on worktype.type=courses.worktype')
            //字段名不区分大小写
            ->field('distinct courses.checked, scheduleplan.courseno,courses.coursename,schools.school,schools.name as schoolname,courses.total,courses.worktype,worktype.name as worktypename,courses.stand,courses.week')
            ->where($condition)->order('courseno')->page($page,$rows)->select();
        $count=$Obj->join('scheduleplan on scheduleplan.courseno=courses.courseno')
            ->where($condition)->count('distinct scheduleplan.courseno');
        if($count>0){ //小于0的话就不返回内容，防止IE下无法解析rows为NULL时的错误。
            $result=array('total'=>$count,'rows'=>$data);
            $this->ajaxReturn($result,'JSON');
        }
    }

    public function update(){
        $updaterow=0;
        //更新部分
        $Trans=D();
        $Trans->startTrans();
        if(isset($_POST["updated"])){
            $updated = $_POST["updated"];
            $listUpdated = json_decode($updated);
            foreach ($listUpdated as $one){
                $condition['COURSENO']= $one->courseno; //这里区分大小写
                $data['WORKTYPE']=$one->worktype;
                $data['CHECKED']=$one->checked;
                $data['TOTAL']=$one->total;
                $data['STAND']=$one->stand;
                $data['WEEK']=$one->week;
                $Obj=D('courses');
                $updaterow+=$Obj->where($condition)->save($data);
            }
        }
        $error=$Trans->getDBError();
        if($error!=''){
            $info=$error;
            $status=0;
            $Trans->rollback();
        }
        else{
            $Trans->commit();
            $info='';
            if($updaterow>0) $info.=$updaterow.'条更新！</br>';
            $info=$info!=''?$info:'没有数据被更新';
            $status=1;
       }
        $result=array('info'=>$info,'status'=>$status);
        $this->ajaxReturn($result,'JSON');
    }

}