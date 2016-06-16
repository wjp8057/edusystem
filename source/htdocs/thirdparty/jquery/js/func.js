//ajax请求
function ajiakesi(url,data,func){
    $.ajax({
        type:'POST',
        url:url,
        data:data,
        success:function(c){
            func(c);
        }
    })
}



//todo:判断是否是一条
function yitiao(dat,str,str2){
    var rowList=dat.datagrid('getSelections');              //获取所有被选中的
    if(rowList.length>1){
        $.messager.alert('提示',str)
    }else if(rowList.length<1){
        $.messager.alert('提示',str2)
    }else{
        return true
    }
    return false;

}

//datagrid
function dataTable(url2,columns,toolbar){
    this.url=url2;
    this.columns=columns;
    this.fit=true;
    this.pageList=[10,20,30,40,50];
    this.pageSize=20;
    this.pagination=true;
    this.toolbar=toolbar
}

//弹窗
function wind(width,height,maximizable,minimizable,closed,modal,fit){
    this.width=width;
    this.height=height;
    this.maximizable=maximizable;
    this.minimizable=minimizable;
    this.closed=closed
    this.modal=modal;
    this.fit=fit;
    this.closable=false;
    this.collapsible=false
}



//todo:去掉前后空格
String.prototype.trim=function() {
    return this.replace(/(^\s*)|(\s*$)/g,'');
}


//todo:保留小数点
function formatFloat(src, pos){
    return Math.round(src*Math.pow(10, pos))/Math.pow(10, pos);

}

function ajiakesi2(url,data,func){
    $.ajax({
        type:'POST',
        url:url,
        async:false,
        data:data,
        success:function(c){
            func(c);
        }
    })
}

function itObjIsNull(obj)
{
	if (typeof obj === "object" && !(obj instanceof Array)){  
	    var hasProp = false;  
	    for (var prop in obj){  
	        hasProp = true;  
	        break;  
	    }  
	    if (hasProp){  
	    	return true;  
	    }else{  
	        return false;  
	    }  
	}  
}


