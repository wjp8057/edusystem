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
// | Created:2016/8/30 14:36
// +----------------------------------------------------------------------

namespace app\common\vendor;
use app\common\service\User;

class CAS {
    /**统一身份登录。
     * @param string $ticket
     * @return int
     */
    static public function signinNormal($ticket=""){
        $service= 'http://'.$_SERVER["SERVER_NAME"]."/web/home/login/caslogin";
        $ssoURL="http://ids.nbcc.cn/sso/";
        if($ticket==""){
            header('Location:'.$ssoURL."login?service=".$service);
        }
        $validateURL=$ssoURL."serviceValidate?ticket=".$ticket."&service=".$service;
        $string= file_get_contents($validateURL);
     //   print_r($string);
        $p = xml_parser_create();
        xml_parse_into_struct($p, $string, $vals, $index);
        xml_parser_free($p);
     //   echo "\nVals array\n";
        $userid=($vals[2]["value"]);
        $user=new User();

        $result=$user->signInAsTeacherNo($userid);
        if($result==0)
            $result=$user->signInAsStudent($userid);
        return $result;
    }
}