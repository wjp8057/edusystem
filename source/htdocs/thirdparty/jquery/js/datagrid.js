var current_datagrid=null;
var current_datagrid_obj=null;
$(function(){
    $(document).keydown(function(event) {
        if (current_datagrid!=null) { //如果正在编辑
            if(event.which==9){
                event.preventDefault();
                if(current_datagrid.datagrid('validateRow',current_datagrid_obj.editIndex))
                    NextEditor(current_datagrid,current_datagrid_obj);
            }
            else if(event.which==13){
                event.preventDefault();
                if(current_datagrid.datagrid('validateRow',current_datagrid_obj.editIndex))
                    NextEditor(current_datagrid,current_datagrid_obj,'col');
            }
        }
    });
});
function NextEditor(tt,obj,type){
    var fields = tt.datagrid('getColumnFields',true).concat(tt.datagrid('getColumnFields'));
    var field=obj.field;
    var length=tt.datagrid('getRows').length;
    //如果是要行切换
    if(type=='col'){
        obj.index=obj.index+1;
    }
    else{
        for(var i=0; i<fields.length; i++){
            if (fields[i] == field){
                if(i+1<fields.length)
                    field=fields[i+1];
                else {
                    field=fields[0];
                    obj.index=obj.index+1;
                }
                break;
            }
        }
    }
    obj.field=field;
    if(obj.index>=length)
        endEditing(tt,obj);
    else{
        var col = tt.datagrid('getColumnOption',field);
        if(col.editor==null)
            NextEditor(tt,obj,type);
        else{
            tt.datagrid('startEditing',obj);
            if(obj.field=='teachername'){
                $('#teacher_dlg').dialog('open');
            }
        }
    }
}