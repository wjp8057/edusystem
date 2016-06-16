/**
 * 重新设置课表颜色
 * @param weekIndex
 */
function resetTimeColor(){
    if(arguments.length>0){
        $.each(arguments, function(index,data){
            $(".schedule [class*='cell-time-"+data+"-']").each(function(index,data){
                _setTimeColor($(this),$.cwebsSchedule.getLessonStatus($(this).html(),$(this).attr("times")));
            });
        });
        return;
    }
    $(".schedule .cell-time").each(function(index,data){
        _setTimeColor($(this),$.cwebsSchedule.getLessonStatus($(this).html(),$(this).attr("times")));
    });
}
function resetTimeColorByRowId(rowName,rowKey){
    $("."+rowName+"-r"+rowKey+" .cell-time").each(function(index,data){
        _setTimeColor($(this),$.cwebsSchedule.getLessonStatus($(this).html(),$(this).attr("times")));
    });
}
function _setTimeColor(obj, colorIndex){
    obj.removeClass("scheduleTime0");
    obj.removeClass("scheduleTime1");
    obj.removeClass("scheduleTime2");
    obj.removeClass("scheduleTime3");
    obj.removeClass("scheduleTime4");
    if(getForcible()==1){
        var orTimes = parseInt(obj.attr("orTimes"));
        if(($.cwebsSchedule.getLesson2Times(obj.html()) & orTimes) > 0 && (orTimes & parseInt(obj.attr("initTimes")))>0)
            obj.addClass("scheduleTime4");
        else  obj.addClass("scheduleTime"+colorIndex);
    }else{
        obj.addClass("scheduleTime"+colorIndex);
    }
}

/**
 * 设置当前工作条目颜色
 * @param color 颜色
 * @param weekIndex 星期
 * @param hexCell 16进制课节，[9,A]=9,A
 */
function setCurrentWorkColor(color, weekIndex, hexCell){
    clearCurrentWorkColor();
    if(typeof(hexCell)=="string") hexCell = hexCell.split(",");
    $.each(hexCell,function(index,data){
        $(".LISTCURRENT .cell-time-"+weekIndex+"-"+parseInt(data,16)).css("background",color);
    })
}
function clearCurrentWorkColor(){
    $(".LISTCURRENT .cell-time").css("background","#0000FF");
}


//设置排课列表
function setListSchedule(){
    $(".lstSchedule-r .lstSchedule-r-day").each(function(index,data){
        $(this).html($.cwebsSchedule.weekMap["W"+$(this).html()]);
    })
    $(".lstSchedule-r .lstSchedule-r-oew").each(function(index,data){
        $(this).html($.cwebsSchedule.oewName[$(this).html()]);
    })
    $(".lstSchedule-r .lstSchedule-r-unit").each(function(index,data){
        $(this).html($.cwebsSchedule.unitMap["U"+$(this).html()]);
    })
    $(".lstSchedule-r .lstSchedule-r-weeks").each(function(index,data){
        $(this).html($.cwebsSchedule.reverse($.cwebsSchedule.decBin($(this).html(),18)));
    })
}

function openSearch(){
    //$("#sSCHOOL").val(getSchool());
    //$("#sAREA").val(getArea());
    //$("#sROOMTYPE").val(getRoomr());
    $("#sESTIMATE").val(getEstimate())

    $("#searchForms").css("display","");
    $("#searchForms").window({top:$(document).scrollTop()+($(window).height()-$("#searchForms").height())/2})
    $("#searchForms").window("open")
}
function addRoom(rowData){
    //alert($(".schedule-room").size())
    var html = '<tr class="schedule schedule-room room-r'+rowData.WHO+'"><th title="'+rowData.WHOSNAME+'">'+rowData.WHO+'</th><th>可用</th>';
    for(var i=1; i<8; i++){
        for(var j=1; j<13; j++){
            html += '<td class="cell-time cell-time-'+i+'-'+j+'" weekIndex="'+i+'" times="'+rowData.TIMES[i]+'" initTimes="'+rowData.TIMES[i]+'">'+parseInt(j).toString(16).toUpperCase()+'</td>';
        }
    }
    $(".schedule-room").first().before(html);
}

/**
 * 加上选课
 * @param weekIndex 星期索引
 * @param hexIndexs 上课节次
 * @param oewType 单双周
 */
