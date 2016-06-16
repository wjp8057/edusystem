/**
 * Created by Administrator on 13-11-18.
 */
//检查每行数据。
//检查数据是否被锁定。1为检查两个字段，2为仅检查check字段,3为单行数据检查两个字段,4为单行数据检查check字段
  function checkRowData(rows,type){
    if(type==1){
        for(var i=0;i<rows.length;i++){
            if(rows[i].audit==1||rows[i].check==1){
                $.messager.alert('锁定','"'+rows[i].name+'"已通过审核或确认，无法更改数据！','error');
                return false;
            }
        }
    }
    else if(type==2)
    {
        for(var i=0;i<rows.length;i++){
            if(rows[i].check==1){
                $.messager.alert('锁定','"'+rows[i].name+'"已通过最终确认，无法更改状态！请点击取消按钮撤销更改','error');
                return false;
            }
        }
    }
    else if(type==3)
    {
        if(rows.audit==1||rows.check==1){
            $.messager.alert('锁定','"'+rows.name+'"已通过审核或确认，无法更改数据！','error');
            return false;
        }
    }
    else if(type==4)
    {
        if(rows.check==1){
            $.messager.alert('锁定','"'+rows.name+'"已通过最终确认，无法更改状态！请点击取消按钮撤销更改','error');
            return false;
        }
    }
    return true;
  }

/**
 * 加载老师的数据
 *  需要一个input:text类型的输入框
 *  由于js文件不会被模板引擎替换，所以需要提供第二个参数
 * @param inputid 输入框ID
 * @param appurl 输入框所在页面的__APP__常量值
 */
function teachersloader(inputid,appurl){
    var nameinput = $("#"+inputid);
    if(!nameinput.hasClass('easyui-combobox')){
        nameinput.addClass('easyui-combobox');
    }
    nameinput.combobox({
        valueField:'val',
        textField:'name',
        onChange:function(newval,oldval){
            $.post(appurl+'/Common/Provider/getjsonteachers',{'input':newval}, function (data) {
                nameinput.combobox('loadData',data);
            });
        }
    });
}
