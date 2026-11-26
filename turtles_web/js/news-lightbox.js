(function () {
	var buttons = document.querySelectorAll(".news-thumb-button");
	if (!buttons.length || typeof HTMLDialogElement === "undefined") {
		return;
	}

	var dialog = document.createElement("dialog");
	dialog.className = "news-lightbox";
	dialog.setAttribute("aria-label", "拡大画像");

	var close = document.createElement("button");
	close.type = "button";
	close.className = "news-lightbox-close";
	close.setAttribute("aria-label", "閉じる");
	close.textContent = "×";

	var image = document.createElement("img");
	image.alt = "";

	dialog.appendChild(close);
	dialog.appendChild(image);
	document.body.appendChild(dialog);

	function openImage(button) {
		var thumb = button.querySelector("img");
		image.src = button.getAttribute("data-full");
		image.alt = thumb ? thumb.alt : "";
		dialog.showModal();
		close.focus();
	}

	buttons.forEach(function (button) {
		button.addEventListener("click", function () {
			openImage(button);
		});
	});

	close.addEventListener("click", function () {
		dialog.close();
	});

	dialog.addEventListener("click", function (event) {
		if (event.target === dialog) {
			dialog.close();
		}
	});

	dialog.addEventListener("close", function () {
		image.removeAttribute("src");
		image.alt = "";
	});
})();