function orLesson(weekIndex, hexIndexs, oewType, xorType){
    //假设定义所有加课为 orTimes, 所有退课为 xorTimes
    //初始时间为：initTimes
    if(typeof(xorType)=="undefined") xorType="course,class,room,teacher";
    var _xorTypes = xorType.split(",");

    var orTimes = $.cwebsSchedule.lesson2Time($.cwebsSchedule.getLesson(hexIndexs),oewType);
    $.each(_xorTypes,function(i, name){
        $("[class*='"+name+"-r"+getCurrValByName(name)+"'] [class*='cell-time-"+weekIndex+"-']").each(function(index,data){
            setAddTimes($(this), orTimes, 0);
        });
    })

    /**
    var orTimes = $.cwebsSchedule.lesson2Time($.cwebsSchedule.getLesson(hexIndexs),oewType);
    //添加课程
    $("[class*='course-r"+getCurrCourseNo()+"'] [class*='cell-time-"+weekIndex+"-']").each(function(index,data){
        setAddTimes($(this), orTimes, 0);
    });
    //添加班级
    $("[class*='class-r"+getCurrClassNo()+"'] [class*='cell-time-"+weekIndex+"-']").each(function(index,data){
        setAddTimes($(this), orTimes, 0);
    });
    //添加教室
    $("[class*='room-r"+getCurrRoomNo()+"'] [class*='cell-time-"+weekIndex+"-']").each(function(index,data){
        setAddTimes($(this), orTimes, 0);
    });
    //添加教师
    $("[class*='teacher-r"+getCurrTeacherNo()+"'] [class*='cell-time-"+weekIndex+"-']").each(function(index,data){
        setAddTimes($(this), orTimes, 0);
    });
     **/
}

function setAddTimes(obj, orTimes, xorTimes){
    orTimes = orTimes | parseInt(obj.attr("orTimes"));
    xorTimes = xorTimes | parseInt(obj.attr("xorTimes"));

    var diffTimes = $.cwebsSchedule.xorLessonDiffTimes(xorTimes, orTimes);
    var oewTime = $.cwebsSchedule.xorLessonTimes(diffTimes.remove, parseInt(obj.attr("initTimes")) | diffTimes.add);
    obj.attr("times",oewTime);
    obj.attr("orTimes",diffTimes.add);
    obj.attr("xorTimes",diffTimes.remove);
}

/**
 * 检查选课冲突
 * @param weekIndex 星期索引
 * @param hexIndexs 上课节次
 * @param oewType 单双周
 */               //新点击的星期 4    B,C      B
function checkLesson(weekIndex, hexIndexs, oewType){
    var returnVal = {right:true,course:0,classes:0,room:0,teacher:0,msg:""};
    var oewTime = $.cwebsSchedule.lesson2Time($.cwebsSchedule.getLesson(hexIndexs),oewType);
    //添加课程
    $("[class*='course-r"+getCurrCourseNo()+"'] [class*='cell-time-"+weekIndex+"-']").each(function(index,data){
        returnVal.course = oewTime & parseInt($(this).attr("times"));
        if(returnVal.course>0) {
            returnVal.right = false;
            returnVal.msg += "课程有冲突；";
        }
        return false;
    });

    //添加班级
    $("[class*='class-r"+getCurrClassNo()+"'] [class*='cell-time-"+weekIndex+"-']").each(function(index,data){
        returnVal.classes = oewTime & parseInt($(this).attr("times"));
        if(returnVal.classes>0) {
            returnVal.right = false;
            returnVal.msg += "班级有冲突；";
        }
        return false;
    });

    //添加教室
    $("[class*='room-r"+getCurrRoomNo()+"'] [class*='cell-time-"+weekIndex+"-']").each(function(index,data){
        returnVal.room = oewTime & parseInt($(this).attr("times"));
        if(returnVal.room>0) {
            returnVal.right = false;
            returnVal.msg += "教室有冲突；";
        }
        return false;
    });

    //添加教师
    $("[class*='teacher-r"+getCurrTeacherNo()+"'] [class*='cell-time-"+weekIndex+"-']").each(function(index,data){
        returnVal.teacher = oewTime & parseInt($(this).attr("times"));
        if(returnVal.teacher>0) {
            returnVal.right = false;
            returnVal.msg += "教师有冲突；";
        }
        return false;
    });

    return returnVal;
}
function checkLessonByRoom(roomNo, weekIndex, hexIndexs, oewType){
    var returnVal = {right:true,course:0,classes:0,room:0,teacher:0,msg:""};
    var oewTime = $.cwebsSchedule.lesson2Time($.cwebsSchedule.getLesson(hexIndexs),oewType);
    $("[class*='room-r"+roomNo+"'] [class*='cell-time-"+weekIndex+"-']").each(function(index,data){
        returnVal.room = oewTime & parseInt($(this).attr("times"));
        if(returnVal.room>0) {
            returnVal.right = false;
            returnVal.msg += "教室有冲突；";
        }
        return false;
    });
    return returnVal;
}

