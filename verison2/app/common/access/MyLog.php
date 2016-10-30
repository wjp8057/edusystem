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

namespace app\common\access;


use think\Db;
use think\db\Query;
use think\Request;

class MyLog {
    /**
     * @var Query
     */
    protected  $query;
    public function __construct(){
        //配置日志服务器
        $config=config('logdb');
        $connection=Db::connect($config);
        $this->query=new Query($connection);
    }

    /**将日志写入数据库
     * @param string $operate
     */
    public function write($operate=''){
        $request = Request::instance();
        $data['host']=$request->domain();
        $data['username']=session("S_USER_NAME");
        $data['name']=session("S_TEACHER_NAME");
        $data['role']=session("S_ROLES");

        $data['url']=$request->url();
        $data['remoteip']=get_client_ip();
        $dataString='';
        //获得插入的内容
        try {
            if (isset($_POST["inserted"])) {
                $string = is_array($_POST["inserted"]) ? json_decode($_POST["inserted"]) : $_POST["inserted"];
                $dataString .= 'insert:' . $string;
                $operate .= 'A';
            }
            //获得更新的内容
            if (isset($_POST["updated"])) {
                $string = is_array($_POST["updated"]) ? json_decode($_POST["updated"]) : $_POST["updated"];
                $dataString .= 'update' . $string;
                $operate .= 'M';
            }
            //获得删除的内容
            if (isset($_POST["deleted"])) {
                $string = is_array($_POST["deleted"]) ? json_decode($_POST["deleted"]) : $_POST["deleted"];
                $dataString .= 'delete' . $string;
                $operate .= 'M';
            }
        }catch (\Exception $e) {
            $dataString='';
                //捕捉异常
        }
        //如果以上都没有，直接输出_POST内容
        $dataString=$dataString==''?json_encode($_POST):$dataString;
        $data['data']=mb_substr($dataString,0,1000,"utf-8");
        $data['operate']=$operate;
        $data['requesttime']=date("Y-m-d H:i:s");
        $this->query->table('log')->insert($data);
    }

    public function getList($page=1, $rows=20,$start='',$end='', $username='%', $url='%'){
        $condition=null;
        $result=['total'=>0,'rows'=>[]];
        if($start!='') $condition['requesttime']=array('egt',$start);
        if($end!='') $condition['requesttime']=array('elt',$end);
        if($username!='%') $condition['username']=array('like',$username);
        if($url!='%') $condition['url']=array('like',$url);
        $data=$this->query->table('log')->field("host,username,name,role,operate,url,remoteip,data,requesttime")
            ->page($page,$rows)->order('requesttime desc')->where($condition)->select();
        $count=$this->query->table('log')->count();
        if(is_array($data)&&count($data)>0){ //小于0的话就不返回内容，防止IE下无法解析rows为NULL时的错误。
            $result=array('total'=>$count,'rows'=>$data);
        }
        return $result;
    }
    public function clear(){

        $rows=$this->query->table('log')->where("1=1")->delete();
        $this->write('R');
        if($rows>0) {
            $status = 1;
            $info='清除完成！';
        }
        else {
            $status=0;
            $info='未清除任何记录！';
        }
        return array('info' => $info, 'status' => $status);

    }
} 