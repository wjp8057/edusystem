/**
 * Created by Lin on 15-04-20.
 */

var qhw={q:'缺考',h:'缓考',w:'违纪',Q:'缺考',H:'缓考',W:'违纪'};
var wuji={1:'优秀',2:'良好',3:'中等',4:'及格',5:'不及格'}  ;
var erji={1:'合格',0:'不合格'};
//识别值转编辑值
function visual2edit(cj){
    switch ($.trim(cj)){
        case '不合格':return 0;
        case '合格':return 1;
        case '优秀':return 1;
        case '良好':return  2;
        case '中等':return  3;
        case '及格':return  4;
        case '不及格':return  5;
        case '缺考':return 'q';
        case '缓考':return  'h';
        case '违纪':return  'w';
        default :return cj;
    }
}

/**
 * 统计人数
 */
var scores = {
    youxiu:0,
    lianghao:0,
    zhongdeng:0,
    jige:0,
    bujige:0
};
function scoreCount(){
    if(arguments.length){
        var score = arguments[0];
        var temp = parseInt(score);
        if(!isNaN(parseInt(score)) ){
            score = temp;
        }else{
            score = $.trim(score);
        }
        console.log(score);
        if(score === '优秀' || score >= 90){
            scores.youxiu ++;
        }else if(score === '良好' || score >= 80){
            scores.lianghao ++;
        }else if(score === '中等' || score >= 70){
            scores.zhongdeng ++;
        }else if(score === '及格' || score === '合格' || score >= 60){
            scores.jige ++;
        }else if(score === '不及格' || score === '不合格' || score < 60){
            scores.bujige++;
        }else{}
    }
    return scores;
}
/**
 * 类型为未定义时不自动转化换
 * @param str
 * @returns {string}
 */
function transOnUndefined(str){
    return typeof str === 'undefined'?'': str;
}
/**
 * 数字转百分比
 * @param $num
 * @param $total
 * @returns {string}
 */
function count2Percent($num,$total){
    return formatFloat($num/$total*100,2)+'%'
}

//编辑值转识别值
function edit2visual(cj,scoretype){
    scoretype = $.trim(scoretype);
    cj = $.trim(cj);
    if($.trim(cj)=='q'|| $.trim(cj)=='h'|| $.trim(cj)=='w'|| $.trim(cj)=='Q'|| $.trim(cj)=='H'|| $.trim(cj)=='W'){
        return qhw[$.trim(cj)];
    }
    if(scoretype=='ten'){
        if(isNaN($.trim(cj))){/*中文*/
            if(!inObj(cj)){/*非qhw中的*/
                return 'ERROR_TEN';
            }
        }else if( parseInt($.trim(cj))<0|| parseInt($.trim(cj))>100){/*非中文（数字） 超出范围的数字*/
            return 'ERROR_TEN';
        }
        return cj;
    }else if(scoretype=='five'){
        if(isNaN($.trim(cj))){
            if(inObj(cj,wuji) || inObj(cj,qhw)){
                return cj;
            }
            return 'ERROR_FIVE';
        }else if( parseInt($.trim(cj)) > 5 || parseInt($.trim(cj)) < 1){
            return 'ERROR_FIVE';
        }else{
            return wuji[parseInt($.trim(cj))];
        }
    }else if(scoretype=='two'){        //todo:二级制
        if(isNaN($.trim(cj)) ){
            if(inObj(cj,erji) || inObj(cj,qhw)){
                return cj;
            }
            return 'ERROR_TWO';
        }else if(parseInt($.trim(cj)) > 1 || parseInt($.trim(cj)) < 0){
            return 'ERROR_TWO';
        }else{
            return erji[parseInt($.trim(cj))];
        }
    }
}

/**
 * @funtion 处理识别值
 * @param val 成绩的显示值
 * @return boolean 如果识别值不是期望值，弹出提示框并返回FALSE
 */
function doWithVitualVal(val){
    switch (val){
        case 'ERROR_TEN':
            $.messager.alert('提示','百分制请输入0-100的数字');
            return false;
        case 'ERROR_FIVE':
            $.messager.alert('提示','五级制输入时成绩为数字1-5或者字母(q、h、w)\n');
            return false;
        case 'ERROR_TWO':
            $.messager.alert('提示','二级制输入时总评成绩1-0或者使用字母(q、h、w)\n');
            return false;
    }
    return true;
}
/**
 * 处理识别值是否是错误的
 * true === 则正确
 * @funtion 处理识别值
 * @param val 成绩的显示值
 * @return boolean 如果识别值不是期望值，弹出提示框并返回FALSE
 */
function checkVisualValue(val){
    switch (val){
        case 'ERROR_TEN':
            val =  '百分制请输入0-100的数字';
            break;
        case 'ERROR_FIVE':
            val = '五级制输入时成绩为数字1-5或者字母(q、h、w)\n';
            break;
        case 'ERROR_TWO':
            val = '二级制输入时总评成绩1-0或者使用字母(q、h、w)\n';
            break;
        default :
            return true;
    }
    return val;
}

function inObj(val,obj){
    var x;
    if(!obj){//when undefined
        obj = qhw;
    }
    for(x in obj){
        if(obj[x] == $.trim(val)){
            return true;
        }
    }
    return false;
}


