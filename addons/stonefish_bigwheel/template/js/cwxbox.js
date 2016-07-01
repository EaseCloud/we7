//定义窗体对象 
var cwxbox = {};

cwxbox.box = function () {
    var bg, wd, cn, ow, oh, o = true, time = null;
    return {
        show: function (c, rs) {
            if (o) {
                if (rs) {
                    switch (rs) {
                        case true: bg = document.getElementById("su_cwxBg"); wd = document.getElementById("su_cwxWd"); cn = document.getElementById("su_cwxCn"); break;
                        case "1": bg = document.getElementById("text_cwxBg1"); wd = document.getElementById("text_cwxWd1"); cn = document.getElementById("text_cwxCn1"); break;
                        case "2": bg = document.getElementById("text_cwxBg2"); wd = document.getElementById("text_cwxWd2"); cn = document.getElementById("text_cwxCn2"); break;
                        default: ; break;
                    }
                    document.body.appendChild(bg);
                    document.body.appendChild(wd);
                    wd.appendChild(cn);
                    bg.onclick = cwxbox.box.hide;
                    //bg.onclick = function () { cwxbox.box.hide(rs); } ();

                } else {
                    bg = document.getElementById("cwxBg"); //document.createElement('div'); bg.id = 'cwxBg';
                    wd = document.getElementById("cwxWd"); //document.createElement('div'); wd.id = 'cwxWd';
                    cn = document.getElementById("cwxCn"); //document.createElement('div'); cn.id = 'cwxCn';
                    document.body.appendChild(bg);
                    document.body.appendChild(wd);
                    wd.appendChild(cn);
                    if (bg.onclick == null || bg.onclick == undefined || bg.onclick == "") bg.onclick = cwxbox.box.hide;
                    //bg.onclick = cwxbox.box.hide;
                }
                window.onresize = this.init;
                window.onscroll = this.scrolls;
            }
            cn.innerHTML = c;
            oh = this.getCss(wd, 'offsetHeight');
            ow = this.getCss(wd, 'offsetWidth');
            this.init();
            this.alpha(bg, 50, 1);
            this.drag(wd);
        },
        hide: function (ra) {
            cwxbox.box.alpha(wd, 0, -1);
            cwxbox.box.alpha(bg, 0, -1);
            clearTimeout(time);
            if (ra == 1) {
                this.alpha(bg, 0, -1);
                cwxbox.box.show(document.getElementById("text_cwxCn1").innerHTML, "1");
            } else if (ra == 2) {
                this.alpha(bg, 0, -1);
                cwxbox.box.show(document.getElementById("text_cwxCn2").innerHTML, "2");
            }
        },
        init: function () {
            bg.style.height = cwxbox.page.total(1) + 'px';
            bg.style.width = '';
            bg.style.width = cwxbox.page.total(0) + 'px';
            var h = (cwxbox.page.height() - oh) / 2;
            wd.style.top = (h + cwxbox.page.top()) + 'px';
            wd.style.left = (cwxbox.page.width() - ow) / 2 + 'px';
        },
        scrolls: function () {
            var h = (cwxbox.page.height() - oh) / 2;
            wd.style.top = (h + cwxbox.page.top()) + 'px';
        },
        alpha: function (e, a, d) {
            clearInterval(e.ai);
            if (d == 1) {
                e.style.opacity = 0;
                e.style.filter = 'alpha(opacity=0)';
                e.style.display = 'block';
            }
            e.ai = setInterval(function () { cwxbox.box.ta(e, a, d) }, 0);
        },
        ta: function (e, a, d) {
            var anum = Math.round(e.style.opacity * 100);
            if (anum == a) {
                clearInterval(e.ai);
                if (d == -1) {
                    e.style.display = 'none';
                    if (e == wd) {
                        this.alpha(bg, 0, -1);
                    }
                } else {
                    if (e == bg) {
                        this.alpha(wd, 100, 1);
                    }
                }
            } else {
                var n = Math.ceil((anum + ((a - anum) * .5)));
                n = n == 1 ? 0 : n;
                e.style.opacity = n / 100;
                e.style.filter = 'alpha(opacity=' + n + ')';
            }
        },
        getCss: function (e, n) {
            var e_style = e.currentStyle ? e.currentStyle : window.getComputedStyle(e, null);
            if (e_style.display === 'none') {
                var clonDom = e.cloneNode(true);
                clonDom.style.cssText = 'position:absolute; display:block; top:-3000px;';
                document.body.appendChild(clonDom);
                var wh = clonDom[n];
                clonDom.parentNode.removeChild(clonDom);
                return wh;
            }
            return e[n];
        },
        drag: function (e) {
            var startX, startY, mouse;
            mouse = {
                mouseup: function () {
                    if (e.releaseCapture) {
                        e.onmousemove = null;
                        e.onmouseup = null;
                        e.releaseCapture();
                    } else {
                        document.removeEventListener("mousemove", mouse.mousemove, true);
                        document.removeEventListener("mouseup", mouse.mouseup, true);
                    }
                },
                mousemove: function (ev) {
                    var oEvent = ev || event;
                    e.style.left = oEvent.clientX - startX + "px";
                    e.style.top = oEvent.clientY - startY + "px";
                }
            }
            e.onmousedown = function (ev) {
                var oEvent = ev || event;
                startX = oEvent.clientX - this.offsetLeft;
                startY = oEvent.clientY - this.offsetTop;
                if (e.setCapture) {
                    e.onmousemove = mouse.mousemove;
                    e.onmouseup = mouse.mouseup;
                    e.setCapture();
                } else {
                    document.addEventListener("mousemove", mouse.mousemove, true);
                    document.addEventListener("mouseup", mouse.mouseup, true);
                }
            }

        }
    }
} ()

cwxbox.page = function () {
    return {
        top: function () { return document.documentElement.scrollTop || document.body.scrollTop },
        width: function () { return self.innerWidth || document.documentElement.clientWidth || document.body.clientWidth },
        height: function () { return self.innerHeight || document.documentElement.clientHeight || document.body.clientHeight },
        total: function (d) {
            var b = document.body, e = document.documentElement;
            return d ? Math.max(Math.max(b.scrollHeight, e.scrollHeight), Math.max(b.clientHeight, e.clientHeight)) :
Math.max(Math.max(b.scrollWidth, e.scrollWidth), Math.max(b.clientWidth, e.clientWidth))
        }
    }
} ()