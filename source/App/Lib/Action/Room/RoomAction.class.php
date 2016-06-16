<?php

/**
 * Class RoomAction 教室管理
 */
class RoomAction extends RightAction{
    private $sy;
    private $mdl;
    private $unit=array();
    private $base;
    /**
     * @var RoomModel
     */
    private $model = null;

    public function __construct(){
        parent::__construct();
        $this->sy=$_POST['arr'];
        $this->mdl=new SqlsrvModel();
        $this->base='Room/';
        $this->assign('quanxian',trim($_SESSION['S_USER_INFO']['ROLES']));
        $this->assign('roles',trim($_SESSION['S_USER_INFO']['ROLES']));
        $this->model = new RoomModel();
    }
    
    /************************ TODO:重写的方法名称，方便望文生意  ********************************************/
    /**
     * 页面：显示添加教室
     */
    public function pageCreateRoom(){
    	$this->xiala('areas','area');  //课时段信息
    	$this->xiala('schools','school');  ////学校数据
    	$this->xiala('roomoptions','roomoption');  //教室类型选择
    	$this->display('pageCreateRoom');
    }
    /**
     * 页面：浏览教室列表
     */
    public function pageSelectRoom(){
    	$this->xiala('areas','area');  //课时段信息
    	$this->xiala('schools','school');  ////学校数据
    	$this->xiala('roomoptions','roomoption');  //教室类型选择
    	$this->xiala('timesections','timesectors');
    	$this->assign('yearTerm',$this->model->getYearTerm('O'));
    	$this->display('pageSelectRoom');
    }
    public function pageSelectRoomBorrow(){
        $this->xiala('areas','area');  //课时段信息
        $this->xiala('schools','school');  ////学校数据
        $this->xiala('roomptions','roomoption');  //教室类型选择
        $this->xiala('timesectors','timesectors');
        $this->assign('yearTerm',$this->model->getYearTerm('O'));
    	$this->display('pageSelectRoomBorrow');
    }
    public function pageConfirmationSheet(){
    	$data = $this->model->getConfirmationSheetDataByRecno($_GET['RECNO']);
    	$data['WEEKS'] = $this->week2String($data['WEEKS']);
    	$this->assign('value',$data);
//		echo '<pre>';
//		var_dump($data);
    	if($_GET['roomtype']  == 'p'){
    		$this->display('pageConfirmationSheet_common');
    	}else{
    		$this->display('pageConfirmationSheet_multi');
    	}
    }
    /**
     * 教室使用情况 查询页面
     */
    public function pageRoomUsageQuery(){
    	$this->xiala('schools','schools');                    //:学校
    	$this->xiala('areas','areas');                     //:  校区
    	$this->xiala('timesectors','timesectors');        //:空闲时段
    	$this->xiala('roomoptions','roomoptions');        //:设施表
    	$this->assign('yearTerm',$this->model->getYearTerm('O'));
    	$this->display('pageRoomUsageQuery');
    }
    /**
     * 教室使用情况 显示页面
     */
    public function pageRoomUsageStatus(){
    	//ajax开启新的窗口
    	$this->assign('get',json_encode($_GET));
    	$this->display('pageRoomUsageStatus');
        exit;
    }

    /**
     * 教室周课表显示界面
     * @param $year
     * @param $term
     * @param $roomno
     */
    public function pageRoomWeekSchedule($year,$term,$roomno){
        $this->getRoomWeekData($year,$term,$roomno);
        $this->display('pageRoomWeekSchedule');
    }
    /**
     * 获取教室列表数据
     */
    public function listRoom(){
    	$rst = $this->model->getRoomsTableList($_POST,$this->_pageDataIndex,$this->_pageDataIndex+$this->_pageSize);
    	if(is_string($rst)){
    		$this->exitWithReport('获取列表失败！'.$rst);
    	}
    	$this->ajaxReturn($rst,'JSON');
    	exit;
    }
    public function listRoomBorrow(){
    	$bind = array(
    			':YEAR'=>$_POST['YEAR'],
    			':TERM'=>$_POST['TERM'],
    			':SCHOOL'=>$_POST['SCHOOL'],
    			':ROOMNO'=>$_POST['ROOMNO'],
    			//                ':APPROVED'=>$APP1,
    	);
    	$filter = '';
    	if($_POST['APPROVED'] !== '%'){
    		$filter = ' and APPROVED = :APPROVED ';
    		$bind[':APPROVED'] = $_POST['APPROVED'];
    	}
    	$rst = $this->model->getBorrowedTableList($bind,$filter,$this->_pageDataIndex,$this->_pageSize);
    	if(is_string($rst)){
    		$this->exitWithReport("获取表格数据失败！{$rst}");
    	}
    	foreach($rst['rows'] as &$val){
    		$val['WEEKS'] = $this->week2String($val['WEEKS']);
    	}
    	$this->ajaxReturn($rst,'JSON');
    	exit;
    }
    /**
     * excel导出教室列表
     */
    public function exportRoomList(){
    	$this->model->initPHPExcel();
    	$rst = $this->model->getRoomsTableList($_GET);
    	if(is_string($rst)){
    		$this->exitWithReport('获取列表失败！'.$rst);
    	}
    	$data['title'] = '教室列表';
    	//表头
    	$data['head'] = array(
    			//默认值如 align type 的设计实例
    			'ROOMNO' => array( '教室号', 'align' => CommonModel::ALI_LEFT,'width'=>10,'type'=>CommonModel::TYPE_STR ),
    			'NO' => array(0=> '房间号','align' => CommonModel::ALI_LEFT,'width'=>10),
    			'JSN' => array( '简称', 'align' => CommonModel::ALI_LEFT,'width'=>30),
    			'BUILDING' => array( '楼名', 'align' => CommonModel::ALI_CENTER,'width'=>20),
    			'AREAVALUE' => array( '校区', 'align' => CommonModel::ALI_LEFT,'width'=>20),
    			'SEATS' => array( '座位数', 'align' => CommonModel::ALI_LEFT,'width'=>10),
    			'TESTERS'   => array( '考位数', 'align' => CommonModel::ALI_LEFT,'width'=>10),
    			'EQUIPMENTVALUE'   => array( '设施', 'align' => CommonModel::ALI_CENTER,'width'=>20),
    			'SCHOOLNAME' => array( '优先学院', 'align' => CommonModel::ALI_LEFT,'width'=>20),
    			'USAGEVALUE' => array( '排课约束', 'align' => CommonModel::ALI_LEFT,'width'=>20),
    			'RESERVED'   => array( '是否保留', 'align' => CommonModel::ALI_LEFT,'width'=>10),
    	);
    	//表体
    	$data['body'] = $rst['rows'];
    	$this->model->fullyExportExcelFile($data, $data['title']);
    	exit;
    }
    /**
     * 教室号验证
     * 检查教室号是否可以使用
     * @param $roomno
     */
    public function checkRoomnoIsvalid($roomno){
        $rst = $this->model->get(array(
                'ROOMNO'    => $roomno,
        ),true);
        if(is_string($$rst)){
            $this->failedWithReport('获取教室信息失败！'.$rst);
        }elseif($rst){
            $this->failedWithReport("教室号为[$roomno]的教室已经存在！$rst");
        }else{
            $this->successWithReport("教室号为[$roomno]的教室不存在，可以使用！$rst",true);
        }
    }

