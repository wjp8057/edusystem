<?php
// +----------------------------------------------------------------------
// |  [ MAKE YOUR WORK EASIER]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2016 http://www.nbcc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: fangrenfu <fangrenfu@126.com>
// +----------------------------------------------------------------------

namespace app\common\service;


use app\common\access\MyService;

/**学院信息
 * Class School
 * @package app\common\service
 */
class School extends MyService {
    /**获取学院列表
     * @param int $page
     * @param int $rows
     * @return array|null
     */
    public function getList($page=1,$rows=20){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        $data=$this->query->table('schools')->field('school,rtrim(name) name,active,manage')->order('school')->page($page,$rows)->where($condition)->select();
        $count=$this->query->table('schools')->where($condition)->count();
        if(is_array($data)&&count($data)>0){ //小于0的话就不返回内容，防止IE下无法解析rows为NULL时的错误。
            $result=array('total'=>$count,'rows'=>$data);
        }
        return $result;
    }

    /**更新学院信息
     * @param $postData
     * @return array
     * @throws \Exception
     */
    public function  update($postData)
    {
        $updateRow = 0;
        $deleteRow = 0;
        $insertRow = 0;
        //更新部分
        //开始事务
        $this->query->startTrans();
        try {
            if (isset($postData["updated"])) {
                $updated = $postData["updated"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $data=null;
                    $condition['school'] = $one->school;
                    $data['name'] = $one->name;
                    $data['active'] = $one->active;
                    $data['manage'] = (int)$one->manage;
                    $updateRow += $this->query->table('schools')->where($condition)->update($data);
                }
            }
            //删除部分
            if (isset($postData["deleted"])) {
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $data=null;
                    $condition['school'] = $one->school;
                    $deleteRow += $this->query->table('schools')->where($condition)->delete();
                }
            }
            if (isset($postData["inserted"])) {
                $updated = $postData["inserted"];
                $listUpdated = json_decode($updated);
                $data = null;
                foreach ($listUpdated as $one) {
                    $data['school'] = $one->school;
                    $data['name'] = $one->name;
                    $data['active'] = (int)$one->active;
                    $data['manage'] = (int)$one->manage;
                    $row = $this->query->table('schools')->insert($data);
                    if ($row > 0)
                        $insertRow++;
                }
            }
        } catch (\Exception $e) {
            $this->query->rollback();
            throw $e;
        }
        $this->query->commit();
        $info = '';
        if ($updateRow > 0) $info .= $updateRow . '条更新！</br>';
        if ($deleteRow > 0) $info .= $deleteRow . '条删除！</br>';
        if ($insertRow > 0) $info .= $insertRow . '条添加！</br>';
        $status = 1;
        if($info=='') {
            $info="没有数据被更新";
            $status=0;
        }
        $result = array('info' => $info, 'status' => $status);
        return $result;
    }
} 