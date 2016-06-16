<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/5/14
 * Time: 9:57
 */
class WorkAction extends RightAction {
    public function query($page=1,$rows=20,$year='2014',$term='2',$coursename='',$courseno='',$school='',$checked='',$worktype=''){
        $Obj=D('works');
        $condition['works.year']=$year;
        $condition['works.term']=$term;
        $condition['works.disable']=0;
        if($coursename!='')  $condition['courses.coursename']=array('like',$coursename);
        if($courseno!='')  $condition['works.courseno']=array('like',$courseno);
        if($school!='')  $condition['works.school']=array('like',$school);
        if($worktype!='')  $condition['works.worktype']=$worktype;
        if($checked!='')  $condition['works.checked']=(int)$checked;
        $data=$Obj->join('courses on works.courseno=courses.courseno')
            ->join('schools on schools.school=works.school')
            ->join('worktype on worktype.type=works.worktype')
            ->join('left join courseclassname as t on t.year=works.year and t.term=works.term and t.courseno=works.courseno and t.[group]=works.[groups]')
            //字段名不区分大小写
            ->field('id,works.year,works.term,works.checked, works.courseno,works.[groups] as [group],courses.coursename,works.school,schools.name as schoolname,works.total,works.worktype,worktype.name as worktypename,works.stand,works.week
            ,works.rem,works.work,works.attendent,works.factor,works.disable,t.classname,works.settled,works.psettled,works.addfactor')
            ->where($condition)->order('courseno')->page($page,$rows)->select();
        $count=$Obj->join('courses on works.courseno=courses.courseno')
            ->where($condition)->count();
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
                $condition['ID']= $one->id; //这里字段名区分大小写
                $data['CHECKED']=$one->checked;
                $Obj=D('works');
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
    public function teacher($page=1,$rows=20,$teacherno='',$name='',$school=''){

        $Obj=D('teachers');
        if($teacherno!='')  $condition['teachers.teacherno']=array('like',$teacherno);
        if($name!='')  $condition['teachers.name']=array('like',$name);
        if($school!='')  $condition['teachers.school']=array('like',$school);
        $data=$Obj->join('schools on schools.school=teachers.school')
            ->field('teachers.name as teachername,teachers.teacherno,schools.name as schoolname')
            ->where($condition)->order('teacherno')->page($page,$rows)->select();
        $count=$Obj->where($condition)->count();
        if($count>0){ //小于0的话就不返回内容，防止IE下无法解析rows为NULL时的错误。
            $result=array('total'=>$count,'rows'=>$data);
            $this->ajaxReturn($result,'JSON');
        }
    }
    public function detail($page=1,$rows=100,$map=0){
        $Obj=D('workdetail');

        $condition['workdetail.map']=$map;
        $data=$Obj->join('teachers on teachers.teacherno=workdetail.teacherno')
            ->join('schools on schools.school=teachers.school')
            ->JOIN('workteachertype on workteachertype.type=workdetail.type')
            //字段名不区分大小写
            ->field('workdetail.id,workdetail.map,workdetail.teacherno,workdetail.work,teachers.name as teachername,schools.name as schoolname,workdetail.type as teachertype,workteachertype.name teachertypename')
            ->where($condition)->order('id')->page($page,$rows)->select();
        $count=$Obj->where($condition)->count();
        if($count>0){ //小于0的话就不返回内容，防止IE下无法解析rows为NULL时的错误。
            $result=array('total'=>$count,'rows'=>$data);
            $this->ajaxReturn($result,'JSON');
        }
    }
    public function updatedetail(){
        $updaterow=0;
        $deleterow=0;
        $insertrow=0;
        //更新部分
        $map=0;
        $Trans=D();
        $Trans->startTrans();
        if(isset($_POST["updated"])){
            $updated = $_POST["updated"];
            $listUpdated = json_decode($updated);
            foreach ($listUpdated as $one){
                $condition=null;
                $condition['ID']= $one->id;
                $map=$one->map;
                $data['TEACHERNO']=$one->teacherno;
                $data['WORK']=$one->work;
                $data['TYPE']=$one->teachertype;
                $Obj=D('workdetail');
                $updaterow+=$Obj->where($condition)->save($data);
            }
        }
        //删除部分
        if(isset($_POST["deleted"])){
            $updated = $_POST["deleted"];
            $listUpdated = json_decode($updated);
            foreach ($listUpdated as $one){
                $condition=null;
                $condition['ID']= $one->id;
                $map=$one->map;
                $Obj=D('workdetail');
                $deleterow+=$Obj->where($condition)->delete();
            }
        }
        if(isset($_POST["inserted"])){
            $updated = $_POST["inserted"];
            $listUpdated = json_decode($updated);
            foreach ($listUpdated as $one){
                $data['WORK']=$one->work;
                $data['TEACHERNO']=$one->teacherno;
                $data['TYPE']=$one->teachertype;
                $data['MAP']=$one->map;
                $map=$one->map;
                $Obj=D('workdetail');
                $row=$Obj->add($data);
                if($row>0)
                    $insertrow++;
            }
        }
        if( is_numeric($map)) {
            $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
            $Model->execute("update works
                    set settled=t.detail,psettled=isnull(t2.detail,0)
                    from works inner join (select map,sum(work) as detail from workdetail where  type='A'
                    group by map ) as t on t.map=works.id
                    left join (select map,sum(work) as detail from workdetail where  type='B'
                    group by map ) as t2 on t2.map=works.id
                    where works.id=" . $map);
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
            if($deleterow>0) $info.=$deleterow.'条删除！</br>';
            if($insertrow>0) $info.=$insertrow.'条添加！</br>';
            $info=$info!=''?$info:'没有数据更新！';
            $status=1;
        }
        $result=array('info'=>$info,'status'=>$status);
        $this->ajaxReturn($result,'JSON');
    }


}