(function () {
	'use strict';

	function initStickyCta() {
		var bar = document.getElementById('pega-sticky-cta');
		var hero = document.querySelector('.pega-hero-wrap');
		var footer = document.getElementById('footer');

		if (!bar || !hero) {
			return;
		}

		var dismissed = false;
		var heroPassed = false;
		var footerVisible = false;

		function syncHeroPassed() {
			var rect = hero.getBoundingClientRect();
			heroPassed = rect.bottom <= 0;
		}

		function syncFooterVisible() {
			if (!footer) {
				footerVisible = false;
				return;
			}
			var rect = footer.getBoundingClientRect();
			var viewHeight = window.innerHeight || document.documentElement.clientHeight;
			footerVisible = rect.top < viewHeight * 0.92;
		}

		var bodyWrap = document.getElementById('body_wrap');

		function setStickyActive(active) {
			document.body.classList.toggle('pega-has-sticky-cta', active);
			if (bodyWrap) {
				bodyWrap.classList.toggle('pega-has-sticky-cta', active);
			}
		}

		function updateVisibility() {
			if (dismissed) {
				bar.classList.remove('is-visible');
				bar.setAttribute('aria-hidden', 'true');
				setStickyActive(false);
				return;
			}
			syncHeroPassed();
			syncFooterVisible();
			var show = heroPassed && !footerVisible;
			bar.classList.toggle('is-visible', show);
			bar.setAttribute('aria-hidden', show ? 'false' : 'true');
			setStickyActive(show);
		}

		if ('IntersectionObserver' in window) {
			var heroObserver = new IntersectionObserver(
				function () {
					updateVisibility();
				},
				{ threshold: [0, 0.01, 0.1] }
			);
			heroObserver.observe(hero);

			if (footer) {
				var footerObserver = new IntersectionObserver(
					function () {
						updateVisibility();
					},
					{ threshold: 0, rootMargin: '0px 0px -5% 0px' }
				);
				footerObserver.observe(footer);
			}
		}

		window.addEventListener('scroll', updateVisibility, { passive: true });
		window.addEventListener('resize', updateVisibility, { passive: true });

		var closeBtn = bar.querySelector('.pega-sticky-cta__close');
		if (closeBtn) {
			closeBtn.addEventListener('click', function () {
				dismissed = true;
				updateVisibility();
			});
		}

		updateVisibility();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initStickyCta);
	} else {
		initStickyCta();
	}
})();
