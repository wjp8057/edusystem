/**
 * Created by fangrenfu on 2016/11/11.
 */
function browsercheck(){
    var userAgent = navigator.userAgent,
        rMsie = /(msie\s|trident.*rv:)([\w.]+)/,
        rFirefox = /(firefox)\/([\w.]+)/,
        rOpera = /(opera).+version\/([\w.]+)/,
        rChrome = /(chrome)\/([\w.]+)/,
        rSafari = /version\/([\w.]+).*(safari)/;
    var ua = userAgent.toLowerCase();
    var match = rMsie.exec(ua);
    if(match != null){
        return { browser : "IE", version : match[2] || "0" };
    }
    match = rFirefox.exec(ua);
    if (match != null) {
        return { browser : match[1] || "", version : match[2] || "0" };
    }
    match = rOpera.exec(ua);
    if (match != null) {
        return { browser : match[1] || "", version : match[2] || "0" };
    }
    match = rChrome.exec(ua);
    if (match != null) {
        return { browser : match[1] || "", version : match[2] || "0" };
    }
    match = rSafari.exec(ua);
    if (match != null) {
        return { browser : match[2] || "", version : match[1] || "0" };
    }
    if (match != null) {
        return { browser : "", version : "0" };
    }
}