/**
* Created by Lin on 2015/8/7.
*/
//-- 函数预定义 --//
function hasEmpty(){
    for(var x in arguments){
        if(!arguments[x] || arguments[x] === '%'){
            return true;
        }
    }
    return false;
}
function cleanSelect(sel){
    sel.html('<option value="%">全部</option>');
}

/**
 * 由于插件中无法传递模板变量，故需要手动传递
 * @param root
 */
function searcher(root) {

    var env = this;
    var pro = searcher.prototype;

    env.gradeSelect = $('.SEARCHER_GRADE').eq(0);
    env.schoolSelect = $('.SEARCHER_SCHOOL').eq(0);
    env.classSelect = $('.SEARCHER_CLASS').eq(0);
    env.studentSelect = $('.SEARCHER_STUDENT').eq(0);
    //-- 预加载 --//
    $.post(root+'/Common/Provider/seacher',{reqtag:'grade'},function(r){
        env.gradeSelect.html('');
        for(var x in r){
            $('<option value="'+r[x].value+'">'+r[x].text+'</option>').appendTo(env.gradeSelect);
        }
        $.post(root+'/Common/Provider/seacher',{reqtag:'class',GREADE:env.gradeSelect.val()},function(r){
            cleanSelect(env.classSelect);
            for(var x in r){
                $('<option value="'+r[x].value+'">'+r[x].text+'</option>').appendTo(env.classSelect);
            }
        });
    });
    $.post(root+'/Common/Provider/seacher',{reqtag:'school'},function(r){
        cleanSelect(env.schoolSelect);
        for(var x in r){
            $('<option value="'+r[x].value+'">'+r[x].text+'</option>').appendTo(env.schoolSelect);
        }
    });
    cleanSelect(env.studentSelect);//学生默认选择全部
    //-- 回调函数 --//
    pro.loadClasses = function(){
        if(0 === env.classSelect.length) return ;
        if(hasEmpty(env.gradeSelect.val(),env.schoolSelect.val())){
            return cleanSelect(env.classSelect);
        }
        $.post(root+'/Common/Provider/seacher',{reqtag:'class',GREADE:env.gradeSelect.val(),SCHOOLNO:env.schoolSelect.val()},function(r){
            cleanSelect(env.classSelect);
            for(var x in r){
                $('<option value="'+r[x].value+'">'+r[x].text+'</option>').appendTo(env.classSelect);
            }
        });
    };
    pro.loadStudents = function(){
        if(0 === env.studentSelect.length) return ;
        if(hasEmpty(env.gradeSelect.val(),env.schoolSelect.val(),env.classSelect.val())){
            return cleanSelect(env.studentSelect);
        }
        $.post(root+'/Common/Provider/seacher',{reqtag:'student',CLASSNO:env.classSelect.val()},function(r){
            cleanSelect(env.studentSelect);
            for(var x in r){
                $('<option value="'+r[x].value+'">'+r[x].text+'</option>').appendTo(env.studentSelect);
            }
        });
    };
    pro.reloadForm = function(){
        pro.loadClasses();
        pro.loadStudents();
    };

    //-- 事件注册 --//
    env.gradeSelect.change(pro.reloadForm);
    env.schoolSelect.change(pro.reloadForm);
    env.classSelect.change(pro.loadStudents);

}






