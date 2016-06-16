/**
 * 菜单及活页生成方法
 * @param souce
 * @param menus
 */
var cwebsFrame = function(bindObj, menus, defaultTabsTitle){
    var _obj = this;
    this.version = "1.0.0";
    this.onlyOpenTitle = defaultTabsTitle || "欢迎页";
    this._menus = menus; //菜单数据集合
    this.bindObj = bindObj; //事件绑定对象
    this.tabsObject = null; //活页对象
    this.souce = null; //左侧菜单对象
    this.contextMenu = null; //右键菜单对象

    this.init = function(){
        $(".easyui-tabs").each(function(i, n){
            if("__mainTabs__"==$(this).attr("name") || "__mainTabs__"==$(this).attr("id")){
                _obj.tabsObject = $(this);
                return;
            }
        });

        this.createHTML(); //创建HTML
        this.createLeftMenu(); //创建左侧HTML
        this.bindLeftMenuEven(); //绑定左侧菜单事件
        this.bindTabEven(); //绑定tabs事件
        this.bindContextMenuEven(); //绑定右键菜单事件
        this.update(0,this.onlyOpenTitle); //更新默认首页标题
    }

    /**
     * 创建必要HTML
     */
    this.createHTML = function(){
        //左边菜单HTML
        this.souce = $( '<div></div>').appendTo(this.bindObj);

        //左键菜单HTML
        this.contextMenu = $('<div class="easyui-menu" style="width:150px;"></div>').appendTo(this.bindObj);
        var contextMenuContent = '<div id="tabupdate">刷新</div><div class="menu-sep"></div>';
        contextMenuContent += '<div id="close">关闭</div><div id="closeall">全部关闭</div><div id="closeother">除此之外全部关闭</div><div class="menu-sep"></div>';
        contextMenuContent += '<div id="closeright">当前页右侧全部关闭</div><div id="closeleft">当前页左侧全部关闭</div><div class="menu-sep"></div>';
        contextMenuContent += '<div id="exit">退出</div>';
        this.contextMenu.html(contextMenuContent);
        this.contextMenu.menu();
    };

    /**
     * 生成左页菜单
     */
    this.createLeftMenu = function(){
        this.souce.accordion({animate:false,fit:true,border:false});
        $.each(this._menus.menus, function(i, n) {
            var menulist = '';
            menulist +='<ul class="navlist">';
            $.each(n.menus, function(j, o) {
                menulist += '<li><div ><a ref="'+o.menuid+'" href="#" rel="' + o.url + '" params="'+(o.params?o.params:"")+'" json="'+(o.json && o.json==true?1:0)+'" confirm="'+(o.confirm?o.confirm:"")+'"><span class="icon '+o.icon+'" >&nbsp;</span><span class="nav">' + o.menuname + '</span></a></div> ';

                if(o.child && o.child.length>0){
                    menulist += '<ul class="third_ul">';
                    $.each(o.child,function(k,p){
                        menulist += '<li><div><a ref="'+p.menuid+'" href="#" rel="' + p.url + '" params="'+(p.params?p.params:"")+'" json="'+(o.json && o.json==true?1:0)+'" confirm="'+(o.confirm?o.confirm:"")+'"><span class="icon '+p.icon+'" >&nbsp;</span><span class="nav">' + p.menuname + '</span></a></div> </li>'
                    });
                    menulist += '</ul>';
                }
                menulist+='</li>';
            })
            menulist += '</ul>';
            _obj.souce.accordion('add', {title: n.menuname,content: menulist,border:false,iconCls: 'icon ' + n.icon});
        });
        this.souce.accordion('select',this._menus.menus[0].menuname);
    };

    /**
     * 绑定左边菜单事件
     */
    this.bindLeftMenuEven = function(){
        this.souce.find(".navlist li a").click(function(){
            _obj.bindSingleLeftMenuEven(this);
        }).hover(function(){
                $(this).parent().addClass("hover");
            },function(){
                $(this).parent().removeClass("hover");
            });
    };

    this.bindSingleLeftMenuEven = function(singleObject){
        var tabTitle = $(singleObject).children('.nav').text();
        var url = $(singleObject).attr("rel");
        var params = $(singleObject).attr("params");
        var menuid = $(singleObject).attr("ref");
        var icon = $(singleObject).find('.icon').attr('class');
        var json = $(singleObject).attr("json");
        var confirm = $(singleObject).attr("confirm");

        if(params!=""){
            var _tmpSplit = params.split(",");
            for(i=0; i<_tmpSplit.length; i++){
                if($("#"+_tmpSplit[i]).val()){
                    url += "/" + _tmpSplit[i] + "/" +$("#"+_tmpSplit[i]).val();
                }
            }
        }

        var third = _obj.findMenu(menuid);
        if(third && third.child && third.child.length>0) {
            _obj.souce.find("li .third_ul").slideUp();
            var ul =$(singleObject).parent().next();
            if(ul.is(":hidden")) ul.slideDown();
            else ul.slideUp();
        }else{
            if(parseInt(json)==1){
                //以json方式打开
                if(confirm!=""){
                    $.messager.confirm('系统消息：',confirm,function(r){
                        if (r) _obj.openForJson(url);
                    });
                }else  _obj.openForJson(url);
            }else {
                //以tabs方式打开
                _obj.addTab(tabTitle,url,icon);
            }
            _obj.souce.find('.navlist li div').removeClass("selected");
            $(singleObject).parent().addClass("selected");
        }
    }

    /**
     * 新增一个活页
     * @param subtitle
     * @param url
     * @param icon
     */
    this.addTab = function(subtitle,url,icon){
        if(!this.tabsObject.tabs('exists',subtitle)){
            this.tabsObject.tabs('add',{
                title : subtitle,
                content : this.createFrame(url),
                closable : true,
                icon : icon
            });
            /*绑定双击关闭TAB选项卡*/
            $(".tabs-inner").die().live("dblclick", function(){
                _obj.tabsObject.tabs('close', $(this).children(".tabs-closable").text());
            })
        }else{
            this.tabsObject.tabs('select',subtitle);
            this.contextMenu.click();
        }
    };

    /**
     * 更新一个活页夹
     *
     * @param which 可以指向选项卡面板标题或索引的
     * @param subtitle 标题
     * @param url 指定的URL
     * @param icon 图标样式
     */
    this.update = function(which, subtitle, url, icon){
        var tab = this.tabsObject.tabs('getTab', which);
        if(tab){
            var options = {title : subtitle};
            if(url) options.content = this.createFrame(url);
            if(icon) options.icon = "icon " + icon;

            this.tabsObject.tabs('update', {tab: tab, options: options});
        }
    }

    /**
     * 绑定tab的双击和右键事件
     */
    this.bindTabEven = function(){
         /*为选项卡绑定右键*/
        this.tabsObject.tabs({
            onContextMenu : function (e, title) {
                e.preventDefault();
                _obj.contextMenu.menu('show', {
                    left : e.pageX,
                    top : e.pageY
                }).data("tabTitle", title);
            }
        });
    };

    /**
     * 绑定右键菜单事件
     */
    this.bindContextMenuEven = function() {
        this.contextMenu.menu({
            onClick: function (item) {
                _obj.doContextMenuEven(item.id);
            }
        });
        return false;
    }

    /**
     * 绑定全局右键事件
     */
    this.bindDocumentContextMenuEven = function(){
        $(document).die().bind("contextmenu",function(e){
            return false;
        });
    }

    /**
     * 从this._menus中查找指定menuid的对象
     * @param menuid
     * @returns {*}
     */
    this.findMenu = function(menuid){
        var obj = null;
        $.each(this._menus.menus, function(i, n) {
            $.each(n.menus, function(j, o) {
                if(o.menuid==menuid){
                    obj = o;
                    return;
                }
            });
        });
        return obj;
    };

    /**
     * 从this._menus中查找指定menuid的对象
     * @param menuid
     * @returns {*}
     */
    this.getIcon = function(menuid){
        var icon = 'icon ';
        $.each(this._menus.menus, function(i, n) {
            $.each(n.menus, function(j, o) {
                if(o.menuid==menuid){
                    icon += o.icon;
                    return;
                }
            })
        })
        return icon;
    };

    /**
     * 响应右键菜单事件
     * tabupdate(刷新) tabupdate(关闭) tabupdate(全部关闭) tabupdate(除此之外全部关闭)
     * tabupdate(当前页右侧全部关闭) tabupdate(当前页左侧全部关闭) tabupdate(退出)
     *
     * @param action
     * @returns {boolean}
     */
    this.doContextMenuEven = function(action){
        var alltabs = this.tabsObject.tabs('tabs');
        var currentTab = this.tabsObject.tabs('getSelected');
        var allTabtitle = [];
        $.each(alltabs,function(i,n){
            allTabtitle.push($(n).panel('options').title);
        })

        switch (action) {
            case "tabupdate":
                var iframe = $(currentTab.panel('options').content);
                var src = iframe.attr('src');

                //没有找到内容说明是第个欢迎页，不能刷新
                if(typeof(src)=="undefined") {
                    $.messager.confirm("系统消息","您要刷新是首页，这会导致首页面重新加载，您确认刷新吗？",function(data){
                        if(data) location.reload();
                    });
                    break;
                }
                this.tabsObject.tabs('update', {
                    tab: currentTab,
                    options: {
                        content : this.createFrame(src)
                    }
                })
                break;
            case "close":
                var currtab_title = currentTab.panel('options').title;
                if (currtab_title != this.onlyOpenTitle){
                    this.tabsObject.tabs('close', currtab_title);
                }
                break;
            case "closeall":
                $.each(allTabtitle, function (i, n) {
                    if (n != _obj.onlyOpenTitle){
                        _obj.tabsObject.tabs('close', n);
                    }
                });
                break;
            case "closeother":
                var currtab_title = currentTab.panel('options').title;
                $.each(allTabtitle, function (i, n) {
                    if (n != currtab_title && n != _obj.onlyOpenTitle)
                    {
                        _obj.tabsObject.tabs('close', n);
                    }
                });
                break;
            case "closeright":
                var tabIndex = this.tabsObject.tabs('getTabIndex', currentTab);
                if (tabIndex == alltabs.length - 1){
                    return false;
                }
                $.each(allTabtitle, function (i, n) {
                    if (i > tabIndex) {
                        if (n != _obj.onlyOpenTitle){
                            _obj.tabsObject.tabs('close', n);
                        }
                    }
                });

                break;
            case "closeleft":
                var tabIndex =this.tabsObject.tabs('getTabIndex', currentTab);
                if (tabIndex == 1) {
                    return false;
                }
                $.each(allTabtitle, function (i, n) {
                    if (i < tabIndex) {
                        if (n != _obj.onlyOpenTitle){
                            _obj.tabsObject.tabs('close', n);
                        }
                    }
                });

                break;
            case "exit":
                this.contextMenu.menu('hide');
                break;
        }
    }

    /**
     *创建一个iframe
     * @param url
     * @returns {string}
     */
    this.createFrame = function(url){
        return '<iframe scrolling="auto" frameborder="0"  src="'+url+'" style="width:100%;height:100%;"></iframe>';
    }

    this.openTab = function(title){
        this.souce.find(".navlist li a").each(function(){
            var tabTitle = $(this).children('.nav').text();
            var menuid = $(this).attr("ref");
            if(title && (title==menuid || tabTitle==title)){
                _obj.bindSingleLeftMenuEven(this);
            }
        })
    }

    this.openForJson = function(url){
        $.post( url, {hasJson:true}, function(rsp) {
            if(rsp && rsp.type && rsp.message){
                $.messager.alert("系统消息：", rsp.message, rsp.type);
            }
        },"JSON");
    }
};

