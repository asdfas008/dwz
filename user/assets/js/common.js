/** EasyWeb v3.1.8 date:2020-05-04 License By http://easyweb.vip */
layui.config({  // common.js是配置layui扩展模块的目录，每个页面都需要引入
    version: '318',   // 更新组件缓存，设为true不缓存，也可以设一个固定值
    base: getProjectUrl() + 'assets/module/'
}).extend({
    steps: 'steps/steps',
    notice: 'notice/notice',
    cascader: 'cascader/cascader',
    dropdown: 'dropdown/dropdown',
    fileChoose: 'fileChoose/fileChoose',
    Split: 'Split/Split',
    Cropper: 'Cropper/Cropper',
    tagsInput: 'tagsInput/tagsInput',
    citypicker: 'city-picker/city-picker',
    introJs: 'introJs/introJs',
    zTree: 'zTree/zTree'
}).use(['layer', 'admin'], function () {
    var $ = layui.jquery;
    var layer = layui.layer;
    var admin = layui.admin;

    // 移除loading动画
    setTimeout(function () {
        admin.removeLoading();
    }, 100);

});

/** 获取当前项目的根路径，通过获取layui.js全路径截取assets之前的地址 */
function getProjectUrl() {
    var layuiDir = layui.cache.dir;
    if (!layuiDir) {
        var js = document.scripts, last = js.length - 1, src;
        for (var i = last; i > 0; i--) {
            if (js[i].readyState === 'interactive') {
                src = js[i].src;
                break;
            }
        }
        var jsPath = src || js[last].src;
        layuiDir = jsPath.substring(0, jsPath.lastIndexOf('/') + 1);
    }
    return layuiDir.substring(0, layuiDir.indexOf('assets'));
}
var userInfo;
function getUserInfo(){
    userInfo = localStorage.getItem("userInfo");
    if(userInfo){
        userInfo = JSON.parse(userInfo);
    }else{
        location.href = '/user/page/template/login/login.html';
    }
}

function setUserInfo(userInfo){
    localStorage.setItem("userInfo", JSON.stringify(userInfo));
}
function delUserInfo(){
    localStorage.removeItem("userInfo");
}


var hashToken;
function getHashToken(){
    hashToken = sessionStorage.getItem("haskToken");
    if(!hashToken){
        hashToken = Number(Math.random().toString().substr(3, 3) + Date.now()).toString(36);
        sessionStorage.setItem("haskToken", hashToken);
    }
}

var API_SERVER = 'http://7u6u.cn';

