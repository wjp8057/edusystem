/**
 * Created by Lin on 2015/9/22.
 * Email:784855684@qq.com
 */

/**
 * console不支持的浏览器的兼容处理
 * @type {Console}
 */
window.console = window.console || (function(){
        var c = {}; c.log = c.warn = c.debug = c.info = c.error = c.time = c.dir = c.profile
            = c.clear = c.exception = c.trace = c.assert = function(){};
        return c;
    })();
/**
 * IE下兼容
 */
if (!Array.prototype.indexOf){
    Array.prototype.indexOf = function(elt /*, from*/){
        var len = this.length >>> 0;
        var from = Number(arguments[1]) || 0;
        from = (from < 0)
            ? Math.ceil(from)
            : Math.floor(from);
        if (from < 0)
            from += len;
        for (; from < len; from++)
        {
            if (from in this &&
                this[from] === elt)
                return from;
        }
        return -1;
    };
}


function string2Jquery(selector){
    if(!(selector instanceof jQuery)){
        if(selector.indexOf('#') !== 0){
            selector = "#"+selector;
        }
        selector = $(selector);
    }
    return selector;
}

function isArray(obj) {
    return Object.prototype.toString.call(obj) === '[object Array]';
}


function formatFloat(src, pos){
    return Math.round(src*Math.pow(10, pos))/Math.pow(10, pos);
}
/**
 * 判断对象是否是一个空对象 {}
 * @param obj
 * @returns {boolean}
 */
function isEmptyObject(obj){
    for(var n in obj){return false}
    return true;
}

// 对Date的扩展，将 Date 转化为指定格式的String
// 月(M)、日(d)、小时(h)、分(m)、秒(s)、季度(q) 可以用 1-2 个占位符，
// 年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字)
// 例子：
// (new Date()).Format("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423
// (new Date()).Format("yyyy-M-d h:m:s.S")      ==> 2006-7-2 8:9:4.18
Date.prototype.Format = function(fmt)
{ //author: meizz
    var o = {
        "M+" : this.getMonth()+1,                 //月份
        "d+" : this.getDate(),                    //日
        "h+" : this.getHours(),                   //小时
        "m+" : this.getMinutes(),                 //分
        "s+" : this.getSeconds(),                 //秒
        "q+" : Math.floor((this.getMonth()+3)/3), //季度
        "S"  : this.getMilliseconds()             //毫秒
    };
    if(/(y+)/.test(fmt))
        fmt=fmt.replace(RegExp.$1, (this.getFullYear()+"").substr(4 - RegExp.$1.length));
    for(var k in o)
        if(new RegExp("("+ k +")").test(fmt))
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));
    return fmt;
};

/**
 * 弹出窗口工具
 * @type {{showWarning: Function, showInfo: Function, showConfirm: Function}}
 */
var Messager = {
    'showWarning':function (message,callback){
        if(callback){
            return $.messager.alert('提示','<span style="color: red" >'+message+'</span>','info',callback);
        }else{
            return $.messager.alert('提示','<span style="color: red" >'+message+'</span>','info');
        }
    },
    //showMessage的简写
    'show': function () {
        if(undefined !== arguments[1] ){
            this.showMessage(arguments[0],arguments[1]);
        }else{
            this.showMessage(arguments[0]);
        }
    },
    'showMessage': function () {
        var message = arguments[0];
        var type = 'info';
        if(arguments[0] instanceof Object){
            message = arguments[0].message;
            type = arguments[0].type;
        }
        if (arguments.length == 1) {
            return $.messager.alert('提示',message,type);
        }else{
            return $.messager.alert('提示',message,type,arguments[1]);
        }
    },
    'showInfo' :function (message,type,callback){
        if(!type)type = 'error';
        if(callback){
            return $.messager.alert('提示',message,type,callback);
        }else{
            return $.messager.alert('提示',message,type);
        }
    },
    'showConfirm': function (message,callable) {
        return $.messager.confirm('提示',message,callable);
    },
    /**
     * 判断是否返回的是提示信息
     * @param backdata ajax返回数据
     * @returns {boolean}
     */
    'isMessage': function (backdata) {
        return backdata.hasOwnProperty('message') && backdata.hasOwnProperty('type');
    }

};

/**
 * 表格助手
 * @type {{selectSingle: Function}}
 */