/**
 * 弹出信息窗口
 * @param title 标题
 * @param msgString 提示信息
 * @param msgType 信息类型[error,info,question,warning]
 */
function msgShow(title, msgString, msgType) {
	$.messager.alert(title, msgString, msgType);
}

/**
 * 关闭选中活页夹
 */
function closeTopTabs(){
    if(parent.menuTabs && parent.menuTabs.openTab){
        var currentTab = parent.menuTabs.tabsObject.tabs('getSelected');
        parent.menuTabs.tabsObject.tabs('close', currentTab.panel('options').title);
    }
    return;

    var objTab = null;
    $(".easyui-tabs").each(function(i, n){
        if("__mainTabs__"==$(this).attr("name") || "__mainTabs__"==$(this).attr("id")){
            objTab = $(this);
            return;
        }
    });
    if(objTab != null) {
        var tab = objTab.tabs("getSelected");
        objTab.tabs('close', objTab.tabs('getTabIndex',tab));
    }
}

function openTopTabs(title){
    if(parent.menuTabs && parent.menuTabs.openTab){
        parent.menuTabs.openTab(title);
    }
}

/**
 * 在活页中打开窗口
 * @param subtitle
 * @param url
 * @param icon
 * @param reload
 */
function openTabsWindows(subtitle,url,icon,reload){
    var m_menuTabs = null;
    if(typeof(menuTabs)!="undefined" && menuTabs.addTab){
        m_menuTabs = menuTabs;
    }else if(parent && typeof(parent.menuTabs)!="undefined" && parent.menuTabs.addTab){
        m_menuTabs = parent.menuTabs;
    }
    if(m_menuTabs!=null){
        m_menuTabs.addTab(subtitle,url,icon);
        if(reload) m_menuTabs.update(subtitle,subtitle,url,icon);
    }
}

