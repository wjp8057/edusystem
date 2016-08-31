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
//CAS Server 主机名
use app\common\service\User;

define('CAS_SERVER_HOSTNAME', 'ids.nbcc.cn');
//CAS Server 端口号
define('CAS_SERVER_PORT', 80);
//CAS Server应用名
define('CAS_SERVER_APP_NAME', '/sso');
//退出登录后返回地址 '.CAS_SERVER_HOSTNAME.':'.CAS_SERVER_PORT.'/logout.action
define('LOGOUT_ADDRESS', 'http://ids.nbcc.cn/sso/login');
require ROOT_PATH . '/vendor/CAS/CAS.php';
class CAS {
    static public function signIn()
    {
        // 初始化CAS客户端参数
        \phpCAS::client(CAS_VERSION_2_0,CAS_SERVER_HOSTNAME,CAS_SERVER_PORT,CAS_SERVER_APP_NAME,true);
        // 不使用SSL服务校验
        \phpCAS::setNoCasServerValidation();
        // 这里会检测服务器端的退出的通知，就能实现php和其他语言平台间同步登出了
        \phpCAS::handleLogoutRequests();
        // 判断是否已经访问CAS验证，true-获取用户信息，false-访问CAS验证
        if(\phpCAS::checkAuthentication()){
            /**
             *获取用户的唯一标识信息
             *由UIA的配置不同可分为两种：
             *(1)学生：学号；教工：身份证号
             *(2)学生：学号；教工：教工号
             **/
            $userid=\phpCAS::getUser();
            // 教工号
            $teaching_number = \phpCAS::getAttribute("comsys_teaching_number");
            // 学生号
            $studentNumber = \phpCAS::getAttribute("comsys_student_number");

            $user=new User();
            $result=0;
            if(isset($teaching_number))
                $result=$user->signInAsUser($teaching_number);
            else
                $result=$user->signInAsStudent($studentNumber);
            return $result;

        }else{
            // 访问CAS的验证
            \phpCAS::forceAuthentication();
        }
    }
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

        $result=$user->signInAsTeacher($userid);
        if($result==0)
            $result=$user->signInAsStudent($userid);
        return $result;
    }
}