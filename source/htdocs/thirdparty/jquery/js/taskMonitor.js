/**
 * 任务监控器
 */
jQuery.extend({taskMonitor: function(key, title, options){
    //全局对话框jquery对象
    var $preparingDialog = null;
    //初始化时间点
    var initTimestamp = Math.round(new Date().getTime()/1000);

    //从options中定义配置
    var settings = $.extend({
        //对话框板面属性
        dialogOptions : { modal:true, closable:false, width:300,height:140 },
        //前置回调函数
        prepareCallback : function (jqObj) { },
        //成功执行后的回调函数
        successCallback : function () { },
        //执行失败时回调函数
        failCallback : function (responseHtml, error) { },
        //回调间隔时间
        checkInterval : 1000,
        //获得运行结果的url
        url : "/taskInfo.php",
        //是否显示详细信息
        showMessage : false
    }, options);

    //定义回调函数
    var internalCallbacks = {
        onPrepare: function () { //准备、初始化工作
            createDialog();
            if(settings.prepareCallback) settings.prepareCallback($preparingDialog);
        },
        onSuccess: function (url) { //任务成功后回调
            if ($preparingDialog!=null) {
                $preparingDialog.dialog('close');
                $preparingDialog.remove();
            }
            if(settings.successCallback) settings.successCallback();
        },
        onFail: function (responseHtml, error) { //任务发生错误后回调
            if ($preparingDialog!=null) {
                $preparingDialog.dialog('close');
                $preparingDialog.remove();
            }
            if(settings.failCallback) settings.failCallback(responseHtml, error);
        }
    };

    /**
     * 检测是否完成
     */
    function checkComplete(){
        var run = true;
        $.ajax({
            url : settings.url,
            dataType : 'json',
            cache:false,
            async:false,
            data:{key:key,clientTime:initTimestamp},
            success:function(rep) { //同步查询
                if(rep && rep[0]){
                    if(rep[0]['CLIENTTIME'] && rep[0]['CLIENTTIME']!=initTimestamp) initTimestamp=rep[0]['CLIENTTIME'];

                    if(rep[0]["R"]===true) {
                        setTaskInfo(rep[0]);
                        if(settings.showMessage==true) setMessageInfo(rep[1]);
                    }else{
                    	if(initTimestamp<=rep[0]["ST"] && rep[0]["R"]=="done"){
                    	//if(rep[0]["R"]=="done"){
                            internalCallbacks.onSuccess();
                            run = false;
                        }
                    }
                    
                }
            },
            error : function(responseHtml,error){
                setTimeout(function () {
                    internalCallbacks.onFail(responseHtml, error);
                    run = false;
                }, 100);
            }
        });
        if(run==true){
            setTimeout(checkComplete, settings.checkInterval);
        }
    }

    /**
     * 创建任务监控对话框
     */
    function createDialog(){
        $preparingDialog = $("<div style='padding:5px;overflow:hidden'>");
        var $taskInfoJq = $('<div style="height:20px"><b>任务消息：</b><span class="__taskMonitor__msg"></span></div>'
                      +'<div style="height:20px"><b>任务总数：</b><span class="__taskMonitor__total">0</span><span style="float: right"><b>当前任务：</b><span class="__taskMonitor__current">0</span></span></div>'
                      +'<div style="height:20px"><b>已花时间：</b><span class="__taskMonitor__time">0秒</span><span style="float: right"><b>估计剩余时间：</b><span class="__taskMonitor__overtime">0秒</span></span></div>'
                      +'<div style="height:20px"><b>成功：</b><span class="__taskMonitor__yes">0</span><span style="float: right"><b>失败：</b><span class="__taskMonitor__no">0</span></span></div>'
                      +'<div class="__taskMonitor__progressbar"></div>');
        $preparingDialog.append($taskInfoJq);

        if(settings.showMessage==true){
            $preparingDialog.append('<div style="height:20px;margin-top:5px"><b>详情：</b></div>');
            $preparingDialog.append('<div style="height:200px; overflow-y:scroll; background-color:#E0E0E0" class="__taskMonitor__message"></div>');
            settings.dialogOptions.height += 225;
        }

        settings.dialogOptions.title = title;
        $preparingDialog.dialog(settings.dialogOptions);
        $preparingDialog.find(".__taskMonitor__progressbar").progressbar({value: 0,width:274, height:14});
    }

    /**
     * 设置任务消息
     * @param taskinfo
     */
    function setTaskInfo(taskinfo){
        if(!taskinfo) return;
        var _time = taskinfo.CT-taskinfo.ST;
        var _finished = taskinfo.C/taskinfo.T;
        $preparingDialog.find(".__taskMonitor__msg").html(taskinfo.MSG);
        $preparingDialog.find(".__taskMonitor__total").html(taskinfo.T);
        $preparingDialog.find(".__taskMonitor__current").html(taskinfo.C);
        $preparingDialog.find(".__taskMonitor__time").html(formatTime(_time)); //formatTime(_time)
        $preparingDialog.find(".__taskMonitor__overtime").html(formatTime(_time*(1-_finished)/_finished)); //formatTime(_time*(1-_finished)/_finished)
        $preparingDialog.find(".__taskMonitor__yes").html(taskinfo.YES);
        $preparingDialog.find(".__taskMonitor__no").html(taskinfo.NO);
        if(taskinfo.T>0) $preparingDialog.find(".__taskMonitor__progressbar").progressbar("setValue",(taskinfo.C/taskinfo.T*100).toFixed(2));
    }

    function setMessageInfo(messages){
        if(!messages || messages.length==0) return;

        var $messageDiv = $preparingDialog.find(".__taskMonitor__message")
        for(var i in messages){
            $messageDiv.append((messages[i]['S']?"[OK]":"[NO]") + " " +timestampFormat(messages[i]['CT']) + " " + messages[i]['MSG'] + '<br>');
            $messageDiv.scrollTop($messageDiv.height() + $messageDiv[0].scrollHeight);
        }
    }

    /**
     * 格式化工作时间(秒数)
     * @param _time
     * @returns {string}
     */
    function formatTime(_time){
        if(!_time) return "0秒";

        var s = "";
        //_time = Math.ceil(_time / 1000);
        var m = Math.floor(_time / 60);
        if(m>0) {
            s = m.toString() + "分";
            _time -= m*60;
        }
        if(_time>0) s += _time.toFixed(0) + "秒";
        return s;
    }

    function timestampFormat(timestamp){
        var d = new Date(timestamp * 1000);
        return d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();
    }


    //todo 1 执行初始化工作
    internalCallbacks.onPrepare();
    //todo 2 执行是否完成
    //setTimeout(checkComplete, settings.checkInterval);
    checkComplete();

}});