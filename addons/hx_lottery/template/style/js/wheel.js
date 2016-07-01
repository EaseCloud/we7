"use strict";
var appUtils = {};
appUtils.preset = {
    errorModal: {
        content: {
            html: "糟糕，网络不给力"
        },
        confirm: {
            html: "重新进入",
            click: function() {
                window.location.reload()
            }
        },
        cancel: "remove"
    }
},
appUtils.modal = function() {
    function n() {
        e && e.remove(),
        e = $($("#apps-modal-tpl").html()),
        i = {};
        for (var n in c) i[n] = e.find(".js-apps-modal-" + n)
    }
    function t(t) {
        n();
        var o, e, a, c;
        for (o in t) if ("string" != typeof t[o]) for (e in t[o]) a = i[o][e],
        $.isFunction(a) && a.call(i[o], t[o][e]);
        else c = t[o],
        "remove" == c && i[o].remove()
    }
    function o(n) {
        return $.extend(!0, {},
        c, n)
    }
    var e, i, a = {
        open: function(n) {
            t(o(n)),
            $(document.body).append(e)
        },
        close: function(n) {
            n === !0 ? e.find(".apps-modal").remove() : e.remove()
        }
    },
    c = {
        content: {
            html: ""
        },
        confirm: {
            html: "确定",
            click: $.noop
        },
        cancel: {
            html: "取消",
            click: a.close
        }
    };
    return a
} (),
appUtils.process = function() {
    function n(n) {
        c.cancel.html = n,
        l.cancel.html = n
    }
    function t() {
        return 0 !== i ? 10999 == i ? (a.open(c), !1) : 10998 == i ? (a.open(l), !1) : (a.open(s), !1) : 0 !== e.costPoint && void 0 != e.costPoint ? (a.open(r), !1) : !0
    }
    var o, e = _apps_global,
    i = e.errorCode,
    a = appUtils.modal,
    c = {
        content: {
            html: e.errorMsg
        },
        confirm: {
            html: "关注平台",
            click: function() {
                location.href = e.subscribe
            }
        },
        cancel: {
            html: "取消抽奖",
            click: function() {
                a.close()
            }
        }
    },
    l = {
        content: {
            html: e.errorMsg
        },
        confirm: {
            html: "关注",
            click: function() {
                window.showGuide && window.showGuide("follow")
            }
        },
        cancel: {
            html: "取消抽奖",
            click: function() {
                a.close()
            }
        }
    },
    r = {
        content: {
            html: '每次抽奖将消耗<span class="important"> ' + e.costName + ':' + e.costPoint +  "</span>"
        },
        confirm: {
            html: "赌一把",
            click: function() {
                a.close(),
                o.onconfirm && o.onconfirm()
            }
        },
        cancel: {
            html: "舍不得",
            click: function() {
                a.close()
            }
        }
    },
    s = {
        content: {
            html: e.errorMsg
        },
        confirm: {
            html: "知道了",
            click: function() {
                a.close()
            }
        },
        cancel: "remove"
    };
    return o = {
        check: t,
        setCancelText: n,
        onconfirm: $.noop
    }
} (),
appUtils.atLeast = function(n, t) {
    function o() {
        a.resolve.apply(null, arguments)
    }
    var e, i = !1,
    a = {};
    return setTimeout(function() {
        i ? t.apply(null, e) : a.resolve = function() {
            t.apply(null, arguments)
        }
    },
    n),
    a.resolve = function() {
        e = arguments,
        i = !0
    },
    {
        resolve: o
    }
},
appUtils.randInt = function(n, t) {
    var o = n + Math.random() * (t - n);
    return parseInt(o)
},
appUtils.format = function(n) {
    var t = Array.prototype.slice.call(arguments, 1);
    return n.replace(/{(\d+)}/g,
    function(n, o) {
        return "undefined" != typeof t[o] ? t[o] : n
    })
},
appUtils.getUrlParam = function(n, t) {
    var o = new RegExp("(^|&)" + n + "=([^&]*)(&|$)"),
    e = "router" === t ? window.location.href: window.location.search,
    i = e.substr(1).match(o);
    return null !== i ? window.unescape(i[2]) : null
},
function() {
    function n(n) {
        var t = Object.create(l);
        t.content = {
            html: n
        },
        c.open(t)
    }
    function t() {
        return $.ajax({
            url: i.logout,
            data: {
                id: i.id,
            },
            cache: !1,
            type: "post",
            dataType: "json",
            timeout: 5e3
        })
    }
    function o() {
        s.clear(),
        c.open(appUtils.preset.errorModal)
    }
    function e() {
        $(".js-start-btn").click(p),
        a.init = function() {}
    }
    var i = _apps_global,
    a = {},
    c = appUtils.modal,
    l = {
        content: {
            html: ""
        },
        confirm: {
            html: "继续抽奖",
            click: function() {
                window.location.reload()
            }
        },
        cancel: "remove"
    },
    r = {
        content: {
            html: ""
        },
        confirm: {
            html: "继续抽奖",
            click: function() {
                window.location.reload()
            }
        },
        cancel: "remove"
    },
    s = function() {
        function n() {
            var n, t, o, e, a = i.prize;
            for (n in a) t = a[n],
            o = $(".prize" + n).find(".wheel-icon"),
            e = t.image_url,
            e && e.length > 0 ? h(o, e) : t.point > 0 ? o.addClass("point-icon") : 0 === t.point && o.addClass("coupon-icon")
        }
        function t() {
            u = 0,
            d = !0,
            v = 100,
            f = function() {},
            l()
        }
        function o() {
            m.get(u).removeClass("active"),
            u++,
            u >= m.length && (u = 0),
            m.get(u).addClass("active"),
            p = setTimeout(o, v),
            f()
        }
        function e() {
            d = !1,
            clearTimeout(p)
        }
        function a() {
            d || (t(), o())
        }
        function c(n, t) {
            var o = 2 * m.length + (n - u);
            o > 2 * m.length && (o -= m.length),
            f = function() {
                v *= 1.08,
                o--,
                0 === o && (e(), t && setTimeout(t, 1400), m.getSelected().addClass("pulse"))
            }
        }
        function l() {
            $(".wheel-block").removeClass("active")
        }
        function r() {
            e(),
            l()
        }
        function s(n, t) {
            c(m.selectByCategory(n), t)
        }
        var u, p, f, m, d = !1,
        v = 100;
        m = function() {
            var n, t, o = [];
            return n = {
                set: function(t, e) {
                    o[t] = e,
                    n.length = o.length
                },
                get: function(n) {
                    return o[n]
                },
                selectByCategory: function(n) {
                    var e = o.filter(function(t) {
                        return t.hasClass(n)
                    }),
                    i = appUtils.randInt(0, e.length);
                    return t = e[i].data("index")
                },
                getSelected: function() {
                    return o[t]
                },
                length: 0
            },
            n.splice = [].splice,
            n.constructor = Array,
            n
        } ();
        var h = function() {
            var n = {},
            t = function(n, t) {
                n.addClass("animated tada"),
                setTimeout(function() {
                    n.addClass("custom-icon").removeClass("animated tada").css("backgroundImage", "url(" + t + ")")
                },
                1200)
            };
            return function(o, e) {
                var i = function() {
                    t(o, e)
                };
                if (n[e]) i();
                else {
                    n[e] = !0;
                    var a = new Image;
                    a.onload = i,
                    a.src = e
                }
            }
        } ();
        return $(".wheel-block").each(function() {
            var n = $(this),
            t = n.data("index");
            n.addClass("animated"),
            m.set(t, n)
        }),
        n(),
        {
            start: a,
            clear: r,
            to: s
        }
    } (),
    u = function() {
        var t = '<span class="important">哇！抽中 {0}！</span>',
        o = "" === i.failedInfo ? "哎呀，真可惜擦身而过!": i.failedInfo,
        e = '<br>手气这么好，再送您 <span class="important">{0}:{1}</span>',
        a = "运气不错，您还可以再玩一次",
        l = '<br>送您 <span class="important">{0}:{1}</span>';
        return function(u) {
            var p = "";
            if (0 === u.code) {
                var f = u.data,
                m = "";
                void 0 != f.point && (m = f.point_name + ":" + f.point),
                void 0 != f.title && (m = f.value + "张" + f.type + f.remsg),
                p = appUtils.format(t, m),
                void 0 != f.give_point && 0 != f.give_point && (p += appUtils.format(e, f.give_name,f.give_point)),
                s.to("prize" + f.level,
                function() {
                    r.content.html = p,
                    f.detail_url && f.detail_url.length > 0 && (r.cancel = {
                        html: "查看奖品",
                        click: function() {
                            window.location.href = f.detail_url
                        }
                    },
                    $(".js-view-prize").attr("href", f.detail_url)),
                    c.open(r)
                })
            } else 2 == i.lotteryAgain ? (p = u.msg || a, s.to("prize-again",
            function() {
                n(p)
            })) : (p = o, s.to("prize-no",
            function() {
                n(p)
            })),
            void 0 != i.givePoint && 0 != i.givePoint && (p += appUtils.format(l, i.giveName,i.givePoint))
        }
    } (),
    p = function() {
        var n = !1;
        return function() {
            if (!n) {
                n = !0,
                s.start();
                var e = appUtils.atLeast(1500, u),
                i = appUtils.atLeast(1500, o);
                t().done(e.resolve).fail(i.resolve)
            }
        }
    } ();
    a.init = e,
    window.gameIns = a
} ();