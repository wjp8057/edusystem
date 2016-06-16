/**
 * 异步延时加载
 * Created by Lin on 2015/5/16.
 * Email : 784855684@qq.com
 * version:1.0
 */
function Lazerloader(){

    var pro = Lazerloader.prototype;
    var env = this;

    env.url = 'source.php';

    env.total = 0;
    env.predata = null;

    env.cur = 0;
    env.size = 10;
    env.ctnselector = 'body';//默认加载到body
    env.tplid = '';
    env.delay = 0;
    env.tag = '';

    //回调函数必须在之前定义或者在$(function(){})中定义
    //已加载完毕后回调，参数是返回的结果
    env.precall = null;
    //预加载后的加载回调
    env.loadcall = null;

    env.isfirst = true;
    env.curtpl = null;
    env.curclone = null;


    /**
     * IE8下将console视为空函数(对象)
     * @type {Console}
     */
    window.console = window.console || (function(){
        var c = {}; c.log = c.warn = c.debug = c.info = c.error = c.time = c.dir = c.profile
            = c.clear = c.exception = c.trace = c.assert = function(){};
        return c;
    })();

    /**
     * 初始化
     * @param obj  初始化参数，属性将被复制到该对象的私有属性上
     */
    pro.init = function (obj) {
        env.tag = 'gettotal';
        env.total = 0;
        env.cur = 0;
        env.isfirst = true;
        for( var x in obj){
            if(x in env) env[x] = obj[x];
        }
        //模板隐藏
        var tpl = $("#"+env.tplid);
        tpl.length &&  tpl.css('display','none');
    };

    /**
     * 异步ajax加载函数
     * @param url  请求URL
     * @param senddata 发送的数据，对象
     * @param isasync 是否异步，默认为true
     * @param callback
     * @param param
     * @returns {*}
     */
    pro.doAjax = function(url,senddata,isasync,callback,param){
        var dat = null;
        //console.log('looksend',senddata);
        senddata.curindex = env.cur;
        senddata.size = env.size;
        senddata.total = env.total;
        $.ajax({
            type:'POST',
            url:env.url,
            data:senddata,
            async:isasync===null?true:isasync,
            success: function (data) {
                if(callback!==null){
                    callback(data,param);
                }
                dat = eval("("+data+")");
            }
        });
        return dat;
    };

    /**
     * 从该容器中克隆模板  子元素全部删除
     * @param tplid  模板的class属性
     * @param container  模板的包裹容器，用于缩小范围
     * @returns {*}
     */
    pro.getClone = function (tplselect,container,tag) {
        //.css('display','')可以使之使用原有的display属性
        var clone = container.find(tplselect).eq(0).clone().css('display','').css('height','auto').removeAttr('id');//高度统一为自己适应
        if(tag !== true){
            clone.html('');
        }
        return  clone;
    };

    /**
     *
     * @param key 键名称，对应模板中的class值，用于发现目标模板，
     * @param val 为基本值时，key所对应的class为单个元素的情况
     *              为对象或数组时，可以对应的class为内嵌元素的情况
     * @param container  如果目标为非内嵌元素的情况，
     */
    pro.nestThrough = function (key,val,container){
        if(val instanceof  Object){
            //是数组或者对象(多元素)
            for(var x in val){
                if(isNaN(x)){
                    //字符串值 ：①模板层 ②替换层
                    //console.log(key,val,x,container);
                    if(val[x] instanceof Object){
                        //还在模板层，待扩展

                    }else{
                        //到了替换层,将对象的属性遍历并替换到模板中
                        var newcontainer = env.getClone('.'+key,env.curtpl,true);
                        for(var i in val){
                            //env.nestThrough(key,val[i],newcontainer);
                            //console.log(i,val[i],newcontainer);
                            newcontainer.find("."+i).html(val[i]);
                        }
                        container.append(newcontainer);
                        break;
                    }
                }else{
                    //如果键是纯数字，按规定是模板层，当时下层一定是替换层，使用完整克隆
                    var newcontainer = env.getClone('.'+key,env.curtpl,true);
                    if(val[x] instanceof Object ){
                        //键为数字的情况下val[x]必定为对象
                        for(var i in val[x]){
                            newcontainer.find('.'+i).html(val[x][i]);
                        }
                    }else{
                        //for(var j in val[x]){//遍历属性
                        //    env.nestThrough(j,val[x][j],newcontainer);
                        //}
                    }
                    container.append(newcontainer); //newcontainer -> container
                }
            }
        }else{
            //如果是单元素就直接输出
            var newcontainer = env.getClone('.'+key,env.curtpl).html(val);
            container.append(newcontainer);
        }
    };

    pro.loadTemplate = function(tplid,data){
        for(var i=0; i < data.length; i++){
            env.curtpl = $('#'+env.tplid,$(env.ctnselector));
            env.curclone  = env.getClone('#'+env.tplid,$(env.ctnselector));
            for(var x in data[i]){
                //将值输出到模板的位置
                env.nestThrough(x,data[i][x],env.curclone);
            }
            $( env.ctnselector).append(env.curclone);
        }
    };

    /**
     * 加载服务器返回的数据，渲染到前台模板中
     * @param data
     */
    pro.loadData = function(data){
        env.loadTemplate(env.tplid,data.data);
        if(env.loadcall){
            env.loadcall(data);
        }
    };

    /**
     * 自动加载数据
     * 按照系统设置的延时进行
     */
    pro.autoLoad = function () {
        if(env.isfirst && env.predata.length > 0){
            //立即加载第一次数据
            env.loadData(env.doAjax(env.url,{'tag':env.tag,'keydata':env.predata.slice(env.cur,env.cur+env.size)},false,null,null));
            env.cur += env.size;
            env.isfirst = false;
        }
        //第二次加载会在一定延时过后执行
        setTimeout(function(){
            if(env.cur < env.total){
                env.loadData(env.doAjax(env.url,{'tag':env.tag,'keydata':env.predata.slice(env.cur,env.cur+env.size)},false,null,null));
                env.cur += env.size;
                return env.autoLoad();
            }
        },env.delay);
    };

    /**
     * 跟随加载模式
     * 上次数据加载完毕后紧接着下一次的加载
     */
    pro.followLoad = function(){
        //待开发
    };

    /**
     * 获取预加载的数据
     * @returns {boolean}
     */
    pro.preload = function (){
        var res = env.doAjax(env.url,{'tag':env.tag},false,null,null);
        //返回的数据用于查询标记
        if(res){
            env.predata = res.data;
            env.total = res.total || env.predata.length;
        }else{
            return false;
        }
        /*********** 预加载模板调用  待开发 **************/
        env.tag = "getdata";
        //预加载完毕后回调自定义的函数
        if(env.precall){
            env.precall(res);
        }
        return true;
    };

    /*自动执行*/
    pro.start = function(obj) {
        env.init(obj);
        if(!env.preload()){
            alert('无法从网络连接中获取数据，请检查网络或服务器是否正常运行!');
        }else{
            //console.log('preloadData',env.predata);
        }
        env.autoLoad();
        return env;
    };
}