/**
 * 减去选课
 * @param weekIndex 星期索引
 * @param hexIndexs 上课节次
 * @param oewType 单双周
 * @param xorType 退课的类型 course,class,room,teacher
 */
function xorLesson(weekIndex, hexIndexs, oewType, xorType){
    //假设定义所有加课为 orTimes, 所有退课为 xorTimes
    //初始时间为：initTimes
    if(typeof(xorType)=="undefined") xorType="course,class,room,teacher";
    var _xorTypes = xorType.split(",");

    var xorTimes = $.cwebsSchedule.lesson2Time($.cwebsSchedule.getLesson(hexIndexs),oewType)
    $.each(_xorTypes,function(i, name){
        $("[class*='"+name+"-r"+getCurrValByName(name)+"'] [class*='cell-time-"+weekIndex+"-']").each(function(index,data){
            setRemoveTimes($(this), 0, xorTimes);
        });
    })
    /**
    //移除课程
    $("[class*='course-r"+getCurrCourseNo()+"'] [class*='cell-time-"+weekIndex+"-']").each(function(index,data){
        setRemoveTimes($(this), 0, xorTimes);
    });
    //移除班级
    $("[class*='class-r"+getCurrClassNo()+"'] [class*='cell-time-"+weekIndex+"-']").each(function(index,data){
        setRemoveTimes($(this), 0, xorTimes);
    });
    //移除教室
    $("[class*='room-r"+getCurrRoomNo()+"'] [class*='cell-time-"+weekIndex+"-']").each(function(index,data){
        setRemoveTimes($(this), 0, xorTimes);
    });
    //移除教师
    $("[class*='teacher-r"+getCurrTeacherNo()+"'] [class*='cell-time-"+weekIndex+"-']").each(function(index,data){
        setRemoveTimes($(this), 0, xorTimes);
    });
     **/
}
function getCurrValByName(name){
    if(name=="course") return getCurrCourseNo();
    else if(name=="class") return getCurrClassNo();
    else if(name=="room") return getCurrRoomNo();
    else if(name=="teacher") return getCurrTeacherNo();
}

function setRemoveTimes(obj, orTimes, xorTimes){
    orTimes = orTimes | parseInt(obj.attr("orTimes"));
    xorTimes = xorTimes | parseInt(obj.attr("xorTimes"));
    //obj.attr("title","orTimes:"+orTimes+",xorTimes:"+xorTimes);


    var diffTimes = $.cwebsSchedule.xorLessonDiffTimes(xorTimes, orTimes);
    var oewTime = $.cwebsSchedule.xorLessonTimes(diffTimes.remove, parseInt(obj.attr("initTimes")) | diffTimes.add);
    obj.attr("times",oewTime);
    obj.attr("orTimes",diffTimes.add);
    obj.attr("xorTimes",diffTimes.remove);
}

function setCurrentWorkLesson(){
    var arr = $.cwebsSchedule.getOewName();
    $(".LISTCURRENT td.cell-time").each(function(index,data){
        $(this).attr("title",arr[parseInt($(this).html(),16)])
    })
}