/**
 * 绑定错误提示事件
 * @param errorMsg 绑定错误消息对象的内容
 * @param callbacks 回调函数
 */
function bindMessageErrorEven(errorMsg, callbacks, messageType){
    if(typeof(callbacks)!=="function") return;
    var mType = messageType || "error";

    $(".messager-body").each(function(i, n){
        if($(this).children(".messager-"+mType).length==1 && typeof($(this).text())==="string" && $(this).text().indexOf(errorMsg)>=0){
            $(this).children(".messager-button").bind("click",function(){
                callbacks();
            })
        }
    })
}

/**
 * 把左边菜单扩展成jquery方法
 */
$.fn.extend({
    cwebsFrame : function(menus, defaultTabsTitle){
        var m =new cwebsFrame(this, menus, defaultTabsTitle);
        m.init();
        m.bindDocumentContextMenuEven();
        return m;
    }
});

/**
 * jquery的扩展
 * 找到　deferred.rejectWith
 * 添加　jQuery.cwebsStatus(status);
 */
jQuery.extend({
    cwebsStatus : function(status, inputMsg){
        var loginUrl = "/Login";
        if(707 == status) {
            var errorMsg = "您还没有登陆系统，请先登陆！";
            $.messager.alert('系统消息',errorMsg, "error");
            bindMessageErrorEven(errorMsg, function(){window.top.location=loginUrl});
            return false;
        }else if(708 == status) {
            var errorMsg = "您的登陆已过期或者被重置，请重新登陆！";
            $.messager.alert('系统消息',errorMsg, "error");
            bindMessageErrorEven(errorMsg, function(){window.top.location=loginUrl});
            return false;
        }
        else if(710 == status) {
            var errorMsg = "您没有权限执行此模块：<br /><br />"+inputMsg;
            $.messager.alert('系统消息',errorMsg, "error");
            //bindMessageErrorEven(errorMsg, function(){window.top.closeTopTabs();});
            return false;
        }
        return true;
    }
});

