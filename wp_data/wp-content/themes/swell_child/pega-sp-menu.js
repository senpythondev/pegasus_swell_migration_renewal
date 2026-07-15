(function () {
	'use strict';

	function closeAllSubmenus(nav) {
		if (!nav) return;
		nav.querySelectorAll('.c-submenuToggleBtn.is-opened').forEach(function (btn) {
			btn.classList.remove('is-opened');
			var sub = btn.parentNode && btn.parentNode.nextElementSibling;
			if (sub) sub.classList.remove('is-opened');
		});
	}

	function initSpMenuAccordion() {
		var nav = document.querySelector('#sp_menu .c-spnav');
		if (!nav) return;

		nav.addEventListener('click', function (e) {
			var btn = e.target.closest('.c-submenuToggleBtn');
			if (!btn || !nav.contains(btn)) return;
			if (!btn.classList.contains('is-opened')) {
				closeAllSubmenus(nav);
			}
		}, true);
	}

	function initMenuBtnTouch() {
		document.querySelectorAll('.l-header__menuBtn [data-onclick="toggleMenu"]').forEach(function (btn) {
			btn.addEventListener('touchend', function (e) {
				if (e.cancelable) e.preventDefault();
				btn.click();
			}, { passive: false });
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			initSpMenuAccordion();
			initMenuBtnTouch();
		});
	} else {
		initSpMenuAccordion();
		initMenuBtnTouch();
	}
})();