// 获得当前索引号
function getCurrIndexNo(){
    return $.trim($("#wINDEXNO").val());
}
// 获得当前课程号
function getCurrCourseNo(){
    return $.trim($("#wCOURSE").val());
}
function setCourseNo(val, mod){
    mod = mod | 1
    if(mod==2 || mod==3) $("#wCOURSE").val(val);
    if(mod==1 || mod==3){
        $(".lstScheduleSelect").attr("courseNo",val);
        var tds = $(".lstScheduleSelect").find("td");
        $(tds[0]).html(val);
    }
}
// 获得当前教师号
function getCurrTeacherNo(){
    return $.trim($("#wTEACHERNO").val());
}
function setTeacherNo(val, mod){
    mod = mod | 1
    if(mod==2 || mod==3) $("#wTEACHERNO").val(val);
    if(mod==1 || mod==3) $(".lstScheduleSelect").attr("teacherNo",val);
}
// 获得当前班级号
function getCurrClassNo(){
    return $.trim($("#wCLASSNO").val());
}
function setClassNo(val, mod){
    mod = mod | 1
    if(mod==2 || mod==3) $("#wCLASSNO").val(val);
    if(mod==1 || mod==3) $(".lstScheduleSelect").attr("classNo",val);
}
// 获得当前教室号
function getCurrRoomNo(){
    return $.trim($("#wROOMNO").val());
}
function setRoomNo(val, mod){
    mod = mod | 1
    if(mod==2 || mod==3) $("#wROOMNO").val(val);
    if(mod==1 || mod==3){
        var tds = $(".lstScheduleSelect").find("td");
        $(tds[5]).html(val);
    }
}
// 获得当前上课时间表
function getCurrTimes(){
    return parseInt($("#wTIMES").val())
}
function setTimes(val, mod){
    mod = mod | 1
    if(mod==2 || mod==3) $("#wTIMES").val(val);
    if(mod==1 || mod==3){
        $(".lstScheduleSelect").attr("times",val);
        var tds = $(".lstScheduleSelect").find("td");
        $(tds[3]).attr("times",val);
        $(tds[3]).html($.cwebsSchedule.getLessonName(val));
    }
}
// 获得当前星期
function getCurrWeek(){
    return $.trim($("#wDAY").val());
}
function setWeek(val, mod){
    mod = mod | 1
    if(mod==2 || mod==3) $("#wDAY").val(val);
    if(mod==1 || mod==3){
        $(".lstScheduleSelect").attr("day",val);
        var tds = $(".lstScheduleSelect").find("td");
        $(tds[2]).attr("day",val);
        $(tds[2]).html($.cwebsSchedule.weekMap["W"+val]);
    }
}
// 获得当前单双周
function getCurrOEW(){
    return $.trim($("#wOEW").val());
}
function setOEW(val, mod){
    mod = mod | 1
    if(mod==2 || mod==3) $("#wOEW").val(val);
    if(mod==1 || mod==3){
        var tds = $(".lstScheduleSelect").find("td");
        $(tds[6]).attr("oew",val);
    }
}
// 获得当前单元
function getCurrUnit(){
    return $.trim($("#wUNIT").val());
}
function setUnit(val, mod){
    mod = mod | 1
    if(mod==2 || mod==3) $("#wUNIT").val(val);
    if(mod==1 || mod==3){
        var tds = $(".lstScheduleSelect").find("td");
        $(tds[10]).attr("unit",val);
        $(tds[10]).html($.cwebsSchedule.unitMap["U"+val]);
    }
}
//强制排课
function getForcible(){
    return parseInt($("input[name='forcible']:checked").val());
}
function setForcible(val){
    $("input[name='forcible'][value='"+val+"']").attr("checked",true);
}
function getSchool(){
    return $.trim($(".lstScheduleSelect .lstSchedule-r-room").attr("school"));
}
function getRoomr(){
    return $.trim($(".lstScheduleSelect .lstSchedule-r-room").attr("roomr"));
}
function getArea(){
    return parseInt($(".lstScheduleSelect .lstSchedule-r-room").attr("area"));
}
function getEstimate(){
    return parseInt($(".lstScheduleSelect").find("td")[12].innerHTML);
}
function getEquipment(){
    return $.trim($(".lstScheduleSelect .lstSchedule-r-room").attr("roomr"));
}
function getYear(){
    return $("#wYEAR").val();
}
function getTerm(){
    return $("#wTERM").val();
}
//把选中秆的值赋给当前form
function scheduleSelectToCurrForm(){
    var tds = $(".lstScheduleSelect").find("td");
    $("#wCOURSE").val($(tds[0]).html());
    $("#wROOMNO").val($(tds[5]).html());
    $("#wCLASSNO").val($(".lstScheduleSelect").attr("classNo"));
    $("#wTEACHERNO").val($(".lstScheduleSelect").attr("teacherNo"));
    $("#wINDEXNO").val($(".lstScheduleSelect").attr("indexNo"));

    $("#wDAY").val($(tds[2]).attr("day"));
    $("#wTIMES").val($(tds[3]).attr("times"));
    $("#wUNIT").val($(tds[10]).attr("unit"));
    $("#wOEW").val($(tds[6]).attr("oew"));
}

