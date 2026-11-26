(function () {
	var INTERVAL = 5;
	var FADE = 1.5;
	var root = document.getElementById("mainImg");
	if (!root) {
		return;
	}

	var imgs = root.querySelectorAll(":scope > img");
	var count = imgs.length;
	if (count < 2) {
		return;
	}

	var cycle = count * INTERVAL;
	var fadeIn = FADE / cycle;
	var hold = INTERVAL / cycle;
	var fadeOut = (INTERVAL + FADE) / cycle;
	var eps = 0.00001;

	function percent(value) {
		return (value * 100).toFixed(4) + "%";
	}

	var css =
		"@keyframes mainv-fade{" +
		"0%{opacity:0;z-index:2}" +
		percent(fadeIn) + "{opacity:1;z-index:2}" +
		percent(hold) + "{opacity:1;z-index:2}" +
		percent(hold + eps) + "{opacity:1;z-index:1}" +
		percent(fadeOut) + "{opacity:1;z-index:1}" +
		percent(Math.min(fadeOut + eps, 0.9999)) + "{opacity:0;z-index:0}" +
		"100%{opacity:0;z-index:0}}";

	var style = document.getElementById("mainv-keyframes");
	if (!style) {
		style = document.createElement("style");
		style.id = "mainv-keyframes";
		document.head.appendChild(style);
	}
	style.textContent = css;

	root.style.setProperty("--mainv-duration", cycle + "s");
	for (var i = 0; i < count; i++) {
		imgs[i].style.setProperty("--mainv-delay", -FADE + i * INTERVAL + "s");
	}
	root.classList.add("is-ready");
})();
