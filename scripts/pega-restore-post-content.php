<?php
/**
 * post_content バックアップファイルから復元
 *
 * Usage:
 *   docker cp scripts/pega-restore-post-content.php pegasus_wp:/tmp/
 *   docker cp docs/block-backups/post-4941-....txt pegasus_wp:/tmp/post-content.txt
 *   docker exec pegasus_wp wp eval-file /tmp/pega-restore-post-content.php --allow-root -- 4941 /tmp/post-content.txt
 */

if ( ! function_exists( 'wp_update_post' ) ) {
	echo "WordPress not loaded\n";
	exit( 1 );
}

$args = array_slice( $argv ?? [], 1 );
if ( count( $args ) < 2 ) {
	echo "Usage: wp eval-file pega-restore-post-content.php -- POST_ID /path/to/backup.txt\n";
	exit( 1 );
}

$post_id = (int) $args[0];
$path    = $args[1];

if ( ! file_exists( $path ) ) {
	echo "File not found: $path\n";
	exit( 1 );
}

$content = file_get_contents( $path );
if ( $content === false || $content === '' ) {
	echo "Empty or unreadable backup: $path\n";
	exit( 1 );
}

$result = wp_update_post(
	[
		'ID'           => $post_id,
		'post_content' => $content,
	],
	true
);

if ( is_wp_error( $result ) ) {
	echo "POST $post_id: ERROR " . $result->get_error_message() . "\n";
	exit( 1 );
}

echo "POST $post_id: restored from $path (" . strlen( $content ) . " bytes)\n";
