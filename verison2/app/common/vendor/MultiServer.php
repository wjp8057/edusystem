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
    //选择一个服务器
    public static function selectServer(){
        $config=config('logdb');
        $condition=null;
        $condition['status']=1;
        $result=Db::connect($config)->table('server')->field('rtrim(ip) ip,cpu')->where($condition)->order('cpu')->find();
        if($result!=null) {
            return  $result['ip'];
        }
        else
           return null;
    }

    // 切换服务器
    public static function changeServer($serverip,$pageurl){
        $username=session('S_USER_NAME');
        $condition['username']=$username;
        $time=time();
        $data['timeflag']=$time;
        Db::table('sessions')->where($condition)->update($data);
        $request = Request::instance();
        header('Location:http://' . $serverip . $request->root() .$pageurl."?username=".$username."&timeflag=".$time);
    }
    public static function checkFlag($username,$timeflag){
        $time=time();
        //如果大于10分钟
        if((int)$time-(int)$timeflag>600)
            return ['status'=>false,'info'=>'凭据失效'];

        $condition['username']=$username;
        $condition['timeflag']=$timeflag;
        $result=Db::table('sessions')->where($condition)->find();
        //凭据清空
        $condition=null;
        $condition['username']=$username;
        $data['timeflag']='';
        Db::table('sessions')->where($condition)->update($data);
        if($result==null)
            return ['status'=>false,'info'=>'凭据无效'];
        else
            return ['status'=>true,'info'=>'校验成功'];
    }


}