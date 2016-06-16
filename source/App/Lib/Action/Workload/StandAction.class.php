<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/5/14
 * Time: 9:57
 */
class StandAction extends RightAction {
    public function query(){
        $Obj=D('teacherjob');
        $data=$Obj->field('job,stand,exceedstand,name as jobname')->select();
        $count=$Obj->count();
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
                $condition['JOB']= $one->job; //这里区分大小写
                $data['STAND']=$one->stand;
                $data['EXCEEDSTAND']=$one->exceedstand;
                $Obj=D('teacherjob');
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


    public function query2(){
        $Obj=D('workstand');
        $data=$Obj->join('teachertype on teachertype.name=workstand.type')
            ->join('teacherlevel on teacherlevel.level=workstand.level')
            ->field('id,teachertype.value as typename,teacherlevel.name as levelname,perwork,exceedperwork')->select();
        $count=$Obj->count();
        if($count>0){ //小于0的话就不返回内容，防止IE下无法解析rows为NULL时的错误。
            $result=array('total'=>$count,'rows'=>$data);
            $this->ajaxReturn($result,'JSON');
        }
    }

    public function update2(){
        $updaterow=0;
        //更新部分
        $Trans=D();
        $Trans->startTrans();
        if(isset($_POST["updated"])){
            $updated = $_POST["updated"];
            $listUpdated = json_decode($updated);
            foreach ($listUpdated as $one){
                $condition['ID']= $one->id; //这里区分大小写
                $data['PERWORK']=$one->perwork;
                $data['EXCEEDPERWORK']=$one->exceedperwork;
                $Obj=D('workstand');
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