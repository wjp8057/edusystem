/**
 * Created by linzh on 2015/4/24.
 */

/**
 * 为datagrid自定义行为
 */
$.extend($.fn.datagrid.methods, {
    addEditor : function(jq, param){
        if (param instanceof Array){
            $.each(param, function(index, item){
                var e = $(jq).datagrid('getColumnOption', item.field);
                e.editor = item.editor;
            });
        }else{
            var e = $(jq).datagrid('getColumnOption', param.field);
            e.editor = param.editor;
        }
    },
    removeEditor : function(jq, param){
        if (param instanceof Array){
            $.each(param, function(index, item){
                var e = $(jq).datagrid('getColumnOption', item);
                e.editor = {};
            });
        }else{
            var e = $(jq).datagrid('getColumnOption', param);
            e.editor = {};
        }
    }
});
/*-- 单个认定  ： 【院系审核（创新）】 【教务处终审（创新）】 【院系审核（素质）】 【教务处终审（素质）】--*/
    /**
     * 查看申请信息，用于学分单个认定的情况
     * 使用到的页面包括单个认定的4个页面
     * @param num 申请ID号
     */
    function lookDetail(url,num){
        openTabsWindows('申请单详情',url+'/shenqingdan/id/'+num,'',true)
    }
    /**
     * 保存表格编辑过的内容，用于学分单个认定的情况
     * 使用到的页面包括单个认定的4个页面
     * @param tablename 编辑表格的ID
     */
    function saveChangeSingle(tablename,saveurl){
        var table = $('#'+tablename);
        var rows=table.datagrid('getRows');
        for(var i=0;i<rows.length;i++){
            table.datagrid('endEdit',i);
        }
        var list=table.datagrid('getChanges','updated');
        $.post(saveurl,{bind:list},function(c){
            $.messager.alert('提示',c);
            table.datagrid('reload');
        });
    }

/*-- 统一认定\学生添加  ： 【学生添加（创新）】  【学生添加（素质）】--*/
    /**
     * 批量添加学生 点击添加
     * @param tablename string 要添加的表的ID
     * @param outrange array  不需要编辑的字段组成的数组
     * @param studentno string 学号
     * @param schoolno string 学院号
     * @returns 返回错误调试窗口
     */
    function addStudentBatch(url,tablename,outrange,studentno,schoolno){
        var table = $('#'+tablename);
        /*添加时编辑的范围在学号和姓名之外*/
        for(x in outrange){
            table.datagrid('removeEditor',outrange[x]);
        }
        if(studentno==''){
            return $.messager.alert('提示','请在填写学生号!')
        }
        /*检查是否属于自己的学院*/
        $.post(url+'/SkillcountSchool/tag/ourschool',
            {'studentno': studentno,'teacher_schoolno':schoolno},
            function(c){
                if(c=='false'){
                    return $.messager.alert('提示','您不能添加其他学院的学生，或您填写的学生号有误')
                }
                var one = c instanceof Object?c:eval('('+c+')');
                console.log(one);
                if(!$.trim(one['NAME'])){
                    /*学生姓名为空*/
                    return $.messager.alert('提示','你要添加的学生不存在！');
                }
                table.datagrid('insertRow',{
                    index:0,
                    row:{   studentno: $.trim(one.STUDENTNO),
                        name:one['NAME'],
                        projectname:'',
                        credit:'',
                        certficatetime:'',
                        addtime:one['time']}});
                table.datagrid('beginEdit',0)
            });
    }

    /**
     * 批量添加学生 拷贝操作
     * @param tablename  string 要添加的表的ID
     * @returns {*}
     */
    function copyStudentBatch(tablename){
        var table = $('#'+tablename);
        var list=table.datagrid('getSelections')
        if(list.length>1){
            return $.messager.alert('提示','请选择一条要复制的数据,不要选择多条')
        }else if(list.length<1){
            return $.messager.alert('提示','请选择一条要复制的数据');
        }else{
            var row=table.datagrid('getSelected');/*获取选择的数据*/
            $('#apply_dat').datagrid('addEditor',{
                field:'studentno',
                editor : {
                    type : 'validatebox'
                }});
            table.datagrid('insertRow',{
                index:0,
                row:{projectname:row.projectname,
                    credit:row.credit,
                    certficatetime:row.certficatetime,
                    addtime:row.addtime}});
            table.datagrid('beginEdit',0)
        }
    }

    /**
     * 批量添加学生 删除操作
     * @param tablename
     * @param param
     */
    function deleteStudentBatch(url,tablename,param){
        var table = $('#'+tablename);
        $.messager.confirm('提示','确定要删除学生吗?',function(c){
            var rowlist=$('#apply_dat').datagrid('getSelections')
            $.post(url+'/SkillcountSchool/tag/delstudent',{bind:rowlist},
                function(c){
                    $.messager.alert('提示',c)
                    param.pd=false
                    $('#apply_dat').datagrid('reload');
                });
        });
    }

    /**
     * 批量添加学生 编辑操作
     * @param tablename  string 要添加的表的ID
     * @param outrange  array  不需要编辑的字段组成的数组
     * @param pd 用于判断单签是否有正在编辑的行，不能同时编辑
     * @returns {*}
     */
    function editStudentBatch(tablename,outrange,pd){
        var table = $('#'+tablename);
        /*添加时编辑的范围在学号和姓名之外*/
        for(x in outrange){
            table.datagrid('removeEditor',outrange[x]);
        }
        if(pd){
            return $.messager.alert('提示','有正在编辑的数据');
        }
        var list=table.datagrid('getSelections');
        if(list.length>1){
            return $.messager.alert('提示','请选择一条要编辑的数据,不要选择多条')
        }else if(list.length<1){
            return $.messager.alert('提示','请选择一条要编辑的数据');
        }else{
            var row=table.datagrid('getSelected');
            if(row.status==1){
                return $.messager.alert('提示','已经通过的不能进行编辑');
            }
            table.datagrid('beginEdit',table.datagrid('getRowIndex',row));
        }
    }

    /**
     * 保存修改
     * @param tablename  string 要添加的表的ID
     * @param param array 学年，学期，学院号，判断是否有正在编辑（传引用）
     */
    function saveChangeBatch(url,tablename,param){
        var table = $('#'+tablename);
        //取消全部编辑
        var list=table.datagrid('getRows')
        for(var j=0;j<list.length;j++){
            table.datagrid('endEdit',j);
        }
        var content={}
        content['update']=table.datagrid('getChanges','updated');
        content['insert']=table.datagrid('getChanges','inserted');
        $.post(url+'/insertstudents',{
                bind:content,
                year:param.year,
                term:param.term,
                subschool:param.subschool,
                credittype:param.credittype},
            function(c){
                if(c!='操作成功'){
                    return $.messager.alert('提示',c);
                }
                $.messager.alert('提示',c);
                param.pd=false
                table.datagrid('reload');
            });
    }


/*-- 统一认定\认定终审  ： 【认定终审（创新）】  【认定终审（素质）】--*/






