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
        return json($result);
    }

} 