/**
 * 取得给定id的JSON
 * @param ids id集合以,隔开
 * @returns {Object}
 */
function getJsonByDocumentId(ids){
    var _ids = ids.split(",");
    var map = new Object()
    for(i=0; i<_ids.length; i++){
        eval('map.'+_ids[i]+'=$("#'+_ids[i]+'").val();');
    }
    return map;
}

/**
 * 检测所有给定id的值是否符合指定值
 * @param ids id集合以,隔开
 * @param value 符合的值
 * @returns {boolean}
 */
function checkAllItemByDocumentId(ids,value){
    var _ids = ids.split(",");
    var count = 0;
    for(i=0; i<_ids.length; i++){
        if($("#"+_ids[i]).val()==value) count++;
    }
    if(count==_ids.length) return false;
    return true;
}

/**
 * 对datagrid的扩展
 */
jQuery.extend($.fn.datagrid.methods, {
    isChecked: function (jq, rowIndex) {
        return jq.datagrid('getPanel').find('div.datagrid-cell-check input[type=checkbox]').get(rowIndex).checked
    },
    selectRecordByKey : function(jq,data){
        //是否需要对select加锁处理，只确保运行一次
        var selectLock = jq.datagrid("options").selectLock || false;
        if(selectLock==true) return;
        jq.datagrid("options").selectLock = true;

        var rows =  jq.datagrid('getRows');
        var _color = getRandomColor();
        jq.datagrid('setSelectColor', {rowIndex:data.currIndex,color:_color});

        for(var i=0; i<rows.length; i++){
            if(i!=data.currIndex && rows[data.currIndex][data.keyField]==rows[i][data.keyField]){
                if(jq.datagrid("isChecked",i)==false){
                    jq.datagrid("selectRow",i);
                    jq.datagrid('setSelectColor', {rowIndex:i,color:_color})
                }
            }
        }

        //完成之后解锁
        jq.datagrid("options").selectLock = false;
    },
    unSelectRecordByKey : function(jq,data){
        //是否需要对select加锁处理，只确保运行一次
        var selectLock = jq.datagrid("options").selectLock || false;
        if(selectLock==true) return;
        jq.datagrid("options").selectLock = true;

        var rows =  jq.datagrid('getRows');
        var _color = "#ffffff";
        jq.datagrid('setSelectColor', {rowIndex:data.currIndex,color:_color});

        for(var i=0; i<rows.length; i++){
            if(i!=data.currIndex && rows[data.currIndex][data.keyField]==rows[i][data.keyField]){
                if(jq.datagrid("isChecked",i)==true){
                    jq.datagrid("unselectRow",i);
                    jq.datagrid('setSelectColor', {rowIndex:i,color:_color})
                }
            }
        }

        //完成之后解锁
        jq.datagrid("options").selectLock = false;
    },
    setSelectColor : function(jq, data){
        jq.datagrid('getPanel').find('#datagrid-row-r1-2-'+data.rowIndex).css("background-color",data.color).css("color",lightColor(data.color));
    }
});

