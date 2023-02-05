<?php
/*正式*/
const APP_DEBUG = false;
const STATIC_FILE_DIR = '/var/www/staticRes/';
const DOMAIN_URL = 'http://7u6u.cn/';
const PWD_URL = 'http://7u6u.cn/redirect.html';

/*本地*/
//const APP_DEBUG = true;
//const STATIC_FILE_DIR = 'd:/www/staticRes/';
//const DOMAIN_URL = 'http://serve.dwz.com/';
//const PWD_URL = 'http://serve.dwz.com/redirect.html';


/*REDIS常量*/
//截流
const ADMIN_JL_VIPTYPE = 'ADMIN-JL-VIPTYPE';
const ADMIN_JL_URL = 'ADMIN-JL-URL';
const ADMIN_JL_PRE = 'ADMIN-JL-PRE';
//过期
const ADMIN_GQ_URL = 'ADMIN-GQ-URL';
//删除
const ADMIN_SC_URL = 'ADMIN-SC-URL';
//IP和名单
const ADMIN_IP_BLACK = 'ADMIN_IP_BLACK';
//支付宝
const ADMIN_ALIPAY_APPID = 'ADMIN_ALIPAY_APPID';
const ADMIN_ALIPAY_PUBLICKEY = 'ADMIN_ALIPAY_PUBLICKEY';
const ADMIN_ALIPAY_PRIVATEKEY = 'ADMIN_ALIPAY_PRIVATEKEY';

//api
const ADMIN_API_BOOL = 'ADMIN-API-BOOL';
const ADMIN_API_MSG = 'ADMIN-API-MSG';
//未实名数量
const ADMIN_WSC_NUM = 'ADMIN-WSC-NUM';
//手机号注册
const ADMIN_TELREG_BOOL = 'ADMIN-TELREG-BOOL';

//点击数
const TOTAL_VIEW_NUMS = 'TOTAL-VIEW-NUMS';//总点击数
const TOTAL_VIEW_NUMS_DATE = 'TOTAL-VIEW-NUMS-DATE-';//每日点击数

//用户点击数
const USER_TOTAL_VIEW_NUMS = 'USER_TOTAL-VIEW-NUMS';//总点击数
const USER_TOTAL_VIEW_NUMS_DATE = 'USER_TOTAL-VIEW-NUMS-DATE-';//每日点击数

//短网址信息前缀
const DWZ_INFO = 'DWZINFO_';

//验证码
const CAPTCHA_KEY = 'CAPTCHA_';

