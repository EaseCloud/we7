"use strict";
var appUtils = {};
appUtils.preset = {
    errorModal: {
        content: {html: "糟糕，网络不给力"}, confirm: {
            html: "重新进入", click: function () {
                window.location.reload()
            }
        }, cancel: "remove"
    }
},

    appUtils.modal = function () {
    function n() {
        o && o.remove(), o = $($("#apps-modal-tpl").html()), c = {};
        for (var n in a)c[n] = o.find(".js-apps-modal-" + n)
    }

    function t(t) {
        n();
        var e, o, i, a;
        for (e in t)if ("string" != typeof t[e])for (o in t[e])i = c[e][o], $.isFunction(i) && i.call(c[e], t[e][o]); else a = t[e], "remove" == a && c[e].remove()
    }

    function e(n) {
        return $.extend(!0, {}, a, n)
    }

    var o, c, i = {
        open: function (n) {
            t(e(n)), $(document.body).append(o)
        }, close: function (n) {
            n === !0 ? o.find(".apps-modal").remove() : o.remove()
        }
    }, a = {content: {html: ""}, confirm: {html: "确定", click: $.noop}, cancel: {html: "取消", click: i.close}};
    return i
}(),

    appUtils.process = function () {
    function n(n) {
        a.cancel.html = n, l.cancel.html = n
    }

    function t() {
        return 0 !== c ? 10999 == c ? (i.open(a), !1) : 10998 == c ? (i.open(l), !1) : (i.open(r), !1) : 0 !== o.costPoint && void 0 != o.costPoint ? (i.open(s), !1) : !0
    }

    var e, o = _apps_global, c = o.errorCode, i = appUtils.modal, a = {
        content: {html: o.errorMsg},
        confirm: {
            html: "登录", click: function () {
                location.href = o.login
            }
        },
        cancel: {
            html: "取消抽奖", click: function () {
                i.close()
            }
        }
    }, l = {
        content: {html: o.errorMsg}, confirm: {
            html: "关注", click: function () {
                window.showGuide && window.showGuide("follow")
            }
        }, cancel: {
            html: "取消抽奖", click: function () {
                i.close()
            }
        }
    }, s = {
        content: {html: '每次抽奖将消耗<span class="important"> ' + o.costPoint + "积分</span>"},
        confirm: {
            html: "赌一把", click: function () {
                i.close(), e.onconfirm && e.onconfirm()
            }
        },
        cancel: {
            html: "舍不得", click: function () {
                i.close()
            }
        }
    }, r = {
        content: {html: o.errorMsg}, confirm: {
            html: "知道了", click: function () {
                i.close()
            }
        }, cancel: "remove"
    };
    return e = {check: t, setCancelText: n, onconfirm: $.noop}
}(),

    appUtils.atLeast = function (n, t) {
    function e() {
        i.resolve.apply(null, arguments)
    }

    var o, c = !1, i = {};
    return setTimeout(function () {
        c ? t.apply(null, o) : i.resolve = function () {
            t.apply(null, arguments)
        }
    }, n), i.resolve = function () {
        o = arguments, c = !0
    }, {resolve: e}
},

    appUtils.randInt = function (n, t) {
    var e = n + Math.random() * (t - n);
    return parseInt(e)
},

    appUtils.format = function (n) {
    var t = Array.prototype.slice.call(arguments, 1);
    return n.replace(/{(\d+)}/g, function (n, e) {
        return "undefined" != typeof t[e] ? t[e] : n
    })
},

    appUtils.getUrlParam = function (n, t) {
    var e = new RegExp("(^|&)" + n + "=([^&]*)(&|$)"), o = "router" === t ? window.location.href : window.location.search, c = o.substr(1).match(e);
    return null !== c ? window.unescape(c[2]) : null
},



    require([], function () {
    function n(n, t) {
        var e, c, i, a;
        i = 5 > n ? ~~(n / 5) : 3 * (~~((n + 1) / 3) - 1), u.each(function (e) {
            var o = ~~i + e;
            $(this).html(o), o == n && (c = e, $(this).addClass("apps-checkin-day-at")), o == t && (a = e)
        }), 0 === i && $(".apps-checkin-progress").addClass("apps-checkin-progress-fromzero"), 0 === c ? (e = 10, f.width(e + 2)) : (e = u.eq(c).position().left + 24, f.width(e)), h.css({left: e}), void 0 === a || a >= 5 || m.prizeAt - m.checkinDay <= 0 ? $(".apps-checkin-prize-wrap").hide() : o(u.eq(a).position().left + 22), $(".apps-checkin-center-content").css({visibility: "visible"})
    }

    function t(n) {
        $(".apps-checkin-center-info-days").html(n)
    }

    function e(n) {
        $(".js-points").html(n)
    }

    function o(n) {
        var t = m.prizeAt - m.checkinDay, e = $(".apps-checkin-prize-wrap");
        $(".js-prize-need").html(t), void 0 !== n && e.css({left: n - e.width() / 2})
    }

    function c() {
        $(".apps-checkin-prize-wrap").css({opacity: 0})
    }

    function i() {
        h.addClass("animate-man-move");
        var n = $(".apps-checkin-day-at"), t = n.next().position().left - n.position().left;
        n.removeClass("apps-checkin-day-at"), f.width("+=" + t), setTimeout(function () {
            n.next().addClass("apps-checkin-day-at"), +m.checkinDay == m.prizeAt ? c() : o()
        }, m.manMoveDuration)
    }

    function a(n, t) {
        function o() {
            var n = $(".apps-checkin-userinfo-points"), e = n.offset();
            e.left += n.width() + 5;
            var o = $('<div class="animate-floating apps-checkin-info">+' + t + "</div>").offset(e).appendTo(document.body);
            setTimeout(function () {
                o.remove()
            }, m.manMoveDuration)
        }

        0 != t && o(), e(n + t)
    }

    function l() {
        d.attr("disabled", !0).html("已签到")
    }

    $(".js-checkin").html("签 到"), setTimeout(function () {

        $(window).scrollTop(80)
    }), function () {
        var n = $("body").width();
        n = Math.min(380, n), n = Math.max(320, n), $(".apps-checkin-center-content").width(n)
    }(), setTimeout(function () {
        $(window).scrollTop(100)
    });


    var s = window._apps_global, r = {
        content: {html: "签到成功"},
        confirm: {html: "知道了", click: appUtils.modal.close},
        cancel: "remove"
    }, p = {
        content: {html: "签到失败"},
        cancel: "remove",
        confirm: {html: "知道了", click: appUtils.modal.close}
    }, u = $(".apps-checkin-day"), h = $(".apps-checkin-man"), f = $(".apps-checkin-progress-filled-wrap"), d = $(".js-checkin"), m = {
        checkinDay: ~~s.total_continuous || 0,
        prizeAt: ~~s.total_continuous + ~~s.next_prize_days_length || 0,
        point: ~~s.user.points || 0,
        additionPoint: 0,
        hasChecked: function () {
            return void 0 === s.is_valid_checkin ? 0 : 1 != s.is_valid_checkin
        }(),
        manMoveDuration: 1e3
    };

    t(m.checkinDay), n(m.checkinDay, m.prizeAt), e(m.point), m.hasChecked && l(), window.__title = s.shared_text || "来签个到吧！";


    var v = {
        init: function () {
            function n(n) {
                var t = "签到成功！<br>", e = _global.url.wap + "/showcase/usercenter?kdt_id=" + _global.kdt_id;
                return void 0 == n.coupon && (n.coupon = []), void 0 == n.present && (n.present = []), n.points > 0 || n.present.length > 0 || n.coupon.length > 0 ? (t += '<div class="prize-list">', t += "本次签到，您获得了以下奖励：<br>", t += '<div class="prize-list-content">', n.points > 0 && (t += n.points + " 积分奖励<br>"), n.present.length > 0 && (t += n.present.map(function (n) {
                    return '"' + n.present_title + '" 一份<br>'
                })), n.coupon.length > 0 && (t += n.coupon.map(function (n) {
                    return '"' + n.title + '" 一份<br>'
                })), 1 == n.coupon.length && 0 == n.present.length ? e = n.coupon[0].url : 1 == n.present.length && 0 == n.coupon.length && (e = n.present[0].present_url), n.coupon.length > 0 && n.present.length > 0 && (t += '<a href="' + e + '">点这里查看</a>'), t += "</div>") : t
            }

            d.click(function () {
                mon_sign();
            })
        }
    };


    appUtils.process.setCancelText("取消"), appUtils.process.check() ? v.init() : $(".js-checkin").click(function () {


        return appUtils.process.check(), !1
    })


});