    /**
     * 检测教室借用申请是否有效
     * @param array $fields 提交教室借用申请必须的字段
     * @return int
     * @throws Exception
     */
    public function checkTimeSegmentIsvalid($fields){
        //todo 取得对应上课节次
        $timer = $this->model->sqlFind("select * from TIMESECTORS WHERE [NAME]=:NAME",array(":NAME"=>$fields['TIME']));
        if($timer===false) return 0;

        //todo 统计供用表中的记录,这里需要额外处理"%"这种情况
        $count = $this->model->countUseRoomByBorrow($fields['YEAR'], $fields['TERM'], $fields['ROOMNO'],
            $fields['DAY'], $fields['WEEKS'], $timer['TIMEBITS']);
        if($count===false || $count>0) return 0;

        //todo 统计排课表中的记录
        $count = $this->model->countUseRoomBySchedule($fields['YEAR'], $fields['TERM'], $fields['ROOMNO'],
            $fields['DAY'], $fields['WEEKS'], $timer['TIMEBITS'], $fields['OEW']);
        if($count===false || $count>0) return 0;

        return 1;
        /**
        $lists = array();//保存这一天的时间安排
        $year = $fields['YEAR'];
        $term = $fields['TERM'];
        $roomno = $fields['ROOMNO'];
        $day = $fields['DAY'];
        $timebits = $fields['TIME'];
        $weeks = $fields['WEEKS'];
        //先获取教室借用的"周几+单双周、学院占用、一天的时间段、借用周次" 信息，   再格式化为指定的格式
        $roomBorrowRecords = $this->model->getRoomBorrowRecordList($year,$term,$roomno);
        // 查询出该教室该学年学期的课程安排占用的相关信息
        $roomScheduleRecords=$this->model->getRoomScheduleRecordList($year,$term,$roomno);
        $roomTimes = array_merge($roomBorrowRecords,$roomScheduleRecords);

        //节次数据
        $mappingModel = new MappingModel();
        $timeSectors = $mappingModel->getTimeSectorsMap();
        $timebits = $timeSectors["$timebits"]['TIMEBITS'];

        //按照周几分组
        foreach($roomTimes as $value){
            if(empty($value['DAY'])){
                throw new Exception('错误的数据'.serialize($value));
            }
            if($day === $value['DAY']){
                unset($value['DAY']);
                $lists[] = $value;
            }
        }

        $occupy = 0;//这一天的时间占用
        foreach($lists as &$element){
            if(trim($element['TIME']) === '%'){//全天(1-12节)
                $element['TIME'] = 4095;//1111 1111 1111
            }else{
                $element['TIME'] = $timeSectors[$element['TIME']]['TIMEBITS'];
            }
            $occupy |= $element['TIME'];
        }
//        mistey($fields,$occupy , $timebits,$occupy & $timebits);

        $timecoincide = $occupy & $timebits;
        if(!$timecoincide){
            return 1;//时间段无重合 一定无冲突
        }else{
            foreach($lists as &$element){
                $subtimecoincide = $element['TIME'] & $timebits;
                if($subtimecoincide){//时间有重合
                    if($weeks & $element['WEEKS']){//时间重合的情况下周次重合
                        return 0;
                    }
                }
            }
        }
        return 1;
         **/
    }