/**
 * 取得随机颜色
 * @returns {string}
 */
function getRandomColor(){
    return '#'+('00000'+(Math.random()*0x1000000<<0).toString(16)).slice(-6);
}

/**
 * 取得给定颜色的对比色
 * @param sColor
 * @returns {string}
 */
function lightColor(sColor){
    var index = sColor.length==7 ? 1 : 0;
    var sRslt="#";
    for(var i=index; i<sColor.length; i+=2){
        iTemp=parseInt(sColor.substr(i,2),16);
        if(iTemp>255) iTemp=255;
        if(iTemp<0) iTemp=0;
        sRslt += (255-iTemp).toString(16);
    }
    return sRslt;
}

var cwebsSchedule = function(){
    this.oewName = {"B":"单双","E":"双周","O":"单周"} //单双周名称
    this.oewVal = {"B":3,"E":2,"O":1} //单双周值
    this.totalLessonLen = 13; //总课程节数为13节
    this.totalLessonMap = 67108863; //总课程节数表，从右到左第2个位代表一节课,现在是13节课11111111111111111111111111
    this.unitMap = {"U1":"单节课","U2":"双节课","U3":"三节课","U4":"四节课"};
    this.weekMap = {"W1":"星期一","W2":"星期二","W3":"星期三","W4":"星期四","W5":"星期五","W6":"星期六","W7":"星期天","W0":"未按排"};
    this.timesMap = {"U1":8191,"U2":4095,"U3":[112,1792],"U4":4095};

    /**
     * 十进制转二进制
     * @param dec 十进制数
     * @param len  字符长度，不足前补0
     */
    this.decBin = function(dec, len){
        return this.zero(parseInt(dec).toString(2),len);
    }

    /**
     * 二进制转十进制
     * @param bin 二进制数
     */
    this.binDec = function(bin){
        return parseInt(bin, 2);
    }

    /**
     * 前补0操作
     * @param str 字符串
     * @param len 字符长度，不足前补0
     */
    this.zero = function(str, len){
        if(!len || len<1 || len<str.length) return str;
        return new Array(len - str.length + 1).join("0") + str;
    }

    /**
     * 反转字符串
     * @param str
     */
    this.reverse = function(str){
        return str.split("").reverse().join("");
    }

    /**
     * 把上课节次转成上课时间
     * @param lensson 上课节次值
     * @param oewType 单双周
     * @param returnBin 是否返回二进制数据，默认为false
     */                       //   4096     B        false
    this.lesson2Time = function(lensson, oewType, returnBin){
        var binLensson = this.decBin(lensson).split("");
        for(var i= 0; i<binLensson.length; i++){
            binLensson[i] = this.decBin(this.binDec(binLensson[i] +""+ binLensson[i]) & this.oewVal[oewType], 2);
        }
        if(returnBin==true) binLensson.join("");
        else return this.binDec(binLensson.join(""));
    }
    /**
     * 根据节次取得双节time
     * @param index
     * @returns {*}
     */
    this.getLesson2Times = function(index){
        return this.binDec('11'+this.zero("0",parseInt(index,16)*2-2));
    }

    /**
     * 获得指定上课节数的排课状态
     * @param index 上课节数，16进制数
     * @param oewTime 当天的上课时间表
     * @returns 0-3，0=空闲 1=单周 2=双周 3=单双周
     */
    this.getLessonStatus = function(index, oewTime){
        var bin = this.decBin(this.totalLessonMap & oewTime,this.totalLessonLen*2);
        var start = (this.totalLessonLen-parseInt(index,16))*2;

        return this.binDec(bin.substring(start, start+2));
    }

    /**
     * 根据节次索引取得上课节次值
     * 注：这里有问题应该是从TIMESECTORS表对应取出相应该的课程节数值
     * @param index 可以为数据，也可以为,字符拼接数,16进制数
     */
    this.getLesson = function(indexs){
        var oewTime = 0;
        if(typeof(indexs)=="string") indexs = indexs.split(",");
        for(var i=0; i<indexs.length; i++){

            oewTime = oewTime | this.binDec(this.reverse(this.zero("1",parseInt(indexs[i],16))));
        }
        return oewTime;
    };

    /**
     * 据节次索引取得上课节次值
     * @param index 索引值1-C的值,,16进制数
     * @param unit 几节课,取值1-4
     * c   2   this.timesMap = {"U1":8191,"U2":4095,"U3":[112,1792],"U4":4095};
     */
    this.getLessons = function(index,unit){
        var returnVal = new Array();
        var lesson = this.getLesson(index);
        var decVal = parseInt(index,16);//16->10

        if(typeof(this.timesMap["U"+unit])=="number") {
            if((this.timesMap["U"+unit] & lesson) == 0) return returnVal;
            var startIndex = (Math.ceil(decVal/unit)-1)*unit+1;
            for(var i=0; i<unit; i++) returnVal.push((startIndex+i).toString(16).toUpperCase());
            return returnVal;
        }else if(typeof(this.timesMap["U"+unit])=="object"){
            for(i=0; i<this.timesMap["U"+unit].length; i++){
                if((this.timesMap["U"+unit][i] & lesson) > 0) {
                    var bin = this.decBin(this.timesMap["U"+unit][i]);
                    var oneIndex = bin.length - bin.lastIndexOf("1");
                    for(var j=0; j<unit; j++) returnVal.push((oneIndex+j).toString(16).toUpperCase());
                    return returnVal;
                }
            }
        }
        return returnVal;
    };

    /**
     * 根据课时间得到节数中文
     * @param times
     * @returns {string}
     */
    this.getLessonName = function(times){
        var _time = parseInt(times);
        if(_time==0) return "时间未定";

        var bin = this.decBin(_time);
        var index = bin.lastIndexOf("1");
        var end = index;
        for(var i=0; i<4; i++){
            if("1"==bin.substring(index-1-i,index-i)){
                end--;
            }else break;
        }
        index = bin.length-index;
        end  = bin.length-end;
        if(end-index==0) return "第"+index+"节";
        else if(end-index==1) return "第"+index+"、"+end+"节";
        else return "第"+index+"-"+end+"节";
    }

    /**
     * 根据课时间得到节数
     * @param times
     * @returns {string}
     */
    this.getLessonIndex = function(times){
        var _time = parseInt(times);
        if(_time==0) return [0];

        var bin = this.decBin(_time);
        var index = bin.lastIndexOf("1");
        var end = index;
        for(var i=0; i<4; i++){
            if("1"==bin.substring(index-1-i,index-i)){
                end--;
            }else break;
        }
        index = bin.length-index;
        end  = bin.length-end;

        var returnVal = new Array();
        for(var i=index; i<=end; i++){
            returnVal.push(i.toString(16).toUpperCase());
        }
        return returnVal;
    }

    /**
     * 从当天的上课时间表中移除某些节数
     * @param removeLensson 移除的上课节数
     * @param oewType 单双周
     * @param oewTime 当天的上课时间表
     */
    this.xorLesson = function(removeLensson, oewType, oewTime){
        return this.xorLessonTimes(this.lesson2Time(removeLensson,oewType), oewTime);
    }

    /**
     * 计算退课
     * @param removeTimes 退课时间表
     * @param oewTime 现有时间表
     */
    this.xorLessonTimes = function(removeTimes, oewTime){
        return (removeTimes ^ this.totalLessonMap) & oewTime;
    }

    /**
     * 计算加课、退课差值
     * @param removeTimes 退课时间表
     * @param addTimes 现有时间表
     */
    this.xorLessonDiffTimes = function(removeTimes, addTimes){
        var strRemoveBin = this.decBin(removeTimes);
        var strAddBin = this.decBin(addTimes,strRemoveBin.length);
        strRemoveBin = this.zero(strRemoveBin,strAddBin.length);

        var rBin = strRemoveBin.split("");
        var aBin = strAddBin.split("");

        for(var i=0; i<rBin.length; i++){
            if(rBin[i]==aBin[i]) {
                rBin[i]="0"; aBin[i]="0";
            }
        }
        return {remove:this.binDec(rBin.join("")), add:this.binDec(aBin.join(""))};
    }

    this.getOewName = function(){
        var returnVal = new Array(this.totalLessonLen+1);
        for(var i=1; i<=this.totalLessonLen; i++){
            returnVal[i] = "";
            for(var j=1; j<=4; j++){
                if(typeof(this.timesMap["U"+j])=="number"){
                    if((this.getLesson(i.toString(16)) & this.timesMap["U"+j])>0){
                        returnVal[i] += this.unitMap["U"+j]+" ";
                    }
                }else{
                    $.each(this.timesMap["U"+j],function(index,data){
                        if(($.cwebsSchedule.getLesson(i.toString(16)) & data)>0){
                            returnVal[i] += $.cwebsSchedule.unitMap["U"+j]+" ";
                            return;
                        }
                    })
                }
            }
        }
        return returnVal;
    }
};
jQuery.extend({cwebsSchedule: new cwebsSchedule()});