//获得需要提交的数据
function getPostData(){
    var postData = {YEAR:getYear(),TERM:getTerm(),lists:[],times:{},forcible:getForcible()};
    $(".lstSchedule-r").each(function(index,data){
        var _list = {};
        _list.RECNO = $(this).attr("recno");
        _list.COURSENO = $(this).attr("courseNo");
        _list.TIMES = $(this).attr("times");
        _list.DAY = $(this).attr("day");
        _list.TIME = $($(this).find(".lstSchedule-r-times")).attr("time");
        _list.ROOMNO = $($(this).find(".lstSchedule-r-room")).html();
        _list.CLASSNO = $(this).attr("classNo");
        _list.TEACHNO = $(this).attr("teacherNo");
        _list.UNIT = $($(this).find(".lstSchedule-r-unit")).attr("unit");
        _list.OEW = $($(this).find(".lstSchedule-r-oew")).attr("oew");
        postData["lists"].push(_list);
    });
    //教室
    postData["times"]["ROOMNO"] = {};
    $("#TimeLists .schedule-room").each(function(index,data){
        var _roomNo = $.trim($(this).find("th").first().html());
        postData["times"]["ROOMNO"][_roomNo] = [];
        for(var j=1; j<8; j++){
            $(this).find("[class*='cell-time-"+j+"-']").each(function(i,d){
                postData["times"]["ROOMNO"][_roomNo].push({WEEK:$(this).attr("weekIndex"), ORTIMES:$(this).attr("orTimes"), XORTIMES:$(this).attr("xorTimes"),
                    TIMES:$(this).attr("times"), INITTIMES:$(this).attr("initTimes")});
                return false;
            })
        }
    });
    postData["times"]["CLASSNO"] = {};
    $("#TimeLists .schedule-class").each(function(index,data){
        var _classNo = $(this).find("th").first().attr("title");
        postData["times"]["CLASSNO"][_classNo] = [];
        for(var j=1; j<8; j++){
            $(this).find("[class*='cell-time-"+j+"-']").each(function(i,d){
                postData["times"]["CLASSNO"][_classNo].push({WEEK:$(this).attr("weekIndex"), ORTIMES:$(this).attr("orTimes"), XORTIMES:$(this).attr("xorTimes"),
                    TIMES:$(this).attr("times"), INITTIMES:$(this).attr("initTimes")});
                return false;
            })
        }
    });
    postData["times"]["TEACHNO"] = {};
    $("#TimeLists .schedule-teacher").each(function(index,data){
        var _teacher = $(this).find("th").first().attr("title");
        postData["times"]["TEACHNO"][_teacher] = [];
        for(var j=1; j<8; j++){
            $(this).find("[class*='cell-time-"+j+"-']").each(function(i,d){
                postData["times"]["TEACHNO"][_teacher].push({WEEK:$(this).attr("weekIndex"), ORTIMES:$(this).attr("orTimes"), XORTIMES:$(this).attr("xorTimes"),
                    TIMES:$(this).attr("times"), INITTIMES:$(this).attr("initTimes")});
                return false;
            })
        }
    });
    postData["times"]["COURSENO"] = {};
    $("#TimeLists .schedule-course").each(function(index,data){
        var _course = $.trim($(this).find("th").first().html());
        postData["times"]["COURSENO"][_course] = [];
        for(var j=1; j<8; j++){
            $(this).find("[class*='cell-time-"+j+"-']").each(function(i,d){
                postData["times"]["COURSENO"][_course].push({WEEK:$(this).attr("weekIndex"), ORTIMES:$(this).attr("orTimes"), XORTIMES:$(this).attr("xorTimes"),
                    TIMES:$(this).attr("times"), INITTIMES:$(this).attr("initTimes")});
                return false;
            })
        }
    });
    return postData;
}