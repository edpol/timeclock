(function () {
    let NS = "http://www.w3.org/2000/svg";
    let ticks = document.getElementById("ticks");
    let nums = document.getElementById("nums");

    for (let i = 0; i < 60; i++) {
        let a = i * 6 * Math.PI / 180;
        let major = i % 5 === 0;
        let r1 = major ? 80 : 86, r2 = 92;
        let l = document.createElementNS(NS, "line");
        l.setAttribute("x1", (Math.sin(a) * r1).toFixed(2));
        l.setAttribute("y1", (-Math.cos(a) * r1).toFixed(2));
        l.setAttribute("x2", (Math.sin(a) * r2).toFixed(2));
        l.setAttribute("y2", (-Math.cos(a) * r2).toFixed(2));
        l.setAttribute("class", "tick" + (major ? " major" : ""));
        ticks.appendChild(l);
    }
    for (let h = 1; h <= 12; h++) {
        let b = h * 30 * Math.PI / 180;
        let t = document.createElementNS(NS, "text");
        t.setAttribute("x", (Math.sin(b) * 64).toFixed(2));
        t.setAttribute("y", (-Math.cos(b) * 64).toFixed(2));
        t.setAttribute("class", "num");
        t.textContent = h;
        nums.appendChild(t);
    }

    let hh = document.getElementById("hh");
    let mh = document.getElementById("mh");
    let sh = document.getElementById("sh");
    let timeEl = document.getElementById("time");
    let dateEl = document.getElementById("date");
    let zoneEl = document.getElementById("zone");

    let use24 = false, showSec = true;
    function pad(n) { return n < 10 ? "0" + n : "" + n; }

    let tz = "";
    try { tz = Intl.DateTimeFormat().resolvedOptions().timeZone || ""; } catch (e) {}
    zoneEl.textContent = tz.replace(/_/g, " ");

    function tick() {
        let d = new Date();
        let ms = d.getMilliseconds();
        let s = d.getSeconds() + ms / 1000;
        let m = d.getMinutes() + s / 60;
        let h = (d.getHours() % 12) + m / 60;

        hh.setAttribute("transform", "rotate(" + (h * 30) + ")");
        mh.setAttribute("transform", "rotate(" + (m * 6) + ")");
        sh.setAttribute("transform", "rotate(" + (s * 6) + ")");

        let H = d.getHours();
        let suffix = "";
        if (!use24) { suffix = H >= 12 ? "PM" : "AM"; H = H % 12 || 12; }
        let txt = (use24 ? pad(H) : H) + ":" + pad(d.getMinutes()) + (showSec ? ":" + pad(d.getSeconds()) : "");
        timeEl.innerHTML = txt + (suffix ? '<span class="ampm">' + suffix + "</span>" : "");

        dateEl.textContent = d.toLocaleDateString(undefined, { weekday: "long", year: "numeric", month: "long", day: "numeric" });
        requestAnimationFrame(tick);
    }

    requestAnimationFrame(tick);
})();
