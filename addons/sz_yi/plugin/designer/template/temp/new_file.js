define(["angular.sanitize", "jquery.ui", "underscore", "fileUploader", "json2", "datetimepicker"],
function(a, b, c, d) {
    a.module("app", ["ngSanitize"]).controller("commonCtrl", ["$scope", "$sanitize",
    function(d, e) {
        var f = [{
            id: "header",
            name: "微页面标题",
            issystem: !0,
            params: {
                title: "微页面标题",
                description: "",
                thumb: "",
                bgColor: ""
            }
        },
        {
            id: "UCheader",
            name: "会员主页",
            issystem: !0,
            params: {
                title: "会员主页",
                cover: "",
                bgImage: ""
            }
        },
        {
            id: "richText",
            name: "富文本",
            params: {
                bgColor: "",
                content: ""
            }
        },
        {
            id: "adImg",
            name: "幻灯片",
            params: {
                listStyle: 1,
                sizeType: 1,
                items: []
            }
        },
        {
            id: "cube",
            name: "图片魔方",
            params: {
                layout: {},
                showIndex: 0,
                selection: {},
                currentPos: {},
                currentLayout: {
                    isempty: !0
                }
            }
        },
        {
            id: "title",
            name: "标题",
            params: {
                title: "",
                template: 1,
                tradition: {
                    subtitle: "",
                    align: "left",
                    bgcolor: "",
                    nav: {
                        title: "",
                        url: "",
                        enable: 0
                    }
                },
                news: {
                    date: "",
                    author: "",
                    title: "",
                    urlType: 1,
                    url: ""
                }
            }
        },
        {
            id: "textNav",
            name: "文本导航",
            params: {
                items: []
            }
        },
        {
            id: "navImg",
            name: "图片导航",
            params: {
                items: [{
                    imgurl: "",
                    title: "",
                    url: ""
                },
                {
                    imgurl: "",
                    title: "",
                    url: ""
                },
                {
                    imgurl: "",
                    title: "",
                    url: ""
                },
                {
                    imgurl: "",
                    title: "",
                    url: ""
                }]
            }
        },
        {
            id: "link",
            name: "关联链接",
            params: {
                items: []
            }
        },
        {
            id: "line",
            name: "辅助线",
            params: {}
        },
        {
            id: "white",
            name: "辅助空白",
            params: {
                height: 1
            }
        },
        {
            id: "audio",
            name: "语音",
            params: {
                style: "1",
                headimg: "",
                align: "left",
                title: "",
                isloop: !1,
                reload: "false",
                audio: {
                    id: "",
                    url: ""
                }
            }
        },
        {
            id: "notice",
            name: "公告",
            params: {
                notice: ""
            }
        }];
        d.modules = [],
        d.editors = [],
        d.activeModules = window.activeModules ? window.activeModules: [],
        d.activeItem = {},
        d.activeIndex = 0,
        d.index = window.activeModules ? window.activeModules.length: 0,
        d.submit = {
            params: {},
            html: ""
        },
        d.addItem = function(e) {
            a.forEach(d.modules,
            function(f, g) {
                if (f.id == e) {
                    for ( - 1 == b.inArray(e, d.editors) && d.editors.push(f.id); 0 != b("#module-" + d.index).size();) d.index++;
                    return d.activeModules.push({
                        id: f.id,
                        name: f.name,
                        params: a.copy(f.params),
                        issystem: f.issystem ? 1 : 0,
                        index: d.index,
                        displayorder: f.displayorder ? f.displayorder: d.activeModules.length
                    }),
                    d.activeIndex = c.findIndex(d.activeModules, {
                        index: parseInt(d.index)
                    }),
                    d.activeItem = d.activeModules[d.activeIndex],
                    d.triggerActiveItem(d.activeIndex),
                    void d.index++
                }
            }),
            b(".modules").sortable({
                update: function(a, b) {
                    d.updateSort(a, b)
                },
                items: ".js-sorttable"
            })
        },
        d.triggerActiveItem = function(a) {
            if (b("#module-" + d.activeModules[a].index).size() && b("#editor" + d.activeModules[a].id).size()) {
                clearTimeout(d.timer);
                var c = b("#module-" + d.activeModules[a].index).offset().top - 200;
                b("#editor" + d.activeModules[a].id).css("marginTop", c),
                b("html,body").animate({
                    scrollTop: c
                },
                500)
            } else d.timer = setTimeout(function() {
                d.triggerActiveItem(d.activeIndex)
            },
            50)
        },
        d.editItem = function(a) {
            var a = c.findIndex(d.activeModules, {
                index: parseInt(a)
            });
            a > -1 && (d.activeIndex = a, d.activeItem = d.activeModules[a]),
            -1 == b.inArray(d.activeItem.id, d.editors) && d.editors.push(d.activeItem.id),
            d.triggerActiveItem(a)
        },
        d.deleteItem = function(a) {
            if (confirm("删除后需要重新提交才会生效，确认吗？")) {
                var a = c.findIndex(d.activeModules, {
                    index: parseInt(a)
                });
                b("#module-" + a).prev().attr("index");
                d.activeModules = c.without(d.activeModules, d.activeModules[a]),
                d.activeItem = {}
            }
        },
        d.submit = function(e) {
            function f(a, b) {
                var c = "&lt;?php  $" + a + " = modulefunc('widget', 'site_widget_" + a + "', array(	'func' => 'site_widget_" + a + "',	'params' => '" + JSON.stringify(b) + "',	'uniacid' => '" + window.sysinfo.uniacid + "',	'acid' => '" + window.sysinfo.acid + "',)); if(is_array($" + a + ")) { $i=0; foreach($" + a + " as $i => $row) { $i++; $row['iteration'] = $i; ?&gt";
                return c
            }
            function g() {
                var a = "&lt;?php }} ?&gt";
                return a
            }
            function h(a) {
                for (var b in a)"$$hashKey" == b ? delete a[b] : "object" == typeof a[b] && h(a[b])
            }
            d.submit.params = a.copy(d.activeModules),
            h(d.submit.params);
            var i = "",
            j = b(b(".modules").html());
            j.find("div.ng-scope[ng-controller$='Ctrl']").each(function() {
                var e = c.findIndex(d.activeModules, {
                    index: parseInt(b(this).parent().parent().attr("index"))
                }),
                h = "",
                j = a.copy(d.activeModules[e].params);
                b(this).find(".js-default-content").remove();
                var k = b(this).parent().parent().attr("name").toLowerCase();
                if ("link" == k) {
                    var l = this;
                    a.forEach(j.items,
                    function(a, c) { (a.selectCate.pid || a.selectCate.cid) && b(l).find(".list-group").children().eq(c).replaceWith("<div>" + f("link", a) + '<div class="list-group-item ng-scope"><a href="{$row[url]}" class="clearfix"><span class="app-nav-title"> {$row[title]}<i class="pull-right fa fa-chevron-right"></i></span></a></div>' + g() + "</div>")
                    }),
                    h = b(this).html()
                } else h = b(this).html();
                i += '<div type="' + k + '">' + h + "</div>",
                e++
            }),
            i = i.replace(/<\!\-\-([^-]*?)\-\->/g, ""),
            i = i.replace(/ng\-[a-zA-Z-]+=\"[^\"]*\"/g, ""),
            i = i.replace(/ng\-[a-zA-Z]+/g, ""),
            d.submit.html = i,
            d.$apply("submit"),
            b(e.target).parents("form").submit()
        },
        d.updateSort = function(a, e) {
            b(".modules").children().each(function() {
                var a = c.findIndex(d.activeModules, {
                    index: parseInt(b(this).attr("index"))
                });
                d.activeModules[a].displayorder = b(this).index()
            }),
            d.activeIndex = c.findIndex(d.activeModules, {
                index: parseInt(e.item.attr("index"))
            }),
            d.$apply()
        },
        d.init = function(e, g) {
            if (c.isNull(e) && (d.modules = f), c.isArray(e)) for (i in e) {
                var h, j = c.findIndex(f, {
                    id: e[i]
                });
                j > -1 && (h = a.copy(f[j]), d.modules.push(h))
            }
            if (c.isArray(g)) for (i in g) {
                var j = c.findIndex(d.modules, {
                    id: g[i]
                });
                j > -1 && (d.modules[j].defaultshow = !0)
            }
            if (d.activeModules.length > 0) {
                var k = [];
                a.forEach(d.activeModules,
                function(a, b) {
                    a && k.push(a.id)
                })
            }
            a.forEach(d.modules,
            function(a, c) {
                a.defaultshow && -1 == b.inArray(a.id, k) && d.addItem(a.id)
            })
        },
        b(".js-editor-submit").click(function(a) {
            d.submit(a)
        }),
        b(".modules").sortable({
            update: function(a, b) {
                d.updateSort(a, b)
            },
            items: ".js-sorttable"
        }),
        b(".modules").disableSelection()
    }]).controller("mainCtrl", ["$scope", "$sanitize",
    function(a, b) {
        a.init(null, ["header"]),
        a.editItem(0)
    }]).controller("headerCtrl", ["$scope",
    function(a) {
        a.addThumb = function(b) {
            d.show(function(c) {
                a.activeItem.params[b] = c.url,
                a.$apply("activeItem")
            },
            {
                direct: !0,
                multiple: !1
            })
        }
    }]).controller("richTextCtrl", ["$scope",
    function(a) {}]).controller("lineCtrl", ["$scope",
    function(a) {}]).controller("componentCtrl", ["$scope",
    function(a) {}]).controller("noticeCtrl", ["$scope",
    function(a) {}]).directive("ngMyEditor",
    function() {
        var a = {
            scope: {
                value: "=ngMyValue"
            },
            template: '<textarea id="editor" rows="10" style="height:600px;"></textarea>',
            link: function(b, c, d) {
                c.data("editor") || (a = UE.getEditor("editor", ueditoroption), c.data("editor", a), a.addListener("contentChange",
                function() {
                    b.value = a.getContent(),
                    b.$root.$$phase || b.$apply("value")
                }), a.addListener("ready",
                function() {
                    a && a.getContent() != b.value && a.setContent(b.value),
                    b.$watch("value",
                    function(b) {
                        a && a.getContent() != b && a.setContent(b ? b: "")
                    })
                }))
            }
        };
        return a
    }).directive("ngMyLinker", ["$http",
    function(a) {
        var d = {
            template: '<div class="dropdown link">	<div class="input-group">		<input type="text" value="" placeholder="链接地址:http://example.com" ng-model="url" class="form-control">		<span class="input-group-btn"><button class="btn btn-default" type="button" onclick="">选择链接 <i class="fa fa-caret-down"></i></button></span>	</div>	<ul class="dropdown-menu" role="menu" style="left: 0; right:0;">		<li><a href="javascript:;" ng-click="searchSystemLinker()">系统菜单</a></li>		<li><a href="javascript:;" ng-click="searchPageLinker()">微页面</a></li>		<li><a href="javascript:;" ng-click="searchCmsLinker()">文章及分类</a></li>		<li><a href="javascript:;" ng-click="searchNewsLinker()">图文回复</a></li>	</ul></div>',
            scope: {
                url: "=ngMyUrl",
                title: "=ngMyTitle"
            },
            link: function(d, e, f) {
                e.find(".input-group-btn").mouseover(function(a) {
                    clearTimeout(d.timer),
                    e.find(".dropdown-menu").show()
                }).mouseout(function() {
                    d.timer = setTimeout(function() {
                        e.find(".dropdown-menu").hide()
                    },
                    500)
                }),
                e.find(".dropdown-menu").mouseover(function() {
                    clearTimeout(d.timer),
                    e.find(".dropdown-menu").show()
                }).mouseout(function() {
                    d.timer = setTimeout(function() {
                        e.find(".dropdown-menu").hide()
                    },
                    500)
                }),
                d.addLink = function(a, b) {
                    d.url = a,
                    b && (d.title = b)
                },
                d.searchSystemLinker = function() {
                    d.modalobj = util.dialog("请选择链接", ["./index.php?c=utility&a=link&callback=selectLinkComplete"], "", {
                        containerName: "link-search-system"
                    }),
                    d.modalobj.modal({
                        keyboard: !1
                    }),
                    d.modalobj.find(".modal-body").css({
                        height: "680px",
                        "overflow-y": "auto"
                    }),
                    d.modalobj.modal("show"),
                    window.selectLinkComplete = function(a, b) {
                        d.addLink(a, b),
                        d.$apply("url", "title"),
                        d.modalobj.modal("hide")
                    }
                },
                d.searchCmsLinker = function(e) {
                    var f = {};
                    f.header = '<ul role="tablist" class="nav nav-pills" style="font-size:14px; margin-top:-20px;">	<li role="presentation" class="active" id="li_goodslist"><a data-toggle="tab" role="tab" aria-controls="articlelist" href="#articlelist">文章</a></li>	<li role="presentation" class="" id="li_category"><a data-toggle="tab" role="tab" aria-controls="category" href="#category">分类</a></li></ul>',
                    f.content = '<div class="tab-content"><div id="articlelist" class="tab-pane active" role="tabpanel">	<table class="table table-hover">		<thead class="navbar-inner">			<tr>				<th style="width:40%;">标题</th>				<th style="width:30%">创建时间</th>				<th style="width:30%; text-align:right">					<div class="input-group input-group-sm">						<input type="text" class="form-control">						<span class="input-group-btn">							<button class="btn btn-default" type="button"><i class="fa fa-search"></i></button>						</span>					</div>				</th>			</tr>		</thead>		<tbody></tbody>	</table>	<div id="pager" style="text-align:center;"></div></div><div id="category" class="tab-pane" role="tabpanel">	<table class="table table-hover">		<thead class="navbar-inner">			<tr>				<th style="width:40%;">标题</th>				<th style="width:30%">创建时间</th>				<th style="width:30%; text-align:right">					<div class="input-group input-group-sm">						<input type="text" class="form-control">						<span class="input-group-btn">							<button class="btn btn-default" type="button"><i class="fa fa-search"></i></button>						</span>					</div>				</th>			</tr>		</thead>		<tbody></tbody>	</table>	<div id="pager" style="text-align:center;"></div></div></div>',
                    f.footer = "",
                    f.articleitem = '<%_.each(items, function(item) {%> \n<tr>\n	<td><a href="#" data-cover-attachment-url="<%=item.attachment%>" title="<%=item.title%>"><%=item.title%></a></td>\n	<td><%=item.createtime%></td>\n	<td class="text-right">\n		<button class="btn btn-default js-btn-select" js-url="./index.php?c=site&a=site&do=detail&id=<%=item.id%>&i=<%=item.uniacid%>" js-title="<%=item.title%>">选取</button>\n	</td>\n</tr>\n<%});%>\n',
                    f.cateitem = '<%_.each(items, function(item) {%> \n	<tr>		<td colspan="2"><a href="#"><%=item.name%></a></td>		<td class="text-right">			<a class="btn btn-default js-btn-select" js-url="./index.php?c=site&a=site&cid=<%=item.id%>&i=<%=item.uniacid%>" js-title="<%=item.name%>">选取</a>		</td>	</tr>	<%_.each(item.children, function(child) {%> \n	<tr>		<td colspan="2" style="padding-left:50px;height:30px;line-height:30px;background-image:url(\'./resource/images/bg_repno.gif\'); background-repeat:no-repeat; background-position: -245px -540px;"><a href="#"><%=child.name%></a></td>		<td class="text-right">			<a class="btn btn-default js-btn-select" js-url="./index.php?c=site&a=site&cid=<%=child.id%>&i=<%=child.uniacid%>" js-title="<%=child.name%>">选取</a>		</td>	</tr><%});%>\n<%});%>\n',
                    b("#link-search-cms")[0] ? d.modalobj = b("#link-search-cms").data("modal") : (d.modalobj = util.dialog(f.header, f.content, f.footer, {
                        containerName: "link-search-cms"
                    }), d.modalobj.find(".modal-body").css({
                        height: "680px",
                        "overflow-y": "auto"
                    }), d.modalobj.modal("show"), d.modalobj.on("hidden.bs.modal",
                    function() {
                        d.modalobj.remove()
                    }), b("#link-search-cms").data("modal", d.modalobj)),
                    e = e || 1,
                    a.get("./index.php?c=site&a=editor&do=articlelist&page=" + e).success(function(a, e, g, h) {
                        var j = {
                            items: []
                        };
                        if (a.message.list) {
                            for (i in a.message.list) j.items.push({
                                title: a.message.list[i].title,
                                id: a.message.list[i].id,
                                uniacid: a.message.list[i].uniacid,
                                attachment: a.message.list[i].thumb_url,
                                createtime: a.message.list[i].createtime
                            });
                            d.modalobj.find("#articlelist tbody").html(c.template(f.articleitem)(j)),
                            d.modalobj.find("#pager").html(a.message.pager),
                            d.modalobj.find("#pager .pagination li[class!='active'] a").click(function() {
                                return d.searchCmsLinker(b(this).attr("page")),
                                !1
                            }),
                            d.modalobj.find(".js-btn-select").click(function() {
                                d.addLink(b(this).attr("js-url"), b(this).attr("js-title")),
                                d.$apply("url", "title"),
                                d.modalobj.modal("hide")
                            })
                        }
                    }),
                    a.get("./index.php?c=site&a=editor&do=catelist&page=" + e).success(function(a, e, g, h) {
                        var j = {
                            items: []
                        };
                        if (a.message) {
                            for (i in a.message) j.items.push({
                                id: a.message[i].id,
                                uniacid: a.message[i].uniacid,
                                name: a.message[i].name,
                                children: a.message[i].children
                            });
                            d.modalobj.find("#category tbody").html(c.template(f.cateitem)(j)),
                            d.modalobj.find(".js-btn-select").click(function() {
                                d.addLink(b(this).attr("js-url"), b(this).attr("js-title")),
                                d.$apply("url", "title"),
                                d.modalobj.modal("hide")
                            })
                        }
                    })
                },
                d.searchNewsLinker = function(e) {
                    var f = {};
                    f.content = '<div id="newslist" class="tab-pane active" role="tabpanel">	<table class="table table-hover">		<thead class="navbar-inner">			<tr>				<th style="width:40%;">标题</th>				<th style="width:30%">创建时间</th>				<th style="width:30%; text-align:right">					<div class="input-group input-group-sm">						<input type="text" class="form-control">						<span class="input-group-btn">							<button class="btn btn-default" type="button"><i class="fa fa-search"></i></button>						</span>					</div>				</th>			</tr>		</thead>		<tbody></tbody>	</table>	<div id="pager" style="text-align:center;"></div></div>',
                    f.footer = "",
                    f.newsitem = '<%_.each(items, function(item) {%> \n<tr>\n	<td><a href="#" data-cover-attachment-url="<%=item.attachment%>" title="<%=item.title%>"><%=item.title%></a></td>\n	<td><%=item.createtime%></td>\n	<td class="text-right">\n		<button class="btn btn-default js-btn-select" js-url="./index.php?i=<%=item.uniacid%>&c=entry&id=<%=item.id%>&do=detail&m=news" js-title="<%=item.title%>">选取</button>\n	</td>\n</tr>\n<%});%>\n',
                    b("#link-search-news")[0] ? d.modalobj = b("#link-search-news").data("modal") : (d.modalobj = util.dialog(f.header, f.content, f.footer, {
                        containerName: "link-search-news"
                    }), d.modalobj.find(".modal-body").css({
                        height: "680px",
                        "overflow-y": "auto"
                    }), d.modalobj.modal("show"), d.modalobj.on("hidden.bs.modal",
                    function() {
                        d.modalobj.remove()
                    }), b("#link-search-news").data("modal", d.modalobj)),
                    e = e || 1,
                    a.get("./index.php?c=site&a=editor&do=newslist&page=" + e).success(function(a, e, g, h) {
                        var j = {
                            items: []
                        };
                        if (a.message.list) {
                            for (i in a.message.list) j.items.push({
                                title: a.message.list[i].title,
                                id: a.message.list[i].id,
                                uniacid: window.sysinfo.uniacid,
                                attachment: a.message.list[i].thumb_url,
                                createtime: a.message.list[i].createtime
                            });
                            d.modalobj.find("#newslist tbody").html(c.template(f.newsitem)(j)),
                            d.modalobj.find("#pager").html(a.message.pager),
                            d.modalobj.find("#pager .pagination li[class!='active'] a").click(function() {
                                return d.searchNewsLinker(b(this).attr("page")),
                                !1
                            }),
                            d.modalobj.find(".js-btn-select").click(function() {
                                d.addLink(b(this).attr("js-url"), b(this).attr("js-title")),
                                d.$apply("url", "title"),
                                d.modalobj.modal("hide")
                            })
                        }
                    })
                },
                d.searchPageLinker = function(e) {
                    var f = {};
                    f.content = '<div id="pageList" class="tab-pane active" role="tabpanel">	<table class="table table-hover">		<thead class="navbar-inner">			<tr>				<th style="width:40%;">名称</th>				<th style="width:30%">创建时间</th>				<th style="width:30%; text-align:right">					<div class="input-group input-group-sm">						<input type="text" class="form-control">						<span class="input-group-btn">							<button class="btn btn-default" type="button"><i class="fa fa-search"></i></button>						</span>					</div>				</th>			</tr>		</thead>		<tbody></tbody>	</table>	<div id="pager" style="text-align:center;"></div></div>',
                    f.footer = "",
                    f.pageItem = '<%_.each(items, function(item) {%> \n<tr>\n	<td><a href="#" title="<%=item.title%>"><%=item.title%></a></td>\n	<td><%=item.createtime%></td>\n	<td class="text-right">\n		<button class="btn btn-default js-btn-select" js-url="./index.php?i=<%=item.uniacid%>&c=home&pageid=<%=item.id%>" js-title="<%=item.title%>">选取</button>\n	</td>\n</tr>\n<%});%>\n',
                    b("#link-search-page")[0] ? d.modalobj = b("#link-search-page").data("modal") : (d.modalobj = util.dialog(f.header, f.content, f.footer, {
                        containerName: "link-search-page"
                    }), d.modalobj.find(".modal-body").css({
                        height: "680px",
                        "overflow-y": "auto"
                    }), d.modalobj.modal("show"), d.modalobj.on("hidden.bs.modal",
                    function() {
                        d.modalobj.remove()
                    }), b("#link-search-page").data("modal", d.modalobj)),
                    e = e || 1,
                    a.get("./index.php?c=site&a=editor&do=pagelist&&page=" + e).success(function(a, e, g, h) {
                        var j = {
                            items: []
                        };
                        if (a.message.list) {
                            for (i in a.message.list) j.items.push({
                                title: a.message.list[i].title,
                                id: a.message.list[i].id,
                                uniacid: window.sysinfo.uniacid,
                                createtime: a.message.list[i].createtime
                            });
                            d.modalobj.find("#pageList tbody").html(c.template(f.pageItem)(j)),
                            d.modalobj.find("#pager").html(a.message.pager),
                            d.modalobj.find("#pager .pagination li[class!='active'] a").click(function() {
                                return d.searchPageLinker(b(this).attr("page")),
                                !1
                            }),
                            d.modalobj.find(".js-btn-select").click(function() {
                                d.addLink(b(this).attr("js-url"), b(this).attr("js-title")),
                                d.$apply("url", "title"),
                                d.modalobj.modal("hide")
                            })
                        }
                    })
                }
            }
        };
        return d
    }]).directive("ngMyDatePicker", ["$http", "$parse",
    function() {
        var a = {
            transclude: !0,
            template: "<span ng-transclude></span>",
            scope: {
                dateValue: "=ngMyDateValue"
            },
            link: function(a, b, c) {
                var d = {
                    lang: "zh",
                    step: "30",
                    format: "Y-m-d H:i:s",
                    closeOnDateSelect: !0,
                    onSelectDate: function(b, c) {
                        a.dateValue = b.dateFormat("Y-m-d H:i:s"),
                        a.$apply("dateValue")
                    },
                    onSelectTime: function(b, c) {
                        a.dateValue = b.dateFormat("Y-m-d H:i:s"),
                        a.$apply("dateValue")
                    }
                };
                b.datetimepicker(d)
            }
        };
        return a
    }]).directive("ngMyColorpicker", [function() {
        var a = {
            template: '<div class="input-group">	<input type="text" name="" value="" ng-model="colorValue" class="form-control">	<span class="input-group-btn">		<button class="btn btn-default colorpicker" type="button">选择颜色<i class="fa fa-caret-down"></i></button>		<button class="btn btn-default colorclean" type="button"><span><i class="fa fa-remove"></i></span></button>	</span></div>',
            scope: {
                colorValue: "=ngMyColor",
                colorDefault: "=ngMyDefaultColor"
            },
            link: function(a, c, d) {
                b(".colorpicker").each(function() {
                    var c = this;
                    b(c).data("data-colorpicker-init") || (util.colorpicker(c,
                    function(d) {
                        b(c).parent().parent().find(":text").val(d.toHexString()),
                        a.colorValue = d.toHexString(),
                        a.$apply("colorValue"),
                        a.$watch("colorValue",
                        function(d) {
                            b(c).spectrum("get") != d && (b(c).spectrum("set", d ? d: a.colorDefault), b(c).parent().parent().find(":text").val(d ? d: a.colorDefault))
                        })
                    }), b(".colorclean").click(function() {
                        b(c).parent().parent().find(":text").val(""),
                        a.colorValue = a.colorDefault,
                        b(c).spectrum("set", a.colorDefault),
                        a.$apply("colorValue")
                    }), b(c).data("data-colorpicker-init", !0))
                })
            }
        };
        return a
    }]).controller("textNavCtrl", ["$scope",
    function(a) {
        a.addItem = function() {
            a.activeItem.params.items.push({
                title: "",
                url: ""
            })
        },
        a.removeItem = function(d) {
            index = b.inArray(d, a.activeItem.params.items),
            items = c.clone(a.activeItem.params.items),
            a.activeItem.params.items = [];
            for (i in items) i != index && a.activeItem.params.items.push(items[i])
        }
    }]).controller("linkCtrl", ["$scope", "$http",
    function(a, d) {
        a.pageSize = c.range(0, 30),
        a.addItem = function() {
            a.activeItem.params.items.push({
                title: "",
                url: "",
                type: 1,
                selectCate: {
                    name: "",
                    id: 0
                },
                pageSize: 3
            })
        },
        a.removeItem = function(d) {
            index = b.inArray(d, a.activeItem.params.items),
            items = c.clone(a.activeItem.params.items),
            a.activeItem.params.items = [];
            for (i in items) i != index && a.activeItem.params.items.push(items[i])
        },
        a.showSearchCateList = function(c) {
            return a.currentItem = c,
            d.get("./index.php?c=site&a=editor&do=catelist").success(function(c, d, e, f) {
                a.searchCateList = [];
                var g = c.message;
                for (i in g) a.searchCateList.push({
                    id: g[i].id,
                    name: g[i].name,
                    children: g[i].children
                });
                a.modalobj = b("#modal-search-cate-link").modal({
                    show: !0
                })
            }),
            !0
        },
        a.selectCateItem = function(b, c, d) {
            return a.currentItem.selectCate = {
                pid: b,
                cid: c,
                name: d
            },
            a.modalobj.modal("hide"),
            !0
        }
    }]).controller("adImgCtrl", ["$scope",
    function(a) {
        a.addItem = function() {
            d.show(function(c) {
                a.activeItem.params.items.push({
                    id: c.id,
                    imgurl: c.url,
                    title: "",
                    url: "",
                    isactive: !1
                }),
                b.each(a.activeItem.params.items,
                function(b, c) {
                    a.activeItem.params.items[0].isactive = 0 == b
                }),
                a.$apply("activeItem")
            },
            {
                direct: !0,
                multiple: !1
            })
        },
        a.removeItem = function(d) {
            index = b.inArray(d, a.activeItem.params.items),
            items = c.clone(a.activeItem.params.items),
            a.activeItem.params.items = [];
            for (i in items) i != index && a.activeItem.params.items.push(items[i])
        },
        a.addEmpty = function() {
            a.activeItem.params.items.push({
                imgurl: "",
                title: "",
                url: ""
            })
        },
        a.changeItem = function(c) {
            d.init(function(d) {
                var e = b.inArray(c, a.activeItem.params.items);
                e > -1 && (a.activeItem.params.items[e].id = d.id, a.activeItem.params.items[e].imgurl = d.url, a.$apply())
            },
            {
                direct: !0,
                multiple: !1
            })
        }
    }]).controller("navImgCtrl", ["$scope",
    function(a) {
        a.changeItem = function(b) {
            d.show(function(c) {
                b.id = c.id,
                b.imgurl = c.url,
                a.$apply()
            },
            {
                direct: !0,
                multiple: !1
            })
        }
    }]).controller("titleCtrl", ["$scope",
    function(a) {
        a.changeNavEnable = function(b) {
            a.activeItem.params.tradition.nav.enable = b
        }
    }]).controller("whiteCtrl", ["$scope",
    function(a) {
        0 == b(".slider-bar .ui-slider-handle").length && b(".slider-bar").slider({
            min: 1,
            max: 100,
            slide: function(b, c) {
                a.activeItem.params.height = c.value,
                a.$apply()
            }
        }),
        b("#module-" + a.activeIndex).click(function() {
            b(".slider-bar").slider("option", "value", a.activeItem.params.height)
        })
    }]).controller("audioCtrl", ["$scope",
    function(a) {
        a.addAudioItem = function() {
            d.init(function(c) {
                c && (a.activeItem.params.audio.id = c.id, a.activeItem.params.audio.url = c.attachment, a.$apply(), b(".audio-player-play").click(function() {
                    var c = a.activeItem.params.audio.url;
                    if (c) {
                        b("#player").remove();
                        var d = b('<div id="player"></div>');
                        b(document.body).append(d),
                        d.data("control", b(this)),
                        d.jPlayer({
                            playing: function() {
                                b(this).data("control").find("i").removeClass("fa-play").addClass("fa-stop")
                            },
                            pause: function(a) {
                                b(this).data("control").find("i").removeClass("fa-stop").addClass("fa-play")
                            },
                            swfPath: "resource/components/jplayer",
                            supplied: "mp3,wma,wav,amr",
                            solution: "html, flash"
                        }),
                        d.jPlayer("setMedia", {
                            mp3: c
                        }).jPlayer("play"),
                        b(this).find("i").hasClass("fa-stop") ? d.jPlayer("stop") : d.jPlayer("setMedia", {
                            mp3: c
                        }).jPlayer("play")
                    }
                }).show())
            },
            {
                direct: !0,
                multiple: !1,
                type: "audio"
            })
        },
        a.addImgItem = function() {
            d.init(function(b) {
                a.activeItem.params.headimg = b.url,
                a.$apply()
            },
            {
                direct: !0,
                multiple: !1
            })
        }
    }]).controller("cubeCtrl", ["$scope",
    function(a) {
        if (a.activeItem.params && a.activeItem.params.layout && c.isEmpty(a.activeItem.params.layout)) for (row = 0; row < 4; row++) for (a.activeItem.params.layout[row] = {},
        col = 0; col < 4; col++) a.activeItem.params.layout[row][col] = {
            cols: 1,
            rows: 1,
            isempty: !0,
            imgurl: "",
            classname: ""
        };
        b(".layout-table").bind("mouseover",
        function(a) {
            if ("LI" == a.target.tagName) {
                b(".layout-table li").removeClass("selected");
                var c = b(a.target).attr("data-rows"),
                d = b(a.target).attr("data-cols");
                b(".layout-table li").filter(function(a, e) {
                    return b(e).attr("data-rows") <= c && b(e).attr("data-cols") <= d
                }).addClass("selected")
            }
        }),
        a.showSelection = function(d, e) {
            a.activeItem.params.currentPos = {
                row: d,
                col: e
            },
            a.activeItem.params.selection = {};
            var f = -1,
            g = 1;
            for (i = d; i < 4; i++) {
                for (y = 1, a.activeItem.params.selection[g] = {},
                j = e; j < 4; j++) f >= 0 && f < j || (!c.isUndefined(a.activeItem.params.layout[i][j]) && a.activeItem.params.layout[i][j].isempty ? (a.activeItem.params.selection[g][y] = {
                    rows: g,
                    cols: y
                },
                y++) : f = j - 1);
                g++
            }
            return b(".layout-table li").removeClass("selected"),
            a.modalobj = b("#modal-cube-layout").modal({
                show: !0
            }),
            !0
        },
        a.selectLayout = function(b, d, e, f) {
            for (c.isUndefined(e) && (e = 0), c.isUndefined(f) && (f = 0), a.activeItem.params.layout[b][d] = {
                cols: f,
                rows: e,
                isempty: !1,
                imgurl: "",
                classname: "index-" + a.activeItem.params.showIndex
            },
            i = b; i < parseInt(b) + parseInt(e); i++) for (j = d; j < parseInt(d) + parseInt(f); j++)(b != i || d != j) && delete a.activeItem.params.layout[i][j];
            return a.activeItem.params.showIndex++,
            a.modalobj.modal("hide"),
            a.changeItem(b, d),
            !0
        },
        a.addItem = function(b, c) {
            d.show(function(b) {
                a.activeItem.params.currentLayout.id = b.id,
                a.activeItem.params.currentLayout.imgurl = b.url,
                a.$apply()
            },
            {
                direct: !0,
                multiple: !1
            })
        },
        a.changeItem = function(c, d) {
            b("#cube-editor td").removeClass("current").filter(function(a, e) {
                return b(e).attr("x") == c && b(e).attr("y") == d
            }).addClass("current"),
            b("#thumb").attr("src", ""),
            a.activeItem.params.currentLayout = a.activeItem.params.layout[c][d]
        }
    }]).controller("quickMenuCtrl", ["$scope",
    function(d) {
        activeItem ? d.activeItem = activeItem: d.activeItem = {
            navStyle: 1,
            bgColor: "#2B2D30",
            menus: [],
            extend: [],
            position: {
                homepage: !0,
                usercenter: !0,
                page: !0,
                article: !0
            },
            ignoreModules: {}
        },
        d.submit = {},
        d.selectNavStyle = function() {
            d.activeItem.navStyle = b('input[name="nav_style"]:checked').val(),
            d.$apply("activeItem")
        },
        d.addMenu = function() {
            d.activeItem.menus.push({
                title: "标题",
                url: "",
                submenus: [],
                icon: {
                    name: "fa-home",
                    color: "#ffffff"
                },
                image: "",
                hoverimage: "",
                hovericon: ""
            })
        },
        d.addSubMenu = function(a) {
            a.submenus.push({
                title: "标题",
                url: ""
            })
        },
        d.submit = function(c) {
            function e(a) {
                for (var b in a)"$$hashKey" == b ? delete a[b] : "object" == typeof a[b] && e(a[b])
            }
            d.submit.params = a.copy(d.activeItem),
            e(d.submit.params);
            var f = b(".nav-menu").html();
            f = f.replace(/<\!\-\-([^-]*?)\-\->/g, ""),
            f = f.replace(/ng\-[a-zA-Z-]+=\"[^\"]*\"/g, ""),
            f = f.replace(/ng\-[a-zA-Z]+/g, ""),
            f = f.replace(/[\t\n\n\r]/g, ""),
            d.submit.html = f,
            d.$apply("submit"),
            b(c.target).parents("form").submit()
        },
        d.removeMenu = function(c) {
            index = b.inArray(c, d.activeItem.menus),
            items = a.copy(d.activeItem.menus),
            d.activeItem.menus = [];
            for (i in items) i != index && d.activeItem.menus.push(items[i])
        },
        d.removeSubMenu = function(a, b) {
            d.activeItem.menus[a].submenus = c.without(d.activeItem.menus[a].submenus, b)
        },
        d.showSearchModules = function() {
            d.moduleDialog = b("#shop-modules-modal").modal(),
            b("#shop-modules-modal .modal-body .btn-primary").html("取消"),
            b("#shop-modules-modal").find(".modal-footer .btn-primary").unbind("click").click(function() {
                d.activeItem.ignoreModules = {},
                b("#shop-modules-modal .modal-body .btn-primary").each(function() {
                    d.hasIgnoreModules = !0,
                    d.activeItem.ignoreModules[b(this).attr("js-name")] = {
                        name: b(this).attr("js-name"),
                        title: b(this).attr("js-title")
                    }
                }),
                d.$apply("activeItem"),
                d.$apply("hasIgnoreModules")
            })
        },
        b(".js-editor-submit").click(function(a) {
            d.submit(a)
        }),
        d.hasIgnoreModules = c.size(d.activeItem.ignoreModules),
        b(".nav-menu").show(),
        b(".app-shopNav-edit").show()
    }]).directive("ngMyIconer",
    function() {
        var a = '<div class="nav-img-box" style="background-color: #2B2D30;"><div class="btns"><a style="height:19px;" ng-click="removeIcon()" href="javascript:;"><i class="fa fa-times"></i></a></div><div class="nav-img" ng-style="{\'background-image\': image ? \'url(\'+image+\')\' : \'\'}"><i ng-hide="menu.image" class="fa" ng-style="{\'color\' : icon.color}" ng-class="icon.name"></i></div><a href="javascript:;" ng-click="selectIcon()"><span ng-transclude></span></a></div>',
        c = {
            scope: {
                image: "=ngMyImage",
                icon: "=ngMyIcon"
            },
            transclude: !0,
            template: a,
            link: function(a, c, e) {
                a.selectIcon = function() {
                    var c = d.show(function(b) {
                        a.image = b.url,
                        a.icon = {},
                        a.$apply("image"),
                        a.$apply("icon")
                    },
                    {
                        direct: !0,
                        multiple: !1
                    });
                    c.on("shown.bs.modal",
                    function() {
                        c.find(".nav-pills").append('<li id="li_icon" role="presentation"><a href="#icon" aria-controls="icon" role="tab" data-toggle="tab">图标</a></li>'),
                        c.find(".tab-content").append('<div id="icon" class="tab-pane icon form-horizontal" role="tabpanel"><div class="form-group" style="border-bottom:1px solid #e5e5e5; padding:0 0 15px 0; margin:10px 0 0 0;">	<label class="col-xs-3 control-label">图标颜色</label>	<div class="col-xs-9">		<input type="color" value="" class="form-control" id="iconcolor" onchange="$(this).parents(\'#icon\').attr(\'color\', this.value);$(this).parents(\'#icon\').find(\'i\').css(\'color\', this.value)">	</div></div></div>'),
                        b.get("./index.php?c=utility&a=icon&callback=selectIconComplete",
                        function(a) {
                            c.find("#icon").append(a)
                        })
                    }),
                    window.selectIconComplete = function(b) {
                        a.icon = {},
                        a.icon.name = b,
                        a.icon.color = c.find("#icon").attr("color"),
                        a.image = "",
                        a.$apply("image"),
                        a.$apply("icon"),
                        c.modal("hide")
                    }
                },
                a.removeIcon = function() {
                    a.image = "",
                    a.icon = {}
                }
            }
        };
        return c
    }).controller("homePageCtrl", ["$scope",
    function(a) {
        activeMenus ? a.activeMenus = activeMenus: a.activeMenus = []
    }]).controller("userCenterCtrl", ["$scope",
    function(a) {
        a.init(null, ["UCheader"]),
        a.addThumb = function(b) {
            d.show(function(c) {
                a.activeItem.params[b] = c.url,
                a.$apply("activeItem")
            },
            {
                direct: !0,
                multiple: !1
            })
        },
        a.showIconBrowser = function(b) {
            util.iconBrowser(function(c) {
                b.css.icon.icon = c,
                a.$apply("activeMenus")
            })
        },
        a.addMenu = function() {
            a.activeMenus.push({
                icon: "",
                css: {
                    icon: {
                        icon: "fa fa-external-link"
                    }
                },
                name: "",
                url: ""
            })
        },
        a.removeMenu = function(b) {
            a.activeMenus = c.without(a.activeMenus, b)
        },
        activeMenus ? a.activeMenus = activeMenus: a.activeMenus = [],
        a.editItem(0)
    }])
});