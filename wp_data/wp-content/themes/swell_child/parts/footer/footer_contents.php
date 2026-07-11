<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pega-footer">
	<?php
	if ( function_exists( 'pega_render_footer_commute' ) ) {
		pega_render_footer_commute();
	}
	?>
	<div class="l-footer__inner pega-footer__inner">
		<div class="l-container">
			<div class="pega-footer__grid">
				<div class="pega-footer__logo-row">
					<div class="pega-footer__logo">
						<?php echo SWELL_PARTS::head_logo(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				</div>
				<div class="pega-footer__brand">
					<?php
					if ( function_exists( 'pega_render_footer_office' ) ) {
						pega_render_footer_office();
					}
					?>
				</div>
				<?php
				if ( function_exists( 'pega_render_footer_nav' ) ) {
					pega_render_footer_nav();
				}
				?>
			</div>
			<div class="pega-footer__meta">
				<a
					class="pega-footer__sns"
					href="https://twitter.com/pegasusjob"
					target="_blank"
					rel="noopener noreferrer"
					aria-label="X（旧Twitter）@pegasusjob"
				>
					<?php echo pega_footer_x_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="pega-footer__sns-label">@pegasusjob</span>
				</a>
				<p class="copyright pega-footer__copyright">
					Copyright &copy; 一般社団法人ペガサス All Rights Reserved.
				</p>
			</div>
		</div>
	</div>
</div>