    /**
     * 获取教室信息 根据教室信息
     * 原名写作selectRoom，但是PHP方法名对大小写不敏感。。。。
     * @param string $roomno 教室号
     */
    public function selectRoomRecord($roomno){
    	$rst =  $this->model->get(array(
    			'ROOMNO' => $roomno
    	));
    	if(is_string($rst) or !$rst){
    		$this->exitWithReport("获取教室[{$roomno}]详细信息失败！{$rst}");
    	}
    	array_pop($rst[0]);
    	array_pop($rst[0]);
    	$this->ajaxReturn($rst[0],'JSON');
    }
    /**
     * 获取教室借用申请记录信息
     * @param string|array $recno
     */
    public function selectRoomBorrow($recno){
    	$rst = $this->model->getBorrowedRecordByRecno($recno);
    	if(is_string($rst) or !$rst){
    		$this->failedWithReport("获取借用信息失败！{$rst}");
    	}else{
    		$rst = $rst[0];
    		$rst['WEEKS'] = $this->week2String($rst['WEEKS']);
    		$two=array();
    		$length=strlen($rst['WEEKS']);
    		for($i=0;$i<$length;$i++){
    			if($rst['WEEKS'][$i]){
    				array_push($two,'C'.($i+1));
    			}
    		}
    		$rst['chek']=$two;
    		$this->ajaxReturn($rst,'JSON');
    		exit;
    	}
    }
    /**
     * 教室使用情况 显示页面 数据来源
     * pageRoomUsageStatus
     */
    public function selectRoomUsageStaus(){
    	$mapingModel = new MappingModel();
    	$timeSections = $mapingModel->getTimeSectionsByCode($_POST['TIME']);    //空闲时段 只取TIMEBITS2
    	$oewOptions   = $mapingModel->getOewOptionByCode($_POST['OEW']);        //只取TIMEBIT
    	$weekTimes = 0;
    	foreach($timeSections as $val){
    		/*
    		 示例：
    		 1010 1010 1010 1010 10 {双周}
    		 & 0000 0000 0000 0011 00 {ITMEBITS2 第二节课}
    		 0000 0000 0000 0010 00 {得到双周的第二节}
    		 */
    		$weekTimes = $weekTimes | ($val['TIMEBITS2'] & $oewOptions['TIMEBIT']);
    	}

    	$mo=array(
    			':ROOMNO'=>doWithBindStr($_POST['ROOMNO']),
    			':JSN'=>doWithBindStr($_POST['JSN']),
    			':SCHOOL'=>doWithBindStr($_POST['SCHOOL']),
    			':EQUIPMENT'=>doWithBindStr($_POST['EQUIPMENT']),//教室类型，多媒体教室等
    			':AREA'=>doWithBindStr($_POST['AREA']),//校区
    			':SEATSDOWN'=>$_POST['SEATSDOWN'],
    			':SEATSUP'=>$_POST['SEATSUP'],
    			':YEAR'=>$_POST['YEAR'],
    			':TERM'=>$_POST['TERM'],
    			':TYPE'=>'R'
    	);

    	$rst = $this->model->getRoomTimeTableList($mo,$this->_pageDataIndex,$this->_pageSize);
    	$str='';

    	foreach($rst['rows'] as $key=>$val){
    		$str.='<tr name="oname" roomno="'.$val['ROOMNO'].'" >';
    		$str.='<td><a href="/Room/Room/roomWeek/roomno/'.$val['ROOMNO'].'/year/'.$_POST['YEAR'].'/term/'.$_POST['TERM'].'" target="_blank" >'.$val['ROOMNO'].'</a></td>';        //教室号
    		$str.='<td>'.$val['JSN'].'</td>';           //简称
    		$str.=$this->zhou($val['MON']);        //星期1
    		$str.=$this->zhou($val['TUE']);        //星期2
    		$str.=$this->zhou($val['WES']);        //星期3
    		$str.=$this->zhou($val['THU']);        //星期4
    		$str.=$this->zhou($val['FRI']);        //星期5
    		$str.=$this->zhou($val['SAT']);        //星期6
    		$str.=$this->zhou($val['SUN']);        //星期7
    		$str.='</tr>';
    	}
    	$ar['str']=$str;
    	$ar['total']=$rst['total'];
    	$ar['page']=ceil($ar['total']/$this->_pageSize);
    	$ar['pagesize'] = count($rst['rows']);
    	if($_POST['page'] >= $ar['page']){
    		$ar['nowpage'] = $ar['page'];
    	}else if($_POST['page'] < 1){
    		$ar['nowpage'] = 1;
    	}else{
    		$ar['nowpage']=$_POST['page'];
    	}
    	$this->ajaxReturn($ar,'JSON');
    }
    /**
     * 创建教室
     * @param string $data
     */
    public function createRoom($data){
    	parse_str($data,$input);
    	$rst = $this->model->add(array(
    			'ROOMNO'    => array($input['ROOMNO'],true),
    			'AREA'    => array($input['AREA'],true),
    			'BUILDING'    => array($input['BUILDING'],true),
    			'NO'    => array($input['NO'],true),
    			'JSN'    => array($input['JSN'],true),
    			'SEATS'    => array($input['SEATS'],true),
    			'TESTERS'    => array($input['TESTERS'],true),
    			'EQUIPMENT'    => array($input['EQUIPMENT'],true),
    			'STATUS'    => array($input['STATUS'],true),
    			'PRIORITY'    => array($input['PRIORITY'],true),
    			'USAGE'    => array($input['USAGE'],true),
    			'REM'    => array($input['REM'],true),
    			'RESERVED'    => array($input['RESERVED'],true),
    	));
    	if(is_string($$rst) or !$rst){
    		$this->exitWithReport('添加教室失败！'.$rst);
    	}else{
    		$this->exitWithReport("教室号为[{$input['ROOMNO']}]的教室添加成功！$rst",true);
    	}
    }
    /**
     * 修改教室借用记录
     */
    public function createRoomBorrow(){
    	$fields = $this->_makeUAFields();
        $rst = $this->checkTimeSegmentIsvalid($fields);
        if($rst){
            $rst = $this->model->addBorrowRecord($fields);
            //return
            if(is_string($rst) or !$rst){
                $this->exitWithReport("添加借用申请失败！$rst");
            }else{
                $this->exitWithReport("成功修改借用申请！$rst",true);
            }
        }else{
            $this->exitWithReport("借用时间冲突，请检查教室！$rst");
        }
    }
    /**
     * 修改教室借用记录
     */
    public function updateRoomRecord(){
    	$rst = $this->model->update(array(
    			'NO'=>$_POST['NO'],
    			'BUILDING'=>$_POST['BUILDING'],
    			'SEATS'=>$_POST['SEATS'],
    			'JSN'=>$_POST['JSN'],
    			'TESTERS'=>$_POST['TESTERS'],
    			'REM'=>$_POST['REM'],
    			'AREA'=>$_POST['AREA'],
    			'EQUIPMENT'=>$_POST['EQUIPMENT'],
    			'STATUS'=>$_POST['STATUS'],
    			'PRIORITY'=>$_POST['PRIORITY'],
    			'USAGE'=>$_POST['USAGE'],
    			'RESERVED'=>$_POST['RESERVED'],
    	),array(
    			'ROOMNO' => $_POST['ROOMNO'],
    	));
    	if(is_string($rst) or !$rst){
    		$this->exitWithReport("修改教室[{$_POST['ROOMNO']}]失败!{$rst}");
    	}else{
    		$this->exitWithReport("修改教室[{$_POST['ROOMNO']}]成功!{$rst}",true);
    	}
    }
    /**
     * 修改教室借用
     */
    public function updateRoomBorrow(){
    	$fields = $this->_makeUAFields();
        $rst = $this->checkTimeSegmentIsvalid($fields);
        if($rst){
            $where = array(
                'RECNO' => $_POST['RECNO'],
            );
            $rst = $this->model->updateBorrowRecord($fields,$where);
            if(is_string($rst) or !$rst){
                $this->exitWithReport("修改借用申请失败！$rst");
            }else{
                $this->exitWithReport("成功修改借用申请！$rst",true);
            }
        }else{
            $this->exitWithReport("借用时间冲突，请检查教室！$rst");
        }
    }
    /**
     * 修改教室申请审核结果
     * @param array $list
     * @throws Exception
     */
    public function updateRoomBorrowAuditingStatus($list){
    	if(!$this->hasAuthority()){
    		$this->failedWithReport('只有教务处的人员才能改动');
    	}
    	$this->mdl->startTrans();
    	foreach($list as $val){
    		if(!isset($val['RECNO']) or !$val['RECNO']){
    			$this->mdl->rollback();
    			$this->failedWithReport('无法从网络中获取提交数据！');
    		}
    		//检查教室“撞车情况”
            $record = $this->model->getBorrowedRecordByRecno($val['RECNO']);
            if(!is_array($record) or 1 !== count($record)){
                throw new Exception();
            }else{
                $record = $record[0];
            }
            $oa = intval($record['APPROVED']);
            $na = intval($val['APPROVED']);
            if($oa === $na){//一改一 零改零 直接跳过
                continue;
            }elseif($na === 1){//0 改 1 才作检查   1改0不检查
                $rst = $this->checkTimeSegmentIsvalid($record);
                if(!$rst){
                    $this->exitWithReport("借用时间冲突，请检查教室！$rst");
                }
            }
//            var_dump($oa , $na);exit;
    		/*-- 修改为传递过来的值 --*/
    		$rst = $this->model->updateBorrowVerifiedStateByRecno($val['RECNO'],$val['APPROVED']);
    		if(is_string($rst) or !$rst){
    			$this->mdl->rollback();
    			$this->failedWithReport("修改教室[{$val['ROOMNO']}]的借用记录失败！{$rst}");
    		}
    	}
    	$this->mdl->commit();
    	$this->successWithReport('审核完成！');
    }
    /**
     * 刷新排课中教室使用情况
     * @param int $year
     * @param int $term
     */
    public function updateScheduleRoomUsageStatus($year,$term){
    	$timelistModel = new TimelistModel();
    	$timelistModel->startTrans();
    	$result = array();
    	for($i = 1; $i < 8;++$i ){
    		$rst = $timelistModel->updateRoomScheduleTimelist($year,$term,$i);
    		if(is_string($rst)){
    			$timelistModel->rollback();
    			$this->failedWithReport("刷新排课计划教室使用情况失败!{$rst}");
    		}
    		$result[$i] = $rst;
    	}
    	$timelistModel->commit();
    	$this->successWithReport("刷新排课计划教室使用情况成功!
    			周一{$result[1]} 周二{$result[2]} 周三{$result[3]} 周四{$result[4]} 周五{$result[5]} 周六{$result[6]} 周日{$result[7]}"  );
    }
    /**
     * 删除教室，根据教室号
     * @param string|array $rooms
     */
    public function deleteRoomsByNo($rooms){
    	if(is_string($rooms)){
    		$rooms = array($rooms);
    	}
    	foreach($rooms as $val){
    		$rst = $this->model->deleteByRoomno($val);
    		if(is_string($rst) or !$rst){
    			$this->exitWithReport("删除教室[$val]失败！");
    		}
    	}
    	$this->exitWithReport('删除成功！',true);
    }
    /**
     * 根据记录号删除教室申请记录
     * @param string|array $recos
     */
    public function deleteRoomBorrowByNo($recos){
    	if(is_string($recos)){
    		$recos = array($recos);
    	}
    	$this->model->startTrans();
    	$count = 0;
    	foreach($recos as $val){
    		$rst = $this->model->deleteBorrowRecordByRecno($val);
    		if(is_string($rst) or !$rst ){
    			$this->failedWithReport("删除教室申请记录失败！{$rst}");
    		}
    		$count ++;
    	}
    	$this->model->commit();
    	$this->successWithReport("成功删除教室申请记录，共[{$count}]条！");
    }
    /**
     * 获取添加和修改借用记录的字段
     * @return array
     */
    private function _makeUAFields(){
    	$zhouci = $this->weeks2Int($_POST['zhouci'],18);
    	if(!$zhouci){
    		$this->exitWithReport('请选择周次！');
    	}
    	//获取教师所在学院
    	$opeatorInfo = $this->model->getTeacherInfo();
    	if(is_string($opeatorInfo)){
    		$this->exitWithReport('查询教师信息失败！');
    	}
    	$fields = array(
			'SCHOOL'    => $opeatorInfo['SCHOOL'],     //学院ID
			'YEAR'      => $_POST['YEAR'],       //学年
			'TERM'      => $_POST['TERM'],       //学期
			'WEEKS'     => $zhouci,           //周次
			'PURPOSE'   => $_POST['PURPOSE'], //目的
			'ROOMNO'    => $_POST['ROOMNO'],   //教室号
			'DAY'       => $_POST['DAY'],         //星期几
			'TIME'      => $_POST['TIME'],         //第几节课
			'OEW'       => $_POST['OEW'],             //单双周
			'USERNAME'  => $_SESSION['S_USER_NAME'],     //申请用户名
			'APPROVED'  => 0,                                //批准状态
    	);
    	return $fields;
    }


    /**
     * 添加教师
     */
    public function addroom(){
    	$this->pageCreateRoom();
    }
    /**
     * 教室号验证
     * @param $roomno
     */
    public function roomyz($roomno){
    	$this->checkRoomnoIsvalid($roomno);
    }
    /**
     * 添加教室
     */
    public function insertroom(){
    	$this->createRoom($_POST['data']);
    }


    /**
     * 查询教室
     */
    public function selectroom(){
        if(REQTAG == 'exportexcel'){//导出列表
        	$this->exportRoomList();
        }
        if($this->_hasJson){//查询教室列表
        	$this->listRoom();
        }
        $this->pageSelectRoom();
    }
    /**
     * 添加教室借用
     */
    public function addjieyong(){
    	$this->createRoomBorrow();
    }


    /**
     * 获取教室信息
     * @param $roomno
     */
    public function editroom($roomno){
        if(IS_POST){
        	$this->selectRoomRecord($roomno);
        }
    }

    /**
     * 删除教室
     * @param $rooms
     */
    public function deleteroom($rooms){
    	$this->deleteRoomsByNo($rooms);
    }
    /**
     * 修改教室
     */
    public function updateroom(){
    	$this->updateRoomRecord();
    }
    /**
     * 查看教室使用情况
     */
    public function selectjieyong(){
        if($this->_hasJson){
        	$this->listRoomBorrow();
        }
        $this->pageSelectRoomBorrow();
    }

    /**
     * 删除借用
     * @param $recos
     */
    public function deletejieyong($recos){
    	$this->deleteRoomBorrowByNo($recos);
    }

    /**
     * 编辑借用记录时获取记录信息
     * @param $recno
     */
    public function editjieyong($recno){
    	$this->selectRoomBorrow($recno);
    }
    public function shenhejieyong($list){
    	$this->updateRoomBorrowAuditingStatus($list);
    }
    /**
     * 弹出教室借用申请单界面
     */
    public function shenqingdan(){
    	$this->pageConfirmationSheet();
    }
    /**
     * 旧的修改教室借用的方法
     *  新的方法是updateRoomBorrow
     */
    public function updatejieyong(){
        $this->updateRoomBorrow();
    }

    /**
     * 查看教室使用情况 页面
     */
    public function shiyong(){
        if(REQTAG === 'showWeeksContent'){//避免新的方法的加入
        	$this->pageRoomUsageStatus();
        }
        $this->pageRoomUsageQuery();
    }

    /**
     * 查看教室使用情况查询
     */
    public function selectshiyong(){
    	$this->selectRoomUsageStaus();
    }
    /**
     * 刷新教室资源列表
     * @param int $year
     * @param int $term
     * @return void
     */
    public function source($year,$term){
    	$this->updateScheduleRoomUsageStatus($year, $term);
    }


    /**
     * 查看教室借用周课表
     */
    public function roomWeek(){
        $this->pageRoomWeekSchedule($_GET['year'],$_GET['term'],$_GET['roomno']);
    }


    /**
     * @param $year
     * @param $term
     * @param $roomno
     * @return string
     * @throws Exception
     */
    private function getRoomWeekData($year,$term,$roomno){
        $lists = $temp = array(
            1   => array(),
            2   => array(),
            3   => array(),
            4   => array(),
            5   => array(),
            6   => array(),
            7   => array()
        );                 // 存放前台的list数组
        $msglist = array(
            1   => array(),
            2   => array(),
            3   => array(),
            4   => array(),
            5   => array(),
            6   => array(),
            7   => array(),
            8   => array(),
            9   => array(),
            0   => '',
        );
        $detail = array(
            1   => array(
                0   => '第一节',
                1   => '第二节',
            ),
            2   => array(
                0   => '第三节',
                1   => '第四节',
            ),
            3   => array(
                0   => '第五节',
                1   => '第六节',
            ),
            4   => array(
                0   => '第七节',
                1   => '第八节',
            ),
            5   => array(
                0   => '第九节',
                1   => '第十节',
            ),
            6   => array(
                0   => '第十一节',
                1   => '第十二节',
            ),
        );
        $daybits = array(
            1 => 3, //1 2
            2 => 12,//3 4
            3 => 48,// 56
            4 => 192,// 7 8
            5 => 768,//9 10
            6 => 3072,//11 12
            7 => 4096,//时间未定
            8 => 8192, //上午课
            9 => 16384 //下午课
        );
        //教室简称信息
        $classroom = $this->model->getByRoomno($roomno);
        if(is_string($classroom)) $this->failedWithReport($classroom);
        $roomname = $classroom['JSN'];

        //先获取教室借用的"周几+单双周、学院占用、一天的时间段、借用周次" 信息，   再格式化为指定的格式
        $roomBorrowRecords = $this->model->getRoomBorrowRecordList($year,$term,$roomno);
        // 查询出该教室该学年学期的课程安排占用的相关信息
        $roomScheduleRecords=$this->model->getRoomScheduleRecordList($year,$term,$roomno);
        $roomTimes = array_merge($roomBorrowRecords,$roomScheduleRecords);

        //节次数据
        $mappingModel = new MappingModel();
        $timeSectors = $mappingModel->getTimeSectorsMap();


        //按照周几分组
        foreach($roomTimes as $value){
            if(empty($value['DAY'])){
                throw new Exception('错误的数据'.serialize($value));
            }
            $lists[$value['DAY']][] = $value;
            unset($value['DAY']);
        }

        //遍历每天,将time
        foreach($lists as $daynum=>&$day){
            //遍历每个元素
            foreach($day as &$ele){
                //确定时间段 和 时间段的长度
                if(trim($ele['TIME']) === '%'){//全天(1-12节)
                    $ele['TIME'] = 4095;//1111 1111 1111
                }else{
                    $ele['TIME'] = $timeSectors[$ele['TIME']]['TIMEBITS'];
                }
                //确定时间所指定的位置
                foreach($daybits as $timenum=>$bit){
                    if($ele['TIME'] === 4096){//时间未定 1 0000 0000 0000
                        if(!is_array($temp[0])){
                            $temp[0] = array();
                        }
                        $temp[0][]    = $ele;
                        $msglist[0] .= $this->_schedule2String($ele);
                        break;
                    }elseif($tmp = $bit & $ele['TIME']){
                        if(!is_array($temp[$daynum][$timenum])){
                            $temp[$daynum][$timenum] = array();
                        }
                        if(!isset($msglist[$daynum][$timenum])){
                            $msglist[$daynum][$timenum] = '';
                        }
                        //判断是上半节还是下半节还是两节
                        // 1100 12     1000 8    0100 4 1111
                        $half = $bit/2;
                        if($bit === $tmp){
                            $ele['detail']  = '';//两节不提示
                        }elseif($tmp < $half){//上半节
                            $ele['detail'] = "({$detail[$timenum][0]})";
                        }elseif($tmp > $half){//下半节
                            $ele['detail'] = "({$detail[$timenum][1]})";
                        }
                        $ele['debug']   = "{$bit} $half {$ele['TIME']}";
                        $temp[$daynum][$timenum][] = $ele;
                        $msglist[$daynum][$timenum] .= $this->_schedule2String($ele);
                    }
                }
            }
        }

//        mistey($roomTimes,$lists);//==》正确
//        mistey($temp);
        $this->assign('msglist',$msglist);
        $this->assign('time',date('Y-m-d H:i:s'));
        $this->assign('year',$year);
        $this->assign('term',$term);
        $this->assign('roomname',$roomname);
    }

    /**
     * 时间安排数组转化为字符串
     * @param $element
     * @return string
     */
    private function _schedule2String($element){
        $OEW = array(
            'B'=>array(1048575,''),
            'E'=>array(699050,'(双周)'),
            'O'=>array(349525,'(单周)')
        );

        $_eleWeeks = $element['WEEKS'];
        if(strpos($element['Message']," 借用")===false) {
            if ($element['OEW'] == 'E') $_eleWeeks = $_eleWeeks & $OEW['E'][0];
            elseif ($element['OEW'] == 'O') $_eleWeeks = $_eleWeeks & $OEW['O'][0];
        }

        $weeks = $this->colorr($_eleWeeks);
        if(isset($element['classname'])){
            return "{$element['detail']}{$element['Message']} {$element['classname']}<br />{$weeks}{$OEW[$element['OEW']][1]} <br />";
        }else{
            return "{$element['detail']}{$element['Message']}<br />{$weeks}<br />";//借用不分单双周
        }
    }
//TODO:151008修订线 ***************************************************************************************************************/


























    //10进制转2进制的方法
    static public function jinzhi($arr){
        foreach($arr as $key=>$val){
            $arr[$key]['WEEKS']=str_pad(strrev(decbin($val['WEEKS'])),19,0);
        }
        return $arr;
    }

    /*
     * 判断教室周次是否有被占用的方法
     */
    public function roomchonghe($arr,$er){          //这边的都是反过来的（相对于数据库的数据是反过来的）数据库里的7（000111）在这是111000
        foreach($arr as $key=>$val){                 //                    （相对于页面选的是对的）
            $weeks=str_pad(strrev(decbin($val['WEEKS'])),18,0);
            for($i=0;$i<18;$i++){
                if($weeks[$i]==1&&$er[$i]==1){
                    return false;
                }
            }
        }
        return true;
    }

    /*
     * 在添加的时候对roomno进行验证的方法 来让用户合理的提交(判断借用的教室号存在不存在的说)
     */
    public function roomnoyz(){
        if($_POST['ROOMNO']==""){
            exit('sev');
        }
        $shuju=M('classrooms');
        $where['ROOMNO']=$_POST['ROOMNO'];
        $where['RESERVED']=0;                   //必须是不保留的
        $one=$shuju->where($where)->find();
        if($one){
            echo 'true';
        }else{
            echo 'false';
        }

    }
    private  function getSchoolnoFromUsername($username){
    	$username = trim($username,' +');
    	$sql = 'SELECT TEACHERS.SCHOOL FROM USERS INNER JOIN TEACHERS on USERS.TEACHERNO = TEACHERS.TEACHERNO where USERS.USERNAME like :username;';
    	$bind = array(':username'=>doWithBindStr($username));
    	$res = $this->mdl->sqlQuery($sql,$bind);
    	if($res === null && count($res) !== 1){
    		return $res[0]['SCHOOL'];
    	}else {
    		return '';
    	}
    }

    public function zhou($num){
        $str2='';
        //反转(原本小的节次在前面，现弄到后面)并填充到24位(一天12个节次,不到的用0填充)
        $str=str_pad(strrev(decbin($num)),24,0);
        //按照两位分割成数组
        $arr=explode('.',trim(chunk_split($str,2,'.'),'.'));
        if( !is_array($arr) || count($arr)>12){
            $arr=array('11','11','11','11','11','11','11','11','11','11','11','11');
        }
        foreach($arr as $val){
            switch($val){
                case '00':
                    $str2.='<td>&nbsp</td>';
                    break;
                case '01':
                    //10 双周
                    $str2.='<td>E</td>';
                    break;
                case '10':
                    //01 单周
                    $str2.='<td>D</td>';
                    break;
                case '11':
                    $str2.='<td>B</td>';
                    break;
            }
        }
        return $str2;
    }

    //todo:弹出教室学年课程的方法
    public function kecheng(){

        //todo:学年和学期
        $data= $this->mdl->sqlFind($this->mdl->getSqlMap("course/getCourseYearTerm.sql"),array(":TYPE"=>"C"));
        // todo:查询出某些学院借用周次
        $as=$this->arrSchool($arrSchool=$this->mdl->sqlQuery($this->mdl->getSqlMap('Room/getRoomCourseSchool.SQL'),array(':year'=>$_POST['year'],':term'=>$_POST['term'],':roomno'=>$_POST['roomno'])));
        //todo:查询出该教室该学年的课程安排
        $arr=$this->mdl->sqlQuery($this->mdl->getSqlMap('Room/getRoomCourse.SQL'),array(':year'=>$_POST['year'],':term'=>$_POST['term'],':roomno'=>$_POST['roomno']));

        $arr=array_merge($arr,$as);

        $arr2=$this->mdl->sqlQuery('select NAME,VALUE,UNIT,TIMEBITS from TIMESECTORS');

        $Tablearr=array();           //todo:前台页面用到的数组
        $ar2=$this->getTime($arr2);                                      //todo:节次数组        (以NAME为下标)

        $both = array("B"=>"","E"=>"（双周）","O"=>"（单周）");      //todo:单双周数组
        $countOneDay=array_reduce($arr2, "countOneDay2");                //todo:统计出一天12节课(单节次的自然下标)
        foreach($arr as $key=>$val){                              //todo:遍历查询出来的该学期的课程
            $str='';
            if($val['WEEKS']!=262143){//不等于 11 11111111 11111111 这种情况下无需反转和补零
                $str='周次'.str_pad(strrev(decbin($val['WEEKS'])),18,0);
            }
            $valTIME=$val['TIME']; //TIME+DAY+OEW(F4E)
            for($i=1;$i<count($countOneDay);$i+=2){
                for($j=0;$j<2;$j++){
                    if(($ar2[$countOneDay[$i-1+$j]]['TIMEBITS'] & $ar2[$valTIME[0]]['TIMEBITS'])>0){
                        $Tablearr[($i-1)/2+1][$valTIME[1]] .= ($ar2[$valTIME[0]]['UNIT']=="1" ? '('.trim($ar2[$valTIME[0]]['VALUE']).')' : '').$both[$valTIME[2]].$val["COURSE"]."{$str}<br/>";
                        break;
                    }
                }
            }
        }

        $this->web($Tablearr,$arr[0]['JSN'],date('Y-m-d H:i:s'),array($_POST['year'],$_POST['term']));
    }


    public function getTime($arr){
        $ar2=array();
        foreach($arr as $val){
            $ar2[$val['NAME']]=$val;
        }
        return $ar2;
    }


    public function web($list,$title='',$time,$week=array('year'=>2000,'term'=>1),$roomname=''){
        empty($title) and $time = '';
        $str=<<<EOF
        <p align="center" style="font-size:22px">{$roomname}{$title}在{$week[year]}学年第{$week['term']}学期的周课表</p>
 <p align="center">打印时间：$time</p>
        <table id="WeekSchedule" name="WeekSchedule"  style="color: #000000; font-size: 10px; border-collapse: collapse" border="1" cellpadding="0" cellspacing="0" width="100%" height="196" bordercolorlight="#336699" bordercolordark="#003399">
        <tr>
            <td width="10%" align="center" bgcolor="#3399CC" bordercolorlight="#008080" bordercolordark="#00FFFF" bordercolor="#00FFFF" height="13"><font size="2">节次/星期</font></td>
            <td width="16%" align="center" bgcolor="#00FFFF" bordercolor="#3399CC" bordercolorlight="#008080" bordercolordark="#FFFFFF" height="13"><font size="2">星期一</font></td>
            <td width="16%" align="center" bgcolor="#00FFFF" bordercolor="#3399CC" bordercolorlight="#008080" bordercolordark="#FFFFFF" height="13"><font size="2">星期二</font></td>
            <td width="16%" align="center" bgcolor="#00FFFF" bordercolor="#3399CC" bordercolorlight="#008080" bordercolordark="#FFFFFF" height="13"><font size="2">星期三</font></td>
            <td width="16%" align="center" bgcolor="#00FFFF" bordercolor="#3399CC" bordercolorlight="#008080" bordercolordark="#FFFFFF" height="13"><font size="2">星期四</font></td>
            <td width="16%" align="center" bgcolor="#00FFFF" bordercolor="#3399CC" bordercolorlight="#008080" bordercolordark="#FFFFFF" height="13"><font size="2">星期五</font></td>
            <td width="5%" align="center" bgcolor="#00FFFF" bordercolor="#3399CC" bordercolorlight="#008080" bordercolordark="#FFFFFF" height="13"><font size="2">星期六</font></td>
            <td width="5%" align="center" bgcolor="#00FFFF" bordercolor="#3399CC" bordercolorlight="#008080" bordercolordark="#FFFFFF" height="13"><font size="2">星期天</font></td>
        </tr>
        <tr>
            <td width="10%" align="center" bgcolor="#3399CC" bordercolorlight="#008080" bordercolordark="#00FFFF" bordercolor="#00FFFF" height="16"><font size="2">第一节</font></td>
            <td width="16%" align="center" rowspan="2" height="34">{$list[1][1]}</td>
            <td width="16%" align="center" rowspan="2" height="34">{$list[1][2]}</td>
            <td width="16%" align="center" height="32" rowspan="2">{$list[1][3]}</td>
            <td width="16%" align="center" height="32" rowspan="2">{$list[1][4]}</td>
            <td width="16%" align="center" height="32" rowspan="2">{$list[1][5]}</td>
            <td width="5%" align="center" height="32" rowspan="2">{$list[1][6]}</td>
            <td width="5%" align="center" height="32" rowspan="2">{$list[1][7]}</td>
        </tr>
        <tr>
            <td width="10%" align="center" bgcolor="#3399CC" bordercolorlight="#008080" bordercolordark="#00FFFF" bordercolor="#00FFFF" height="16"><font size="2">第二节</font></td>
        </tr>
        <tr>
            <td width="10%" align="center" bgcolor="#3399CC" bordercolorlight="#008080" bordercolordark="#00FFFF" bordercolor="#00FFFF" height="16"><font size="2">第三节</font></td>
            <td width="16%" align="center" rowspan="2" height="34">{$list[2][1]}</td>
            <td width="16%" align="center" rowspan="2" height="34">{$list[2][2]}</td>
            <td width="16%" align="center" height="32" rowspan="2">{$list[2][3]}</td>
            <td width="16%" align="center" height="32" rowspan="2">{$list[2][4]}</td>
            <td width="16%" align="center" height="32" rowspan="2">{$list[2][5]}</td>
            <td width="5%" align="center" height="32" rowspan="2">{$list[2][6]}</td>
            <td width="5%" align="center" height="32" rowspan="2">{$list[2][7]}</td>
        </tr>
        <tr>
            <td width="10%" align="center" bgcolor="#3399CC" bordercolorlight="#008080" bordercolordark="#00FFFF" bordercolor="#00FFFF" height="16"><font size="2">第四节</font></td>
        </tr>
        <tr>
            <td width="10%" align="center" bgcolor="#3399CC" bordercolorlight="#008080" bordercolordark="#00FFFF" bordercolor="#00FFFF" height="16"><font size="2">第五节</font></td>
            <td width="16%" align="center" rowspan="2" height="34">{$list[3][1]}</td>
            <td width="16%" align="center" rowspan="2" height="34">{$list[3][2]}</td>
            <td width="16%" align="center" height="32" rowspan="2">{$list[3][3]}</td>
            <td width="16%" align="center" height="32" rowspan="2">{$list[3][4]}</td>
            <td width="16%" align="center" height="32" rowspan="2">{$list[3][5]}</td>
            <td width="5%" align="center" height="32" rowspan="2">{$list[3][6]}</td>
            <td width="5%" align="center" height="32" rowspan="2">{$list[3][7]}</td>
        </tr>
        <tr>
            <td width="10%" align="center" bgcolor="#3399CC" bordercolorlight="#008080" bordercolordark="#00FFFF" bordercolor="#00FFFF" height="16"><font size="2">第六节</font></td>
        </tr>
        <tr>
            <td width="10%" align="center" bgcolor="#3399CC" bordercolorlight="#008080" bordercolordark="#00FFFF" bordercolor="#00FFFF" height="16"><font size="2">第七节</font></td>
            <td width="16%" align="center" rowspan="2" height="34">{$list[4][1]}</td>
            <td width="16%" align="center" rowspan="2" height="34">{$list[4][2]}</td>
            <td width="16%" align="center" height="32" rowspan="2">{$list[4][3]}</td>
            <td width="16%" align="center" height="32" rowspan="2">{$list[4][4]}</td>
            <td width="16%" align="center" height="32" rowspan="2">{$list[4][5]}</td>
            <td width="5%" align="center" height="32" rowspan="2">{$list[4][6]}</td>
            <td width="5%" align="center" height="32" rowspan="2">{$list[4][7]}</td>
        </tr>
        <tr>
            <td width="10%" align="center" bgcolor="#3399CC" bordercolorlight="#008080" bordercolordark="#00FFFF" bordercolor="#00FFFF" height="17"><font size="2">第八节</font></td>
        </tr>
        <tr>
            <td width="10%" align="center" bgcolor="#3399CC" bordercolorlight="#008080" bordercolordark="#00FFFF" bordercolor="#00FFFF" height="16"><font size="2">第九节</font></td>
            <td width="16%" align="center" rowspan="2" height="34">{$list[5][1]}</td>
            <td width="16%" align="center" rowspan="2" height="34">{$list[5][2]}</td>
            <td width="16%" align="center" height="32" rowspan="2">{$list[5][3]}</td>
            <td width="16%" align="center" height="32" rowspan="2">{$list[5][4]}</td>
            <td width="16%" align="center" height="32" rowspan="2">{$list[5][5]}</td>
            <td width="5%" align="center" height="32" rowspan="2">{$list[5][6]}</td>
            <td width="5%" align="center" height="32" rowspan="2">{$list[5][7]}</td>
        </tr>
        <tr>
            <td width="10%" align="center" bgcolor="#3399CC" bordercolorlight="#008080" bordercolordark="#00FFFF" bordercolor="#00FFFF" height="16"><font size="2">第十节</font></td>
        </tr>
        <tr>
            <td width="10%" align="center" bgcolor="#3399CC" bordercolorlight="#008080" bordercolordark="#00FFFF" bordercolor="#00FFFF" height="16"><font size="2">第11节</font></td>
            <td width="16%" align="center" rowspan="2" height="34">{$list[6][1]}</td>
            <td width="16%" align="center" rowspan="2" height="34">{$list[6][2]}</td>
            <td width="16%" align="center" height="32" rowspan="2">{$list[6][3]}</td>
            <td width="16%" align="center" height="32" rowspan="2">{$list[6][4]}</td>
            <td width="16%" align="center" height="32" rowspan="2">{$list[6][5]}</td>
            <td width="5%" align="center" height="32" rowspan="2">{$list[6][6]}</td>
            <td width="5%" align="center" height="32" rowspan="2">{$list[6][7]}</td>
        </tr>
        <tr>
            <td width="10%" align="center" bgcolor="#3399CC" bordercolorlight="#008080" bordercolordark="#00FFFF" bordercolor="#00FFFF" height="16"><font size="2">第12节</font></td>
        </tr>
        <tr>
            <td width="10%" align="center" bgcolor="#3399CC" bordercolorlight="#008080" bordercolordark="#00FFFF" bordercolor="#00FFFF" height="16"><font size="2">时间未定</font></td>
            <td colspan="7">{$list[0]}</td></tr>
    </table>
    <br>
    <br>
EOF;
        return $str;
    }

    /**
     * 把学院的格式和正常的一样
     *      将timeselector与time合并到time字段中
     *  例如：
     *      array(1) { [0]=> array(4) { ["TIME"]=> string(2) "4B" ["LEASE"]=> string(18) "A学院占用" ["TIMESECTOR"]=> string(1) "I" ["WEEKS"]=> int(512) } }
     *      array(1) { [0]=> array(3) { ["TIME"]=> string(3) "I4B" ["COURSE"]=> string(18) "A学院占用" ["WEEKS"]=> int(512) } }
     * @param $arr
     * @return array
     */
    public function arrSchool($arr){
        $arr2=array();
        foreach($arr as $key=>$val){
            if($val['TIMESECTOR']=='%'){
                array_push($arr2,$this->push('E',$val));//第1、2节
                array_push($arr2,$this->push('F',$val));//...
                array_push($arr2,$this->push('G',$val));
                array_push($arr2,$this->push('H',$val));
                array_push($arr2,$this->push('I',$val));
                array_push($arr2,$this->push('J',$val));//第十一、十二节
            }else{
                array_push($arr2,$this->push($val['TIMESECTOR'],$val));
            }
        }
        return $arr2;
    }

    //todo:把学院信息压入数组
    public function push($timesector,$val){
        $newarr['TIME']=$timesector.$val['TIME'];
        $newarr['COURSE']=$val['LEASE'];
        $newarr['WEEKS']=$val['WEEKS'];
        return $newarr;
    }

    /**
     * 给周次上色
     * @param $str2
     * @return string
     */
    public function colorr(&$str2){
        $aa=str_pad(strrev(decbin($str2)),18,0);
        $str2='';
        $str2.='<font color="blue">'.substr($aa,0,4).'</font>&nbsp';//1到4周
        $str2.='<font color="#222">'.substr($aa,4,4).'</font>&nbsp';
        $str2.='<font color="green">'.substr($aa,8,4).'</font>&nbsp';
        $str2.='<font color="red">'.substr($aa,12,4).'</font>&nbsp';
        $str2.='<font color="black">'.substr($aa,16).'</font>&nbsp';
        return $str2;
    }

    public function syqingkuang(){

        $shuju=new SqlsrvModel();

        $mo=array(':order'=>$this->sy['ORDER'],':MON'=>'0',':TUE'=>'0',':WES'=>'0',':THU'=>'0',':FRI'=>'0',':SAT'=>'0',':SUN'=>'0',
            ':ROOMNO'=>doWithBindStr(str_replace('_','',$_GET['ROOMNO'])),':JSN'=>doWithBindStr(str_replace('_','',$_GET['JSN'])),':SCHOOL'=>doWithBindStr(str_replace('_','',$_GET['SCHOOL'])),
            ':EQUIPMENT'=>doWithBindStr(str_replace('_','',$_GET['EQUIPMENT'])),':AREA'=>doWithBindStr(str_replace('_','',$_GET['AREA'])),':SEATSDOWN'=>str_replace('_','',$_GET['SEATSDOWN']),
            ':SEATSUP'=>str_replace('_','',$_GET['SEATSUP']),':YEAR'=>str_replace('_','',$_GET['YEAR']),':TERM'=>str_replace('_','',$_GET['TERM']),':TYPE'=>'R');
        if($_GET['DAY']!=-1){
            $mo[$_GET]=-1;

        }
        $arr2=$shuju->sqlFind($shuju->getSqlMap('Room/countList.SQL'),$mo);

        if($arr2['']==0){
            exit;
        }
        $ar['total']=$arr2[''];
        $ar['page']=ceil($arr2['']/$this->_pageSize);
        if($_POST['page']>=$ar['page']){
            $ar['nowpage']=$ar['page'];
        }else if($_POST['page']<1){
            $ar['nowpage']=1;
        }else{
            $ar['nowpage']=$_POST['page'];
        }

        $mo2=array(':order'=>$this->sy['ORDER'],':MON'=>'0',':TUE'=>'0',':WES'=>'0',':THU'=>'0',':FRI'=>'0',':SAT'=>'0',':SUN'=>'0',
            ':ROOMNO'=>doWithBindStr(str_replace('_','',$_GET['ROOMNO'])),':JSN'=>doWithBindStr(str_replace('_','',$_GET['JSN'])),':SCHOOL'=>doWithBindStr(str_replace('_','',$_GET['SCHOOL'])),
            ':EQUIPMENT'=>doWithBindStr(str_replace('_','',$_GET['EQUIPMENT'])),':AREA'=>doWithBindStr(str_replace('_','',$_GET['AREA'])),':SEATSDOWN'=>str_replace('_','',$_GET['SEATSDOWN']),
            ':SEATSUP'=>str_replace('_','',$_GET['SEATSUP']),':YEAR'=>str_replace('_','',$_GET['YEAR']),':TERM'=>str_replace('_','',$_GET['TERM']),':TYPE'=>'R',':start'=>($ar['nowpage']-1)*$this->_pageSize,':end'=>$ar['nowpage']*$this->_pageSize);
        if($_GET['DAY']!=-1){

            $mo2[$_GET['DAY']]=-1;
        }
        $arr=$shuju->sqlQuery($shuju->getSqlMap('Room/selectList.SQL'),$mo2);

        $str='';

        foreach($arr as $key=>$val){
            $str.='<tr name="oname" roomno="'.$val['ROOMNO'].'" >';
            $str.='<td><a href="javascript:void(0)" onclick="tanchu(this)">'.$val['ROOMNO'].'</a></td>';        //教室号
            $str.='<td>'.$val['JSN'].'</td>';           //简称
            $str.=$this->zhou($val['MON']);        //星期1
            $str.=$this->zhou($val['TUE']);        //星期2
            $str.=$this->zhou($val['WES']);        //星期3
            $str.=$this->zhou($val['THU']);        //星期4
            $str.=$this->zhou($val['FRI']);        //星期5
            $str.=$this->zhou($val['SAT']);        //星期6
            $str.=$this->zhou($val['SUN']);        //星期7
            $str.='</tr>';
        }
        $ar['str']=$str;


        $this->assign('ccc',json_encode($ar));

        $this->display();
    }

    public function returntime($arr){
        $time=array(1=>0,0,0,0,0,0,0);
        foreach($arr as $val){
            if($val['TIME']=='Q'||$val['TIME']=='R')continue;
            $vtime=$this->fz(decbin($this->unit[$val['TIME']]),cwebsSchedule::$oewVal[$val['OEW']]);
            $a= $time[$val['DAY']] | $vtime;
            $time[$val['DAY']]=$a;
        }
        return $time;
    }

    public function fz($bin,$oew){
        $str='';
        $bin=str_split($bin);
        foreach($bin as $v){
            $v.=$v;
            $vv=decbin($v)&$oew;
            if($vv=='1'||$vv=='0')
                $str.='0'.$vv;
            else
                $str.=decbin($vv);
        }
        return bindec($str);
    }

}

/**
 * 统计正常的一天有几节课
 *   处理的是直接从timeselector中取出的数据
 * @param $v1
 * @param $v2
 * @return array
 */
function countOneDay2($v1, $v2){
    //!v1 为真时表示第一次传入，确切地说是$v1 === null
    if(!$v1) $v1 = array();
    //单节次
    if($v2['UNIT']=="1") $v1[]=$v2["NAME"];
    return $v1;
}


