(function () {
	var button = document.querySelector(".nav-toggle");
	var menu = document.getElementById("menu");
	if (!button || !menu) {
		return;
	}

	function setOpen(open) {
		menu.classList.toggle("is-open", open);
		button.setAttribute("aria-expanded", open ? "true" : "false");
		document.body.classList.toggle("nav-open", open);
	}

	button.addEventListener("click", function () {
		setOpen(button.getAttribute("aria-expanded") !== "true");
	});

	menu.addEventListener("click", function (event) {
		if (event.target.closest("a")) {
			setOpen(false);
		}
	});

	document.addEventListener("click", function (event) {
		if (button.getAttribute("aria-expanded") !== "true") {
			return;
		}
		if (event.target.closest("#header")) {
			return;
		}
		setOpen(false);
	});

	document.addEventListener("keydown", function (event) {
		if (event.key === "Escape") {
			setOpen(false);
		}
	});

	window.addEventListener("resize", function () {
		if (window.innerWidth > 980) {
			setOpen(false);
		}
	});
})();
