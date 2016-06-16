/**
 * Created with JetBrains PhpStorm.
 * User: admin
 * Date: 13-7-18
 * Time: 上午6:45
 * To change this template use File | Settings | File Templates.
 */
    // 扩展比较验证 equals['#rpwd']
$.extend($.fn.validatebox.defaults.rules, {
    equals: {
        validator: function(value,param){
            return value == $(param[0]).val();
        },
        message: '两次输入不同！'
    },
//扩展最小-最大验证 minmaxLength[6,20]
    minmaxLength: {
        validator: function(value, param){
            return value.length >= param[0]&&value.length <= param[1];
        },
        message: '必须{0}-{1}个字符之间！'
    },
    minLength: {
        validator: function(value, param){
            return value.length >= param[0];
        },
        message: '至少{0}个字符！'
    },
    maxLength: {
        validator: function(value, param){
            return value.length <= param[0];
        },
        message: '不得超过{0}个字符！'
    },
    equalLength: {
        validator: function(value, param){
            return value.length == param[0];
        },
        message: '应为{0}个字符！'
    }
});
