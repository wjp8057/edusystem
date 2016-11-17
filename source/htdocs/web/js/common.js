
    //重写jquery的ajax方法,以便处理异常
    //错误代码！
    var errorCode={
        '200':'正常',
        '403':'禁止访问！',
        '404':'页面未找到!',
        '500':'内部服务器错误！',
        '700':'未定义错误！',
        '701':'您还没有登陆系统，请先登陆！',
        '702':'您的账户已在其他电脑登录！',
        '703':'无权访问！',
        '704':'用户不存在！',
        '705':'访问参数错误或不完整!',
        '706':'数据不存在!'
    };
    var _ajax=$.ajax;
    $.ajax=function(opt){
        //备份opt中error和success方法
        var fn = {
            error:function(XMLHttpRequest, textStatus, errorThrown){},
            success:function(data, textStatus){},
            complete:function(XMLHttpRequest, textStatus){},
            beforeSend:function(XMLHttpRequest){}
        };
        if(opt.error){
            fn.error=opt.error;
        }
        if(opt.success){
            fn.success=opt.success;
        }
        if(opt.complete) {
            fn.complete=opt.complete;
        }
        if(opt.beforeSend) {
            fn.beforeSend=opt.beforeSend;
        }
        //扩展增强处理

        var _opt = $.extend(opt,{
            error:function(XMLHttpRequest, textStatus, errorThrown){
                //错误增强处理
                var loginUrl = "/web/";
                $.messager.alert('错误',errorCode[XMLHttpRequest.status]+"</br>"+XMLHttpRequest.statusText,"error");
                //未登录或者超时情况
                if(XMLHttpRequest.status==701||XMLHttpRequest.status==702){
                    //绑定确定按钮事件
                    bindMessageErrorEven(XMLHttpRequest.statusText,function(){window.top.location=loginUrl;},"error");
                }
                $('#w').window('close');
                fn.error(XMLHttpRequest, textStatus, errorThrown);
            },
            success:function(data, textStatus){
                //成功回调方法增强处理
                $('#w').window('close');
                fn.success(data, textStatus);
            },
            complete:function(XMLHttpRequest, textStatus){
                $('#w').window('close');
                fn.complete(XMLHttpRequest, textStatus);
            },
            beforeSend:function(XMLHttpRequest){
                $('#w').window('open');
                fn.beforeSend(XMLHttpRequest)
            }
        });
        return  _ajax(_opt);
    };
/**
 * 绑定错误提示窗口点击确定事件
 * @param errorMsg 绑定错误消息对象的内容
 * @param callbacks 回调函数
 * @param messageType 消息的类型
 */
