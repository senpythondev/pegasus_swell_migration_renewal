<?php
/**
 * Plugin Name: Pegasus 資料請求ダウンロードシステム（選択肢B）
 * Description: メール限定配布。トークン認証付き /download-secure/ で protected/ のPDFを readfile 配信。リード(メール)収集。
 * Version: 1.0
 *
 * 配置: wp-content/mu-plugins/ に置くと自動有効化（mu-plugin）。ソースは git: scripts/download_system/。
 * ステージング/本番 共通で動作（home_url ベース、PDFパスは protected/）。
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'PEGA_DL_TABLE', 'pega_download_tokens' ); // 実テーブル名 = $wpdb->prefix . この値
define( 'PEGA_DL_EXPIRE_DAYS', 7 );
define( 'PEGA_DL_MAX_USES', 5 );

/** 資料ID → 実ファイル（protected/ 配下）。複数資料に拡張可。 */
function pega_dl_resources() {
	return array(
		566 => array(
			'path' => WP_CONTENT_DIR . '/uploads/protected/pegasus-shiryou.pdf',
			'name' => '一般社団法人ペガサス_在宅支援サービス資料.pdf',
		),
	);
}

/** トークンテーブル作成（mu-pluginは有効化フックが無いため init で一度だけ） */
function pega_dl_install() {
	global $wpdb;
	$table   = $wpdb->prefix . PEGA_DL_TABLE;
	$charset = $wpdb->get_charset_collate();
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( "CREATE TABLE $table (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		token CHAR(64) NOT NULL,
		resource_id BIGINT UNSIGNED NOT NULL,
		email VARCHAR(190) NOT NULL,
		created_at DATETIME NOT NULL,
		expires_at DATETIME NOT NULL,
		max_uses INT NOT NULL DEFAULT 5,
		used_count INT NOT NULL DEFAULT 0,
		PRIMARY KEY  (id),
		UNIQUE KEY token (token),
		KEY resource_id (resource_id)
	) $charset;" );
}
add_action( 'init', function () {
	if ( get_option( 'pega_dl_db_ver' ) !== '1' ) {
		pega_dl_install();
		update_option( 'pega_dl_db_ver', '1' );
	}
}, 1 );

/** トークン発行（64桁hex）。返り値=token */
function pega_dl_issue_token( $email, $resource_id ) {
	global $wpdb;
	$table = $wpdb->prefix . PEGA_DL_TABLE;
	$token = bin2hex( random_bytes( 32 ) );
	$now_ts = current_time( 'timestamp' );
	$wpdb->insert( $table, array(
		'token'       => $token,
		'resource_id' => (int) $resource_id,
		'email'       => $email,
		'created_at'  => current_time( 'mysql' ),
		'expires_at'  => date( 'Y-m-d H:i:s', $now_ts + PEGA_DL_EXPIRE_DAYS * DAY_IN_SECONDS ),
		'max_uses'    => PEGA_DL_MAX_USES,
		'used_count'  => 0,
	), array( '%s', '%d', '%s', '%s', '%s', '%d', '%d' ) );
	return $token;
}

/** rewrite: /download-secure/ */
add_action( 'init', function () {
	add_rewrite_rule( '^download-secure/?$', 'index.php?pega_dl=1', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { $v[] = 'pega_dl'; return $v; } );

/** エンドポイント: トークン検証 → readfile 配信 / 回数+1 */
add_action( 'template_redirect', 'pega_dl_serve' );
function pega_dl_serve() {
	if ( ! get_query_var( 'pega_dl' ) ) { return; }

	$token = isset( $_GET['token'] ) ? preg_replace( '/[^a-f0-9]/', '', (string) $_GET['token'] ) : '';
	$rid   = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
	if ( strlen( $token ) !== 64 || ! $rid ) { pega_dl_fail( '不正なURLです。' ); }

	global $wpdb;
	$table = $wpdb->prefix . PEGA_DL_TABLE;
	$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE token=%s AND resource_id=%d", $token, $rid ) );
	if ( ! $row ) { pega_dl_fail( 'トークンが無効です。' ); }
	if ( strtotime( $row->expires_at ) < current_time( 'timestamp' ) ) { pega_dl_fail( '有効期限（7日）が切れています。お手数ですが再度ご請求ください。' ); }
	if ( (int) $row->used_count >= (int) $row->max_uses ) { pega_dl_fail( 'ダウンロード回数の上限に達しました。' ); }

	$res = pega_dl_resources();
	if ( empty( $res[ $rid ] ) || ! is_readable( $res[ $rid ]['path'] ) ) { pega_dl_fail( 'ファイルが見つかりません。' ); }

	$wpdb->update( $table, array( 'used_count' => (int) $row->used_count + 1 ), array( 'id' => $row->id ) );

	$f = $res[ $rid ];
	nocache_headers();
	header( 'Content-Type: application/pdf' );
	header( 'Content-Disposition: attachment; filename="' . rawurlencode( $f['name'] ) . '"' );
	header( 'Content-Length: ' . filesize( $f['path'] ) );
	readfile( $f['path'] );
	exit;
}
function pega_dl_fail( $msg ) {
	status_header( 410 ); // Gone（期限/回数/不正）
	nocache_headers();
	wp_die( esc_html( $msg ), '資料ダウンロード', array( 'response' => 410 ) );
}

/** CF7 資料請求フォーム送信時：トークン発行 → 自動返信(Mail2)の {{DOWNLOAD_URL}} を置換 */
add_action( 'wpcf7_before_send_mail', 'pega_dl_cf7_issue', 10, 1 );
function pega_dl_cf7_issue( $cf7 ) {
	$form_id = (int) get_option( 'pega_dl_form_id' );
	if ( ! $form_id || (int) $cf7->id() !== $form_id ) { return; }
	$sub = WPCF7_Submission::get_instance();
	if ( ! $sub ) { return; }
	$data  = $sub->get_posted_data();
	$email = isset( $data['your-email'] ) ? sanitize_email( $data['your-email'] ) : '';
	$rid   = isset( $data['resource_id'] ) ? (int) $data['resource_id'] : 566;
	if ( ! $email ) { return; }

	$token = pega_dl_issue_token( $email, $rid );
	$url   = home_url( '/download-secure/?token=' . $token . '&id=' . $rid );
	add_filter( 'wpcf7_mail_components', function ( $c ) use ( $url ) {
		if ( ! empty( $c['body'] ) ) { $c['body'] = str_replace( '{{DOWNLOAD_URL}}', $url, $c['body'] ); }
		return $c;
	}, 10, 3 );
}
