! function (n)
{
	function e(e)
	{
		for (var o, u, a = e[0], l = e[1], c = e[2], f = 0, p = []; f < a.length; f++) u = a[f], r[u] && p.push(r[u][0]), r[u] = 0;
		for (o in l) Object.prototype.hasOwnProperty.call(l, o) && (n[o] = l[o]);
		for (s && s(e); p.length;) p.shift()();
		return i.push.apply(i, c || []), t()
	}

	function t()
	{
		for (var n, e = 0; e < i.length; e++)
		{
			for (var t = i[e], o = !0, a = 1; a < t.length; a++)
			{
				var l = t[a];
				0 !== r[l] && (o = !1)
			}
			o && (i.splice(e--, 1), n = u(u.s = t[0]))
		}
		return n
	}
	var o = {},
		r = {
			13: 0,
			16: 0
		},
		i = [];

	function u(e)
	{
		if (o[e]) return o[e].exports;
		var t = o[e] = {
			i: e,
			l: !1,
			exports:
			{}
		};
		return n[e].call(t.exports, t, t.exports, u), t.l = !0, t.exports
	}
	u.m = n, u.c = o, u.d = function (n, e, t)
	{
		u.o(n, e) || Object.defineProperty(n, e,
		{
			enumerable: !0,
			get: t
		})
	}, u.r = function (n)
	{
		"undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(n, Symbol.toStringTag,
		{
			value: "Module"
		}), Object.defineProperty(n, "__esModule",
		{
			value: !0
		})
	}, u.t = function (n, e)
	{
		if (1 & e && (n = u(n)), 8 & e) return n;
		if (4 & e && "object" == typeof n && n && n.__esModule) return n;
		var t = Object.create(null);
		if (u.r(t), Object.defineProperty(t, "default",
			{
				enumerable: !0,
				value: n
			}), 2 & e && "string" != typeof n)
			for (var o in n) u.d(t, o, function (e)
			{
				return n[e]
			}.bind(null, o));
		return t
	}, u.n = function (n)
	{
		var e = n && n.__esModule ? function ()
		{
			return n.default
		} : function ()
		{
			return n
		};
		return u.d(e, "a", e), e
	}, u.o = function (n, e)
	{
		return Object.prototype.hasOwnProperty.call(n, e)
	}, u.p = "";
	var a = window.webpackJsonp = window.webpackJsonp || [],
		l = a.push.bind(a);
	a.push = e, a = a.slice();
	for (var c = 0; c < a.length; c++) e(a[c]);
	var s = l;
	i.push([34, 0]), t()
}(
{
	34: function (n, e, t)
	{
		"use strict";
		(function (n)
		{
			t(6), n(document).ready(function ()
			{
				var e = n(".mp-header__mobile-menu-toggle"),
					t = n(".mp-header__options"),
					o = n(".mp-main-menu"),
					p = n(".mp-profile-tag__photo"),
					r = function ()
					{
						o.addClass("mp-main-menu--shown")
						
					};
				p.on("click", r), o.on("click", ".mp-main-menu__overlay", function ()
				{
					
					o.removeClass("mp-main-menu--shown")
				})
			})
		}).call(this, t(0))
	},
	6: function (n, e, t)
	{
		"use strict";
		(function (n)
		{
			function e(n, e)
			{
				for (var t = 0; t < e.length; t++)
				{
					var o = e[t];
					o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(n, o.key, o)
				}
			}
			var t = {},
				o = function ()
				{
					function o(e, r)
					{
						! function (n, e)
						{
							if (!(n instanceof e)) throw new TypeError("Cannot call a class as a function")
						}(this, o), this.element = e, this.options = n.extend(
						{}, t, r), this.init()
					}
					return function (n, t, o)
					{
						t && e(n.prototype, t), o && e(n, o)
					}(o, [
					{
						key: "init",
						value: function ()
						{
							var n = this;
							this.toggle = this.element.children(".mp-dropdown__toggle"), this.toggle.on("click", function (e)
							{
								e.stopPropagation(), n.open()
							}), this.menu = this.element.children(".mp-dropdown__menu"), this.element.on("click", function ()
							{
								n.close()
							})
						}
					},
					{
						key: "open",
						value: function ()
						{
							this.element.addClass("mp-dropdown--shown")
						}
					},
					{
						key: "close",
						value: function ()
						{
							this.element.removeClass("mp-dropdown--shown")
						}
					}]), o
				}();
			n.fn.dropdown = function (e)
			{
				var t = this;
				return this.each(function ()
				{
					n.data(t, "dropdown") || n.data(t, "dropdown", new o(t, e))
				})
			}
		}).call(this, t(0))
	}
});