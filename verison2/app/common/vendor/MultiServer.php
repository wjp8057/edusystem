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
// | Created:2016/12/16 10:16
// +----------------------------------------------------------------------

namespace app\common\vendor;

//多服务器自动选择，需要结合服务器资源检测工具
use think\Db;
use think\Request;

class MultiServer {

    public static function selectServer(){
        $config=config('logdb');
        $condition=null;
        $condition['status']=1;
        $result=Db::connect($config)->table('server')->field('rtrim(ip) ip,cpu')->where($condition)->order('cpu')->find();
        if($result!=null) {
            $ip = $result['ip'];
            $request = Request::instance();
            if(session('S_LOGIN_TYPE')==2) {
                header('Location:http://'.$ip.'/' . $request->root().'/');
        }
        else
            echo '无可用服务器';

    }
}