<?php

/* 子テーマのfunctions.phpは、親テーマのfunctions.phpより先に読み込まれることに注意してください。 */


/**
 * 親テーマのfunctions.phpのあとで読み込みたいコードはこの中に。
 */
// add_filter('after_setup_theme', function(){
// }, 11);


/**
 * 子テーマでのファイルの読み込み
 */
add_action('wp_enqueue_scripts', function() {

	wp_enqueue_style(
		'pega-noto-sans',
		'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700;800&display=swap',
		[],
		null
	);

	$timestamp = date( 'Ymdgis', filemtime( get_stylesheet_directory() . '/style.css' ) );
	wp_enqueue_style( 'child_style', get_stylesheet_directory_uri() . '/style.css', [ 'pega-noto-sans' ], $timestamp );

	/* トップヘッダーは position:relative のためスクロール追従JSは不使用 */

	wp_enqueue_script(
		'pega-sp-menu',
		get_stylesheet_directory_uri() . '/pega-sp-menu.js',
		[],
		filemtime( get_stylesheet_directory() . '/pega-sp-menu.js' ),
		true
	);

	if ( is_front_page() ) {
		wp_enqueue_script(
			'pega-sticky-cta',
			get_stylesheet_directory_uri() . '/pega-sticky-cta.js',
			[],
			filemtime( get_stylesheet_directory() . '/pega-sticky-cta.js' ),
			true
		);
	}

	/* その他の読み込みファイルはこの下に記述 */

}, 11);

/**
 * SWELL ロゴ画像（旧TCDから未移行のため、既存メディア ID=50 を設定）
 */
add_action( 'after_setup_theme', function() {
	$settings = get_option( 'loos_customizer', [] );
	if ( ! is_array( $settings ) ) {
		$settings = [];
	}
	$updated = false;
	if ( empty( $settings['logo_id'] ) && wp_get_attachment_url( 50 ) ) {
		$settings['logo_id'] = 50;
		$updated = true;
	}
	if ( empty( $settings['spmenu_main_title'] ) || 'MENU' === $settings['spmenu_main_title'] ) {
		$settings['spmenu_main_title'] = 'メニュー';
		$updated = true;
	}
	if ( empty( $settings['acc_submenu'] ) ) {
		$settings['acc_submenu'] = true;
		$updated = true;
	}
	if ( ! empty( $updated ) ) {
		update_option( 'loos_customizer', $settings );
	}
}, 20 );

/**
 * ヘッダー連絡先（ハンバーガー表示時）
 * 電話: 現サイトアクセスページより 0463-74-6683
 * 受付時間: サイトに明示なし → 暫定 平日 9:00〜18:00
 */
function pega_render_header_contact() {
	$tel_display = '0463-74-6683';
	$tel_href    = 'tel:0463746683';
	$hours       = '平日 9:00〜18:00';
	$inquiry_url = home_url( '/contact/' );
	?>
	<div class="pega-header-contact" aria-label="受付時間・お問い合わせ">
		<div class="pega-header-contact__info">
			<p class="pega-header-contact__hours">
				<span class="pega-header-contact__label">受付</span>
				<?php echo esc_html( $hours ); ?>
			</p>
			<p class="pega-header-contact__tel">
				<a href="<?php echo esc_attr( $tel_href ); ?>"><?php echo esc_html( $tel_display ); ?></a>
			</p>
		</div>
		<a class="pega-header-contact__link" href="<?php echo esc_url( $inquiry_url ); ?>">お問い合わせ</a>
	</div>
	<?php
}

/**
 * フッター上部：通所サブ動線（じょうご型3つ目）
 */
