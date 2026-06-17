<?php
/**
 * Plugin Name: Techcomputer - Corrección post-migración
 * Description: Arregla URLs de Local/Elementor tras importar la base de datos en producción.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TC_URL_MIGRATION_FIX_VERSION', '2' );

add_action( 'init', 'tc_maybe_run_url_migration_fix', 1 );
add_action( 'admin_init', 'tc_maybe_run_url_migration_fix_on_admin', 5 );

/**
 * Dominios heredados que pueden quedar en la BD tras migrar.
 *
 * @return string[]
 */
function tc_legacy_site_hosts() {
	return array(
		'techcomputerv3.local',
		'localhost',
		'127.0.0.1',
		'royalblue-dunlin-248831.hostingersite.com',
	);
}

function tc_current_site_base_url() {
	if ( defined( 'WP_HOME' ) && WP_HOME ) {
		return untrailingslashit( WP_HOME );
	}
	if ( defined( 'WP_SITEURL' ) && WP_SITEURL ) {
		return untrailingslashit( WP_SITEURL );
	}
	return untrailingslashit( home_url() );
}

/**
 * Reescribe medios de /wp-content/uploads/ al dominio actual.
 */
function tc_remap_legacy_media_url( $url ) {
	if ( ! is_string( $url ) || '' === $url ) {
		return $url;
	}

	if ( preg_match( '#^(https?:)?//#i', $url ) && ! preg_match( '#/wp-content/uploads/#i', $url ) ) {
		return $url;
	}

	if ( preg_match( '#/wp-content/uploads/(.+)$#i', $url, $matches ) ) {
		return trailingslashit( wp_get_upload_dir()['baseurl'] ) . ltrim( $matches[1], '/' );
	}

	return $url;
}

/**
 * @param mixed $value
 * @return mixed
 */
function tc_replace_legacy_urls_deep( $value ) {
	if ( is_string( $value ) ) {
		$updated = $value;
		$base    = tc_current_site_base_url();

		foreach ( tc_legacy_site_hosts() as $host ) {
			$updated = str_replace( 'http://' . $host, $base, $updated );
			$updated = str_replace( 'https://' . $host, $base, $updated );
		}

		$updated = str_replace( 'http://techcomputer.cl', $base, $updated );
		$updated = str_replace( 'https://techcomputer.cl', $base, $updated );

		if ( str_contains( $updated, '/wp-content/uploads/' ) ) {
			$updated = tc_remap_legacy_media_url( $updated );
		}

		return $updated;
	}

	if ( is_array( $value ) ) {
		foreach ( $value as $key => $item ) {
			$value[ $key ] = tc_replace_legacy_urls_deep( $item );
		}
	}

	return $value;
}

function tc_site_has_legacy_urls() {
	global $wpdb;

	$hosts = tc_legacy_site_hosts();
	foreach ( array( 'siteurl', 'home' ) as $option ) {
		$value = (string) get_option( $option, '' );
		foreach ( $hosts as $host ) {
			if ( str_contains( $value, $host ) ) {
				return true;
			}
		}
	}

	$like = '%techcomputerv3.local%';
	$hit  = $wpdb->get_var( $wpdb->prepare( "SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_key = '_elementor_data' AND meta_value LIKE %s LIMIT 1", $like ) );
	return ! empty( $hit );
}

function tc_fix_wp_site_options() {
	$base = tc_current_site_base_url();
	if ( ! $base ) {
		return;
	}

	foreach ( array( 'siteurl', 'home' ) as $option ) {
		$value = (string) get_option( $option, '' );
		if ( '' === $value ) {
			continue;
		}
		foreach ( tc_legacy_site_hosts() as $host ) {
			if ( str_contains( $value, $host ) ) {
				update_option( $option, $base );
				break;
			}
		}
	}
}

function tc_fix_elementor_post( $post_id ) {
	$raw = get_post_meta( $post_id, '_elementor_data', true );
	if ( empty( $raw ) ) {
		return false;
	}

	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) ) {
		return false;
	}

	$data = tc_replace_legacy_urls_deep( $data );
	if ( function_exists( 'tc_process_elementor_elements' ) ) {
		$context = function_exists( 'tc_elementor_context_for_post' ) ? tc_elementor_context_for_post( $post_id ) : array();
		$data    = tc_process_elementor_elements( $data, $context );
	}

	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
	return true;
}

