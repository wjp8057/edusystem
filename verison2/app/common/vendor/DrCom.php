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
// | Created:2016/11/18 9:45
// +----------------------------------------------------------------------

namespace app\common\vendor;


class DrCom {
    public static  function stop($userid,$order=0){
        //开通帐号
        $url="http://172.18.0.100:8080/DrcomSrv/DrcomServlet?business=";
        $string='003'.$userid.'	6888	'.date("YmdHis",time()).$order;
        $base64=base64_encode($string);
        file_get_contents($url.$base64);
        //踢下线
        $string='014'.$userid.'	6888	'.date("YmdHis",time()).$order;
        $base64=base64_encode($string);
        $result=file_get_contents($url.$base64);
        //账号禁用
     /*   $string='004'.$userid.'	0	6999	'.date("YmdHis",time());
        $base64=base64_encode($string);
        $result=file_get_contents($url.$base64);*/
        if($result=="E00")
            return true;
        return false;

    }
    public static  function start($userid){
        $url="http://172.18.0.100:8080/DrcomSrv/DrcomServlet?business=";
        $string='005'.$userid.'	0	6999	'.date("YmdHis",time());
        $base64=base64_encode($string);
        $result=file_get_contents($url.$base64);
        if($result=="E00")
            return true;
        return false;
    }
}