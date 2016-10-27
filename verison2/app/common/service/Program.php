<?php
// +----------------------------------------------------------------------
// |  [ MAKE YOUR WORK EASIER]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2016 http://www.nbcc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: fangrenfu <fangrenfu@126.com> 2016/6/19 8:48
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\Item;
use app\common\access\MyAccess;
use app\common\access\MyService;

/**教学计划
 * Class Program
 * @package app\common\service
 */
class Program extends MyService{
    /**获取教学计划列表
     * @param int $page
     * @param int $rows
     * @param string $programno
     * @param string $programname
     * @param string $school
     * @return array|null
     */
    public function  getList($page=1, $rows=20,$programno="%",$programname="%",$school=''){
        $result=null;
        $condition=null;
        if($programno!='%') $condition['programs.programno']=array('like',$programno);
        if($programname!='%') $condition['programs.progname']=array('like',$programname);
        if($school!='') $condition['programs.school']=$school;
        $data= $this->query->table('programs')
            ->join('schools','schools.school=programs.school')
            ->join('zo','zo.name=programs.valid')
            ->join('programtype','programtype.name=programs.type')
            ->field('rtrim(programno) programno,rtrim(progname) progname,date,valid,rtrim(zo.value) validname,schools.school,rtrim(schools.name) schoolname,type,rtrim(programtype.value) typename,
            rtrim(url) url,rtrim(rem) rem')
            ->where($condition)->page($page,$rows)->order('programno')->select();
        $count= $this->query->table('programs')->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }

    /**更新教学计划
     * @param $postData
     * @return array
     * @throws \Exception
     */
    public function  update($postData){
        $updateRow=0;
        $deleteRow=0;
        $insertRow=0;
        $errorRow=0;
        $info="";
        $status=1;
        //更新部分
        //开始事务
        $this->query->startTrans();
        try {
            if (isset($postData["inserted"])) {
                $updated = $postData["inserted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $data = null;
                    $data['programno'] = $one->programno;
                    $data['progname'] = $one->progname;
                    $data['date'] = $one->date;
                    $data['valid'] = $one->valid;
                    $data['type'] = $one->type;
                    $data['school'] = $one->school;
                    $data['url'] = $one->url;
                    $data['rem'] = $one->rem;
                    if ($data['school'] != session('S_USER_SCHOOL') && session('S_MANAGE') == 0) {
                        $info .= '无法为其它学院添加教学计划'.$one->progname .'</br>';
                        $status=0;
                        $errorRow++;
                    }
                    else {
                        $row = $this->query->table('programs')->insert($data);
                        if ($row > 0)
                            $insertRow++;
                    }
                }
            }
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $data = null;
                    $condition['programno'] = $one->programno;
                    $data['progname'] = $one->progname;
                    $data['date'] = $one->date;
                    $data['valid'] = $one->valid;
                    $data['type'] = $one->type;
                    $data['school'] = $one->school;
                    $data['url'] = $one->url;
                    $data['rem'] = $one->rem;
                    if(MyAccess::checkProgramSchool($one->programno))
                        $updateRow += $this->query->table('programs')->where($condition)->update($data);
                    else{
                        $info.=$one->name.'不是本学院教学计划，无法更改信息</br>';
                        $errorRow++;
                        $status=0;
                    }

                }
            }
            //删除部分
            if (isset($postData["deleted"])) {
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['programno'] = $one->programno;
                    if(MyAccess::checkProgramSchool($one->programno))
                        $deleteRow += $this->query->table('programs')->where($condition)->delete();
                    else{
                        $info.=$one->name.'不是本学院教学计划，无法删除</br>';
                        $errorRow++;
                        $status=0;
                    }
                }
            }
        }
        catch(\Exception $e){
            $this->query->rollback();
            throw $e;
        }
        $this->query->commit();
        if($updateRow+$deleteRow+$insertRow+$errorRow==0){
            $status=0;
            $info="没有数据更新";
        }
        else {
            if ($updateRow > 0) $info .= $updateRow . '条更新！</br>';
            if ($deleteRow > 0) $info .= $deleteRow . '条删除！</br>';
            if ($insertRow > 0) $info .= $insertRow . '条添加！</br>';
        }
        $result=array('info'=>$info,'status'=>$status);
        return $result;
    }

    /**获取等价课程列表
     * @param int $page
     * @param int $rows
     * @param string $courseno
     * @param string $equalcourseno
     * @param string $programno
     * @param string $school
     * @return array|null
     */
    public function equalCourseList($page=1,$rows=20,$courseno='%',$equalcourseno='%',$programno='%',$school=''){
        $result=null;
        $condition=null;
        if($courseno!='%') $condition['r33.courseno']=array('like',$courseno);
        if($equalcourseno!='%') $condition['r33.eqno']=array('like',$equalcourseno);
        if($programno!='%') $condition['r33.programno']=array('like',$programno);
        if($school!='') $condition['programs.school']=$school;
        $data= $this->query->table('programs')->join('r33','r33.programno=programs.programno')
            ->join('courses c','c.courseno=r33.courseno')
            ->join('courses eqc','eqc.courseno=r33.eqno')
            ->join('schools cs','cs.school=c.school')
            ->join('schools eqs','eqs.school=eqc.school')
            ->join('schools s','s.school=programs.school')
            ->field('programs.programno,rtrim(progname) progname,s.school progschool,rtrim(s.name) progschoolname,c.courseno,rtrim(c.coursename) coursename,
            c.credits,rtrim(cs.name) schoolname,eqc.courseno eqcourseno,rtrim(eqc.coursename) eqcoursename,eqc.credits eqcredits,
            rtrim(eqs.name) eqschoolname')
            ->where($condition)->page($page,$rows)->order('programno')->select();
        $count= $this->query->table('programs')->join('r33','r33.programno=programs.programno')->where($condition)->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }

    public function equalCourseUpdate($postData){
        //检查输入有效性
        $result=null;
        if (isset($postData["inserted"])) {
            $updated = $postData["inserted"];
            $updated= json_decode($updated);
            if(MyAccess::checkProgramSchool($updated->programno)) {
                Item::getCourseItem($updated->courseno);
                Item::getCourseItem($updated->eqcourseno);
                $data['programno'] = $updated->programno;
                $data['courseno'] = $updated->courseno;
                $data['eqno'] = $updated->eqcourseno;
                $this->query->table('r33')->insert($data);
                $result = ['status' => 1, 'info' => '添加成功'];
            }
            else{
                $result = ['status' => 0, 'info' => '您无法为其他学院教学计划的添加等价课程'];
            }
        }

        if (isset($postData["deleted"])) {
            $updated = $postData["deleted"];
            $updated= json_decode($updated);
            if(MyAccess::checkProgramSchool($updated->programno)) {
                Item::getCourseItem($updated->courseno);
                Item::getCourseItem($updated->eqcourseno);
                $condition['programno'] = $updated->programno;
                $condition['courseno'] = $updated->courseno;
                $condition['eqno'] = $updated->eqcourseno;
                $this->query->table('r33')->where($condition)->delete();
                $result = ['status' => 1, 'info' => '删除成功'];
            }
            else
                $result = ['status' => 0, 'info' => '您无法删除其他学院教学计划的等价课程'];
        }
        return $result;
    }

} 