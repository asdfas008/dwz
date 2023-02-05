(window.webpackJsonp = window.webpackJsonp || []).push([[12], {
    338: function(t, e, n) {
        t.exports = n.p + "img/655eab5.jpg"
    },
    340: function(t, e, n) {},
    341: function(t, e, n) {},
    342: function(t, e, n) {},
    388: function(t, e, n) {
        "use strict";
        var o = n(340);
        n.n(o).a
    },
    389: function(t, e, n) {
        t.exports = n.p + "img/abc7ffb.png"
    },
    390: function(t, e, n) {
        t.exports = n.p + "img/0d9166b.png"
    },
    391: function(t, e, n) {
        "use strict";
        var o = n(341);
        n.n(o).a
    },
    392: function(t, e, n) {
        "use strict";
        var o = n(342);
        n.n(o).a
    },
    409: function(t, e, n) {
        "use strict";
        n(32);
        var o = n(4)
          , r = {
            props: {
                isLogin: {
                    type: Boolean,
                    default: !1
                }
            },
            data: function() {
                return {
                    scrollTop: 0,
                    isFixed: ""
                }
            },
            computed: {
                siteOrigin: function() {
                    var t = window.location;
                    return t.origin ? t.origin : t.protocol + "//" + t.hostname + (t.port ? ":" + t.port : "")
                }
            },
            watch: {
                scrollTop: function(t, e) {
                    this.isFixed = this.scrollTop > 0 ? "header-fixed" : ""
                }
            },
            beforeMount: function() {
                document.addEventListener("scroll", this.headbarScroll),
                this.SSO_URL = "//user.4m.cn"
            },
            beforeDestroy: function() {
                document.removeEventListener("scroll", this.headbarScroll)
            },
            methods: {
                headbarScroll: function() {
                    this.scrollTop = document.body.scrollTop + document.documentElement.scrollTop
                },
                goDemo: function() {
                    this.$cookies.set("statisticsDemo", 1),
                    this.$router.push("/report/overview")
                },
                doLogin: function() {
                    location.href = "".concat(this.SSO_URL, "/sso?source=dwz&redirect=").concat(encodeURIComponent(this.siteOrigin), "/panel")
                },
                doRegister: function() {
                    location.href = "".concat(this.SSO_URL, "/register?source=dwz&redirect=").concat(encodeURIComponent(this.siteOrigin), "/panel")
                },
                doUser: function(t) {
                    this[t]()
                },
                logout: function() {
                    var t = this;
                    return Object(o.a)(regeneratorRuntime.mark((function e() {
                        return regeneratorRuntime.wrap((function(e) {
                            for (; ; )
                                switch (e.prev = e.next) {
                                case 0:
                                    return e.next = 2,
                                    t.$store.dispatch("LOGOUT", (function() {
                                        location.href = "".concat("//user.4m.cn/", "/sso/logout?source=dwz&redirect=").concat(encodeURIComponent(window.location.href))
                                    }
                                    ));
                                case 2:
                                case "end":
                                    return e.stop()
                                }
                        }
                        ), e)
                    }
                    )))()
                }
            }
        }
          , c = (n(388),
        n(31))
          , component = Object(c.a)(r, (function() {
            var t = this
              , e = t.$createElement
              , o = t._self._c || e;
            return o("header", {
                staticClass: "header",
                class: t.isFixed
            }, [o("el-row", {
                staticClass: "h-fex",
                attrs: {
                    type: "flex",
                    justify: "space-between",
                    align: "middle"
                }
            }, [o("el-col", [o("ul", {
                staticClass: "nav-menu"
            }, [o("li", {
                staticClass: "item item-logo no-hover"
            }, [o("nuxt-link", {
                staticClass: "logo",
                attrs: {
                    to: "/",
                    tag: "a"
                }
            }, [o("i", {
                staticClass: "icon iconfont icon-logo"
            }), t._v(" "), o("span", {
                staticClass: "logo-txt"
            }, [t._v("短链分发平台")])])], 1), t._v(" "), o("li", {
                staticClass: "item"
            }, [o("nuxt-link", {
                attrs: {
                    to: "/"
                }
            }, [t._v("首页")])], 1), t._v(" "), o("li", {
                staticClass: "item"
            }, [o("nuxt-link", {
                attrs: {
                    to: "/case"
                }
            }, [t._v("成功案例")])], 1), t._v(" "), o("li", {
                staticClass: "item"
            }, [o("nuxt-link", {
                attrs: {
                    to: "/customize"
                }
            }, [t._v("企业定制")])], 1), t._v(" "), o("li", {
                staticClass: "item"
            }, [o("a", {
                attrs: {
                    href: "/doc/lian-xi-wo-men.html",
                    target: "_blank"
                }
            }, [t._v("关于我们")])]), t._v(" "), o("li", {
                staticClass: "item"
            }, [o("a", {
                attrs: {
                    href: "/doc",
                    target: "_blank"
                }
            }, [t._v("帮助文档")])])])]), t._v(" "), o("el-col", [o("div", {
                staticClass: "nav-top"
            }, [o("ul", {
                class: ["nav-info", {
                    "is-login": t.isLogin
                }]
            }, [o("li", {
                staticClass: "item hover"
            }, [o("el-popover", {
                attrs: {
                    trigger: "hover",
                    placement: "bottom"
                }
            }, [o("div", {
                staticClass: "wechat-qrcode-wrapper"
            }, [o("img", {
                staticClass: "img-qrcode",
                attrs: {
                    src: n(338)
                }
            }), t._v(" "), o("div", {
                staticClass: "guide-msg"
            }, [o("p", [t._v("① 关注公众号“我要啦统计”")]), t._v(" "), o("p", [t._v("② 点击底部菜单【缩短网址】")])])]), t._v(" "), o("a", {
                attrs: {
                    slot: "reference"
                },
                slot: "reference"
            }, [o("i", {
                staticClass: "el-icon-mobile-phone"
            }), t._v(" 手机版")])])], 1), t._v(" "), t.isLogin ? [o("li", {
                staticClass: "item btn-panel"
            }, [o("nuxt-link", {
                attrs: {
                    to: "/panel"
                }
            }, [t._v("短链列表")])], 1), t._v(" "), o("li", {
                staticClass: "item no-hover"
            }, [o("a", [o("el-dropdown", {
                attrs: {
                    trigger: "click",
                    size: "medium"
                },
                on: {
                    command: t.doUser
                }
            }, [o("span", {
                staticClass: "el-dropdown-link"
            }, [t._v(t._s(t.$store.state.user && t.$store.state.user.account))]), t._v(" "), o("el-dropdown-menu", {
                attrs: {
                    slot: "dropdown"
                },
                slot: "dropdown"
            }, [o("el-dropdown-item", {
                attrs: {
                    command: "logout"
                }
            }, [t._v("退出")])], 1)], 1)], 1)])] : [o("li", {
                staticClass: "item"
            }, [o("a", {
                attrs: {
                    href: "javascript:;"
                },
                on: {
                    click: t.goDemo
                }
            }, [t._v("产品演示")])]), t._v(" "), o("li", {
                staticClass: "item"
            }, [o("a", {
                attrs: {
                    href: "javascript:;"
                },
                on: {
                    click: t.doLogin
                }
            }, [t._v("登录")])]), t._v(" "), o("li", {
                staticClass: "item btn-register no-hover"
            }, [o("a", {
                attrs: {
                    href: "javascript:;"
                },
                on: {
                    click: t.doRegister
                }
            }, [t._v("注册")])])]], 2)])])], 1)], 1)
        }
        ), [], !1, null, "5453d3f4", null);
        e.a = component.exports
    },
    410: function(t, e, n) {
        "use strict";
        var o = {
            props: {
                isLogin: {
                    type: Boolean,
                    default: !1
                }
            },
            data: function() {
                return {}
            },
            methods: {
                doRegister: function() {
                    location.href = "//www.4m.cn/register?source=duanlian&redirect=".concat(this.siteOrigin, "/panel?source=duanlian")
                },
                goDemo: function() {
                    this.$cookies.set("statisticsDemo", 1),
                    this.$router.push("/report/overview")
                }
            }
        }
          , r = (n(392),
        n(31))
          , component = Object(r.a)(o, (function() {
            var t = this
              , e = t.$createElement
              , n = t._self._c || e;
            return n("div", {
                staticClass: "section section-try"
            }, [n("div", {
                staticClass: "inner-wrap"
            }, [n("div", {
                staticClass: "big-txt"
            }, [t._v("需要实时追踪渠道推广效果，清晰用户画像？")]), t._v(" "), t.isLogin ? [n("nuxt-link", {
                attrs: {
                    to: "/panel/uri-add"
                }
            }, [n("el-button", [t._v("创建短链")])], 1)] : [n("a", {
                attrs: {
                    href: "javascript:;"
                },
                on: {
                    click: t.doRegister
                }
            }, [n("el-button", [t._v("立即注册")])], 1), t._v(" "), n("a", {
                staticClass: "demo",
                attrs: {
                    href: "javascript:;"
                },
                on: {
                    click: t.goDemo
                }
            }, [t._v("查看演示")])]], 2)])
        }
        ), [], !1, null, "83f2c90c", null);
        e.a = component.exports
    },
    411: function(t, e, n) {
        "use strict";
        n(391);
        var o = n(31)
          , component = Object(o.a)({}, (function() {
            var t = this
              , e = t.$createElement
              , o = t._self._c || e;
            return o("footer", {
                staticClass: "footer"
            }, [o("el-row", {
                staticClass: "inner-wrap",
                attrs: {
                    type: "flex"
                }
            }, [o("el-col", {
                attrs: {
                    span: 4
                }
            }, [o("dl", [o("dt", {
                staticClass: "fs-md"
            }, [t._v("产品中心")]), t._v(" "), o("dd", [o("a", {
                attrs: {
                    href: "https://www.4m.cn",
                    target: "_blank"
                }
            }, [t._v("4M网站统计")])]), t._v(" "), o("dd", [o("a", {
                attrs: {
                    href: "https://kf.4m.cn",
                    target: "_blank"
                }
            }, [t._v("4M智能客服")])]), t._v(" "), o("dd", [o("a", {
                attrs: {
                    href: "https://mpa.4m.cn",
                    target: "_blank"
                }
            }, [t._v("4M小程序统计")])])])]), t._v(" "), o("el-col", {
                attrs: {
                    span: 4
                }
            }, [o("dl", [o("dt", {
                staticClass: "fs-md"
            }, [t._v("联系我们")]), t._v(" "), o("dd", [o("span", [t._v("QQ客服: 3008049513")])]), t._v(" "), o("dd", [o("span", [t._v("QQ群：790732703")])])])]), t._v(" "), o("el-col", {
                attrs: {
                    span: 4
                }
            }, [o("dl", [o("dt", {
                staticClass: "fs-md"
            }, [t._v("文档中心")]), t._v(" "), o("dd", [o("a", {
                attrs: {
                    href: "/doc",
                    target: "_blank"
                }
            }, [t._v("使用帮助")])]), t._v(" "), o("dd", [o("a", {
                attrs: {
                    href: "/doc/ru-he-zheng-que-shi-yong-duan-lian-ff1f.html",
                    target: "_blank"
                }
            }, [t._v("4M链接管理规范")])])])]), t._v(" "), o("el-col", {
                attrs: {
                    span: 4
                }
            }), t._v(" "), o("el-col", {
                attrs: {
                    span: 4
                }
            }, [o("dl", {
                staticClass: "float-right"
            }, [o("dt", {
                staticClass: "fs-md"
            }, [t._v("微信公众号")]), t._v(" "), o("dd", [o("img", {
                attrs: {
                    src: n(389),
                    alt: "微信公众号二维码",
                    width: "130",
                    height: "130"
                }
            })])])]), t._v(" "), o("el-col", {
                attrs: {
                    span: 4
                }
            }, [o("dl", {
                staticClass: "float-right"
            }, [o("dt", {
                staticClass: "fs-md"
            }, [t._v("微信客服")]), t._v(" "), o("dd", [o("img", {
                attrs: {
                    src: n(390),
                    alt: "微信客服二维码",
                    width: "130",
                    height: "130"
                }
            })])])])], 1), t._v(" "), t._m(0)], 1)
        }
        ), [function() {
            var t = this.$createElement
              , e = this._self._c || t;
            return e("div", {
                staticClass: "bottom inner-wrap"
            }, [e("p", [this._v("本平台禁止违法违规内容生成短链接，如有发现一律删除！")]), this._v(" "), e("p", [this._v("\n      Copyright © 2016-2019 4m.cn , All Rights Reserved |\n      "), e("a", {
                attrs: {
                    href: "http://www.miitbeian.gov.cn/",
                    target: "_blank",
                    rel: "noopener noreferrer"
                }
            }, [this._v("粤ICP备17055553号-1")]), this._v(" |\n      "), e("a", {
                attrs: {
                    href: "http://www.beian.gov.cn/portal/registerSystemInfo?recordcode=44010602004893",
                    target: "_blank",
                    rel: "noopener noreferrer"
                }
            }, [this._v("粤公网安备 44010602004893号")])])])
        }
        ], !1, null, "8f1f3046", null);
        e.a = component.exports
    },
    497: function(t, e, n) {},
    498: function(t, e, n) {},
    956: function(t, e, n) {
        t.exports = n.p + "img/82b33fa.png"
    },
    957: function(t, e, n) {
        t.exports = n.p + "img/6f92c0e.png"
    },
    958: function(t, e, n) {
        t.exports = n.p + "img/f5a6165.jpg"
    },
    959: function(t, e, n) {
        t.exports = n.p + "img/4535ab7.png"
    },
    960: function(t, e, n) {
        t.exports = n.p + "img/41c6088.png"
    },
    961: function(t, e, n) {
        t.exports = n.p + "img/e420bc5.png"
    },
    962: function(t, e, n) {
        t.exports = n.p + "img/355dc12.png"
    },
    963: function(t, e, n) {
        t.exports = n.p + "img/b620112.png"
    },
    964: function(t, e, n) {
        t.exports = n.p + "img/1291061.png"
    },
    965: function(t, e, n) {
        t.exports = n.p + "img/ef05e01.png"
    },
    966: function(t, e, n) {
        t.exports = n.p + "img/24b2418.png"
    },
    967: function(t, e, n) {
        t.exports = n.p + "img/d8139f8.png"
    },
    968: function(t, e, n) {
        t.exports = n.p + "img/72c3fae.png"
    },
    969: function(t, e, n) {
        t.exports = n.p + "img/93334e8.png"
    },
    970: function(t, e, n) {
        t.exports = n.p + "img/3ef5da9.png"
    },
    971: function(t, e, n) {
        t.exports = n.p + "img/c2f241e.png"
    },
    972: function(t, e, n) {
        t.exports = n.p + "img/df2ba7b.png"
    },
    973: function(t, e, n) {
        t.exports = n.p + "img/bd527c2.png"
    },
    974: function(t, e, n) {
        t.exports = n.p + "img/6c7fead.png"
    },
    975: function(t, e, n) {
        t.exports = n.p + "img/a5888a7.png"
    },
    976: function(t, e, n) {
        "use strict";
        var o = n(497);
        n.n(o).a
    },
    977: function(t, e, n) {
        "use strict";
        var o = n(498);
        n.n(o).a
    },
    997: function(t, e, n) {
        "use strict";
        n.r(e);
        var o = [function() {
            var t = this
              , e = t.$createElement
              , n = t._self._c || e;
            return n("div", {
                staticClass: "advantage"
            }, [n("div", {
                staticClass: "inner-wrap"
            }, [n("div", {
                staticClass: "box"
            }, [n("span", [n("i", [n("i", {
                staticClass: "icon iconfont icon-zhuanye1"
            })])]), t._v(" "), n("div", [n("p", {
                staticClass: "title"
            }, [t._v("专业")]), t._v(" "), n("p", {
                staticClass: "content"
            }, [t._v("15年数据分析经验，拥有卓越大数据处理、数据报表分析能力")])])]), t._v(" "), n("div", {
                staticClass: "box"
            }, [n("span", [n("i", [n("i", {
                staticClass: "icon iconfont icon-wending1"
            })])]), t._v(" "), n("div", [n("p", {
                staticClass: "title"
            }, [t._v("稳定")]), t._v(" "), n("p", {
                staticClass: "content"
            }, [t._v("企业级云数据服务器集群，秒速响应，保证链接持续稳定，安全运行")])])]), t._v(" "), n("div", {
                staticClass: "box"
            }, [n("span", [n("i", [n("i", {
                staticClass: "icon iconfont icon-yiyong1"
            })])]), t._v(" "), n("div", [n("p", {
                staticClass: "title"
            }, [t._v("易用")]), t._v(" "), n("p", {
                staticClass: "content"
            }, [t._v("傻瓜式一键操作，各个子链接数据报表可追踪、可回查")])])])])])
        }
        , function() {
            var t = this.$createElement
              , e = this._self._c || t;
            return e("div", {
                staticClass: "section section-ads"
            }, [e("div", {
                staticClass: "inner-wrap"
            }, [e("ul", {
                staticClass: "ad-list"
            }, [e("li", {
                staticClass: "ad-item"
            }, [e("a", {
                staticClass: "link",
                attrs: {
                    href: "http://t.6ltt.com/1boa",
                    target: "_blank"
                }
            }, [this._v("\n                短网址在网络推广中的应用场景\n              ")])]), this._v(" "), e("li", {
                staticClass: "ad-item"
            }, [e("a", {
                staticClass: "link",
                attrs: {
                    href: "http://t.6ltt.com/5coa",
                    target: "_blank"
                }
            }, [this._v("\n                短链数量免费提升  5种方式自行选择\n              ")])]), this._v(" "), e("li", {
                staticClass: "ad-item"
            }, [e("a", {
                staticClass: "link",
                attrs: {
                    href: "http://t.6ltt.com/2doa",
                    target: "_blank"
                }
            }, [this._v("\n                访问明细数据分析指南\n              ")])])])])])
        }
        , function() {
            var t = this.$createElement
              , e = this._self._c || t;
            return e("div", {
                staticClass: "section section-intro"
            }, [e("div", {
                staticClass: "inner-wrap"
            }, [e("div", {
                staticClass: "big-txt fs-xl"
            }, [this._v("专业短链接分发服务商")]), this._v(" "), e("p", {
                staticClass: "fs-md"
            }, [this._v("\n            4M短链分发平台，支持批量长链接转换短链接，可按城市、设备、时段、特定参数等条件无限次分发子链接，\n            "), e("br"), this._v("专业稳定，链接永不过期，免费查看各个子链接数据报表。应用4M统计大数据分析能力，智能过滤假量，实时分析渠道推广效果。\n          ")])])])
        }
        , function() {
            var t = this
              , e = t.$createElement
              , o = t._self._c || e;
            return o("div", {
                staticClass: "section section-report"
            }, [o("div", {
                staticClass: "inner-wrap"
            }, [o("div", {
                staticClass: "big-txt fs-xl"
            }, [t._v("专业的统计报表")]), t._v(" "), o("p", {
                staticClass: "fs-md"
            }, [t._v("报表显示访问数据，包含用户访问量、访问机型、地理分布、浏览器等数据一应俱全，让您精准分析用户")]), t._v(" "), o("div", {
                staticClass: "content"
            }, [o("div", {
                staticClass: "left-box"
            }, [o("dl", [o("dt", [t._v("全面的访问统计")]), t._v(" "), o("dd", [t._v("获得链接访问情况，国内精确到地级市，追踪每一个访问IP的详细信息，精准度高于任何网站")])]), t._v(" "), o("dl", [o("dt", [t._v("高精度设备识别追踪技术")]), t._v(" "), o("dd", [t._v("支持识别PC和移动端的访问情况，利用独有分辨技术识别任何设备，智能筛选有效流量，让统计更精确")])]), t._v(" "), o("dl", [o("dt", [t._v("多维度表报")]), t._v(" "), o("dd", [t._v("支持全局图表、客户端和访客详情等三种维度展现报表，从不同角度对数据进行观察分析")])])]), t._v(" "), o("div", {
                staticClass: "rigth-box"
            }, [o("img", {
                attrs: {
                    src: n(965)
                }
            })])])])])
        }
        , function() {
            var t = this
              , e = t.$createElement
              , o = t._self._c || e;
            return o("div", {
                staticClass: "section section-coo"
            }, [o("div", {
                staticClass: "inner-wrap"
            }, [o("div", {
                staticClass: "big-txt fs-xl"
            }, [t._v("合作伙伴")]), t._v(" "), o("ul", {
                staticClass: "img-list other-list"
            }, [o("li", {
                staticClass: "img-item"
            }, [o("img", {
                attrs: {
                    src: n(966),
                    alt: ""
                }
            })]), t._v(" "), o("li", {
                staticClass: "img-item"
            }, [o("img", {
                attrs: {
                    src: n(967),
                    alt: ""
                }
            })]), t._v(" "), o("li", {
                staticClass: "img-item"
            }, [o("img", {
                attrs: {
                    src: n(968),
                    alt: ""
                }
            })]), t._v(" "), o("li", {
                staticClass: "img-item"
            }, [o("img", {
                attrs: {
                    src: n(969),
                    alt: ""
                }
            })]), t._v(" "), o("li", {
                staticClass: "img-item"
            }, [o("img", {
                attrs: {
                    src: n(970),
                    alt: ""
                }
            })])]), t._v(" "), o("ul", {
                staticClass: "img-list"
            }, [o("li", {
                staticClass: "img-item"
            }, [o("img", {
                attrs: {
                    src: n(971),
                    alt: ""
                }
            })]), t._v(" "), o("li", {
                staticClass: "img-item"
            }, [o("img", {
                attrs: {
                    src: n(972),
                    alt: ""
                }
            })]), t._v(" "), o("li", {
                staticClass: "img-item"
            }, [o("img", {
                attrs: {
                    src: n(973),
                    alt: ""
                }
            })]), t._v(" "), o("li", {
                staticClass: "img-item"
            }, [o("img", {
                attrs: {
                    src: n(974),
                    alt: ""
                }
            })]), t._v(" "), o("li", {
                staticClass: "img-item"
            }, [o("img", {
                attrs: {
                    src: n(975),
                    alt: ""
                }
            })])])])])
        }
        ]
          , r = n(409)
          , c = n(411)
          , l = n(410)
          , v = {
            components: {
                HeadbarHome: r.a,
                FootbarHome: c.a,
                BottomOperate: l.a
            },
            data: function() {
                return {
                    scrollTop: 0,
                    isFixed: "",
                    input2: "",
                    tableData: [{
                        original: "",
                        uri: ""
                    }],
                    isAddUriNow: !1,
                    preset: "",
                    dialogVisible: !1,
                    anonymousUriNumber: 0,
                    sceneType: "message",
                    SSO_URL: ""
                }
            },
            computed: {
                isLogin: function() {
                    return !!this.$store.state.user
                },
                siteOrigin: function() {
                    var t = window.location;
                    return t.origin ? t.origin : t.protocol + "//" + t.hostname + (t.port ? ":" + t.port : "")
                }
            },
            mounted: function() {
                this.SSO_URL = "//user.4m.cn"
            },
            methods: {
                doCopy: function(content) {
                    var t = this;
                    this.$copyText(content).then((function(e) {
                        t.$message.success("已复制到粘贴板！")
                    }
                    ), (function(e) {
                        t.$message.error(e)
                    }
                    ))
                },
                goDemo: function() {
                    this.$cookies.set("statisticsDemo", 1),
                    this.$router.push("/report/overview")
                },
                doLogin: function() {
                    location.href = "".concat(this.SSO_URL, "/sso?source=dwz&redirect=").concat(encodeURIComponent(this.siteOrigin), "/panel")
                },
                doRegister: function() {
                    location.href = "".concat(this.SSO_URL, "/register?source=dwz&redirect=").concat(encodeURIComponent(this.siteOrigin), "/panel")
                },
                changeSceneType: function(t) {
                    this.sceneType = t
                },
                addUri: function() {
                    var t = this;
                    if (this.preset = this.$cookies.get("4M_URI_PRESET"),
                    this.anonymousUriNumber = this.$cookies.get("4M_URI_AnonymousUriNumber") || 0,
                    this.anonymousUriNumber >= 5)
                        this.dialogVisible = !0;
                    else {
                        if (this.isAddUriNow = !0,
                        !/^(http|https|ftp):\/\//.test(this.input2))
                            return this.$message.error("格式不正确"),
                            void (this.isAddUriNow = !1);
                        var e = {
                            url: this.input2
                        };
                        this.preset && (e.preset = this.preset),
                        this.$axios.post("/rule/visitorStore", e).then((function(e) {
                            1 === e.code ? (t.tableData[0].uri = e.data.url,
                            t.tableData[0].original = t.input2,
                            t.preset = e.data.preset,
                            t.input2 = "",
                            t.$cookies.set("4M_URI_PRESET", t.preset),
                            t.anonymousUriNumber++,
                            t.$cookies.set("4M_URI_AnonymousUriNumber", t.anonymousUriNumber)) : t.$message.error(e.msg),
                            t.isAddUriNow = !1
                        }
                        ))
                    }
                }
            }
        }
          , d = (n(976),
        n(977),
        n(31))
          , component = Object(d.a)(v, (function() {
            var t = this
              , e = t.$createElement
              , o = t._self._c || e;
            return o("div", {
                staticClass: "home"
            }, [o("HeadbarHome", {
                attrs: {
                    "is-login": t.isLogin
                }
            }), t._v(" "), o("div", {
                staticClass: "container"
            }, [o("div", {
                staticClass: "main"
            }, [o("div", {
                staticClass: "section jumbotron"
            }, [o("div", {
                staticClass: "huge-txt"
            }, [t._v("一键生成，永久有效")]), t._v(" "), o("p", {
                staticClass: "fs-md"
            }, [t._v("过滤假量，实时统计，微信防封")]), t._v(" "), t.isLogin ? [o("nuxt-link", {
                attrs: {
                    to: "/panel/uri-add"
                }
            }, [o("el-button", {
                staticClass: "fs-lg",
                attrs: {
                    type: "primary"
                }
            }, [t._v("创建短链")])], 1)] : [o("div", {
                staticClass: "addUri"
            }, [o("el-input", {
                attrs: {
                    placeholder: "粘贴需要缩短的网址"
                },
                model: {
                    value: t.input2,
                    callback: function(e) {
                        t.input2 = e
                    },
                    expression: "input2"
                }
            }, [o("el-button", {
                attrs: {
                    slot: "append",
                    disabled: t.isAddUriNow
                },
                on: {
                    click: t.addUri
                },
                slot: "append"
            }, [t._v("一键缩短")])], 1)], 1), t._v(" "), t.preset ? o("div", [o("el-table", {
                staticClass: "addTable",
                attrs: {
                    data: t.tableData
                }
            }, [o("el-table-column", {
                attrs: {
                    prop: "original",
                    label: "原链接",
                    align: "left"
                }
            }), t._v(" "), o("el-table-column", {
                attrs: {
                    prop: "uri",
                    label: "短链接",
                    align: "left"
                },
                scopedSlots: t._u([{
                    key: "default",
                    fn: function(e) {
                        return [o("a", {
                            attrs: {
                                href: e.row.uri,
                                target: "_blank"
                            }
                        }, [t._v(t._s(e.row.uri))]), t._v(" "), o("el-button", {
                            staticClass: "btn-copy",
                            attrs: {
                                size: "mini",
                                type: "primary"
                            },
                            on: {
                                click: function(n) {
                                    return t.doCopy(e.row.uri)
                                }
                            }
                        }, [t._v("\n                    复制\n                  ")])]
                    }
                }], null, !1, 1547292233)
            }), t._v(" "), o("el-table-column", {
                attrs: {
                    prop: "",
                    label: "有效期",
                    align: "center"
                }
            }, [o("span", {
                staticClass: "time"
            }, [t._v("30分钟")]), t._v("（\n                "), o("a", {
                attrs: {
                    href: "javascript:;"
                },
                on: {
                    click: t.doLogin
                }
            }, [t._v("登录")]), t._v(" 后可永久保存）\n              ")]), t._v(" "), o("el-table-column", {
                attrs: {
                    width: "100"
                }
            })], 1)], 1) : t._e()], t._v(" "), t._m(0)], 2), t._v(" "), t._m(1), t._v(" "), t._m(2), t._v(" "), o("div", {
                staticClass: "section section-feature"
            }, [o("div", {
                staticClass: "inner-wrap"
            }, [o("div", {
                staticClass: "card-list other-list"
            }, [o("el-card", {
                staticClass: "box-card"
            }, [o("p", {
                staticClass: "text"
            }, [t._v("支持http和https两种协议，一键缩短长链接，同时保留原链接")]), t._v(" "), o("img", {
                attrs: {
                    src: n(956)
                }
            })]), t._v(" "), o("el-card", {
                staticClass: "box-card"
            }, [o("p", {
                staticClass: "text"
            }, [t._v("可按城市、设备、时段、特定参数等条件无限次分发子链接，特定场景跳转链接，满足个性化推广需求")]), t._v(" "), o("img", {
                attrs: {
                    src: n(957)
                }
            })])], 1), t._v(" "), o("div", {
                staticClass: "card-list"
            }, [o("el-card", {
                staticClass: "box-card"
            }, [o("p", {
                staticClass: "text"
            }, [t._v("\n                免费查看各个子链接数据报表，自动同步生成访问量、设备型号、浏览器类型、地域分布、IP地址、用户行为轨迹等多维度数据报表，智能过滤假量，助你实时了解用户画像，用数据驱动业务增长\n              ")]), t._v(" "), o("img", {
                attrs: {
                    src: n(958)
                }
            })]), t._v(" "), o("el-card", {
                staticClass: "box-card"
            }, [o("p", {
                staticClass: "text"
            }, [t._v("对接百种营销工具，所有功能均开放API，支持接入CRM、ERP、二次开发")]), t._v(" "), o("img", {
                attrs: {
                    src: n(959)
                }
            })])], 1)])]), t._v(" "), o("div", {
                staticClass: "section section-scene"
            }, [o("div", {
                staticClass: "inner-wrap"
            }, [o("div", {
                staticClass: "big-txt fs-xl"
            }, [t._v("使用场景")]), t._v(" "), o("ul", {
                staticClass: "tabs"
            }, [o("li", {
                class: {
                    active: "message" === t.sceneType
                },
                on: {
                    click: function(e) {
                        return t.changeSceneType("message")
                    }
                }
            }, [t._v("短信推广")]), t._v(" "), o("li", {
                class: {
                    active: "mail" === t.sceneType
                },
                on: {
                    click: function(e) {
                        return t.changeSceneType("mail")
                    }
                }
            }, [t._v("邮件推广")]), t._v(" "), o("li", {
                class: {
                    active: "socialContact" === t.sceneType
                },
                on: {
                    click: function(e) {
                        return t.changeSceneType("socialContact")
                    }
                }
            }, [t._v("\n              社交推广\n            ")]), t._v(" "), o("li", {
                class: {
                    active: "app" === t.sceneType
                },
                on: {
                    click: function(e) {
                        return t.changeSceneType("app")
                    }
                }
            }, [t._v("APP推广")]), t._v(" "), o("li", {
                class: {
                    active: "activity" === t.sceneType
                },
                on: {
                    click: function(e) {
                        return t.changeSceneType("activity")
                    }
                }
            }, [t._v("\n              活动推广\n            ")])]), t._v(" "), o("div", {
                staticClass: "content"
            }, ["message" === t.sceneType ? o("div", [o("p", {
                staticClass: "title"
            }, [t._v("通过链接点击量，反推文案效果，实现低成本推广")]), t._v(" "), o("img", {
                attrs: {
                    src: n(960)
                }
            })]) : t._e(), t._v(" "), "mail" === t.sceneType ? o("div", [o("p", {
                staticClass: "title"
            }, [t._v("追踪邮件推广效果，降低营销成本")]), t._v(" "), o("img", {
                attrs: {
                    src: n(961)
                }
            })]) : t._e(), t._v(" "), "socialContact" === t.sceneType ? o("div", [o("p", {
                staticClass: "title"
            }, [t._v("内容传播更高效、便捷，迅速提升曝光量")]), t._v(" "), o("img", {
                attrs: {
                    src: n(962)
                }
            })]) : t._e(), t._v(" "), "app" === t.sceneType ? o("div", [o("p", {
                staticClass: "title"
            }, [t._v("根据不同设备指向不同长链接，实现精准推广")]), t._v(" "), o("img", {
                attrs: {
                    src: n(963)
                }
            })]) : t._e(), t._v(" "), "activity" === t.sceneType ? o("div", [o("p", {
                staticClass: "title"
            }, [t._v("在一个短链下添加多个页面方案，设置权重控制跳转，找出最佳营销方案")]), t._v(" "), o("img", {
                attrs: {
                    src: n(964)
                }
            })]) : t._e()])])]), t._v(" "), t._m(3), t._v(" "), t._m(4), t._v(" "), o("bottom-operate", {
                attrs: {
                    isLogin: t.isLogin
                }
            })], 1)]), t._v(" "), o("el-dialog", {
                attrs: {
                    visible: t.dialogVisible,
                    title: "提示",
                    width: "30%"
                },
                on: {
                    "update:visible": function(e) {
                        t.dialogVisible = e
                    }
                }
            }, [o("span", [t._v("匿名用户最多创建5个短链")]), t._v(" "), o("span", {
                staticClass: "dialog-footer",
                attrs: {
                    slot: "footer"
                },
                slot: "footer"
            }, [o("el-button", {
                attrs: {
                    type: "primary"
                },
                on: {
                    click: function(e) {
                        t.dialogVisible = !1
                    }
                }
            }, [t._v("确 定")])], 1)]), t._v(" "), o("FootbarHome")], 1)
        }
        ), o, !1, null, "35f6fbf4", null);
        e.default = component.exports
    }
}]);