function bindMessageErrorEven(errorMsg, callbacks, messageType){
    if(typeof(callbacks)!=="function") return;
    var mType = messageType || "error";
    $(".messager-body").each(function(i, n){
        if($(this).children(".messager-"+mType).length==1 && typeof($(this).text())==="string" && $(this).text().indexOf(errorMsg)>=0){
            //1.3.4与1.4.4的对话框不同，1.3.4用$(this).parent().children(".messager-button")获取元素
            $(this).parent().children(".messager-button").bind("click",function(){
                callbacks();
            })
        }
    })
}
    /**
     * 菜单及活页生成方法
     * @param bindObj 事件绑定对象
     * @param menus 菜单
     * @param defaultTabsTitle 默认标签标题
     */
    var cwebsFrame = function(bindObj, menus, defaultTabsTitle){
        var _obj = this;
        this.version = "1.0.0";
        this.onlyOpenTitle = defaultTabsTitle || "欢迎页";//后一个为默认值
        this._menus = menus; //菜单数据集合
        this.bindObj = bindObj; //事件绑定对象
        this.tabsObj = null; //活页对象
        this.menuObj = null; //左侧菜单对象
        this.contextMenu = null; //右键菜单对象

        /**
         * 初始化
         */
        this.init = function(){
            //获取活页对象
            $(".easyui-tabs").each(function(i, n){
                if("__mainTabs__"==$(this).attr("name") || "__mainTabs__"==$(this).attr("id")){
                    _obj.tabsObj = $(this);
                    return;
                }
            });

            this.createHTML(); //创建HTML
            this.createLeftMenu(); //创建左侧HTML
            this.bindLeftMenuEven(); //绑定左侧菜单事件
            this.bindTabEven(); //绑定tabs事件
            this.bindContextMenuEven(); //绑定右键菜单事件
            this.update(0,this.onlyOpenTitle); //更新默认首页标题
        };

        /**
         * 创建必要HTML
         */
        this.createHTML = function(){
            //左边菜单HTML
            this.menuObj = $( '<div></div>').appendTo(this.bindObj);

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
            this.menuObj.accordion({animate:false,fit:true,border:false});
            $.each(this._menus.menus, function(i, n) {
                var menulist = '';
                menulist +='<ul class="navlist">';
                    $.each(n.menus, function(j, o) {
                        menulist += '<li><div><a '+'menuid="'+o.menuid+'"  href="#" rel="' + o.url + '" params="'+(o.params?o.params:"")+'" json="'+(o.json && o.json==true?1:0)+'" confirm="'+(o.confirm?o.confirm:"")+'"><span class="icon '+o.icon+'" >&nbsp;</span><span class="nav">' + o.menuname + '</span></a></div> ';
                    if(o.menus && o.menus.length>0){
                        menulist += '<ul class="third_ul">';
                        $.each(o.menus,function(k,p){
                            menulist += '<li><div><a '+'menuid="'+p.menuid+'" href="#" rel="' + p.url + '" params="'+(p.params?p.params:"")+'" json="'+(o.json && o.json==true?1:0)+'" confirm="'+(o.confirm?o.confirm:"")+'"><span class="icon '+p.icon+'" >&nbsp;</span><span class="nav">' + p.menuname + '</span></a></div> </li>'
                        });
                        menulist += '</ul>';
                    }
                    menulist+='</li>';
                });
                menulist += '</ul>';
                _obj.menuObj.accordion('add', {title: n.menuname,content: menulist,border:false,iconCls: 'icon ' + n.icon});
            });
            this.menuObj.accordion('select',this._menus.menus[0].menuname);
        };

        /**
         * 绑定左边菜单事件
         * 鼠标悬浮时增加hover样式，离开时去掉
         */
        this.bindLeftMenuEven = function(){
            this.menuObj.find(".navlist li a").click(function(){
                _obj.bindSingleLeftMenuEven(this);
            }).hover(function(){
                $(this).parent().addClass("hover");
            },function(){
                $(this).parent().removeClass("hover");
            });
        };

        this.bindSingleLeftMenuEven = function(singleObject){
           // console.log(singleObject);
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
                _obj.menuObj.find("li .third_ul").slideUp();
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
                _obj.menuObj.find('.navlist li div').removeClass("selected");
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
            if(!this.tabsObj.tabs('exists',subtitle)){
                this.tabsObj.tabs('add',{
                    title : subtitle,
                    content : this.createFrame(url),
                    closable : true,
                    icon : icon
                });
                /*绑定双击关闭TAB选项卡*/
                $(".tabs-inner").bind("dblclick", function(){
                    _obj.tabsObj.tabs('close', $(this).children(".tabs-closable").text());
                })
            }else{
                this.tabsObj.tabs('select',subtitle);
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
            var tab = this.tabsObj.tabs('getTab', which);
            if(tab){
                var options = {title : subtitle};
                if(url) options.content = this.createFrame(url);
                if(icon) options.icon = "icon " + icon;
                this.tabsObj.tabs('update', {tab: tab, options: options});
            }
        };

        /**
         * 绑定tab的双击和右键事件
         */
        this.bindTabEven = function(){
            /*为选项卡绑定右键*/
            this.tabsObj.tabs({
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
        };

        /**
         * 绑定全局右键事件
         */
        this.bindDocumentContextMenuEven = function(){
            $(document).bind("contextmenu",function(e){
              //  return false;
            });
        };

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
            });
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
            var alltabs = this.tabsObj.tabs('tabs');
            var currentTab = this.tabsObj.tabs('getSelected');
            var allTabtitle = [];
            $.each(alltabs,function(i,n){
                allTabtitle.push($(n).panel('options').title);
            });

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
                    this.tabsObj.tabs('update', {
                        tab: currentTab,
                        options: {
                            content : this.createFrame(src)
                        }
                    })
                    break;
                case "close":
                    var currtab_title = currentTab.panel('options').title;
                    if (currtab_title != this.onlyOpenTitle){
                        this.tabsObj.tabs('close', currtab_title);
                    }
                    break;
                case "closeall":
                    $.each(allTabtitle, function (i, n) {
                        if (n != _obj.onlyOpenTitle){
                            _obj.tabsObj.tabs('close', n);
                        }
                    });
                    break;
                case "closeother":
                    var currtab_title = currentTab.panel('options').title;
                    $.each(allTabtitle, function (i, n) {
                        if (n != currtab_title && n != _obj.onlyOpenTitle)
                        {
                            _obj.tabsObj.tabs('close', n);
                        }
                    });
                    break;
                case "closeright":
                    var tabIndex = this.tabsObj.tabs('getTabIndex', currentTab);
                    if (tabIndex == alltabs.length - 1){
                        return false;
                    }
                    $.each(allTabtitle, function (i, n) {
                        if (i > tabIndex) {
                            if (n != _obj.onlyOpenTitle){
                                _obj.tabsObj.tabs('close', n);
                            }
                        }
                    });

                    break;
                case "closeleft":
                    var tabIndex =this.tabsObj.tabs('getTabIndex', currentTab);
                    if (tabIndex == 1) {
                        return false;
                    }
                    $.each(allTabtitle, function (i, n) {
                        if (i < tabIndex) {
                            if (n != _obj.onlyOpenTitle){
                                _obj.tabsObj.tabs('close', n);
                            }
                        }
                    });

                    break;
                case "exit":
                    this.contextMenu.menu('hide');
                    break;
                default:break;
            }
        };

        /**
         *创建一个iframe
         * @param url
         * @returns {string}
         */
        this.createFrame = function(url){
            return '<iframe scrolling="auto" frameborder="0"  src="'+url+'" style="width:100%;height:98%;"></iframe>';
        };

        this.openTab = function(title){
            this.menuObj.find(".navlist li a").each(function(){
                var tabTitle = $(this).children('.nav').text();
                var menuid = $(this).attr("ref");
                if(title && (title==menuid || tabTitle==title)){
                    _obj.bindSingleLeftMenuEven(this);
                }
            })
        };

        this.openForJson = function(url){
            $.post( url, {hasJson:true}, function(rsp) {
                if(rsp && rsp.type && rsp.message){
                    $.messager.alert("系统消息：", rsp.message, rsp.type);
                }
            },"JSON");
        }
    };

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
    //获取URL中的参数
    function getQueryString(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)","i");
        var r = window.location.search.substr(1).match(reg);
        if (r!=null) return (r[2]); return null;
    }
    //获取url问号前面的地址 http://host.com/name?a=1返回http://host.com/name
    function getURLString(){
        var url=window.location.href;
        var index=url.indexOf('?');
        url=index==-1?url:url.substring(0,index);
        return url;
    }
    //检查是否为数字
    function IsNum(s)
    {
        if (s!=null && s!="")
        {
            return !isNaN(s);
        }
        else
            return false;
    }
    //获取url？符号后面部分
    function getRequest() {
        var url = window.location.href;//  location.search; //获取url中"?"符后的字串
        var paramIndex=url.indexOf("?");
        if (paramIndex!= -1) {
            return  url.substring(paramIndex+1,url.length);

        }
        return -1;
    }
    /**
     * 在字符串后面补填补支付到固定长度
     * @param string 输入字符串
     * @param lenth 最高长度
     * @param fix 填补字符
     * @param direct 方向 1从左边，0从右边
     * @returns {*}
     */
    function str_pad(string,lenth,fix,direct){
        var i = (string + "").length;
        if(direct==1)
            while(i++ < lenth) string = fix + string;
        else
            while(i++ < lenth) string = string +fix;
        return string;
    }
    /**
     * 字符反向
     * @param string
     * @returns {string}
     */
    function string_reserve(string) {
        return  string.split('').reverse().join('');
    }
    /**
     * 分组
     * @param string
     * @param separate
     * @param amount
     * @returns {string}
     */
    function str_split(string,separate,amount){
        var len=string.length;
        var perlen=Math.ceil(len/amount);
        var result='';
        for(var i=0;i<amount;i++){
            result=result+separate+string.substr(i*perlen,perlen);
        }
        return result;
    }

    function get_lesson(string,order){
        order=(order-1)*2;
        string=parseInt(string).toString(2);
        string=str_pad(string,24,'0',1);
        string=string_reserve(string);
        string=string.substring(order,order+2);
        switch(string){
            case '10':
                string='D';break;
            case '01':
                string='E';break;
            case '11':
                string='B';break;
            case '00':
                string=' ';break;
            default :
                string='';
        }
        return string;
    }

