(function () {
    var NS = "http://www.w3.org/2000/svg";
    var ticks = document.getElementById("ticks");
    var nums = document.getElementById("nums");

    for (var i = 0; i < 60; i++) {
        var a = i * 6 * Math.PI / 180;
        var major = i % 5 === 0;
        var r1 = major ? 80 : 86, r2 = 92;
        var l = document.createElementNS(NS, "line");
        l.setAttribute("x1", (Math.sin(a) * r1).toFixed(2));
        l.setAttribute("y1", (-Math.cos(a) * r1).toFixed(2));
        l.setAttribute("x2", (Math.sin(a) * r2).toFixed(2));
        l.setAttribute("y2", (-Math.cos(a) * r2).toFixed(2));
        l.setAttribute("class", "tick" + (major ? " major" : ""));
        ticks.appendChild(l);
    }
    for (var h = 1; h <= 12; h++) {
        var b = h * 30 * Math.PI / 180;
        var t = document.createElementNS(NS, "text");
        t.setAttribute("x", (Math.sin(b) * 64).toFixed(2));
        t.setAttribute("y", (-Math.cos(b) * 64).toFixed(2));
        t.setAttribute("class", "num");
        t.textContent = h;
        nums.appendChild(t);
    }

    var hh = document.getElementById("hh");
    var mh = document.getElementById("mh");
    var sh = document.getElementById("sh");
    var timeEl = document.getElementById("time");
    var dateEl = document.getElementById("date");
    var zoneEl = document.getElementById("zone");

    var use24 = false, showSec = true;
    function pad(n) { return n < 10 ? "0" + n : "" + n; }

    var tz = "";
    try { tz = Intl.DateTimeFormat().resolvedOptions().timeZone || ""; } catch (e) {}
    zoneEl.textContent = tz.replace(/_/g, " ");

    function tick() {
        var d = new Date();
        var ms = d.getMilliseconds();
        var s = d.getSeconds() + ms / 1000;
        var m = d.getMinutes() + s / 60;
        var h = (d.getHours() % 12) + m / 60;

        hh.setAttribute("transform", "rotate(" + (h * 30) + ")");
        mh.setAttribute("transform", "rotate(" + (m * 6) + ")");
        sh.setAttribute("transform", "rotate(" + (s * 6) + ")");

        var H = d.getHours();
        var suffix = "";
        if (!use24) { suffix = H >= 12 ? "PM" : "AM"; H = H % 12 || 12; }
        var txt = (use24 ? pad(H) : H) + ":" + pad(d.getMinutes()) + (showSec ? ":" + pad(d.getSeconds()) : "");
        timeEl.innerHTML = txt + (suffix ? '<span class="ampm">' + suffix + "</span>" : "");

        dateEl.textContent = d.toLocaleDateString(undefined, { weekday: "long", year: "numeric", month: "long", day: "numeric" });
        requestAnimationFrame(tick);
    }

    requestAnimationFrame(tick);
})();