function pega_render_footer_commute() {
	$access_url = home_url( '/access/' );
	?>
	<div class="pega-footer__commute">
		<div class="l-container">
			<div class="pega-footer__commute-inner">
				<div class="pega-footer__commute-body">
					<p class="pega-footer__commute-title">通所をご希望の方へ</p>
					<p class="pega-footer__commute-text">
						平塚教室（ペガサス平塚センター）では、対面でのサポートも行っております。JR平塚駅より徒歩8分。
					</p>
				</div>
				<div class="pega-footer__commute-links">
					<a class="pega-footer__commute-btn pega-footer__commute-btn--access" href="<?php echo esc_url( $access_url ); ?>">アクセス</a>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * フッター事業所情報（平塚事業所のみ）
 */
function pega_render_footer_office() {
	$tel_display = '0463-74-6683';
	$tel_href    = 'tel:0463746683';
	?>
	<address class="pega-footer__office">
		<p class="pega-footer__office-name">平塚事業所</p>
		<p class="pega-footer__office-line">神奈川県平塚市明石町1-17 チェアフル湘南3F</p>
		<p class="pega-footer__office-line">
			TEL：<a href="<?php echo esc_attr( $tel_href ); ?>"><?php echo esc_html( $tel_display ); ?></a>
		</p>
		<p class="pega-footer__office-line">JR東海道線 平塚駅より徒歩8分</p>
	</address>
	<?php
}

/**
 * フッターナビ（13件）
 *
 * @return array<string, array<int, array{0: string, 1: string}>>
 */
function pega_get_footer_nav_groups() {
	return [
		'就労サポート' => [
			[ '充実したプログラム', '/program/' ],
			[ '就職前に企業実習', '/practice/' ],
			[ '就職活動サポート', '/result/' ],
			[ '就職後も安心のアフターケア', '/aftercare/' ],
			[ '在宅型就労移行支援サービス', '/homeworker/' ],
		],
		'ご案内'       => [
			[ '法人概要', '/about/' ],
			[ 'アクセス', '/access/' ],
			[ 'ご利用案内', '/guide/' ],
			[ 'スタッフ求人情報', '/recruit/' ],
		],
		'お申し込み・その他' => [
			[ '個別説明会申し込み', '/application/' ],
			[ 'お問い合わせ', '/contact/' ],
			[ '資料請求', '/download/' ],
			[ 'プライバシーポリシー', '/privacy/' ],
		],
	];
}

/**
 * フッターナビ出力
 */
function pega_render_footer_nav() {
	$groups = pega_get_footer_nav_groups();
	?>
	<nav class="pega-footer__nav" aria-label="フッターナビゲーション">
		<?php foreach ( $groups as $label => $items ) : ?>
			<div class="pega-footer__nav-group">
				<p class="pega-footer__nav-heading"><?php echo esc_html( $label ); ?></p>
				<ul class="pega-footer__nav-list">
					<?php foreach ( $items as $item ) : ?>
						<li>
							<a href="<?php echo esc_url( home_url( $item[1] ) ); ?>"><?php echo esc_html( $item[0] ); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endforeach; ?>
	</nav>
	<?php
}

/**
 * X（旧Twitter）アイコン SVG
 */
function pega_footer_x_icon() {
	return '<svg class="pega-footer__sns-icon" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>';
}

/**
 * スクロール追従CTA（トップページのみ・ヒーロー後〜フッター前）
 */
function pega_render_sticky_cta() {
	if ( ! is_front_page() ) {
		return;
	}

	$application_url = home_url( '/application/' );
	$contact_url       = home_url( '/contact/' );
	?>
	<aside id="pega-sticky-cta" class="pega-sticky-cta" aria-hidden="true" aria-label="お申し込み・お問い合わせ">
		<button type="button" class="pega-sticky-cta__close" aria-label="追従ボタンを閉じる">
			<span aria-hidden="true">&times;</span>
		</button>
		<div class="pega-sticky-cta__inner">
			<a class="pega-sticky-cta__btn pega-sticky-cta__btn--primary" href="<?php echo esc_url( $application_url ); ?>">
				<span class="pega-sticky-cta__btn-label">個別説明会（無料）</span>
			</a>
			<a class="pega-sticky-cta__btn pega-sticky-cta__btn--contact" href="<?php echo esc_url( $contact_url ); ?>">
				<span class="pega-sticky-cta__btn-label">お問い合わせ</span>
			</a>
		</div>
	</aside>
	<?php
}
add_action( 'wp_footer', 'pega_render_sticky_cta', 5 );

/**
 * トップページ限定のブランドスコープ class（.pega-top）を付与。
 * SWELL は body_class を #body_wrap に出力するため、.pega-top も #body_wrap に乗る。
 * 目的: SWELL内部クラス上書き（.pega-top #header 等）をトップ限定にスコープし、
 *       SWELL既存クラスとの競合・他ページへの波及を避ける（橋本様ご指摘対応）。
 * 注意: .home（WPコア）と同一要素に付くため .home→.pega-top の付け替えは挙動不変。
 */
function pega_body_class_top( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'pega-top';
	}
	return $classes;
}
add_filter( 'body_class', 'pega_body_class_top' );