function tc_purge_elementor_css_cache() {
	$css_dir = WP_CONTENT_DIR . '/uploads/elementor/css/';
	if ( is_dir( $css_dir ) ) {
		foreach ( glob( trailingslashit( $css_dir ) . '*.css' ) as $file ) {
			if ( is_file( $file ) ) {
				wp_delete_file( $file );
			}
		}
	}

	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
}

function tc_fix_elementor_css_files_on_disk() {
	$css_dir = WP_CONTENT_DIR . '/uploads/elementor/css/';
	if ( ! is_dir( $css_dir ) ) {
		return 0;
	}

	$count = 0;
	foreach ( glob( trailingslashit( $css_dir ) . '*.css' ) as $file ) {
		$contents = file_get_contents( $file );
		if ( false === $contents ) {
			continue;
		}
		$fixed = tc_replace_legacy_urls_deep( $contents );
		if ( $fixed !== $contents ) {
			file_put_contents( $file, $fixed );
			++$count;
		}
	}
	return $count;
}

/**
 * @return array{posts:int,css:int}
 */
function tc_run_url_migration_fix() {
	global $wpdb;

	tc_fix_wp_site_options();

	$post_ids = $wpdb->get_col(
		"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_elementor_edit_mode' AND meta_value = 'builder'"
	);

	$fixed_posts = 0;
	foreach ( array_map( 'intval', (array) $post_ids ) as $post_id ) {
		if ( tc_fix_elementor_post( $post_id ) ) {
			++$fixed_posts;
		}
	}

	$fixed_css = tc_fix_elementor_css_files_on_disk();
	tc_purge_elementor_css_cache();

	if ( function_exists( 'tc_apply_techcomputer_brand_colors' ) ) {
		tc_apply_techcomputer_brand_colors();
	}

	update_option( 'tc_url_migration_fix_version', TC_URL_MIGRATION_FIX_VERSION, false );
	flush_rewrite_rules( false );

	return array(
		'posts' => $fixed_posts,
		'css'   => $fixed_css,
	);
}

function tc_maybe_run_url_migration_fix() {
	if ( get_option( 'tc_url_migration_fix_version' ) === TC_URL_MIGRATION_FIX_VERSION ) {
		return;
	}
	if ( ! tc_site_has_legacy_urls() ) {
		update_option( 'tc_url_migration_fix_version', TC_URL_MIGRATION_FIX_VERSION, false );
		return;
	}
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		tc_run_url_migration_fix();
		return;
	}
	if ( is_admin() && current_user_can( 'manage_options' ) ) {
		return;
	}
}

function tc_maybe_run_url_migration_fix_on_admin() {
	if ( get_option( 'tc_url_migration_fix_version' ) === TC_URL_MIGRATION_FIX_VERSION ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! tc_site_has_legacy_urls() ) {
		update_option( 'tc_url_migration_fix_version', TC_URL_MIGRATION_FIX_VERSION, false );
		return;
	}
	$result = tc_run_url_migration_fix();
	set_transient(
		'tc_url_migration_fix_notice',
		sprintf(
			'URLs de migración corregidas: %d páginas Elementor y %d archivos CSS.',
			(int) $result['posts'],
			(int) $result['css']
		),
		120
	);
}

add_action( 'admin_notices', 'tc_url_migration_fix_admin_notice' );

function tc_url_migration_fix_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$message = get_transient( 'tc_url_migration_fix_notice' );
	if ( ! $message ) {
		return;
	}
	delete_transient( 'tc_url_migration_fix_notice' );
	echo '<div class="notice notice-success is-dismissible"><p><strong>Techcomputer:</strong> ' . esc_html( $message ) . '</p></div>';
}

function tc_force_url_migration_fix() {
	delete_option( 'tc_url_migration_fix_version' );
	return tc_run_url_migration_fix();
}
