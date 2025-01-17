/*
 dhtmlxScheduler v.4.1.0 Stardard

 This software is covered by GPL license. You also can obtain Commercial or Enterprise license to use it in non-GPL project - please contact sales@dhtmlx.com. Usage without proper license is prohibited.

 (c) Dinamenta, UAB.
 */
!function () {
    function e(e) {
        return e.replace(y, "\n").replace(p, "")
    }

    function t(e, t) {
        e = parseFloat(e), t = parseFloat(t), isNaN(t) || (e -= t);
        var r = s(e);
        return e = e - r.width + r.cols * g, isNaN(e) ? "auto" : 100 * e / g
    }

    function r(e, t, r) {
        e = parseFloat(e), t = parseFloat(t), !isNaN(t) && r && (e -= t);
        var a = s(e);
        return e = e - a.width + a.cols * g, isNaN(e) ? "auto" : 100 * e / (g - (isNaN(t) ? 0 : t))
    }

    function s(e) {
        for (var t = 0, r = scheduler._els.dhx_cal_header[0].childNodes, s = r[1] ? r[1].childNodes : r[0].childNodes, a = 0; a < s.length; a++) {
            var n = s[a].style ? s[a] : s[a].parentNode, i = parseFloat(n.style.width);
            if (!(e > i))break;
            e -= i + 1, t += i + 1
        }
        return{width: t, cols: a}
    }

    function a(e) {
        return e = parseFloat(e), isNaN(e) ? "auto" : 100 * e / m
    }

    function n(e, t) {
        return(window.getComputedStyle ? window.getComputedStyle(e, null)[t] : e.currentStyle ? e.currentStyle[t] : null) || ""
    }

    function i(e, t) {
        for (var r = parseInt(e.style.left, 10), s = 0; s < scheduler._cols.length; s++)if (r -= scheduler._cols[s], 0 > r)return s;
        return t
    }

    function d(e, t) {
        for (var r = parseInt(e.style.top, 10), s = 0; s < scheduler._colsS.heights.length; s++)if (scheduler._colsS.heights[s] > r)return s;
        return t
    }

    function l(e) {
        return e ? "<" + e + ">" : ""
    }

    function o(e) {
        return e ? "</" + e + ">" : ""
    }

    function _(e, t, r, s) {
        var a = "<" + e + " profile='" + t + "'";
        return r && (a += " header='" + r + "'"), s && (a += " footer='" + s + "'"), a += ">"
    }

    function h() {
        var t = "", r = scheduler._mode;
        if (scheduler.matrix && scheduler.matrix[scheduler._mode] && (r = "cell" == scheduler.matrix[scheduler._mode].render ? "matrix" : "timeline"), t += "<scale mode='" + r + "' today='" + scheduler._els.dhx_cal_date[0].innerHTML + "'>", "week_agenda" == scheduler._mode)for (var s = scheduler._els.dhx_cal_data[0].getElementsByTagName("DIV"), a = 0; a < s.length; a++)"dhx_wa_scale_bar" == s[a].className && (t += "<column>" + e(s[a].innerHTML) + "</column>");
        else if ("agenda" == scheduler._mode || "map" == scheduler._mode) {
            var s = scheduler._els.dhx_cal_header[0].childNodes[0].childNodes;
            t += "<column>" + e(s[0].innerHTML) + "</column><column>" + e(s[1].innerHTML) + "</column>"
        } else if ("year" == scheduler._mode)for (var s = scheduler._els.dhx_cal_data[0].childNodes, a = 0; a < s.length; a++)t += "<month label='" + e(s[a].childNodes[0].innerHTML) + "'>", t += u(s[a].childNodes[1].childNodes), t += c(s[a].childNodes[2]), t += "</month>"; else {
            t += "<x>";
            var s = scheduler._els.dhx_cal_header[0].childNodes;
            t += u(s), t += "</x>";
            var n = scheduler._els.dhx_cal_data[0];
            if (scheduler.matrix && scheduler.matrix[scheduler._mode]) {
                t += "<y>";
                for (var a = 0; a < n.firstChild.rows.length; a++) {
                    var i = n.firstChild.rows[a];
                    t += "<row><![CDATA[" + e(i.cells[0].innerHTML) + "]]></row>"
                }
                t += "</y>", m = n.firstChild.rows[0].cells[0].offsetHeight
            } else if ("TABLE" == n.firstChild.tagName)t += c(n); else {
                for (n = n.childNodes[n.childNodes.length - 1]; -1 == n.className.indexOf("dhx_scale_holder");)n = n.previousSibling;
                n = n.childNodes, t += "<y>";
                for (var a = 0; a < n.length; a++)t += "\n<row><![CDATA[" + e(n[a].innerHTML) + "]]></row>";
                t += "</y>", m = n[0].offsetHeight
            }
        }
        return t += "</scale>"
    }

    function c(t) {
        for (var r = "", s = t.firstChild.rows, a = 0; a < s.length; a++) {
            for (var n = [], i = 0; i < s[a].cells.length; i++)n.push(s[a].cells[i].firstChild.innerHTML);
            r += "\n<row height='" + t.firstChild.rows[a].cells[0].offsetHeight + "'><![CDATA[" + e(n.join("|")) + "]]></row>", m = t.firstChild.rows[0].cells[0].offsetHeight
        }
        return r
    }

    function u(t) {
        var r, s = "";
        scheduler.matrix && scheduler.matrix[scheduler._mode] && (scheduler.matrix[scheduler._mode].second_scale && (r = t[1].childNodes), t = t[0].childNodes);
        for (var a = 0; a < t.length; a++)s += "\n<column><![CDATA[" + e(t[a].innerHTML) + "]]></column>";
        if (g = t[0].offsetWidth, r)for (var n = 0, i = t[0].offsetWidth, d = 1, a = 0; a < r.length; a++)s += "\n<column second_scale='" + d + "'><![CDATA[" + e(r[a].innerHTML) + "]]></column>", n += r[a].offsetWidth, n >= i && (i += t[d] ? t[d].offsetWidth : 0, d++), g = r[0].offsetWidth;
        return s
    }

    function f(s) {
        var l = "", o = scheduler._rendered, _ = scheduler.matrix && scheduler.matrix[scheduler._mode];
        if ("agenda" == scheduler._mode || "map" == scheduler._mode)for (var h = 0; h < o.length; h++)l += "<event><head><![CDATA[" + e(o[h].childNodes[0].innerHTML) + "]]></head><body><![CDATA[" + e(o[h].childNodes[2].innerHTML) + "]]></body></event>";
        else if ("week_agenda" == scheduler._mode)for (var h = 0; h < o.length; h++)l += "<event day='" + o[h].parentNode.getAttribute("day") + "'><body>" + e(o[h].innerHTML) + "</body></event>"; else if ("year" == scheduler._mode)for (var o = scheduler.get_visible_events(), h = 0; h < o.length; h++) {
            var c = o[h].start_date;
            for (c.valueOf() < scheduler._min_date.valueOf() && (c = scheduler._min_date); c < o[h].end_date;) {
                var u = c.getMonth() + 12 * (c.getFullYear() - scheduler._min_date.getFullYear()) - scheduler.week_starts._month, f = scheduler.week_starts[u] + c.getDate() - 1, v = s ? n(scheduler._get_year_cell(c), "color") : "", g = s ? n(scheduler._get_year_cell(c), "backgroundColor") : "";
                if (l += "<event day='" + f % 7 + "' week='" + Math.floor(f / 7) + "' month='" + u + "' backgroundColor='" + g + "' color='" + v + "'></event>", c = scheduler.date.add(c, 1, "day"), c.valueOf() >= scheduler._max_date.valueOf())break
            }
        } else if (_ && "cell" == _.render)for (var o = scheduler._els.dhx_cal_data[0].getElementsByTagName("TD"), h = 0; h < o.length; h++) {
            var v = s ? n(o[h], "color") : "", g = s ? n(o[h], "backgroundColor") : "";
            l += "\n<event><body backgroundColor='" + g + "' color='" + v + "'><![CDATA[" + e(o[h].innerHTML) + "]]></body></event>"
        } else for (var h = 0; h < o.length; h++) {
            var p, y;
            if (scheduler.matrix && scheduler.matrix[scheduler._mode])p = t(o[h].style.left), y = t(o[h].offsetWidth) - 1; else {
                var x = scheduler.config.use_select_menu_space ? 0 : 26;
                p = r(o[h].style.left, x, !0), y = r(o[h].style.width, x) - 1
            }
            if (!isNaN(1 * y)) {
                var b = a(o[h].style.top), w = a(o[h].style.height), E = o[h].className.split(" ")[0].replace("dhx_cal_", "");
                if ("dhx_tooltip_line" !== E) {
                    var k = scheduler.getEvent(o[h].getAttribute("event_id"));
                    if (k) {
                        var f = k._sday, D = k._sweek, N = k._length || 0;
                        if ("month" == scheduler._mode)w = parseInt(o[h].offsetHeight, 10), b = parseInt(o[h].style.top, 10) - scheduler.xy.month_head_height, f = i(o[h], f), D = d(o[h], D);
                        else if (scheduler.matrix && scheduler.matrix[scheduler._mode]) {
                            f = 0;
                            var M = o[h].parentNode.parentNode.parentNode;
                            D = M.rowIndex;
                            var L = m;
                            m = o[h].parentNode.offsetHeight, b = a(o[h].style.top), b -= .2 * b, m = L
                        } else {
                            if (o[h].parentNode == scheduler._els.dhx_cal_data[0])continue;
                            var C = scheduler._els.dhx_cal_data[0].childNodes[0], O = parseFloat(-1 != C.className.indexOf("dhx_scale_holder") ? C.style.left : 0);
                            p += t(o[h].parentNode.style.left, O)
                        }
                        if (l += "\n<event week='" + D + "' day='" + f + "' type='" + E + "' x='" + p + "' y='" + b + "' width='" + y + "' height='" + w + "' len='" + N + "'>", "event" == E) {
                            l += "<header><![CDATA[" + e(o[h].childNodes[1].innerHTML) + "]]></header>";
                            var v = s ? n(o[h].childNodes[2], "color") : "", g = s ? n(o[h].childNodes[2], "backgroundColor") : "";
                            l += "<body backgroundColor='" + g + "' color='" + v + "'><![CDATA[" + e(o[h].childNodes[2].innerHTML) + "]]></body>"
                        } else {
                            var v = s ? n(o[h], "color") : "", g = s ? n(o[h], "backgroundColor") : "";
                            l += "<body backgroundColor='" + g + "' color='" + v + "'><![CDATA[" + e(o[h].innerHTML) + "]]></body>"
                        }
                        l += "</event>"
                    }
                }
            }
        }
        return l
    }

    function v(e, t, r, s, a, n, i) {
        var d = !1;
        "fullcolor" == a && (d = !0, a = "color"), a = a || "color";
        var c = scheduler.uid(), u = document.createElement("div");
        u.style.display = "none", document.body.appendChild(u), u.innerHTML = '<form id="' + c + '" method="post" target="_blank" action="' + s + '" accept-charset="utf-8" enctype="application/x-www-form-urlencoded"><input type="hidden" name="mycoolxmlbody"/> </form>';
        var v = "";
        if (e) {
            var g = scheduler._date, m = scheduler._mode;
            t = scheduler.date[r + "_start"](t), t = scheduler.date["get_" + r + "_end"] ? scheduler.date["get_" + r + "_end"](t) : scheduler.date.add(t, 1, r), v = _("pages", a, n, i);
            for (var p = new Date(e); +t > +p; p = scheduler.date.add(p, 1, r))scheduler.setCurrentView(p, r), v += l("page") + h().replace("–", "-") + f(d) + o("page");
            v += o("pages"), scheduler.setCurrentView(g, m)
        } else v = _("data", a, n, i) + h().replace("–", "-") + f(d) + o("data");
        document.getElementById(c).firstChild.value = encodeURIComponent(v), document.getElementById(c).submit(), u.parentNode.removeChild(u)
    }

    var g, m, p = new RegExp("<[^>]*>", "g"), y = new RegExp("<br[^>]*>", "g");
    scheduler.toPDF = function (e, t, r, s) {
        return v.apply(this, [null, null, null, e, t, r, s])
    }, scheduler.toPDFRange = function (e, t) {
        return"string" == typeof e && (e = scheduler.templates.api_date(e), t = scheduler.templates.api_date(t)), v.apply(this, arguments)
    }
}();
//# sourceMappingURL=../sources/ext/dhtmlxscheduler_pdf.js.map