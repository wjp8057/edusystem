<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/7
 * Time: 9:29
 */

namespace app\common\service;


use app\common\access\MyService;

/**职称信息
 * Class Position
 * @package app\common\service
 */
class Position extends  MyService {
    /**读取
     * @param int $page
     * @param int $rows
     * @return array|null
     */
    function getList($page=1,$rows=20){
        $result=['total'=>0,'rows'=>[]];
        $condition=null;
        $data=$this->query->table('positions')->join('teacherlevel ',' teacherlevel.level=positions.level')
            ->page($page,$rows)->field('positions.name,rtrim(positions.value) as value,positions.level,rtrim(teacherlevel.name) as levelname')->select();
        $count= $this->query->table('positions')->count();
        if(is_array($data)&&count($data)>0)
            $result=array('total'=>$count,'rows'=>$data);
        return $result;
    }

    /**更新
     * @param $postData
     * @return array
     * @throws \Exception
     */
    public function  update($postData){
        $updateRow=0;
        $deleteRow=0;
        $insertRow=0;
        //更新部分
        //开始事务
        $this->query->startTrans();
        try {
            if (isset($postData["inserted"])) {
                $updated = $postData["inserted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $data = null;
                    $data['name'] = $one->name;
                    $data['value'] = $one->value;
                    $data['level']=$one->level;
                    $row = $this->query->table('positions')->insert($data);
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
                    $condition['name'] = $one->name;
                    $data['value'] = $one->value;
                    $data['level']=$one->level;
                    $updateRow += $this->query->table('positions')->where($condition)->update($data);
                }
            }
            //删除部分
            if (isset($postData["deleted"])) {
                $updated = $postData["deleted"];
                $listUpdated = json_decode($updated);
                foreach ($listUpdated as $one) {
                    $condition = null;
                    $condition['name'] = $one->name;
                    $deleteRow += $this->query->table('positions')->where($condition)->delete();
                }
            }
        }
        catch(\Exception $e){
            $this->query->rollback();
            throw $e;
        }
        $this->query->commit();
        $info='';
        if($updateRow>0) $info.=$updateRow.'条更新！</br>';
        if($deleteRow>0) $info.=$deleteRow.'条删除！</br>';
        if($insertRow>0) $info.=$insertRow.'条添加！</br>';
        $status=1;
        if($info=='') {
            $info="没有数据被更新";
            $status=0;
        }
        $result=array('info'=>$info,'status'=>$status);
        return $result;
    }
}