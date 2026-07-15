(function () {
	'use strict';

	function prefersReducedMotion() {
		return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	}

	function revealAll(targets) {
		targets.forEach(function (el) {
			el.classList.add('is-visible');
		});
	}

	function initInview() {
		var targets = document.querySelectorAll('.pega-animate');
		if (!targets.length) {
			return;
		}

		document.documentElement.classList.add('pega-motion-js');

		if (prefersReducedMotion()) {
			revealAll(targets);
			return;
		}

		if (!('IntersectionObserver' in window)) {
			revealAll(targets);
			return;
		}

		var observer = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (!entry.isIntersecting) {
						return;
					}
					entry.target.classList.add('is-visible');
					observer.unobserve(entry.target);
				});
			},
			{
				threshold: 0.15,
				rootMargin: '0px 0px -8% 0px',
			}
		);

		targets.forEach(function (el) {
			observer.observe(el);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initInview);
	} else {
		initInview();
	}
})();
