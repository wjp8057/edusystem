<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/5/14
 * Time: 9:57
 */
class CheckAction extends RightAction {
    public function query($page=1,$rows=20,$year='2014',$term='2',$coursename='',$courseno='',$school='',$checked='',$worktype='',$group='',$lock='',$disable=''){
        $Obj=D('works');
        $condition['works.year']=$year;
        $condition['works.term']=$term;
        if($coursename!='')  $condition['courses.coursename']=array('like',$coursename);
        if($courseno!='')  $condition['works.courseno']=array('like',$courseno);
        if($group!='')  $condition['works.groups']=array('like',$group);
        if($school!='')  $condition['works.school']=array('like',$school);
        if($worktype!='')  $condition['works.worktype']=$worktype;
        if($checked!='')  $condition['works.checked']=(int)$checked;
        if($lock!='')  $condition['works.lock']=(int)$lock;
        if($disable!='')  $condition['works.disable']=(int)$disable;
        $data=$Obj->join('courses on works.courseno=courses.courseno')
            ->join('schools on schools.school=works.school')
            ->join('worktype on worktype.type=works.worktype')
            ->join('courseclassname as t on t.year=works.year and t.term=works.term and t.courseno=works.courseno and t.[group]=works.[groups]')
            //字段名不区分大小写
            ->field('works.lock,id,works.year,works.term,works.checked, works.courseno,works.[groups] as [group],courses.coursename,schools.name as schoolname,works.total,works.worktype,worktype.name as worktypename,works.stand,works.week
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
                $data['LOCK']=$one->lock;
                $data['DISABLE']=$one->disable;
                $data['REM']=$one->rem;
                if($one->worktype=='A'&&$one->lock==0) {
                    $data['WORK'] = $one->total * ($one->factor + $one->addfactor);
                    $data['ADDFACTOR']=$one->addfactor;
                }
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
    public function detail($page=1,$rows=20,$map=0){
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
    public function init($year="",$term=""){
        $info="";
        $status=1;
        if(is_numeric($year)&&is_numeric($term)){
            $Trans=D();
            $Trans->startTrans();
            $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
            $effectRow=$Model->execute("INSERT INTO WORKS(YEAR,TERM,COURSENO,[GROUPS],TOTAL,WORKTYPE,STAND,ATTENDENT,SCHOOL,WEEK)
              SELECT SCHEDULEPLAN.YEAR,SCHEDULEPLAN.TERM,SCHEDULEPLAN.COURSENO,SCHEDULEPLAN.[GROUP],COURSES.TOTAL,COURSES.WORKTYPE,COURSES.STAND,isnull(T.AMOUNT,0),COURSES.SCHOOL,COURSES.WEEK
              FROM COURSES INNER JOIN SCHEDULEPLAN ON SCHEDULEPLAN.COURSENO=COURSES.COURSENO
              LEFT JOIN (SELECT COURSENO,[GROUP],COUNT(*) AS AMOUNT FROM R32 WHERE YEAR=".$year." AND TERM=".$term." GROUP BY COURSENO,[GROUP]) AS T
             ON T.COURSENO=SCHEDULEPLAN.COURSENO AND T.[GROUP]=SCHEDULEPLAN.[GROUP]
             WHERE SCHEDULEPLAN.YEAR=".$year." AND SCHEDULEPLAN.TERM=".$term." AND NOT EXISTS(SELECT * FROM WORKS WHERE WORKS.YEAR=SCHEDULEPLAN.YEAR AND WORKS.TERM=SCHEDULEPLAN.TERM
             AND WORKS.COURSENO=SCHEDULEPLAN.COURSENO and WORKS.[GROUPS]=SCHEDULEPLAN.[GROUP])");
            if( $effectRow>0) $info.= $effectRow.'条记录新插入！</br>';


            $effectRow=$Model->execute("UPDATE WORKS
            SET WORKS.WORKTYPE=COURSES.WORKTYPE,WORKS.STAND=COURSES.STAND,WORKS.ATTENDENT=ISNULL(T.AMOUNT,0),WORKS.WEEK=COURSES.WEEK,WORKS.TOTAL=COURSES.TOTAL
             FROM WORKS INNER JOIN COURSES ON COURSES.COURSENO=WORKS.COURSENO INNER JOIN SCHEDULEPLAN ON SCHEDULEPLAN.COURSENO=WORKS.COURSENO AND SCHEDULEPLAN.[GROUP]=WORKS.[GROUPS]
             AND WORKS.YEAR=SCHEDULEPLAN.YEAR AND WORKS.TERM=SCHEDULEPLAN.TERM
             LEFT JOIN (SELECT COURSENO,[GROUP],COUNT(*) AS AMOUNT FROM R32 WHERE YEAR=".$year." AND TERM=".$term." GROUP BY COURSENO,[GROUP]) AS T
             ON T.COURSENO=SCHEDULEPLAN.COURSENO AND T.[GROUP]=SCHEDULEPLAN.[GROUP]
             WHERE WORKS.LOCK=0 AND SCHEDULEPLAN.YEAR=".$year." AND SCHEDULEPLAN.TERM=".$term."");
            if( $effectRow>0) $info.= $effectRow.'条工作量表数据被同步！</br>';


            $effectRow=$Model->execute("UPDATE WORKS
            SET FACTOR=1+(ATTENDENT-STAND)*1.0/ATTENDENT
            WHERE  WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term." AND WORKTYPE='A' AND ATTENDENT!=0");
            if( $effectRow>0) $info.="计算普通课程系数，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET FACTOR=0.9
            WHERE   WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term." and FACTOR<0.9 AND WORKTYPE='A'");
            if( $effectRow>0) $info.="系数最低为0.9，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET FACTOR=1.0
            WHERE  WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term." and FACTOR<1.0 AND WORKTYPE='A' AND COURSENO NOT LIKE '08%'");
            if( $effectRow>0) $info.="非公选课系数最低为1.0，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET FACTOR=1.6
            WHERE  WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term." and FACTOR>1.6 AND WORKTYPE='A'");
            if( $effectRow>0) $info.="系数最高为1.6，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET FACTOR=(1.0+FACTOR)/2.0
            FROM WORKS INNER JOIN SCHEDULEPLAN ON SCHEDULEPLAN.COURSENO+SCHEDULEPLAN.[GROUP]=WORKS.COURSENO+WORKS.[GROUPS] AND WORKS.YEAR=SCHEDULEPLAN.YEAR AND  WORKS.TERM=SCHEDULEPLAN.TERM
            WHERE  WORKS.LOCK=0 AND  EXISTS(SELECT * FROM TEACHERPLAN INNER JOIN TEACHERS ON TEACHERS.TEACHERNO=TEACHERPLAN.TEACHERNO WHERE TEACHERPLAN.MAP=SCHEDULEPLAN.RECNO AND TEACHERS.SCHOOL='10') AND WORKTYPE='A'
            AND EXISTS(SELECT * FROM TEACHERPLAN INNER JOIN TEACHERS ON TEACHERS.TEACHERNO=TEACHERPLAN.TEACHERNO WHERE TEACHERPLAN.MAP=SCHEDULEPLAN.RECNO AND TEACHERS.SCHOOL!='10')
            AND WORKS.YEAR=".$year." AND WORKS.TERM=".$term);
            if( $effectRow>0) $info.="同时有外聘教师和校内老师的的系数取平均值，共". $effectRow.'条！</br>';


            $effectRow=$Model->execute("UPDATE WORKS
            SET FACTOR=1.0
            FROM WORKS INNER JOIN SCHEDULEPLAN ON SCHEDULEPLAN.COURSENO+SCHEDULEPLAN.[GROUP]=WORKS.COURSENO+WORKS.[GROUPS] AND WORKS.YEAR=SCHEDULEPLAN.YEAR AND  WORKS.TERM=SCHEDULEPLAN.TERM
            WHERE  WORKS.LOCK=0 AND  EXISTS(SELECT * FROM TEACHERPLAN INNER JOIN TEACHERS ON TEACHERS.TEACHERNO=TEACHERPLAN.TEACHERNO WHERE TEACHERPLAN.MAP=SCHEDULEPLAN.RECNO AND TEACHERS.SCHOOL='10') AND WORKTYPE='A'
            AND NOT EXISTS(SELECT * FROM TEACHERPLAN INNER JOIN TEACHERS ON TEACHERS.TEACHERNO=TEACHERPLAN.TEACHERNO WHERE TEACHERPLAN.MAP=SCHEDULEPLAN.RECNO AND TEACHERS.SCHOOL!='10')
            AND WORKS.YEAR=".$year." AND WORKS.TERM=".$term);
            if( $effectRow>0) $info.="只有外聘老师的系数为1，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET WORK=TOTAL*(FACTOR+ADDFACTOR)
            WHERE  WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term." AND WORKTYPE='A'");
            if( $effectRow>0) $info.="计算普通课程工作量，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET WORK=TOTAL*0.5
            WHERE  WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term."  AND WORKTYPE='B'");
            if( $effectRow>0) $info.="计算集中实训，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET WORK=WEEK*ATTENDENT*0.15
            WHERE  WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term."  AND WORKTYPE in ('C','I')");
            if( $effectRow>0) $info.="计算分散实训与毕业实习，共". $effectRow.'条！</br>';


            $effectRow=$Model->execute("UPDATE WORKS
            SET WORK=ATTENDENT*2
            WHERE  WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term."  AND WORKTYPE='D'");
            if( $effectRow>0) $info.="计算毕业设计指导，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET WORK=ATTENDENT*4
            FROM WORKS INNER JOIN COURSEPLAN ON COURSEPLAN.COURSENO=WORKS.COURSENO AND COURSEPLAN.[GROUP]=WORKS.[GROUPS]
            AND WORKS.YEAR=COURSEPLAN.YEAR AND WORKS.TERM=COURSEPLAN.TERM
            WHERE  WORKS.LOCK=0 AND WORKS.YEAR=".$year." AND WORKS.TERM=".$term."  AND WORKS.WORKTYPE='D'AND COURSEPLAN.CLASSNO LIKE '__4%'");
            if( $effectRow>0) $info.="计算本科毕业设计指导，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET WORK=TOTAL*2
            WHERE  WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term."  AND WORKTYPE='E'");
            if( $effectRow>0) $info.="计算专题讲座，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET WORK=ATTENDENT*1.2
            WHERE  WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term."  AND WORKTYPE='F'");
            if( $effectRow>0) $info.="计算指导试讲、微格教学，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET WORK=ATTENDENT*1.0/STAND*TOTAL
            WHERE  WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term."  AND WORKTYPE in('J','L')");
            if( $effectRow>0) $info.="计算形势与政策、公司制，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET WORK=ATTENDENT*1.0/STAND*TOTAL*1.2
            WHERE  WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term."  AND WORKTYPE='K'");
            if( $effectRow>0) $info.="计算职业素养，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("INSERT INTO WORKDETAIL(TEACHERNO,MAP,WORK,TYPE)
            SELECT DISTINCT TEACHERPLAN.TEACHERNO,WORKS.ID,WORKS.WORK,'A'
            FROM WORKS INNER JOIN SCHEDULEPLAN ON SCHEDULEPLAN.COURSENO+SCHEDULEPLAN.[GROUP]=WORKS.COURSENO+WORKS.[GROUPS]
            AND SCHEDULEPLAN.YEAR=WORKS.YEAR AND SCHEDULEPLAN.TERM=WORKS.TERM
            INNER JOIN TEACHERPLAN ON TEACHERPLAN.MAP=SCHEDULEPLAN.RECNO
            WHERE WORKS.YEAR=".$year." AND WORKS.TERM=".$term."  AND TEACHERPLAN.TEACHERNO!='000000'AND NOT EXISTS (SELECT * FROM WORKDETAIL
            WHERE  WORKS.LOCK=0 AND WORKDETAIL.MAP=WORKS.ID AND WORKDETAIL.TEACHERNO=TEACHERPLAN.TEACHERNO)  ");
            if( $effectRow>0) $info.="进行默认分配，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET SETTLED=ISNULL(T.DETAIL,0),PSETTLED=ISNULL(T2.DETAIL,0)
            FROM WORKS INNER JOIN (SELECT MAP,SUM(WORK) AS DETAIL FROM WORKDETAIL WHERE  TYPE='A'
            GROUP BY MAP ) AS T ON T.MAP=WORKS.ID
            LEFT JOIN (SELECT MAP,SUM(WORK) AS DETAIL FROM WORKDETAIL WHERE  TYPE='B'
            GROUP BY MAP ) AS T2 ON T2.MAP=WORKS.ID
            WHERE YEAR=".$year." AND TERM=".$term."");
            if( $effectRow>0) $info.="重新计算已分配量，共". $effectRow.'条！</br>';

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


    public function synchro($year="",$term=""){
        $info="";
        $status=1;
        if(is_numeric($year)&&is_numeric($term)){
            $Trans=D();
            $Trans->startTrans();
            $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
            $effectRow=$Model->execute("INSERT INTO WORKS(YEAR,TERM,COURSENO,[GROUPS],TOTAL,WORKTYPE,STAND,ATTENDENT,SCHOOL,WEEK)
              SELECT SCHEDULEPLAN.YEAR,SCHEDULEPLAN.TERM,SCHEDULEPLAN.COURSENO,SCHEDULEPLAN.[GROUP],COURSES.TOTAL,COURSES.WORKTYPE,COURSES.STAND,isnull(T.AMOUNT,0),COURSES.SCHOOL,COURSES.WEEK
              FROM COURSES INNER JOIN SCHEDULEPLAN ON SCHEDULEPLAN.COURSENO=COURSES.COURSENO
              LEFT JOIN (SELECT COURSENO,[GROUP],COUNT(*) AS AMOUNT FROM R32 WHERE YEAR=".$year." AND TERM=".$term." GROUP BY COURSENO,[GROUP]) AS T
             ON T.COURSENO=SCHEDULEPLAN.COURSENO AND T.[GROUP]=SCHEDULEPLAN.[GROUP]
             WHERE SCHEDULEPLAN.YEAR=".$year." AND SCHEDULEPLAN.TERM=".$term." AND NOT EXISTS(SELECT * FROM WORKS WHERE WORKS.YEAR=SCHEDULEPLAN.YEAR AND WORKS.TERM=SCHEDULEPLAN.TERM
             AND WORKS.COURSENO=SCHEDULEPLAN.COURSENO and WORKS.[GROUPS]=SCHEDULEPLAN.[GROUP])");
            if( $effectRow>0) $info.= $effectRow.'条记录新插入！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET WORKS.WORKTYPE=COURSES.WORKTYPE,WORKS.STAND=COURSES.STAND,WORKS.ATTENDENT=ISNULL(T.AMOUNT,0),WORKS.WEEK=COURSES.WEEK,WORKS.TOTAL=COURSES.TOTAL
             FROM WORKS INNER JOIN COURSES ON COURSES.COURSENO=WORKS.COURSENO INNER JOIN SCHEDULEPLAN ON SCHEDULEPLAN.COURSENO=WORKS.COURSENO AND SCHEDULEPLAN.[GROUP]=WORKS.[GROUPS]
             AND WORKS.YEAR=SCHEDULEPLAN.YEAR AND WORKS.TERM=SCHEDULEPLAN.TERM
             LEFT JOIN (SELECT COURSENO,[GROUP],COUNT(*) AS AMOUNT FROM R32 WHERE YEAR=".$year." AND TERM=".$term." GROUP BY COURSENO,[GROUP]) AS T
             ON T.COURSENO=SCHEDULEPLAN.COURSENO AND T.[GROUP]=SCHEDULEPLAN.[GROUP]
             WHERE WORKS.LOCK=0 AND SCHEDULEPLAN.YEAR=".$year." AND SCHEDULEPLAN.TERM=".$term."");
            if( $effectRow>0) $info.= $effectRow.'条工作量表数据被同步！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET FACTOR=1+(ATTENDENT-STAND)*1.0/ATTENDENT
            WHERE  WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term." AND WORKTYPE='A' AND ATTENDENT!=0");
            if( $effectRow>0) $info.="计算普通课程系数，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET FACTOR=0.9
            WHERE   WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term." and FACTOR<0.9 AND WORKTYPE='A'");
            if( $effectRow>0) $info.="系数最低为0.9，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET FACTOR=1.0
            WHERE  WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term." and FACTOR<1.0 AND WORKTYPE='A' AND COURSENO NOT LIKE '08%'");
            if( $effectRow>0) $info.="非公选课系数最低为1.0，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET FACTOR=1.6
            WHERE  WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term." and FACTOR>1.6 AND WORKTYPE='A'");
            if( $effectRow>0) $info.="系数最高为1.6，共". $effectRow.'条！</br>';


            $effectRow=$Model->execute("UPDATE WORKS
            SET FACTOR=(1.0+FACTOR)/2.0
            FROM WORKS INNER JOIN SCHEDULEPLAN ON SCHEDULEPLAN.COURSENO+SCHEDULEPLAN.[GROUP]=WORKS.COURSENO+WORKS.[GROUPS] AND WORKS.YEAR=SCHEDULEPLAN.YEAR AND  WORKS.TERM=SCHEDULEPLAN.TERM
            WHERE  WORKS.LOCK=0 AND  EXISTS(SELECT * FROM TEACHERPLAN INNER JOIN TEACHERS ON TEACHERS.TEACHERNO=TEACHERPLAN.TEACHERNO WHERE TEACHERPLAN.MAP=SCHEDULEPLAN.RECNO AND TEACHERS.SCHOOL='10') AND WORKTYPE='A'
            AND EXISTS(SELECT * FROM TEACHERPLAN INNER JOIN TEACHERS ON TEACHERS.TEACHERNO=TEACHERPLAN.TEACHERNO WHERE TEACHERPLAN.MAP=SCHEDULEPLAN.RECNO AND TEACHERS.SCHOOL!='10')
            AND WORKS.YEAR=".$year." AND WORKS.TERM=".$term);
            if( $effectRow>0) $info.="同时有外聘教师和校内老师的的系数取平均值，共". $effectRow.'条！</br>';


            $effectRow=$Model->execute("UPDATE WORKS
            SET FACTOR=1.0
            FROM WORKS INNER JOIN SCHEDULEPLAN ON SCHEDULEPLAN.COURSENO+SCHEDULEPLAN.[GROUP]=WORKS.COURSENO+WORKS.[GROUPS] AND WORKS.YEAR=SCHEDULEPLAN.YEAR AND  WORKS.TERM=SCHEDULEPLAN.TERM
            WHERE  WORKS.LOCK=0 AND  EXISTS(SELECT * FROM TEACHERPLAN INNER JOIN TEACHERS ON TEACHERS.TEACHERNO=TEACHERPLAN.TEACHERNO WHERE TEACHERPLAN.MAP=SCHEDULEPLAN.RECNO AND TEACHERS.SCHOOL='10') AND WORKTYPE='A'
            AND NOT EXISTS(SELECT * FROM TEACHERPLAN INNER JOIN TEACHERS ON TEACHERS.TEACHERNO=TEACHERPLAN.TEACHERNO WHERE TEACHERPLAN.MAP=SCHEDULEPLAN.RECNO AND TEACHERS.SCHOOL!='10')
            AND WORKS.YEAR=".$year." AND WORKS.TERM=".$term);
            if( $effectRow>0) $info.="只有外聘老师的系数为1，共". $effectRow.'条！</br>';


            $effectRow=$Model->execute("UPDATE WORKS
            SET WORK=TOTAL*(FACTOR+ADDFACTOR)
            WHERE WORKS.LOCK=0 AND  YEAR=".$year." AND TERM=".$term." AND WORKTYPE='A'");
            if( $effectRow>0) $info.="计算普通课程工作量，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET WORK=TOTAL*0.5
            WHERE WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term."  AND WORKTYPE='B'");
            if( $effectRow>0) $info.="计算集中实训，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET WORK=WEEK*ATTENDENT*0.15
            WHERE  WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term."  AND WORKTYPE in ('C','I')");
            if( $effectRow>0) $info.="计算分散实训与毕业实习，共". $effectRow.'条！</br>';


            $effectRow=$Model->execute("UPDATE WORKS
            SET WORK=ATTENDENT*2
            WHERE  WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term."  AND WORKTYPE='D'");
            if( $effectRow>0) $info.="计算毕业设计指导，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET WORK=ATTENDENT*4
            FROM WORKS INNER JOIN COURSEPLAN ON COURSEPLAN.COURSENO=WORKS.COURSENO AND COURSEPLAN.[GROUP]=WORKS.[GROUPS]
            AND WORKS.YEAR=COURSEPLAN.YEAR AND WORKS.TERM=COURSEPLAN.TERM
            WHERE  WORKS.LOCK=0 AND WORKS.YEAR=".$year." AND WORKS.TERM=".$term."  AND WORKS.WORKTYPE='D'AND COURSEPLAN.CLASSNO LIKE '__4%'");
            if( $effectRow>0) $info.="计算本科毕业设计指导，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET WORK=TOTAL*2
            WHERE  WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term."  AND WORKTYPE='E'");
            if( $effectRow>0) $info.="计算专题讲座，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET WORK=ATTENDENT*1.2
            WHERE  WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term."  AND WORKTYPE='F'");
            if( $effectRow>0) $info.="计算指导试讲、微格教学，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET WORK=ATTENDENT*1.0/STAND*TOTAL
            WHERE  WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term."  AND WORKTYPE in('J','L')");
            if( $effectRow>0) $info.="计算形势与政策、公司制，共". $effectRow.'条！</br>';

            $effectRow=$Model->execute("UPDATE WORKS
            SET WORK=ATTENDENT*1.0/STAND*TOTAL*1.2
            WHERE  WORKS.LOCK=0 AND YEAR=".$year." AND TERM=".$term."  AND WORKTYPE='K'");
            if( $effectRow>0) $info.="计算职业素养，共". $effectRow.'条！</br>';


            $effectRow=$Model->execute("UPDATE WORKS
            SET SETTLED=ISNULL(T.DETAIL,0),PSETTLED=ISNULL(T2.DETAIL,0)
            FROM WORKS INNER JOIN (SELECT MAP,SUM(WORK) AS DETAIL FROM WORKDETAIL WHERE  TYPE='A'
            GROUP BY MAP ) AS T ON T.MAP=WORKS.ID
            LEFT JOIN (SELECT MAP,SUM(WORK) AS DETAIL FROM WORKDETAIL WHERE  TYPE='B'
            GROUP BY MAP ) AS T2 ON T2.MAP=WORKS.ID
            WHERE YEAR=".$year." AND TERM=".$term."");
            if( $effectRow>0) $info.="重新计算已分配量，共". $effectRow.'条！</br>';

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

    public function repeat($year="",$term=""){
        $info="";
        $status=1;
        if(is_numeric($year)&&is_numeric($term)){
            $Trans=D();
            $Trans->startTrans();
            $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
            $effectRow=$Model->execute("UPDATE WORKDETAIL
            SET REPEAT=1
            FROM WORKDETAIL INNER JOIN WORKS ON WORKS.ID=WORKDETAIL.MAP
            WHERE WORKS.WORKTYPE!='A' AND  YEAR=".$year." AND TERM=".$term."");
            if( $effectRow>0) $info.= $effectRow.'条分配记录重复课系数被重置为1！</br>';

            $effectRow=$Model->execute("UPDATE WORKDETAIL
            SET REPEAT=0
            FROM WORKDETAIL INNER JOIN WORKS ON WORKS.ID=WORKDETAIL.MAP
            WHERE WORKS.WORKTYPE='A' AND WORKS.CHECKED=0 AND YEAR=".$year." AND TERM=".$term."");
            if( $effectRow>0) $info.= $effectRow.'条未确认的普通课程重复系数重置为0！</br>';

            $effectRow=$Model->execute("UPDATE WORKDETAIL
            SET REPEAT=1
            FROM WORKDETAIL INNER JOIN WORKS ON WORKS.ID=WORKDETAIL.MAP
            WHERE WORKS.WORKTYPE='A' AND WORKS.CHECKED=1 AND YEAR=".$year." AND TERM=".$term."");
            if( $effectRow>0) $info.= $effectRow.'条已确认的普通课程重复系数初始化为1！</br>';

            $effectRow=$Model->execute("UPDATE WORKDETAIL
            SET REPEAT=0.9
            FROM WORKDETAIL INNER JOIN WORKS ON WORKS.ID=WORKDETAIL.MAP
            INNER JOIN TEACHERS ON TEACHERS.TEACHERNO=WORKDETAIL.TEACHERNO
            INNER JOIN (
            SELECT ROW_NUMBER() OVER(PARTITION BY WORKDETAIL.TEACHERNO,WORKS.COURSENO ORDER BY WORKDETAIL.WORK  DESC)  AS RANK ,WORKS.COURSENO,WORKDETAIL.TEACHERNO,WORKS.WORK,WORKDETAIL.ID
             FROM WORKS INNER JOIN WORKDETAIL ON WORKDETAIL.MAP=WORKS.ID
            WHERE WORKS.YEAR=".$year." AND WORKS.TERM=".$term.") AS T ON T.ID=WORKDETAIL.ID
            WHERE WORKS.WORKTYPE='A' AND TEACHERS.SCHOOL!='10' AND WORKDETAIL.TYPE='A' AND RANK!=1 AND CHECKED=1");
            if( $effectRow>0) $info.="共". $effectRow.'条记录非外聘任课教师重复课系数设置为0.9！</br>';

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