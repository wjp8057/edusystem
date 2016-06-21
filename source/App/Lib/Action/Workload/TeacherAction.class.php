<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/5/14
 * Time: 9:57
 */
class TeacherAction extends RightAction {
    public function query($page=1,$rows=20,$year='2014',$school='',$newenter='',$teacherno='',$teachername='',$job='',$type='',$leave='',$partterm=''){
        $Obj=D('workteacher');
        $condition['workteacher.year']=$year;
        if($school!='')  $condition['workteacher.school']=array('like',$school);
        if($teacherno!='')  $condition['teachers.teacherno']=array('like',$teacherno);
        if($teachername!='')  $condition['teachers.name']=array('like',$teachername);
        if($newenter!='')  $condition['workteacher.newenter']=(int)$newenter;
        if($job!='')  $condition['workteacher.job']=array('like',$job);
        if($type!='')  $condition['workteacher.type']=array('like',$type);
        if($partterm!='')   $condition['partterm']=array('gt',0);
        if($leave!='')   $condition['leaveday']=array('gt',0);
        $data=$Obj->join('teachers on teachers.teacherno=workteacher.teacherno')
            ->join('schools on schools.school=workteacher.school')
            ->join('teachertype on teachertype.name=workteacher.type')
            ->join('positions on positions.name=workteacher.position')
            ->join('teacherjob on teacherjob.job=workteacher.job')
            ->field('workteacher.school,workteacher.id,workteacher.teacherno,teachers.name as teachername,schools.name as schoolname,teachertype.value as typename,
            positions.value positionname,teacherjob.name as jobname,workteacher.job,workteacher.type,workteacher.position,workteacher.perwork,workteacher.exceedperwork,
            workone,worktwo,leaveday,partterm,workone+worktwo as worktotal,workteacher.personalstand,workteacher.personalexceedstand,workteacher.rem,workteacher.checked,workteacher.disable,exceedadd,
            workoneup+worktwoup as workup,workonepratice+worktwopratice as workpratice,workteacher.newenter')
            ->where($condition)->page($page,$rows)->order('teachername')->select();
        $count=$Obj->join('join teachers on teachers.teacherno=workteacher.teacherno')->where($condition)->count();
        if($count>0){ //小于0的话就不返回内容，防止IE下无法解析rows为NULL时的错误。
            $result=array('total'=>$count,'rows'=>$data);
            $this->ajaxReturn($result,'JSON');
        }
    }

    public function update(){
        $updaterow=0;
        //更新部分
        $Trans=D();
        $Obj=D('workteacher');
        $Trans->startTrans();
        $Model = new Model();
        if(isset($_POST["updated"])){
            $updated = $_POST["updated"];
            $listUpdated = json_decode($updated);
            foreach ($listUpdated as $one){
                $condition['ID']= $one->id; //这里区分大小写
                $data['NEWENTER']=(int)$one->newenter;
                $data['DISABLE']=(int)$one->disable;
                $data['REM']=$one->rem;
                $data['LEAVEDAY']=$one->leaveday;
                $data['PARTTERM']=$one->partterm;
                $updaterow+=$Obj->where($condition)->save($data);

                if(is_numeric($one->id)){
                $Model->execute("UPDATE WORKTEACHER
                  SET PERSONALSTAND=(STAND-120*PARTTERM)*(38-LEAVEDAY/7.0)/38,PERSONALEXCEEDSTAND=(STAND-120*PARTTERM)*(38-LEAVEDAY/7.0)/38*4/3+EXCEEDADD
                  WHERE WORKTEACHER.ID=". $one->id);
                $Model->execute("UPDATE WORKTEACHER
                  SET PERSONALSTAND=0
                  WHERE  NEWENTER=1 AND WORKTEACHER.ID=" . $one->id);
                }
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

    /**同步教师信息
     * @param string $year
     */
    public function init($year=""){
        $info="";
        $status=1;
        if(is_numeric($year)){
            $Trans=D();
            $Trans->startTrans();
            $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
            $effectRow=$Model->execute("INSERT INTO  WORKTEACHER(YEAR,TEACHERNO,SCHOOL,TYPE,POSITION,JOB)
                        SELECT ".$year.",TEACHERNO,SCHOOL,TYPE,POSITION,JOB FROM TEACHERS
                        WHERE EXISTS (
                        SELECT  * FROM WORKDETAIL
                       WHERE EXISTS (SELECT * FROM WORKS WHERE WORKS.YEAR=".$year." AND WORKS.ID=WORKDETAIL.MAP) AND WORKDETAIL.TEACHERNO=TEACHERS.TEACHERNO
                       AND NOT EXISTS (SELECT * FROM WORKTEACHER WHERE YEAR=".$year." AND WORKTEACHER.TEACHERNO=WORKDETAIL.TEACHERNO))");
            if( $effectRow>0) $info.= $effectRow.'条教师信息新加入！</br>';

            $effectRow=$Model->execute("UPDATE WORKTEACHER
                    SET SCHOOL=TEACHERS.SCHOOL,TYPE=TEACHERS.TYPE,POSITION=TEACHERS.POSITION,
                    JOB=TEACHERS.JOB,PERWORK=WORKSTAND.PERWORK,EXCEEDPERWORK=WORKSTAND.PERWORK,
                    STAND=TEACHERJOB.STAND,EXCEEDSTAND=TEACHERJOB.EXCEEDSTAND FROM WORKTEACHER
                    INNER JOIN TEACHERS ON TEACHERS.TEACHERNO=WORKTEACHER.TEACHERNO
                    INNER JOIN POSITIONS ON POSITIONS.NAME=TEACHERS.POSITION
                    INNER JOIN WORKSTAND ON WORKSTAND.TYPE=TEACHERS.TYPE AND POSITIONS.LEVEL=WORKSTAND.LEVEL
                    INNER JOIN TEACHERJOB ON TEACHERJOB.JOB=TEACHERS.JOB
                    WHERE WORKTEACHER.YEAR=".$year);
            if( $effectRow>0) $info.= $effectRow.'条教师信息更新！</br>';

            $effectRow=$Model->execute("UPDATE WORKTEACHER
                    SET PERSONALSTAND=(STAND-120*PARTTERM)*(38-LEAVEDAY/7.0)/38,PERSONALEXCEEDSTAND=(STAND-120*PARTTERM)*(38-LEAVEDAY/7.0)/38*4/3+EXCEEDADD
                    WHERE WORKTEACHER.YEAR=".$year);
            if( $effectRow>0) $info.= $effectRow.'条考核标准更新！</br>';


            $effectRow=$Model->execute("UPDATE WORKTEACHER
                  SET PERSONALSTAND=0
                  WHERE  NEWENTER=1 AND WORKTEACHER.YEAR=".$year);
            if( $effectRow>0) $info.= $effectRow.'位新进教师考核工作量设置为0！</br>';

            $error=$Trans->getDBError();
            if($error!=''){
                $info=$error;
                $status=0;
                $Trans->rollback();
            }
            else{
                $Trans->commit();
                $info=$info!=''?$info:'没有数据更新！';
                $status=1;
            }


        }
        else{
            $info="学年参数不正确！";
        }
        $result=array('info'=>$info,'status'=>$status);
        $this->ajaxReturn($result,'JSON');

    }
    public function initwork1($year="2015"){
        $info="";
        $status=1;
        if(is_numeric($year)){
            $Trans=D();
            $Trans->startTrans();
            $Model = new Model(); // 实例化一个model对象 没有对应任何数据表

            $effectRow=$Model->execute("UPDATE WORKTEACHER
                    SET WORKONE=0
                    WHERE WORKTEACHER.YEAR=".$year);
            if( $effectRow>0) $info.= $effectRow.'条工作量初始化！</br>';

            $effectRow=$Model->execute("UPDATE WORKTEACHER
                    SET WORKONE=TEMP.WORK
                    FROM  WORKTEACHER INNER JOIN (SELECT WORKDETAIL.TEACHERNO,SUM(WORKDETAIL.WORK*WORKDETAIL.REPEAT) AS WORK FROM WORKS INNER JOIN WORKDETAIL ON WORKDETAIL.MAP=WORKS.ID
                    WHERE WORKS.YEAR=".$year." AND TERM=1 AND DISABLE=0 AND WORKS.WORKTYPE NOT IN ('H','K','L')
                    GROUP BY WORKDETAIL.TEACHERNO) AS TEMP ON TEMP.TEACHERNO=WORKTEACHER.TEACHERNO
                    WHERE WORKTEACHER.YEAR=".$year);
            if( $effectRow>0) $info.= $effectRow.'条工作量数据更新！</br>';


            $Model->execute("UPDATE WORKTEACHER
                  SET WORKONEUP=0
                  WHERE WORKTEACHER.YEAR=".$year);
            $effectRow=$Model->execute("UPDATE WORKTEACHER
                    SET WORKONEUP=TEMP.WORK
                    FROM WORKTEACHER
                    INNER JOIN (
                    SELECT WORKDETAIL.TEACHERNO,SUM(WORKDETAIL.WORK*WORKDETAIL.REPEAT) AS WORK FROM WORKS INNER JOIN WORKDETAIL ON WORKS.ID=WORKDETAIL.MAP
                    WHERE WORKS.YEAR=".$year." AND TERM=1 AND WORKS.REM LIKE '%专升本%'  AND DISABLE=0 AND WORKS.WORKTYPE NOT IN ('H','K','L')
                    GROUP BY WORKDETAIL.TEACHERNO) AS TEMP ON TEMP.TEACHERNO=WORKTEACHER.TEACHERNO
                    WHERE WORKTEACHER.YEAR=".$year);
            if( $effectRow>0) $info.= $effectRow.'条专升本数据更新(仅提取备注中写明”专升本“的课程)！</br>';

            $Model->execute("UPDATE WORKTEACHER
                  SET WORKONEPRATICE=0
                  WHERE WORKTEACHER.YEAR=".$year);
            $effectRow=$Model->execute("UPDATE WORKTEACHER
                    SET WORKONEPRATICE=TEMP.WORK
                    FROM WORKTEACHER
                    INNER JOIN (
                    SELECT WORKDETAIL.TEACHERNO,SUM(WORKDETAIL.WORK*WORKDETAIL.REPEAT) AS WORK FROM WORKS INNER JOIN WORKDETAIL ON WORKS.ID=WORKDETAIL.MAP
                    WHERE WORKS.YEAR=".$year."  AND TERM=1 AND WORKDETAIL.TYPE='B' AND DISABLE=0 AND WORKS.WORKTYPE NOT IN ('H','K','L')
                    GROUP BY WORKDETAIL.TEACHERNO) AS TEMP ON TEMP.TEACHERNO=WORKTEACHER.TEACHERNO");
            if( $effectRow>0) $info.= $effectRow.'条实践指导工作量数据被更新)！</br>';


            $error=$Trans->getDBError();
            if($error!=''){
                $info=$error;
                $status=0;
                $Trans->rollback();
            }
            else{
                $Trans->commit();
                $info=$info!=''?$info:'没有数据更新！';
                $status=1;
            }


        }
        else{
            $info="学年学期参数不正确！";
        }
        $result=array('info'=>$info,'status'=>$status);
        $this->ajaxReturn($result,'JSON');

    }
    public function initwork2($year="2015"){
        $info="";
        $status=1;
        if(is_numeric($year)){
            $Trans=D();
            $Trans->startTrans();
            $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
            $effectRow=$Model->execute("UPDATE WORKTEACHER
                    SET WORKTWO=0
                    WHERE WORKTEACHER.YEAR=".$year);
            if( $effectRow>0) $info.= $effectRow.'条工作量初始化！</br>';


            $effectRow=$Model->execute("UPDATE WORKTEACHER
                    SET WORKTWO=TEMP.WORK
                    FROM  WORKTEACHER INNER JOIN (SELECT WORKDETAIL.TEACHERNO,SUM(WORKDETAIL.WORK*WORKDETAIL.REPEAT) AS WORK FROM WORKS INNER JOIN WORKDETAIL ON WORKDETAIL.MAP=WORKS.ID
                    WHERE WORKS.YEAR=".$year." AND TERM=2 AND DISABLE=0 AND WORKS.WORKTYPE NOT IN ('H','K','L')
                    GROUP BY WORKDETAIL.TEACHERNO) AS TEMP ON TEMP.TEACHERNO=WORKTEACHER.TEACHERNO
                    WHERE WORKTEACHER.YEAR=".$year);
            if( $effectRow>0) $info.= $effectRow.'条工作量数据更新！</br>';


            $Model->execute("UPDATE WORKTEACHER
                  SET WORKTWOUP=0
                  WHERE WORKTEACHER.YEAR=".$year);
            $effectRow=$Model->execute("UPDATE WORKTEACHER
                    SET WORKTWOUP=TEMP.WORK
                    FROM WORKTEACHER
                    INNER JOIN (
                    SELECT WORKDETAIL.TEACHERNO,SUM(WORKDETAIL.WORK*WORKDETAIL.REPEAT) AS WORK FROM WORKS INNER JOIN WORKDETAIL ON WORKS.ID=WORKDETAIL.MAP
                    WHERE WORKS.YEAR=".$year." AND TERM=2 AND WORKS.REM LIKE '%专升本%'  AND DISABLE=0 AND WORKS.WORKTYPE NOT IN ('H','K','L')
                    GROUP BY WORKDETAIL.TEACHERNO) AS TEMP ON TEMP.TEACHERNO=WORKTEACHER.TEACHERNO
                    WHERE WORKTEACHER.YEAR=".$year);
            if( $effectRow>0) $info.= $effectRow.'条专升本数据更新(仅提取备注中写明”专升本“的课程)！</br>';

            $Model->execute("UPDATE WORKTEACHER
                  SET WORKTWOPRATICE=0
                  WHERE WORKTEACHER.YEAR=".$year);
            $effectRow=$Model->execute("UPDATE WORKTEACHER
                    SET WORKTWOPRATICE=TEMP.WORK
                    FROM WORKTEACHER
                    INNER JOIN (
                    SELECT WORKDETAIL.TEACHERNO,SUM(WORKDETAIL.WORK*WORKDETAIL.REPEAT) AS WORK FROM WORKS INNER JOIN WORKDETAIL ON WORKS.ID=WORKDETAIL.MAP
                    WHERE WORKS.YEAR=".$year."  AND TERM=2 AND WORKDETAIL.TYPE='B' AND DISABLE=0 AND WORKS.WORKTYPE NOT IN ('H','K','L')
                    GROUP BY WORKDETAIL.TEACHERNO) AS TEMP ON TEMP.TEACHERNO=WORKTEACHER.TEACHERNO");
            if( $effectRow>0) $info.= $effectRow.'条实践指导工作量数据被更新)！</br>';


            $error=$Trans->getDBError();
            if($error!=''){
                $info=$error;
                $status=0;
                $Trans->rollback();
            }
            else{
                $Trans->commit();
                $info=$info!=''?$info:'没有数据更新！';
                $status=1;
            }


        }
        else{
            $info="学年学期参数不正确！";
        }
        $result=array('info'=>$info,'status'=>$status);
        $this->ajaxReturn($result,'JSON');

    }
}