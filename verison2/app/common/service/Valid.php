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

/**许可时间区间
 * Class Area
 * @package app\common\service
 */
class Valid extends MyService{
    public function update($start,$stop,$type){
        $condition=null;
        $condition['type']=$type;
        $data['start']=$start;
        $data['stop']=$stop;
        $this->query->table('valid')->where($condition)->update($data);
        return array('info'=>'设置完成','status'=>'1');
    }

} 