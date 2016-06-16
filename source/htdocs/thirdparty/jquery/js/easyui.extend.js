/**
 * Created by fangrenfu@126.com on 13-11-6.
 * easyui 的扩展函数定义
 */

function endEditing(tt,obj){
    if (obj.editIndex == undefined){return true}
    if (tt.datagrid('validateRow',obj.editIndex)){
        tt.datagrid('endEdit', obj.editIndex);
        obj.editIndex = undefined;
        return true;
    } else {
        return false;
    }
}
//拓展定义了datagrid
$.extend($.fn.datagrid.methods, {
    //editCell方法
    editCell: function(jq,param){
        return jq.each(function(){
            var opts = $(this).datagrid('options');
            //获取所有冻结的与非冻结的字段
            var fields = $(this).datagrid('getColumnFields',true).concat($(this).datagrid('getColumnFields'));
            for(var i=0; i<fields.length; i++){
                var col = $(this).datagrid('getColumnOption', fields[i]);
                col.editor1 = col.editor;
                if (fields[i] != param.field){
                    col.editor = null;
                }
            }
            $(this).datagrid('beginEdit', param.index);
            for(var i=0; i<fields.length; i++){
                var col = $(this).datagrid('getColumnOption', fields[i]);
                col.editor = col.editor1;
            }
        });
    },
    //startEditing方法，进入编辑状态
    startEditing:function(jq,obj){
        return jq.each(function(){
            if (endEditing($(this),obj)){
                $(this).datagrid('selectRow', obj.index)
                    .datagrid('editCell',{index:obj.index,field:obj.field});
                obj.editIndex = obj.index;
                var ed = $(this).datagrid('getEditor', {index:obj.index,field:obj.field});
                $(ed.target).select(); //要使用target才能获得对象
                $(ed.target).focus();
            }
        });
    },
    //endEditing方法，结束编辑状态
    /*  这个拓展定义有点问题，返回值无法正常获取，比如tt.datagrid('endEditing',admin_user_obj)，返回的不是true或者false，而是对象
    endEditing:function(jq,obj){
        return jq.each(function(){ //自定义的时候必须将jq.each用上，其实我也不知道为什么
            if (obj.editIndex == undefined){return true}
            if ($(this).datagrid('validateRow',obj.editIndex)){
                $(this).datagrid('endEdit', obj.editIndex);
                obj.editIndex = undefined;
                return true;
            } else {
                return false;
            }
        });
    },
    */
    //创建表头菜单，使用对象的cmenu
    createColumnMenu:function(jq,cmenu_obj){
        return jq.each(function(){ //自定义的时候必须将jq.each用上，其实我也不知道为什么
            cmenu_obj.cmenu = $('<div/>').appendTo('body');
            tt=$(this);//定义一个，在onclick事件中用
            cmenu_obj.cmenu.menu({
                onClick: function(item){
                    if (item.iconCls == 'icon-ok'){
                        tt.datagrid('hideColumn', item.name);
                        cmenu_obj.cmenu.menu('setIcon', {
                            target: item.target,
                            iconCls: 'icon-empty'
                        });
                    } else {
                        tt.datagrid('showColumn', item.name);
                        cmenu_obj.cmenu.menu('setIcon', {
                            target: item.target,
                            iconCls: 'icon-ok'
                        });
                    }
                }
            });
            var fields = $(this).datagrid('getColumnFields');
            for(var i=0; i<fields.length; i++){
                var field = fields[i];
                var col = $(this).datagrid('getColumnOption', field);
                cmenu_obj.cmenu.menu('appendItem', {
                    text: col.title,
                    name: field,
                    iconCls: 'icon-ok'
                });
            }
        });
    }
});

/**
 * linkbutton方法扩展
 * @param {Object} jq
 */
$.extend($.fn.linkbutton.methods, {
    /**
     * 激活选项（覆盖重写）
     * @param {Object} jq
     */
    enable: function(jq){
        return jq.each(function(){
            var state = $.data(this, 'linkbutton');
            if ($(this).hasClass('l-btn-disabled')) {
                var itemData = state._eventsStore;
                //恢复超链接
                if (itemData.href) {
                    $(this).attr("href", itemData.href);
                }
                //回复点击事件
                if (itemData.onclicks) {
                    for (var j = 0; j < itemData.onclicks.length; j++) {
                        $(this).bind('click', itemData.onclicks[j]);
                    }
                }
                //设置target为null，清空存储的事件处理程序
                itemData.target = null;
                itemData.onclicks = [];
                $(this).removeClass('l-btn-disabled');
            }
        });
    },
    /**
     * 禁用选项（覆盖重写）
     * @param {Object} jq
     */
    disable: function(jq){
        return jq.each(function(){
            var state = $.data(this, 'linkbutton');

            if (!state._eventsStore)
                state._eventsStore = {};
            if (!$(this).hasClass('l-btn-disabled')) {
                var eventsStore = {};
                eventsStore.target = this;
                eventsStore.onclicks = [];
                //处理超链接
                var strHref = $(this).attr("href");
                if (strHref) {
                    eventsStore.href = strHref;
                    $(this).attr("href", "javascript:void(0)");
                }
                //处理直接耦合绑定到onclick属性上的事件
                var onclickStr = $(this).attr("onclick");
                if (onclickStr && onclickStr != "") {
                    eventsStore.onclicks[eventsStore.onclicks.length] = new Function(onclickStr);
                    $(this).attr("onclick", "");
                }
                //处理使用jquery绑定的事件
                var eventDatas = $(this).data("events") || $._data(this, 'events');
                if (eventDatas["click"]) {
                    var eventData = eventDatas["click"];
                    for (var i = 0; i < eventData.length; i++) {
                        if (eventData[i].namespace != "menu") {
                            eventsStore.onclicks[eventsStore.onclicks.length] = eventData[i]["handler"];
                            $(this).unbind('click', eventData[i]["handler"]);
                            i--;
                        }
                    }
                }
                state._eventsStore = eventsStore;
                $(this).addClass('l-btn-disabled');
            }
        });
    }
});
//拓展定义datagrid的editors控件