var Datagrid = {
    /**
     * 判断是否选中了一条数据
     * @param datagrid
     * @returns {boolean}
     */
    'selectSingle' : function(datagrid){
        datagrid = string2Jquery(datagrid);
        var rows = datagrid.datagrid('getSelections');
        if(rows.length>1){
            $.messager.alert('提示','选择的条数过多！');
        }else if(rows.length<1){
            $.messager.alert('提示','请选择！')
        }else{
            return true
        }
        return false;
    },
    /**
     * 判断是否选中了至少一条记录
     * @param datagrid
     * @returns {boolean}
     */
    'hasSelected': function (datagrid) {
        datagrid = string2Jquery(datagrid);
        var rows = datagrid.datagrid('getSelections');
        if(!rows.length){
            $.messager.alert('提示','请至少选择一条记录！');
            return false;
        }
        return true;
    },
    'getAll': function (datagrid) {
        datagrid = string2Jquery(datagrid);
        return datagrid.datagrid('getRows');
    },
    /**
     * 获取选中的纪录
     * @param datagrid
     * @returns {*}
     */
    'getRows': function (datagrid) {
        datagrid = string2Jquery(datagrid);
        return datagrid.datagrid('getRows');
    },
    'refreshRow': function (datagrid,rowIndex) {
        datagrid = string2Jquery(datagrid);
        return datagrid.datagrid('refreshRow',rowIndex);
    },
    'getSelections': function (datagrid) {
        datagrid = string2Jquery(datagrid);
        return datagrid.datagrid('getSelections');
    },
    'getSelected': function (datagrid) {
        datagrid = string2Jquery(datagrid);
        return datagrid.datagrid('getSelected');
    },
    'loadData': function (datagrid,data) {
        datagrid = string2Jquery(datagrid);
        return datagrid.datagrid('loadData',data);
    },
    'getUpdated': function (datagrid) {
        datagrid = string2Jquery(datagrid);
        return datagrid.datagrid('getChanges','updated');
    },
    'endEdit': function (datagrid) {
        datagrid = string2Jquery(datagrid);
        var rows = datagrid.datagrid('getRows');
        for(var x in rows){
            datagrid.datagrid("endEdit",x);
        }
    }
};
var Ajaxor = {
    'postInSync': function (url,obj,callback) {
        $.ajax({
            type:'POST',
            url:url,
            async:false,
            data:obj,
            success:callback
        });
    },
    /**
     * 判断是否返回的是提示信息
     * @param backdata ajax返回数据
     * @returns {boolean}
     */
    'isMessage': function (backdata) {
        return backdata.hasOwnProperty('message') && backdata.hasOwnProperty('type');
    }
};

/**
 * 表单助手
 * @type {{makeWeeksArray: Function, autoFillWeeks: Function}}
 */
var Formor = {
    /**
     * 从checkbox中还原周次
     * @param jboxes jquery数组
     * @returns {object}
     */
    'makeWeeksArray'   : function(jboxes){
        var obj={};                  //周次
        for(var i=0;i<jboxes.length;i++){
            if(jboxes[i].checked){
                obj[jboxes[i].name]=1;
            }else{
                obj[jboxes[i].name]=0;
            }
        }
        return obj;
    },
    /**
     * 根据ajax返回的数组填写表单中的box部分
     * @param jboxes
     * @param cArray
     */
    'autoFillWeeks' : function(jboxes,cArray){
    },
    /**
     * 获取周次
     * @param selector 可以是选择器，也可以是jquery对象
     * @returns {Array}
     */
    'getWeeks'  : function (selector) {
        selector = string2Jquery(selector);
        var weeks=[];                  //周次
        for(var i=0;i<selector.length;i++){
            weeks[i] = selector[i].checked?1:0;
        }
        return weeks;
    },
    /**
     * 自动填写表单
     * @param selector string|jQuery ID选择器或者jquery对象
     * @param data
     */
    'autoTableFill' : function (selector,data) {
        selector = string2Jquery(selector);
        for(var x in data){
            var element = selector.find("#"+x);
            if(element.length){
                //console.log(element,data[x]);
                element.val(data[x]);//自动装填
            }
        }
    },
    'getDateBox': function (selector) {
        selector = string2Jquery(selector);
        return selector.datebox('getValue');
    },
    'setDateBox': function(selector,date){
        selector = string2Jquery(selector);
        return selector.datebox('setValue',date);
    }

};

var GenKits = {
    'getParentYear' : function(){
        return $.trim(parent.$("#YEAR").val());
    },
    'getParentTerm' : function(){
        return $.trim(parent.$("#TERM").val());
    },
    'inArray':function(search,array){
        var i = null;
        if(arguments[2]){
            //表示启用严格模式，默认为false
            for(i in array){
                if(array[i]===search) return true;
            }
        }else{
            for(i in array){
                if(array[i]==search) return true;
            }
        }
        return false;
    },
    'isArray': function (obj) {
        return Object.prototype.toString.call(obj) === '[object Array]';
    },

    'formatFloat': function (src, pos) {
        return Math.round(src*Math.pow(10, pos))/Math.pow(10, pos);
    }

};








