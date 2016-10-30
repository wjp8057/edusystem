<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 8:58
 */

namespace app\common\service;


use app\common\access\MyAccess;
use app\common\access\MyService;

/**教师信息
 * Class Teacher
 * @package app\common\service
 */
class Teacher extends  MyService
{
    /**读取
     * @param int $page
     * @param int $rows
     * @param $teacherno
     * @param $name
     * @param $school
     * @return array|null
     */
    function getList($page = 1, $rows = 20, $teacherno, $name, $school)
    {
        $result=['total'=>0,'rows'=>[]];
        $condition = null;
        if ($teacherno != '%') $condition['teachers.teacherno'] = array('like', $teacherno);
        if ($name != '%') $condition['teachers.name'] = array('like', $name);
        if ($school != '') $condition['teachers.school'] = $school;
        $data = $this->query->table('teachers')->join('teachertype ', ' teachertype.name=teachers.type')
            ->join('teacherjob ', ' teacherjob.job=teachers.job','LEFT')
            ->join('positions ', ' positions.name=teachers.position')
            ->join('schools ', ' schools.school=teachers.school')
            ->join('sexcode ', ' sexcode.code=teachers.sex')
            ->page($page, $rows)
            ->field('teachers.enteryear,rtrim(teachers.name) name,teachers.teacherno,teachers.position,rtrim(positions.value) positionname,teachers.type,rtrim(teachertype.value) typename,
            rtrim(teacherjob.name) jobname,teachers.job,rtrim(schools.name) schoolname,teachers.school,teachers.sex,rtrim(sexcode.name) sexname,teachers.rem')
            ->where($condition)->order('teacherno')->select();
        $count = $this->query->table('teachers')->where($condition)->count();
        if (is_array($data) && count($data) > 0)
            $result = array('total' => $count, 'rows' => $data);
        return $result;
    }

    /**更新教师信息
     * @param $postData
     * @return array
     * @throws \Exception
     */
    public function  update($postData)
    {
        $updateRow = 0;
        $deleteRow = 0;
        $insertRow = 0;
        $errorRow = 0;
        $info = "";
        $status = 1;
        //更新部分
/*        //开始事务
        $this->query->startTrans();
        try {*/
            if (isset($postData["inserted"])) {
                $updated = $postData["inserted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $data = null;
                    $data['teacherno'] = $one->teacherno;
                    $data['enteryear'] = $one->enteryear;
                    $data['name'] = $one->name;
                    $data['school'] = $one->school;
                    $data['sex'] = $one->sex;
                    $data['position'] = $one->position;
                    $data['type'] = $one->type;
                    $data['job'] = $one->job;
                    $row = $this->query->table('teachers')->insert($data);
                    $user = new User();
                    $user->addUser($one->teacherno, $one->teacherno, '123456');
                    if ($row > 0)
                        $insertRow++;
                }
            }
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $data = null;
                    $condition['teacherno'] = $one->teacherno;
                    $data['enteryear'] = $one->enteryear;
                    $data['name'] = $one->name;
                    $data['school'] = $one->school;
                    $data['sex'] = $one->sex;
                    $data['position'] = $one->position;
                    $data['type'] = $one->type;
                    $data['job'] = $one->job;
                    if (MyAccess::checkTeacherSchool($one->teacherno))
                        $updateRow += $this->query->table('teachers')->where($condition)->update($data);
                    else {
                        $info .= $one->name . '不是本学院教师，无法更改信息</br>';
                        $errorRow++;
                        $status = 0;
                    }

                }
            }
            //删除部分
            if (isset($postData["deleted"])) {
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['teacherno'] = $one->teacherno;
                    if (MyAccess::checkTeacherSchool($one->teacherno)) {
                        $user = new User();
                        if($user->deleteUserByTeacherNo($one->teacherno))
                            $deleteRow++;
                    } else {
                        $info .= $one->name . '不是本学院教师，无法删除</br>';
                        $errorRow++;
                        $status = 0;
                    }
                }
            }
       /* } catch (\Exception $e) {
            $this->query->rollback();
            throw $e;
        }
        $this->query->commit();*/
        if ($updateRow + $deleteRow + $insertRow + $errorRow == 0) {
            $status = 0;
            $info = "没有数据更新";
        } else {
            if ($updateRow > 0) $info .= $updateRow . '条更新！</br>';
            if ($deleteRow > 0) $info .= $deleteRow . '条删除！</br>';
            if ($insertRow > 0) $info .= $insertRow . '条添加！</br>';
        }
        $result = array('info' => $info, 'status' => $status);
        return $result;

    }
}