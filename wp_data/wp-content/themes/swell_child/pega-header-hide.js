(function () {
	'use strict';

	var wrap = document.getElementById('body_wrap');
	if (!wrap || !wrap.classList.contains('pega-top')) {
		return;
	}

	var header = document.getElementById('header');
	if (!header) {
		return;
	}

	var lastY = window.scrollY || 0;
	var ticking = false;
	var threshold = 72;

	function updateHeaderHide() {
		var y = window.scrollY || 0;

		if (y <= threshold) {
			wrap.classList.remove('pega-headhide');
		} else if (y > lastY + 4) {
			wrap.classList.add('pega-headhide');
		} else if (y < lastY - 4) {
			wrap.classList.remove('pega-headhide');
		}

		lastY = y;
		ticking = false;
	}

	window.addEventListener(
		'scroll',
		function () {
			if (!ticking) {
				window.requestAnimationFrame(updateHeaderHide);
				ticking = true;
			}
		},
		{ passive: true }
	);
})();