/****************************** 表格行为 ***************************************************************************/
/**
 * 注意：使用前需要倒导入一下js文件
 <script type="text/javascript" src='__ROOT__/thirdparty/jquery/js/datagrid-bufferview.js'> </script>
 <script type="text/javascript" src="__ROOT__/thirdparty/jquery/js/easyui.validate.js"></script>
 <script type="text/javascript" src="__ROOT__/thirdparty/jquery/js/easyui.extend.js"></script>
 <script type="text/javascript" src="__ROOT__/thirdparty/jquery/js/common.js"></script>
 <script type="text/javascript" src="__ROOT__/thirdparty/jquery/js/modules/results.js"></script>
 */



/**
 * 通用Datagrid编辑工具
 * @type {{currow: {index: null, field: null}, initDatagrid: Function}}
 */
var GenDataGridEditor = {
    'currow'    :{
        index:null,
        field:null
    },
    'initDatagrid':function(selector,fields){
        selector = string2Jquery(selector);
        selector.datagrid({
            onClickCell:function(index, field){
                if(!isArray(fields)) fields = [fields];
                for(var x in fields) {
                    if ($.trim(field) != fields[x]) {
                        if(GenDataGridEditor.currow.index !== null){
                            selector.datagrid('endEdit', GenDataGridEditor.currow.index);//结束上一次的编辑
                        }
                    }
                }
                GenDataGridEditor.currow.index = index; //初始化参数
                GenDataGridEditor.currow.field = field;
                selector.datagrid('startEditing',GenDataGridEditor.currow);
            },
            onAfterEdit:function(index,dataRow){
                $(this).datagrid('refreshRow', index);
            }
        });

        $(document).keydown(function(event) {
            if (GenDataGridEditor.currow.index!=null){    //如果正在编辑
                if(event.which==9 || event.which==13){  //如果是tab键或回车键
                    event.preventDefault();
                    if(selector.datagrid('getRows').length > GenDataGridEditor.currow.index+1){     //如果编辑下一个的下标小于列表长度
                        GenDataGridEditor.currow.index++; //下标+1
                        selector.datagrid('endEdit',GenDataGridEditor.currow.index-1);   //结束上一行的编辑

                        selector.datagrid('startEditing',GenDataGridEditor.currow);  //开始编辑下一行
                        selector.datagrid('selectRow',GenDataGridEditor.currow.index);  //设置选中下一行
                    }else if(selector.datagrid('getRows').length-1 == GenDataGridEditor.currow.index){  //如果编辑下标处于最后一条位置
                        selector.datagrid('endEdit',GenDataGridEditor.currow.index);    //结束编辑
                        GenDataGridEditor.currow.index=null;  //设置下标为空
                    }
                }
            }
        });
    }
};



/**
 * 获取表格修改后的数据
 * (如果是增加的或者删除的行则待扩展)
 * @param dgrid 表格选择器或者对象
 * @param fields 选择的表格字段
 * @param (可选) callback 对值进行梳理的回掉函数
 *      回掉函数的错误信息返回方式:
 *      _error设置为true，_info设置为错误提示信息
 * @param (可选) params 毁掉函数的参数
 * @returns {*}
 */
function getDatagridByFields(dgrid,fields){
    dgrid = string2Jquery(dgrid);

    //先结束正在编辑的行
    //Datagrid.endEdit(dgrid);
    try{
        if(currentrow.index !== null){
            dgrid.datagrid('endEdit', currentrow.index);
        }
    }catch (e){}
    //获取全部更新过后的行
    var rowlist = Datagrid.getUpdated(dgrid);
    console.log(rowlist);
    var resultlist = [];
    //检查非空
    for(var i=0; i<rowlist.length; i++){
        var result = {};
        dgrid.datagrid('endEdit',i);
        if(!isArray(fields)){
            fields = [fields];
        }
        for(var x in fields){
            var value = $.trim(rowlist[i][fields[x]]);
            //检查是否有错误信息
            if((value instanceof Object) && (value['_error'] === true)){
                //console.log(temp);
                return Messager.showMessage(value['_info']);
            }
            result[fields[x]] = value;//代表这个字段域 加工后的 结果
            result['_origin'] = rowlist[i];//原始值
        }
        resultlist[i]=result;
    }
    return resultlist;
}

/********************************* PHP类似函数 **************************************************************************/

/**
 * 判断参数中是否包含空字符串
 * @returns {boolean}
 */
function hasEmptySting(){
    for(var x in arguments){
        if('' === $.trim(arguments[x])){
            return true;
        }
    }
    return false;
}








