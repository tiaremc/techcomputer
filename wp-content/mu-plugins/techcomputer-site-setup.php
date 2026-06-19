<?php
/**
 * Plugin Name: Techcomputer - Configuración del sitio
 * Description: Header/footer HFE, productos filtrados, botones con enlaces y páginas principales.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TC_WHATSAPP', 'https://wa.me/56932194619' );
define( 'TC_PHONE', '+56 9 3219 4619' );
define( 'TC_EMAIL', 'ventas@techcomputer.cl' );
define( 'TC_ADDRESS', 'Los Militares 5620, Oficina 1801, Las Condes' );
define( 'TC_HOURS', "Lunes - Viernes 10:00 am - 17:30 hrs\nSábado 11:00 a 15:00 hrs" );
define( 'TC_KIT_FOLDER', '6e624946d31ff0b4561c6367b546c446' );
define( 'TC_IDX_HOME', 12 );
define( 'TC_IDX_FOOTER', 13 );
define( 'TC_IDX_HEADER', 14 );
define( 'TC_IDX_ABOUT', 11 );
define( 'TC_IDX_CONTACT', 6 );
define( 'TC_IDX_PRODUCT', 9 );
define( 'TC_SETUP_VERSION', '45' );
define( 'TC_IDX_GLOBAL', 0 );

add_filter( 'hello_elementor_header_footer', 'tc_disable_hello_header_when_hfe' );
add_filter( 'elementor/frontend/builder_content_data', 'tc_filter_elementor_content', 999, 2 );
add_action( 'init', 'tc_ensure_hfe_canvas_header_display', 5 );
add_action( 'init', 'tc_migrate_header_logo_slot', 15 );
add_action( 'init', 'tc_cleanup_duplicate_page_headers', 16 );
add_action( 'init', 'tc_maybe_runtime_sync', 20 );
add_action( 'elementor/page_templates/canvas/before_content', 'tc_prevent_double_hfe_header_in_editor', 0 );
add_action( 'wp_enqueue_scripts', 'tc_enqueue_brand_styles', 99 );
add_action( 'elementor/editor/after_enqueue_styles', 'tc_enqueue_brand_styles' );
add_action( 'wp_enqueue_scripts', 'tc_enqueue_directions_assets', 100 );
add_action( 'wp_enqueue_scripts', 'tc_enqueue_shop_catalog_assets', 101 );
add_action( 'wp_enqueue_scripts', 'tc_enqueue_single_product_assets', 102 );
add_action( 'wp_enqueue_scripts', 'tc_enqueue_home_responsive_assets', 9999 );
add_action( 'wp_enqueue_scripts', 'tc_enqueue_header_mobile_fix', 99999 );
add_action( 'wp', 'tc_single_product_bootstrap' );
add_filter( 'the_content', 'tc_append_directions_to_home', 99 );
add_shortcode( 'tc_shop_catalog', 'tc_shop_catalog_shortcode' );
add_shortcode( 'tc_contact_form', 'tc_contact_form_shortcode' );
add_shortcode( 'tc_contact_form_panel', 'tc_contact_form_panel_shortcode' );
add_shortcode( 'tc_contact_whatsapp_card', 'tc_contact_whatsapp_card_shortcode' );
add_shortcode( 'tc_contact_breadcrumb', 'tc_contact_breadcrumb_shortcode' );
add_action( 'wp_enqueue_scripts', 'tc_enqueue_contact_assets', 102 );
add_action( 'wp_footer', 'tc_render_contact_page_directions', 18 );
add_action( 'wp_footer', 'tc_render_whatsapp_float', 25 );
add_action( 'admin_post_tc_contact_form', 'tc_handle_contact_form' );
add_action( 'admin_post_nopriv_tc_contact_form', 'tc_handle_contact_form' );

function tc_disable_hello_header_when_hfe( $show ) {
	if ( function_exists( 'hfe_header_enabled' ) && ( hfe_header_enabled() || hfe_footer_enabled() ) ) {
		return false;
	}
	return $show;
}

function tc_filter_elementor_content( $data, $post_id ) {
	if ( empty( $data ) || ! is_array( $data ) ) {
		return $data;
	}
	$context = tc_elementor_context_for_post( $post_id );
	if ( empty( $context['header_template'] ) && tc_elementor_data_is_header( $data ) ) {
		$context['header_template'] = true;
	}
	return tc_process_elementor_elements( $data, $context );
}

function tc_elementor_data_is_header( $elements ) {
	foreach ( (array) $elements as $element ) {
		if ( '3e5e84b8' === ( $element['id'] ?? '' ) ) {
			return true;
		}
		if ( ! empty( $element['elements'] ) && tc_elementor_data_is_header( $element['elements'] ) ) {
			return true;
		}
	}
	return false;
}

/** Paleta extraída de techcomputer.cl (verde marca + azul WooCommerce). */
function tc_brand_palette() {
	return array(
		'primary'   => '#528A31',
		'primary_d' => '#4A893E',
		'blue'      => '#0066B4',
		'blue_d'    => '#044A80',
		'dark'      => '#111111',
		'body'      => '#333333',
		'muted'     => '#767676',
		'white'     => '#FFFFFF',
		'section'   => '#F8F9FA',
		'card'      => '#F4F8F2',
		'tint'      => '#EEF4EA',
	);
}

function tc_should_preserve_elementor_media() {
	return (bool) get_option( 'tc_preserve_elementor_media', true );
}

function tc_is_kit_demo_media_url( $url ) {
	if ( ! is_string( $url ) || '' === $url ) {
		return false;
	}
	foreach ( array( 'nvb.nirmanavisual.com', 'nirmanavisual.com', '/electromart/' ) as $needle ) {
		if ( str_contains( $url, $needle ) ) {
			return true;
		}
	}
	return false;
}

function tc_brand_logo_file_candidates() {
	return array(
		WP_CONTENT_DIR . '/uploads/2026/06/techcomputer-logo.webp',
		WP_CONTENT_DIR . '/uploads/2026/06/techcomputer-logo.png',
		WP_CONTENT_DIR . '/mu-plugins/assets/techcomputer-logo.webp',
		WP_CONTENT_DIR . '/mu-plugins/assets/techcomputer-logo.png',
	);
}

function tc_brand_logo_remote_url() {
	return 'https://royalblue-dunlin-248831.hostingersite.com/wp-content/uploads/2025/11/techcomputer-servicio-tecnico-notebook-pc1.webp';
}

function tc_brand_logo_file_exists() {
	foreach ( tc_brand_logo_file_candidates() as $path ) {
		if ( file_exists( $path ) ) {
			return true;
		}
	}
	return false;
}

function tc_ensure_brand_logo_asset() {
	if ( tc_brand_logo_file_exists() ) {
		return true;
	}

	$dir = WP_CONTENT_DIR . '/mu-plugins/assets';
	if ( ! wp_mkdir_p( $dir ) ) {
		return false;
	}

	$response = wp_remote_get(
		tc_brand_logo_remote_url(),
		array(
			'timeout'   => 20,
			'sslverify' => false,
		)
	);
	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		return false;
	}

	$body = wp_remote_retrieve_body( $response );
	if ( ! $body ) {
		return false;
	}

	$dest = trailingslashit( $dir ) . 'techcomputer-logo.webp';
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	return false !== file_put_contents( $dest, $body );
}

function tc_clear_elementor_files_cache() {
	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
}

/**
 * Aplica cambios de código sin panel admin. Solo logo + header; no toca home/tienda.
 */
function tc_maybe_runtime_sync() {
	if ( (string) get_option( 'tc_setup_version', '' ) === TC_SETUP_VERSION ) {
		return;
	}

	tc_ensure_hfe_canvas_header_display();
	tc_migrate_header_logo_slot();

	if ( function_exists( 'wc_get_product' ) ) {
		tc_ensure_featured_service_products();
	}

	$home_id = tc_get_home_page_id();
	if ( $home_id > 0 && 'builder' === get_post_meta( $home_id, '_elementor_edit_mode', true ) ) {
		tc_persist_elementor_data( $home_id );
	}
	if ( function_exists( 'get_hfe_header_id' ) && get_hfe_header_id() ) {
		tc_persist_elementor_data( get_hfe_header_id() );
	}
	$contact_id = tc_get_contact_page_id();
	if ( $contact_id > 0 && 'builder' === get_post_meta( $contact_id, '_elementor_edit_mode', true ) ) {
		tc_persist_elementor_data( $contact_id );
	}
	tc_clear_elementor_files_cache();
	update_option( 'tc_setup_version', TC_SETUP_VERSION, false );
}

function tc_is_header_logo_slot_element( $element ) {
	if ( '3e5e84b8' === ( $element['id'] ?? '' ) ) {
		return true;
	}

	$classes = (string) ( $element['settings']['_css_classes'] ?? '' );
	if ( str_contains( $classes, 'tc-header-logo' ) ) {
		return true;
	}

	if ( 'heading' === ( $element['widgetType'] ?? '' ) ) {
		$title = (string) ( $element['settings']['title'] ?? '' );
		if ( false !== stripos( $title, 'techcomputer' ) ) {
			return true;
		}
	}

	return false;
}

function tc_scan_header_for_user_logo_image( $elements ) {
	foreach ( (array) $elements as $element ) {
		if ( tc_header_logo_has_user_image( $element ) && ! tc_is_header_logo_slot_element( $element ) ) {
			return $element['settings']['image'];
		}
		if ( ! empty( $element['elements'] ) ) {
			$found = tc_scan_header_for_user_logo_image( $element['elements'] );
			if ( $found ) {
				return $found;
			}
		}
	}
	return null;
}

function tc_migrate_header_logo_slot_in_elements( &$elements, $user_logo, &$changed ) {
	foreach ( $elements as &$element ) {
		if ( tc_is_header_logo_slot_element( $element ) ) {
			if ( tc_header_logo_has_user_image( $element ) ) {
				$element['settings']['_css_classes'] = trim( ( $element['settings']['_css_classes'] ?? '' ) . ' tc-header-logo' );
				continue;
			}

			$image       = $user_logo ? $user_logo : tc_header_logo_placeholder_for_elementor();
			$placeholder = ! $user_logo;
			$element     = tc_build_header_logo_image_element( $image, $placeholder );
			$changed     = true;
			continue;
		}

		if ( ! empty( $element['elements'] ) ) {
			tc_migrate_header_logo_slot_in_elements( $element['elements'], $user_logo, $changed );
		}
	}
	unset( $element );
}

/**
 * Convierte el texto del header en widget Imagen (guardado en BD). Solo una vez.
 */
function tc_migrate_header_logo_slot() {
	if ( '24' === (string) get_option( 'tc_header_logo_slot_version', '' ) ) {
		return;
	}

	if ( ! function_exists( 'get_hfe_header_id' ) ) {
		return;
	}

	$header_id = (int) get_hfe_header_id();
	if ( $header_id <= 0 ) {
		return;
	}

	$raw = get_post_meta( $header_id, '_elementor_data', true );
	if ( empty( $raw ) ) {
		update_option( 'tc_header_logo_slot_version', '24', false );
		return;
	}

	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) ) {
		return;
	}

	$user_logo = tc_scan_header_for_user_logo_image( $data );
	$changed   = false;
	tc_migrate_header_logo_slot_in_elements( $data, $user_logo, $changed );

	if ( $changed ) {
		update_post_meta( $header_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
		tc_clear_elementor_files_cache();
	}

	update_option( 'tc_header_logo_slot_version', '24', false );
}

function tc_elementor_tree_has_widget( $elements, $widget_type ) {
	foreach ( (array) $elements as $element ) {
		if ( ( $element['widgetType'] ?? '' ) === $widget_type ) {
			return true;
		}
		if ( ! empty( $element['elements'] ) && tc_elementor_tree_has_widget( $element['elements'], $widget_type ) ) {
			return true;
		}
	}
	return false;
}

function tc_extract_user_logo_from_elements( $elements ) {
	foreach ( (array) $elements as $element ) {
		if ( tc_header_logo_has_user_image( $element ) ) {
			return $element['settings']['image'];
		}
		if ( ! empty( $element['elements'] ) ) {
			$found = tc_extract_user_logo_from_elements( $element['elements'] );
			if ( $found ) {
				return $found;
			}
		}
	}
	return null;
}

function tc_is_duplicate_page_header_block( $element ) {
	$type = $element['elType'] ?? '';
	if ( ! in_array( $type, array( 'section', 'container' ), true ) ) {
		return false;
	}
	return tc_elementor_tree_has_widget( array( $element ), 'navigation-menu' );
}

function tc_strip_duplicate_page_headers( $elements, &$removed_logo = null ) {
	$out = array();
	foreach ( (array) $elements as $element ) {
		if ( tc_is_duplicate_page_header_block( $element ) ) {
			if ( null === $removed_logo ) {
				$removed_logo = tc_extract_user_logo_from_elements( array( $element ) );
			}
			continue;
		}
		if ( ! empty( $element['elements'] ) ) {
			$element['elements'] = tc_strip_duplicate_page_headers( $element['elements'], $removed_logo );
		}
		$out[] = $element;
	}
	return $out;
}

function tc_apply_logo_to_header_post( $user_logo ) {
	if ( ! $user_logo || ! function_exists( 'get_hfe_header_id' ) ) {
		return;
	}
	$header_id = (int) get_hfe_header_id();
	if ( $header_id <= 0 ) {
		return;
	}
	$raw = get_post_meta( $header_id, '_elementor_data', true );
	if ( empty( $raw ) ) {
		return;
	}
	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) ) {
		return;
	}
	$changed = false;
	tc_migrate_header_logo_slot_in_elements( $data, $user_logo, $changed );
	if ( $changed ) {
		update_post_meta( $header_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
		tc_clear_elementor_files_cache();
	}
}

function tc_ensure_hfe_canvas_header_display() {
	if ( ! function_exists( 'get_hfe_header_id' ) || ! function_exists( 'get_hfe_footer_id' ) ) {
		return;
	}

	$header_id = (int) get_hfe_header_id();
	if ( $header_id > 0 && '1' !== get_post_meta( $header_id, 'display-on-canvas-template', true ) ) {
		update_post_meta( $header_id, 'display-on-canvas-template', '1' );
	}

	$footer_id = (int) get_hfe_footer_id();
	if ( $footer_id > 0 && '1' !== get_post_meta( $footer_id, 'display-on-canvas-template', true ) ) {
		update_post_meta( $footer_id, 'display-on-canvas-template', '1' );
	}
}

/**
 * En páginas Elementor Canvas (Inicio, etc.) el header HFE solo aparece con este meta activo.
 */
function tc_fix_hfe_header_editor_settings() {
	tc_ensure_hfe_canvas_header_display();
}

/**
 * Quita headers duplicados pegados en páginas (Inicio, etc.) y mueve el logo al header real.
 */
function tc_cleanup_duplicate_page_headers() {
	if ( '24' === (string) get_option( 'tc_page_header_cleanup_version', '' ) ) {
		return;
	}

	tc_fix_hfe_header_editor_settings();

	$page_ids = array_filter(
		array(
			function_exists( 'tc_get_home_page_id' ) ? tc_get_home_page_id() : 0,
			function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : 0,
			( $p = get_page_by_path( 'nosotros' ) ) ? (int) $p->ID : 0,
			( $p = get_page_by_path( 'contactanos' ) ) ? (int) $p->ID : 0,
		)
	);

	$found_logo = null;
	foreach ( $page_ids as $page_id ) {
		$page_id = (int) $page_id;
		if ( $page_id <= 0 || 'builder' !== get_post_meta( $page_id, '_elementor_edit_mode', true ) ) {
			continue;
		}
		$raw = get_post_meta( $page_id, '_elementor_data', true );
		if ( empty( $raw ) ) {
			continue;
		}
		$data = json_decode( $raw, true );
		if ( ! is_array( $data ) ) {
			continue;
		}
		$removed_logo = null;
		$clean        = tc_strip_duplicate_page_headers( $data, $removed_logo );
		if ( $removed_logo && ! $found_logo ) {
			$found_logo = $removed_logo;
		}
		if ( wp_json_encode( $clean ) !== wp_json_encode( $data ) ) {
			update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $clean ) ) );
		}
	}

	if ( $found_logo ) {
		tc_apply_logo_to_header_post( $found_logo );
	}

	delete_option( 'tc_header_logo_slot_version' );
	tc_migrate_header_logo_slot();

	tc_clear_elementor_files_cache();
	update_option( 'tc_page_header_cleanup_version', '24', false );
}

function tc_prevent_double_hfe_header_in_editor() {
	if ( ! function_exists( 'get_hfe_header_id' ) || ! class_exists( 'HFE_Elementor_Canvas_Compat' ) ) {
		return;
	}

	// Solo en el editor de Elementor, no en el sitio público.
	if ( ! isset( $_GET['action'] ) || 'elementor' !== $_GET['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $post_id <= 0 || (int) get_hfe_header_id() !== $post_id ) {
		return;
	}

	remove_action(
		'elementor/page_templates/canvas/before_content',
		array( HFE_Elementor_Canvas_Compat::instance(), 'render_header' )
	);
}

function tc_header_logo_placeholder_for_elementor() {
	return array(
		'id'     => 0,
		'url'    => content_url( 'mu-plugins/assets/logo-placeholder.svg' ),
		'size'   => '',
		'alt'    => __( 'Sube tu logo aquí', 'techcomputer' ),
		'source' => 'library',
	);
}

function tc_is_header_logo_placeholder_url( $url ) {
	if ( ! is_string( $url ) || '' === $url ) {
		return true;
	}
	foreach ( array(
		'placeholder-v4.svg',
		'logo-placeholder.svg',
		'techcomputer-logo.webp',
		'techcomputer-logo.png',
	) as $needle ) {
		if ( str_contains( $url, $needle ) ) {
			return true;
		}
	}
	return false;
}

function tc_header_logo_has_user_image( $element ) {
	if ( 'image' !== ( $element['widgetType'] ?? '' ) ) {
		return false;
	}
	$url = $element['settings']['image']['url'] ?? '';
	if ( ! $url || tc_is_kit_demo_media_url( $url ) || tc_is_header_logo_placeholder_url( $url ) ) {
		return false;
	}
	return true;
}

function tc_build_header_logo_image_element( $image, $placeholder = false ) {
	$classes = 'tc-header-logo';
	if ( $placeholder ) {
		$classes .= ' tc-header-logo--placeholder';
	}

	return array(
		'id'         => '3e5e84b8',
		'elType'     => 'widget',
		'widgetType' => 'image',
		'isInner'    => false,
		'elements'   => array(),
		'settings'   => array(
			'image'        => $image,
			'image_size'   => 'full',
			'link_to'      => 'custom',
			'link'         => array(
				'url'               => home_url( '/' ),
				'is_external'       => '',
				'nofollow'          => '',
				'custom_attributes' => '',
			),
			'align'        => 'left',
			'align_tablet' => 'left',
			'align_mobile' => 'center',
			'_css_classes' => $classes,
			'width'        => array(
				'unit'  => 'px',
				'size'  => 210,
				'sizes' => array(),
			),
			'width_tablet' => array(
				'unit'  => 'px',
				'size'  => 185,
				'sizes' => array(),
			),
			'width_mobile' => array(
				'unit'  => 'px',
				'size'  => 165,
				'sizes' => array(),
			),
		),
	);
}

function tc_brand_logo_url() {
	foreach ( tc_brand_logo_file_candidates() as $path ) {
		if ( file_exists( $path ) ) {
			$uploads = wp_get_upload_dir();
			if ( str_starts_with( $path, $uploads['basedir'] ) ) {
				return $uploads['baseurl'] . str_replace( $uploads['basedir'], '', $path );
			}
			return content_url( str_replace( WP_CONTENT_DIR, '', $path ) );
		}
	}
	return tc_brand_logo_remote_url();
}

function tc_brand_logo_basename() {
	foreach ( tc_brand_logo_file_candidates() as $path ) {
		if ( file_exists( $path ) ) {
			return basename( $path );
		}
	}
	return 'techcomputer-logo.webp';
}

function tc_import_brand_logo_attachment() {
	$existing = (int) get_option( 'tc_brand_logo_attachment_id', 0 );
	if ( $existing > 0 && wp_attachment_is_image( $existing ) ) {
		return $existing;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$source = '';
	foreach ( tc_brand_logo_file_candidates() as $candidate ) {
		if ( file_exists( $candidate ) ) {
			$source = $candidate;
			break;
		}
	}
	if ( ! $source ) {
		tc_ensure_brand_logo_asset();
		foreach ( tc_brand_logo_file_candidates() as $candidate ) {
			if ( file_exists( $candidate ) ) {
				$source = $candidate;
				break;
			}
		}
	}
	if ( ! $source ) {
		return 0;
	}

	$uploads = wp_get_upload_dir();
	$dest    = trailingslashit( $uploads['path'] ) . tc_brand_logo_basename();
	if ( ! wp_mkdir_p( dirname( $dest ) ) ) {
		return 0;
	}
	if ( ! file_exists( $dest ) ) {
		copy( $source, $dest );
	}

	$attachment_id = attachment_url_to_postid( trailingslashit( $uploads['url'] ) . tc_brand_logo_basename() );
	if ( ! $attachment_id ) {
		$filetype = wp_check_filetype( basename( $dest ), null );
		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => $filetype['type'],
				'post_title'     => 'Techcomputer Logo',
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$dest
		);
		if ( ! is_wp_error( $attachment_id ) ) {
			wp_generate_attachment_metadata( $attachment_id, $dest );
		}
	}

	if ( $attachment_id && ! is_wp_error( $attachment_id ) ) {
		update_option( 'tc_brand_logo_attachment_id', (int) $attachment_id, false );
	}

	return (int) $attachment_id;
}

function tc_get_brand_logo_for_elementor() {
	$custom_id = (int) get_option( 'tc_custom_logo_attachment_id', 0 );
	if ( $custom_id > 0 && wp_attachment_is_image( $custom_id ) ) {
		$attachment_id = $custom_id;
	} else {
		$attachment_id = tc_import_brand_logo_attachment();
	}

	if ( $attachment_id > 0 ) {
		return array(
			'id'     => $attachment_id,
			'url'    => wp_get_attachment_url( $attachment_id ),
			'size'   => '',
			'alt'    => 'Techcomputer',
			'source' => 'library',
		);
	}

	return array(
		'id'     => 0,
		'url'    => tc_brand_logo_file_exists() ? tc_brand_logo_url() : tc_brand_logo_remote_url(),
		'size'   => '',
		'alt'    => 'Techcomputer',
		'source' => 'library',
	);
}

function tc_collect_elementor_media_map( $elements, &$map = array() ) {
	foreach ( $elements as $element ) {
		$id = $element['id'] ?? '';
		if ( $id ) {
			$media = array();
			foreach ( array( 'image', 'background_image', 'background_image_mobile', 'background_image_tablet' ) as $key ) {
				if ( ! empty( $element['settings'][ $key ]['url'] ) ) {
					$media[ $key ] = $element['settings'][ $key ];
				}
			}
			if ( $media ) {
				$map[ $id ] = $media;
			}
		}
		if ( ! empty( $element['elements'] ) ) {
			tc_collect_elementor_media_map( $element['elements'], $map );
		}
	}
	return $map;
}

function tc_restore_elementor_media_map( &$elements, $map ) {
	foreach ( $elements as &$element ) {
		$id = $element['id'] ?? '';
		if ( $id && isset( $map[ $id ] ) ) {
			foreach ( $map[ $id ] as $key => $saved ) {
				$saved_url = $saved['url'] ?? '';
				if ( ! $saved_url || tc_is_kit_demo_media_url( $saved_url ) ) {
					continue;
				}
				$element['settings'][ $key ] = $saved;
			}
		}
		if ( ! empty( $element['elements'] ) ) {
			tc_restore_elementor_media_map( $element['elements'], $map );
		}
	}
	unset( $element );
}

function tc_brand_elementor_colors() {
	$p = tc_brand_palette();
	return array(
		'system_colors' => array(
			array(
				'_id'   => 'primary',
				'title' => 'Techcomputer Green',
				'color' => $p['primary'],
			),
			array(
				'_id'   => 'secondary',
				'title' => 'White',
				'color' => $p['white'],
			),
			array(
				'_id'   => 'text',
				'title' => 'Light Text',
				'color' => $p['white'],
			),
			array(
				'_id'   => 'accent',
				'title' => 'Body Text',
				'color' => $p['body'],
			),
		),
		'custom_colors' => array(
			array(
				'_id'   => '2eddc8f',
				'title' => 'Dark Overlay',
				'color' => '#111111B3',
			),
			array(
				'_id'   => 'eb79eb2',
				'title' => 'Green Tint',
				'color' => $p['tint'],
			),
			array(
				'_id'   => '434fe79',
				'title' => 'Section Background',
				'color' => $p['section'],
			),
			array(
				'_id'   => 'c144476',
				'title' => 'Card Background',
				'color' => $p['card'],
			),
		),
	);
}

function tc_apply_techcomputer_brand_colors() {
	if ( ! class_exists( '\Elementor\Plugin' ) ) {
		return false;
	}

	$colors  = tc_brand_elementor_colors();
	$kit_id  = (int) get_option( 'elementor_active_kit' );
	$updated = false;

	if ( $kit_id > 0 ) {
		$settings = get_post_meta( $kit_id, '_elementor_page_settings', true );
		if ( ! is_array( $settings ) ) {
			$settings = array();
		}
		$settings['system_colors'] = $colors['system_colors'];
		$settings['custom_colors'] = $colors['custom_colors'];
		update_post_meta( $kit_id, '_elementor_page_settings', $settings );
		$updated = true;
	}

	$global_path = tc_kit_path() . 'templates/global.json';
	if ( file_exists( $global_path ) ) {
		$global = json_decode( file_get_contents( $global_path ), true );
		if ( ! empty( $global['page_settings'] ) ) {
			$global['page_settings']['system_colors'] = $colors['system_colors'];
			$global['page_settings']['custom_colors'] = $colors['custom_colors'];
			file_put_contents( $global_path, wp_json_encode( $global, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) );
		}
	}

	\Elementor\Plugin::$instance->files_manager->clear_cache();
	return $updated;
}

function tc_replace_legacy_brand_hex( $value ) {
	if ( ! is_string( $value ) || ! str_contains( $value, '#' ) ) {
		return $value;
	}
	$p   = tc_brand_palette();
	$map = array(
		'#2C2D3A' => $p['dark'],
		'#2c2d3a' => $p['dark'],
		'#5D5E6F' => $p['body'],
		'#5d5e6f' => $p['body'],
		'#FFFEF5' => $p['white'],
		'#fffef5' => $p['white'],
		'#F8F7ED' => $p['section'],
		'#f8f7ed' => $p['section'],
		'#F4F3E9' => $p['card'],
		'#f4f3e9' => $p['card'],
		'#DFDFD5' => $p['tint'],
		'#dfdfd5' => $p['tint'],
	);
	return str_replace( array_keys( $map ), array_values( $map ), $value );
}

function tc_normalize_brand_settings( &$settings ) {
	foreach ( $settings as $key => &$value ) {
		if ( is_array( $value ) ) {
			tc_normalize_brand_settings( $value );
		} elseif ( is_string( $value ) ) {
			$value = tc_replace_legacy_brand_hex( $value );
		}
	}
	unset( $value );
}

/**
 * IDs del header Elementor (kit original y header publicado en HFE).
 *
 * @return array{shell:string[],nav:string[],actions:string[]}
 */
function tc_header_layout_ids() {
	return array(
		'shell'    => array( '2f89bce4', '3cca832c' ),
		'nav'      => array( '2dbc6304', '41cdefe5' ),
		'actions'  => array( '128e2831', '1a7aa264' ),
	);
}

function tc_header_element_in_group( $element_id, $group ) {
	$map = tc_header_layout_ids();
	return in_array( (string) $element_id, $map[ $group ] ?? array(), true );
}

/**
 * CSS de header móvil (carga al final para ganar a Elementor).
 */
function tc_header_mobile_css() {
	$header_id = function_exists( 'get_hfe_header_id' ) ? (int) get_hfe_header_id() : 0;
	if ( $header_id <= 0 ) {
		return '';
	}

	$scope = '.elementor-' . $header_id;
	$shell = implode( ',', array_map(
		static fn( $id ) => "{$scope} .elementor-element-{$id},{$scope} .elementor-element-{$id}>.e-con-inner",
		tc_header_layout_ids()['shell']
	) );
	$nav = implode( ',', array_map(
		static fn( $id ) => "{$scope} .elementor-element-{$id}",
		tc_header_layout_ids()['nav']
	) );
	$actions = implode( ',', array_map(
		static fn( $id ) => "{$scope} .elementor-element-{$id}",
		tc_header_layout_ids()['actions']
	) );

	return "
@media(max-width:1024px){
{$shell}{
--flex-direction:row!important;
--flex-wrap-mobile:nowrap!important;
--container-widget-width:initial!important;
--container-widget-height:100%!important;
--container-widget-flex-grow:1!important;
--container-widget-align-self:stretch!important;
--padding-top:12px!important;
--padding-bottom:12px!important;
--padding-left:14px!important;
--padding-right:14px!important;
display:grid!important;
grid-template-columns:minmax(0,1fr) auto auto!important;
grid-template-areas:\"logo actions nav\"!important;
align-items:center!important;
column-gap:10px!important;
width:100%!important;
max-width:100%!important;
}
{$scope} .elementor-element-3cca832c.e-con,{$scope} .elementor-element-2f89bce4.e-con{--align-self:stretch!important}
{$scope} .elementor-element-3cca832c>.e-con-inner,{$scope} .elementor-element-2f89bce4>.e-con-inner{display:contents!important}
{$scope} .elementor-element-3e5e84b8,{$scope} .elementor-element.tc-header-logo{
grid-area:logo!important;
width:auto!important;
max-width:100%!important;
justify-self:start!important;
align-self:center!important;
text-align:left!important;
margin:0!important;
}
{$scope} .elementor-element-3e5e84b8 img,{$scope} .elementor-element.tc-header-logo img{
width:auto!important;
max-width:118px!important;
max-height:34px!important;
height:auto!important;
}
{$actions}{
grid-area:actions!important;
width:auto!important;
max-width:none!important;
--width:auto!important;
--justify-content:flex-end!important;
justify-self:end!important;
align-self:center!important;
margin:0!important;
}
{$actions} .e-con-inner{
display:flex!important;
flex-direction:row!important;
flex-wrap:nowrap!important;
align-items:center!important;
justify-content:flex-end!important;
gap:8px!important;
width:auto!important;
}
{$nav}{
grid-area:nav!important;
width:auto!important;
max-width:none!important;
justify-self:end!important;
align-self:center!important;
margin:0!important;
}
{$nav} .elementor-widget-container,{$nav} .hfe-nav-menu__layout-horizontal{
width:auto!important;
max-width:none!important;
display:flex!important;
justify-content:flex-end!important;
align-items:center!important;
}
{$nav} .hfe-nav-menu__layout-horizontal .hfe-nav-menu{display:none!important}
{$nav} .hfe-nav-menu__toggle{
display:inline-flex!important;
align-items:center!important;
justify-content:center!important;
margin:0!important;
padding:0!important;
}
}";
}

function tc_enqueue_header_mobile_fix() {
	if ( is_admin() ) {
		return;
	}

	$deps = array( 'tc-brand-colors' );
	if ( wp_style_is( 'elementor-frontend', 'registered' ) ) {
		$deps[] = 'elementor-frontend';
	}
	if ( function_exists( 'get_hfe_header_id' ) ) {
		$header_id = (int) get_hfe_header_id();
		if ( $header_id > 0 ) {
			$handle = 'elementor-post-' . $header_id;
			if ( wp_style_is( $handle, 'registered' ) ) {
				$deps[] = $handle;
			}
		}
	}

	wp_register_style( 'tc-header-mobile-fix', false, $deps, TC_SETUP_VERSION );
	wp_enqueue_style( 'tc-header-mobile-fix' );
	wp_add_inline_style( 'tc-header-mobile-fix', tc_header_mobile_css() );
}

function tc_enqueue_brand_styles() {
	$p = tc_brand_palette();
	$css = ':root{--tc-primary:' . $p['primary'] . ';--tc-primary-dark:' . $p['primary_d'] . ';--tc-blue:' . $p['blue'] . ';--tc-blue-dark:' . $p['blue_d'] . ';--tc-dark:' . $p['dark'] . ';--tc-body:' . $p['body'] . '}
a{color:' . $p['primary'] . '}
a:hover,a:focus{color:' . $p['primary_d'] . '}
.elementor-button:not(.hfe-cart-container),.elementor-button:not(.hfe-cart-container):visited,.woocommerce a.button,.woocommerce button.button,.woocommerce input.button,.woocommerce #respond input#submit,.woocommerce a.button.alt,.woocommerce button.button.alt,.woocommerce input.button.alt{background-color:' . $p['primary'] . '!important;border-color:' . $p['primary'] . '!important;color:#fff!important}
.elementor-button:not(.hfe-cart-container):hover,.woocommerce a.button:hover,.woocommerce button.button:hover,.woocommerce input.button:hover,.woocommerce a.button.alt:hover,.woocommerce button.button.alt:hover,.woocommerce input.button.alt:hover{background-color:' . $p['primary_d'] . '!important;border-color:' . $p['primary_d'] . '!important;color:#fff!important}
.hfe-menu-cart__toggle .elementor-button.hfe-cart-container,.hfe-menu-cart__toggle .elementor-button.hfe-cart-container:hover,.hfe-menu-cart__toggle .elementor-button.hfe-cart-container:focus{background:transparent!important;border:0!important;box-shadow:none!important;padding:6px 8px!important;min-height:0!important;line-height:1!important;color:' . $p['primary'] . '!important}
.hfe-menu-cart__toggle .elementor-button-icon{color:' . $p['primary'] . '!important;font-size:28px!important;line-height:1!important}
.hfe-menu-cart__toggle .elementor-button-icon .eicon:before{color:' . $p['primary'] . '!important}
.hfe-menu-cart--items-indicator-bubble .hfe-menu-cart__toggle .elementor-button-icon[data-counter]:before{background-color:' . $p['primary'] . '!important;color:#fff!important;min-width:18px!important;height:18px!important;line-height:18px!important;font-size:11px!important;font-weight:700!important;top:-6px!important;right:-8px!important}
.hfe-cart-menu-wrap-default .hfe-cart-count,.hfe-cart-menu-wrap-default .hfe-cart-count:after{background-color:' . $p['primary'] . '!important;border-color:' . $p['primary'] . '!important;color:#fff!important}
.woocommerce span.onsale{background-color:' . $p['primary'] . '!important}
.woocommerce-info,.woocommerce-message{border-top-color:' . $p['primary'] . '!important}
.tc-header-user-decor a,.tc-header-user-decor .elementor-icon-wrapper{pointer-events:none!important;cursor:default!important}
.elementor-widget-image.tc-header-logo,.elementor-element.tc-header-logo{display:flex;align-items:center;flex:0 0 auto}
.elementor-widget-image.tc-header-logo img,.tc-header-logo img{display:block;width:auto!important;max-width:210px!important;height:auto!important;max-height:56px!important;object-fit:contain!important}
.elementor-widget-image.tc-header-logo--placeholder img,.tc-header-logo--placeholder img{max-width:240px!important;max-height:64px!important;opacity:.95}
.elementor-element.tc-header-shell{align-items:center!important;gap:12px 20px!important}
.elementor-element.tc-header-shell>.elementor-element-3e5e84b8,.elementor-element.tc-header-shell>.elementor-element.tc-header-logo{flex:0 1 auto;min-width:0}
.elementor-element.tc-header-shell>.elementor-element-128e2831,.elementor-element.tc-header-shell>.tc-header-actions{flex:0 0 auto;margin-left:auto}
.elementor-element.tc-header-shell>.elementor-element-2dbc6304,.elementor-element.tc-header-shell>.tc-header-nav{flex:1 1 100%;width:100%!important;max-width:100%!important}
.elementor-element.tc-header-nav .hfe-nav-menu__layout-horizontal .hfe-nav-menu{display:flex;flex-wrap:nowrap;justify-content:center;gap:0}
.elementor-element.tc-header-nav .hfe-nav-menu__layout-horizontal .hfe-nav-menu>li>a{white-space:nowrap;padding:8px 12px!important}
@media(min-width:1025px){
.elementor-element.tc-header-shell>.elementor-element-2dbc6304,.elementor-element.tc-header-shell>.tc-header-nav{flex:1 1 auto;width:auto!important;max-width:none!important}
.elementor-element.tc-header-nav .hfe-nav-menu__layout-horizontal .hfe-nav-menu{justify-content:flex-end}
}
@media(max-width:1024px){
.elementor-element.tc-header-nav .hfe-nav-menu__layout-horizontal .hfe-nav-menu>li>a{padding:6px 10px!important;font-size:13px!important}
}
.tc-wa-float{position:fixed;right:22px;bottom:22px;z-index:99999;width:58px;height:58px;border-radius:50%;background:#25D366;color:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 10px 28px rgba(0,0,0,.22);transition:transform .2s ease,box-shadow .2s ease;text-decoration:none}
.tc-wa-float:hover{transform:scale(1.06);box-shadow:0 14px 34px rgba(0,0,0,.28);color:#fff}
.tc-wa-float svg{width:30px;height:30px;fill:currentColor}
.tc-contact-form{display:grid;gap:14px}
.tc-contact-form__row{display:grid;gap:14px}
@media(min-width:640px){.tc-contact-form__row--2{grid-template-columns:1fr 1fr}}
.tc-contact-form label{display:block;margin-bottom:6px;font-weight:600;color:' . $p['body'] . ';font-size:.92rem}
.tc-contact-form input[type=text],.tc-contact-form input[type=email],.tc-contact-form input[type=tel],.tc-contact-form textarea,.tc-contact-form select{width:100%;min-height:44px;border:1px solid #ddd;border-radius:10px;padding:10px 12px;background:#fff;font:inherit}
.tc-contact-form textarea{min-height:120px;resize:vertical}
.tc-contact-form__choices{display:flex;flex-wrap:wrap;gap:12px}
.tc-contact-form__choices label{display:flex;align-items:center;gap:8px;font-weight:500;margin:0}
.tc-contact-form__submit{background:' . $p['primary'] . '!important;border-color:' . $p['primary'] . '!important;color:#fff!important;border-radius:10px!important;padding:12px 24px!important;cursor:pointer;font-weight:700}
.tc-contact-form__submit:hover{background:' . $p['primary_d'] . '!important;border-color:' . $p['primary_d'] . '!important}
.tc-contact-form__notice{padding:12px 14px;border-radius:10px;margin-bottom:12px}
.tc-contact-form__notice--ok{background:' . $p['tint'] . ';color:' . $p['primary_d'] . '}
.tc-contact-form__notice--error{background:#fdecec;color:#9b1c1c}
html,body{overflow-x:hidden}
body{padding-bottom:env(safe-area-inset-bottom,0)}
' . tc_shared_hero_mobile_css() . '
.tc-wa-float{right:max(16px,env(safe-area-inset-right,16px));bottom:max(16px,env(safe-area-inset-bottom,16px))}
@media(max-width:767px){
.tc-wa-float{width:52px;height:52px;right:max(14px,env(safe-area-inset-right,14px));bottom:max(14px,env(safe-area-inset-bottom,14px))}
.tc-wa-float svg{width:26px;height:26px}
.tc-contact-form__submit{width:100%}
.tc-contact-form__choices{flex-direction:column;align-items:flex-start}
}
';
	wp_register_style( 'tc-brand-colors', false, array(), TC_SETUP_VERSION );
	wp_enqueue_style( 'tc-brand-colors' );
	wp_add_inline_style( 'tc-brand-colors', $css );
}

function tc_get_pantallas_slugs() {
	return array(
		'pantallas-notebook',
		'pantallas-para-notebook',
		'pantallas-de-notebook',
		'Pantallas Notebook',
		'Pantallas para Notebook',
		'Pantallas de Notebook',
	);
}

function tc_catalog_repuesto_parent_slug() {
	return 'repuestos-para-notebook';
}

function tc_catalog_repuesto_config() {
	return array(
		'pantallas-notebook'  => array(
			'label'         => 'Pantallas Notebook',
			'aliases'       => array( 'pantallas-para-notebook', 'pantallas-de-notebook' ),
			'brands'        => array(
				'pantallas-hp'     => 'HP',
				'pantallas-lenovo' => 'Lenovo',
				'pantallas-dell'   => 'Dell',
				'pantallas-asus'   => 'Asus',
				'pantallas-acer'   => 'Acer',
			),
			'legacy_brands' => array(
				'hp'            => 'HP',
				'lenovo'        => 'Lenovo',
				'dell'          => 'Dell',
				'asus'          => 'Asus',
				'acer'          => 'Acer',
				'otros-modelos' => 'Otros Modelos',
				'gaming'        => 'Gaming',
			),
			'screen_specs'  => true,
		),
		'teclados-notebook'   => array(
			'label'         => 'Teclados Notebook',
			'aliases'       => array(),
			'brands'        => array(
				'teclados-hp'     => 'HP',
				'teclados-lenovo' => 'Lenovo',
				'teclados-dell'   => 'Dell',
			),
			'legacy_brands' => array(
				'hp'     => 'HP',
				'lenovo' => 'Lenovo',
				'dell'   => 'Dell',
			),
			'screen_specs'  => false,
		),
		'baterias-notebook'   => array(
			'label'        => 'Baterías Notebook',
			'aliases'      => array(),
			'brands'       => array(),
			'screen_specs' => false,
		),
		'cargadores-notebook' => array(
			'label'        => 'Cargadores Notebook',
			'aliases'      => array(),
			'brands'       => array(),
			'screen_specs' => false,
		),
		'bisagras-notebook'   => array(
			'label'        => 'Bisagras Notebook',
			'aliases'      => array(),
			'brands'       => array(),
			'screen_specs' => false,
		),
	);
}

function tc_catalog_repuesto_type_slugs() {
	return array_keys( tc_catalog_repuesto_config() );
}

function tc_catalog_normalize_repuesto_slug( $slug ) {
	if ( ! $slug ) {
		return '';
	}
	$slug = sanitize_title( $slug );
	foreach ( tc_catalog_repuesto_config() as $canonical => $cfg ) {
		if ( $canonical === $slug ) {
			return $canonical;
		}
		if ( in_array( $slug, (array) ( $cfg['aliases'] ?? array() ), true ) ) {
			return $canonical;
		}
	}
	return '';
}

function tc_catalog_repuesto_supports_marca( $repuesto_slug ) {
	$repuesto_slug = tc_catalog_normalize_repuesto_slug( $repuesto_slug );
	if ( ! $repuesto_slug ) {
		return false;
	}
	$cfg = tc_catalog_repuesto_config()[ $repuesto_slug ] ?? array();
	return ! empty( $cfg['brands'] ) || ! empty( $cfg['legacy_brands'] );
}

function tc_catalog_repuesto_supports_screen_specs( $repuesto_slug ) {
	$repuesto_slug = tc_catalog_normalize_repuesto_slug( $repuesto_slug );
	if ( ! $repuesto_slug ) {
		return false;
	}
	return ! empty( tc_catalog_repuesto_config()[ $repuesto_slug ]['screen_specs'] );
}

function tc_catalog_marca_slugs_for_repuesto( $repuesto_slug ) {
	$repuesto_slug = tc_catalog_normalize_repuesto_slug( $repuesto_slug );
	if ( ! $repuesto_slug || ! tc_catalog_repuesto_supports_marca( $repuesto_slug ) ) {
		return array();
	}
	$cfg = tc_catalog_repuesto_config()[ $repuesto_slug ];
	return array_values(
		array_unique(
			array_merge(
				array_keys( (array) ( $cfg['brands'] ?? array() ) ),
				array_keys( (array) ( $cfg['legacy_brands'] ?? array() ) )
			)
		)
	);
}

function tc_ensure_product_category( $name, $slug, $parent = 0 ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	if ( $term && ! is_wp_error( $term ) ) {
		if ( $parent && (int) $term->parent !== (int) $parent ) {
			wp_update_term(
				(int) $term->term_id,
				'product_cat',
				array(
					'parent' => (int) $parent,
				)
			);
		}
		return (int) $term->term_id;
	}

	$result = wp_insert_term(
		$name,
		'product_cat',
		array(
			'slug'   => $slug,
			'parent' => (int) $parent,
		)
	);
	if ( is_wp_error( $result ) ) {
		return 0;
	}

	return (int) $result['term_id'];
}

function tc_setup_repuesto_categories() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return false;
	}

	$parent_id = tc_ensure_product_category( 'Repuestos para Notebook', tc_catalog_repuesto_parent_slug(), 0 );
	if ( ! $parent_id ) {
		return false;
	}

	foreach ( tc_catalog_repuesto_config() as $slug => $cfg ) {
		$type_id = tc_ensure_product_category( $cfg['label'], $slug, $parent_id );
		if ( ! $type_id ) {
			continue;
		}

		foreach ( (array) ( $cfg['aliases'] ?? array() ) as $alias_slug ) {
			$alias_term = get_term_by( 'slug', $alias_slug, 'product_cat' );
			if ( $alias_term && ! is_wp_error( $alias_term ) && (int) $alias_term->parent !== $type_id ) {
				wp_update_term(
					(int) $alias_term->term_id,
					'product_cat',
					array(
						'parent' => $type_id,
					)
				);
			}
		}

		foreach ( (array) ( $cfg['brands'] ?? array() ) as $brand_slug => $brand_name ) {
			tc_ensure_product_category( $brand_name, $brand_slug, $type_id );
		}
	}

	return true;
}

function tc_get_servicios_slugs() {
	return array(
		'servicios-techcomputer',
		'Servicios Techcomputer',
		'soluciones-techcomputer',
		'Soluciones TechComputer',
		'mantenciones-techcomputer',
		'Mantenciones TechComputer',
		'mantenimiento-de-notebook-pc',
		'mantenimiento-de-notebook-amp-pc',
		'Mantenimiento de Notebook & Pc',
	);
}

function tc_resolve_category_term( $candidate ) {
	$term = get_term_by( 'slug', $candidate, 'product_cat' );
	if ( ! $term ) {
		$term = get_term_by( 'name', $candidate, 'product_cat' );
	}
	if ( $term && ! is_wp_error( $term ) ) {
		return $term;
	}
	return null;
}

function tc_resolve_category_slugs( $candidates ) {
	$found = array();
	foreach ( (array) $candidates as $candidate ) {
		$term = tc_resolve_category_term( $candidate );
		if ( $term ) {
			$found[] = $term->slug;
		}
	}
	return array_values( array_unique( $found ) );
}

function tc_cat_url( $slugs ) {
	$slugs = tc_resolve_category_slugs( (array) $slugs );
	if ( empty( $slugs ) ) {
		return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	}
	$term = get_term_by( 'slug', $slugs[0], 'product_cat' );
	return ( $term && ! is_wp_error( $term ) ) ? get_term_link( $term ) : wc_get_page_permalink( 'shop' );
}

function tc_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' );
}

function tc_localize_url( $url ) {
	if ( ! is_string( $url ) || '' === $url ) {
		return $url;
	}
	if ( str_contains( $url, 'techcomputer.cl/wp-content/uploads' ) ) {
		return $url;
	}
	if ( str_contains( $url, 'ui-avatars.com' ) ) {
		return $url;
	}
	if ( str_contains( $url, 'contactanos' ) || str_contains( $url, 'contacto' ) ) {
		return tc_page_url( 'contactanos' );
	}
	if ( str_contains( $url, 'wa.me' ) ) {
		return $url;
	}
	if ( str_contains( $url, '/wp-content/uploads/' ) ) {
		return function_exists( 'tc_remap_legacy_media_url' ) ? tc_remap_legacy_media_url( $url ) : $url;
	}
	if ( preg_match( '/\.(png|jpe?g|webp|gif|svg)(\?|$)/i', $url ) ) {
		return $url;
	}
	if ( str_contains( $url, 'nirmanavisual.com' ) ) {
		return home_url( '/' );
	}
	if ( str_contains( $url, 'techcomputer.cl' ) ) {
		return home_url( '/' );
	}
	return $url;
}

/** Reseñas reales publicadas en Google / techcomputer.cl */
function tc_google_reviews() {
	return array(
		array(
			'aliases' => array( 'Paulo Villagrán Salinas', 'James Miller' ),
			'role'    => 'Ejecutivo',
			'quote'   => 'Excelente atención y solución del problema. Logré recuperar un notebook que estuve a punto de tirar a reciclaje.',
			'avatar'  => 'https://ui-avatars.com/api/?name=Paulo+Villagran&size=256&background=1e3a5f&color=ffffff&format=png',
		),
		array(
			'aliases' => array( 'Valentina Gonzalez', 'Valentina González', 'Daniel Brooks', 'Daniel Brooks ' ),
			'role'    => 'Enfermera',
			'quote'   => 'Solución rápida e información clara desde un comienzo, muy amables en todo, los recomiendo 100%.',
			'avatar'  => 'https://ui-avatars.com/api/?name=Valentina+Gonzalez&size=256&background=2563eb&color=ffffff&format=png',
		),
		array(
			'aliases' => array( 'Matti Marchese', 'Ethan Parker' ),
			'role'    => 'Estudiante',
			'quote'   => 'Profesionales, prolijos, buena atención al cliente y muy buen precio del servicio. El cambio de pantalla demoró 30 minutos, se pasaron.',
			'avatar'  => 'https://ui-avatars.com/api/?name=Matti+Marchese&size=256&background=0f766e&color=ffffff&format=png',
		),
	);
}

function tc_find_google_review( $name ) {
	$name = trim( (string) $name );
	foreach ( tc_google_reviews() as $review ) {
		if ( in_array( $name, $review['aliases'], true ) ) {
			return $review;
		}
	}
	return null;
}

function tc_format_google_review_quote( $quote ) {
	return '<p>&ldquo;' . esc_html( $quote ) . '&rdquo;</p><p style="text-align:center;margin-top:10px;font-size:13px;color:#5D5E6F;">⭐⭐⭐⭐⭐ Reseña en Google</p>';
}

function tc_apply_google_testimonial_settings( $settings, $widget ) {
	if ( 'heading' === $widget && ! empty( $settings['title'] ) ) {
		$review = tc_find_google_review( $settings['title'] );
		if ( $review && ! str_contains( $settings['title'], '·' ) ) {
			$settings['title'] = $review['aliases'][0] . ' · ' . $review['role'];
		}
		if ( 'La confianza de miles de clientes satisfechos' === $settings['title'] ) {
			$settings['title'] = 'Reseñas verificadas en Google';
		}
	}

	if ( 'text-editor' === $widget && ! empty( $settings['editor'] ) ) {
		$plain   = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $settings['editor'] ) ) );
		$reviews = tc_google_reviews();
		$snippets = array(
			'Fast delivery, fresh items'     => 0,
			'Super easy to shop'             => 1,
			'Fresh products, fast delivery'  => 2,
			'Excelente atención y solución'  => 0,
			'Solución rápida e información'  => 1,
			'Profesionales, prolijos'        => 2,
		);
		foreach ( $snippets as $snippet => $index ) {
			if ( str_contains( $plain, $snippet ) ) {
				$settings['editor'] = tc_format_google_review_quote( $reviews[ $index ]['quote'] );
				break;
			}
		}
	}

	return $settings;
}

function tc_localize_elementor_settings( &$settings, $widget = '' ) {
	foreach ( $settings as $key => &$value ) {
		if ( 'url' === $key && is_string( $value ) ) {
			$value = tc_localize_url( $value );
		} elseif ( is_array( $value ) ) {
			tc_localize_elementor_settings( $value, $widget );
		}
	}
	unset( $value );
}

function tc_product_shortcode( $category_slugs, $limit = 4, $columns = 4, $paginate = false ) {
	$slugs = tc_resolve_category_slugs( (array) $category_slugs );
	$base  = 'limit="' . (int) $limit . '" columns="' . (int) $columns . '" orderby="date" order="DESC"';
	if ( $paginate ) {
		$base .= ' paginate="true"';
	}
	if ( empty( $slugs ) ) {
		return '[products ' . $base . ']';
	}
	return '[products ' . $base . ' category="' . esc_attr( implode( ',', $slugs ) ) . '"]';
}

function tc_service_button_url( $service_title ) {
	$map = array(
		'Cambio de Pantalla'     => tc_cat_url( tc_get_pantallas_slugs() ),
		'Reparación de Bisagras' => TC_WHATSAPP . '?text=' . rawurlencode( 'Hola, necesito reparación de bisagras para mi notebook' ),
		'Reparación de Consolas' => tc_cat_url( tc_get_servicios_slugs() ),
		'Kit Actualización'      => tc_cat_url( array( 'soluciones-techcomputer', 'servicios-techcomputer' ) ),
	);
	if ( isset( $map[ $service_title ] ) ) {
		return $map[ $service_title ];
	}
	return tc_cat_url( tc_get_servicios_slugs() );
}

function tc_button_link( $text, $context = array() ) {
	switch ( $text ) {
		case 'Cotiza Aquí':
			return TC_WHATSAPP;
		case 'Nosotros':
			return tc_page_url( 'nosotros' );
		case 'Ver Más':
			return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
		case 'Ver Servicio':
			return tc_service_button_url( $context['service_title'] ?? '' );
		case 'Contáctanos':
		case 'Contáctanos por WhatsApp':
			return tc_page_url( 'contactanos' ) ?: TC_WHATSAPP;
		default:
			return null;
	}
}

function tc_apply_link_to_button( $settings, $context ) {
	$text = $settings['text'] ?? '';
	$url  = tc_button_link( $text, $context );
	if ( ! $url ) {
		return $settings;
	}
	$settings['link'] = array(
		'url'               => $url,
		'is_external'       => str_contains( $url, 'wa.me' ) ? 'on' : '',
		'nofollow'          => '',
		'custom_attributes' => '',
	);
	return $settings;
}

function tc_jkit_is_available() {
	return defined( 'JEG_ELEMENTOR_KIT' ) || class_exists( 'Jeg\Elementor_Kit\Init' );
}

function tc_get_category_ids_string( $slugs ) {
	$ids = array();
	foreach ( tc_resolve_category_slugs( (array) $slugs ) as $slug ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $term && ! is_wp_error( $term ) ) {
			$ids[] = (int) $term->term_id;
		}
	}
	return implode( ',', $ids );
}

/** Catálogo SEO de servicios destacados (home → Servicios Destacados). */
function tc_featured_services_catalog() {
	return array(
		array(
			'title'   => 'Pantallas Notebook',
			'slug'    => 'pantallas-notebook',
			'search'  => array( 'pantallas notebook', 'pantalla notebook' ),
			'excerpt' => 'Pantallas originales y compatibles por marca, tamaño y resolución. Cotiza según tu modelo.',
		),
		array(
			'title'   => 'Cambio de Pantalla Notebook',
			'slug'    => 'cambio-de-pantalla-notebook',
			'search'  => array( 'reparación de pantalla notebook', 'reparacion de pantalla notebook', 'cambio de pantalla' ),
			'excerpt' => 'Reemplazo profesional con diagnóstico, garantía escrita y repuestos de calidad en Santiago.',
		),
		array(
			'title'   => 'Reparación de Bisagras Notebook',
			'slug'    => 'reparacion-de-bisagras-notebook',
			'search'  => array( 'reparacion bisagras', 'reparación de bisagras', 'bisagras para notebook' ),
			'excerpt' => 'Reforzamos o reemplazamos bisagras dañadas para recuperar la apertura y cierre de tu notebook.',
		),
		array(
			'title'   => 'Cambio de Disco Duro a SSD',
			'slug'    => 'cambio-de-disco-duro-a-ssd',
			'search'  => array( 'cambio de disco duro a ssd', 'cambio de disco duro normal a ssd', 'disco duro a ssd' ),
			'excerpt' => 'Mejora la velocidad de arranque y programas migrando tu disco a SSD con respaldo de datos.',
		),
		array(
			'title'   => 'Cambio de Teclado Notebook',
			'slug'    => 'cambio-de-teclado-notebook',
			'search'  => array( 'cambio de teclado notebook', 'teclado notebook' ),
			'excerpt' => 'Sustitución de teclados dañados o con teclas fallidas, con repuestos según marca y modelo.',
		),
		array(
			'title'   => 'Reparación de Puerto de Carga',
			'slug'    => 'reparacion-de-puerto-de-carga',
			'search'  => array( 'puerto de carga', 'puertos de carga', 'jackpower', 'puerto de cargar' ),
			'excerpt' => 'Reparación o cambio de conector de carga cuando tu notebook no enciende o carga intermitente.',
		),
		array(
			'title'   => 'Mantención de Notebook',
			'slug'    => 'mantencion-de-notebook',
			'search'  => array( 'mantención preventiva', 'mantencion preventiva', 'limpieza interna', 'mantenimiento preventivo' ),
			'excerpt' => 'Limpieza interna, pasta térmica y revisión general para alargar la vida útil del equipo.',
		),
		array(
			'title'   => 'Reparación de Placa Madre Notebook',
			'slug'    => 'reparacion-de-placa-madre-notebook',
			'search'  => array( 'placa madre', 'reparación placa madre', 'reparacion placa madre' ),
			'excerpt' => 'Diagnóstico y reparación de fallas en placa madre, cortocircuitos y componentes dañados.',
		),
	);
}

function tc_find_product_for_featured_service( $def ) {
	if ( ! empty( $def['slug'] ) ) {
		$by_slug = get_page_by_path( $def['slug'], OBJECT, 'product' );
		if ( $by_slug ) {
			return (int) $by_slug->ID;
		}
	}

	if ( ! function_exists( 'wc_get_products' ) ) {
		return 0;
	}

	foreach ( (array) ( $def['search'] ?? array() ) as $term ) {
		$term = trim( (string) $term );
		if ( '' === $term ) {
			continue;
		}
		$ids = wc_get_products(
			array(
				'limit'  => 1,
				'status' => 'publish',
				's'      => $term,
				'return' => 'ids',
			)
		);
		if ( ! empty( $ids[0] ) ) {
			return (int) $ids[0];
		}
	}

	return 0;
}

function tc_assign_product_service_categories( $product_id, $extra_slugs = array(), $only_given = false ) {
	if ( $only_given ) {
		$slugs = tc_resolve_category_slugs( (array) $extra_slugs );
	} else {
		$slugs = array_values(
			array_unique(
				array_merge(
					tc_resolve_category_slugs( tc_get_servicios_slugs() ),
					tc_resolve_category_slugs( (array) $extra_slugs )
				)
			)
		);
	}
	if ( ! $slugs ) {
		return;
	}

	$term_ids = array();
	foreach ( $slugs as $slug ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $term && ! is_wp_error( $term ) ) {
			$term_ids[] = (int) $term->term_id;
		}
	}
	if ( $term_ids ) {
		wp_set_object_terms( $product_id, $term_ids, 'product_cat', false );
	}
}

function tc_sync_featured_service_product( $product_id, $def ) {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return 0;
	}

	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return 0;
	}

	$product->set_name( $def['title'] );
	if ( ! empty( $def['excerpt'] ) ) {
		$product->set_short_description( $def['excerpt'] );
	}
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_virtual( true );
	$product->set_sold_individually( true );
	if ( '' === (string) $product->get_regular_price() ) {
		$product->set_regular_price( '0' );
	}
	$product->save();

	update_post_meta( $product_id, '_tc_featured_service', '1' );

	$cats = (array) ( $def['cats'] ?? array( 'servicios-techcomputer' ) );
	if ( 'Pantallas Notebook' === $def['title'] ) {
		$cats = array_merge( $cats, tc_get_pantallas_slugs() );
	}
	tc_assign_product_service_categories( $product_id, $cats, true );

	return $product_id;
}

function tc_product_grid_term_list_post_id() {
	foreach ( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 12 ) as $frame ) {
		if ( 'get_the_term_list' === ( $frame['function'] ?? '' ) && ! empty( $frame['args'][0] ) ) {
			return (int) $frame['args'][0];
		}
	}

	return 0;
}

/** Texto compacto bajo la imagen en grillas JKit (sin listar todas las categorías). */
function tc_product_grid_card_subtitle( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return '';
	}

	if ( get_post_meta( $post_id, '_tc_featured_service', true ) && function_exists( 'wc_get_product' ) ) {
		$product = wc_get_product( $post_id );
		if ( $product ) {
			$excerpt = wp_strip_all_tags( $product->get_short_description() );
			if ( '' !== $excerpt ) {
				return $excerpt;
			}
		}
	}

	$terms = get_the_terms( $post_id, 'product_cat' );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return '';
	}

	$exclude = array_merge(
		tc_catalog_producto_root_slugs(),
		tc_catalog_servicio_root_slugs(),
		array( 'uncategorized', 'sin-categorizar' )
	);

	$parts = array();
	foreach ( $terms as $term ) {
		if ( in_array( $term->slug, $exclude, true ) ) {
			continue;
		}

		$name = html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' );
		if ( '' !== $name && ! in_array( $name, $parts, true ) ) {
			$parts[] = $name;
		}
	}

	return implode( ' · ', $parts );
}

function tc_product_grid_term_links( $term_links ) {
	$post_id = tc_product_grid_term_list_post_id();
	if ( $post_id <= 0 || 'product' !== get_post_type( $post_id ) ) {
		return $term_links;
	}

	$subtitle = tc_product_grid_card_subtitle( $post_id );
	if ( '' === $subtitle && ! empty( $term_links ) ) {
		$skip  = array(
			'Pantallas Notebook',
			'Pantallas para Notebook',
			'Pantallas de Notebook',
			'Servicios Techcomputer',
			'Mantenciones TechComputer',
			'Soluciones TechComputer',
			'Mantenimiento de Notebook & Pc',
			'Repuestos para Notebook',
		);
		$parts = array();
		foreach ( $term_links as $link ) {
			$text = trim( wp_strip_all_tags( $link ) );
			if ( '' === $text || in_array( $text, $skip, true ) ) {
				continue;
			}
			$parts[] = $text;
		}
		$subtitle = implode( ' · ', array_values( array_unique( $parts ) ) );
	}

	if ( '' === $subtitle ) {
		return $term_links;
	}

	return array( esc_html( $subtitle ) );
}

function tc_create_featured_service_product( $def ) {
	if ( ! class_exists( 'WC_Product_Simple' ) ) {
		return 0;
	}

	$product = new WC_Product_Simple();
	$product->set_name( $def['title'] );
	$product->set_slug( $def['slug'] ?? sanitize_title( $def['title'] ) );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_virtual( true );
	$product->set_sold_individually( true );
	$product->set_regular_price( '0' );
	if ( ! empty( $def['excerpt'] ) ) {
		$product->set_short_description( $def['excerpt'] );
	}

	$product_id = $product->save();
	if ( ! $product_id ) {
		return 0;
	}

	return tc_sync_featured_service_product( $product_id, $def );
}

function tc_ensure_featured_service_products() {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return array();
	}

	$ids = array();
	foreach ( tc_featured_services_catalog() as $def ) {
		$product_id = tc_find_product_for_featured_service( $def );
		if ( $product_id <= 0 ) {
			$product_id = tc_create_featured_service_product( $def );
		} else {
			$product_id = tc_sync_featured_service_product( $product_id, $def );
		}
		if ( $product_id > 0 ) {
			$ids[] = $product_id;
		}
	}

	update_option( 'tc_featured_service_product_ids', $ids, false );
	return $ids;
}

function tc_get_featured_service_product_ids() {
	$ids = get_option( 'tc_featured_service_product_ids', array() );
	if ( ! is_array( $ids ) || count( $ids ) < 8 ) {
		$ids = tc_ensure_featured_service_products();
	}
	return array_values( array_filter( array_map( 'intval', $ids ) ) );
}

function tc_product_grid_pantalla_widget_ids() {
	return array( 'a8e6ac5', 'a93798', '11fa3b06' );
}

function tc_product_grid_featured_widget_ids() {
	return array( '5c9ca81d', '1c28689f' );
}

function tc_product_grid_all_services_widget_ids() {
	return array( '26eec478' );
}

/** Grillas del kit: id del widget => tipo y cantidad. */
function tc_product_grid_config() {
	return array(
		'a8e6ac5'  => array( 'type' => 'pantallas', 'slugs' => tc_get_pantallas_slugs(), 'limit' => 4 ),
		'a93798'   => array( 'type' => 'pantallas', 'slugs' => tc_get_pantallas_slugs(), 'limit' => 4 ),
		'11fa3b06' => array( 'type' => 'pantallas', 'slugs' => tc_get_pantallas_slugs(), 'limit' => 4 ),
		'5c9ca81d' => array( 'type' => 'featured_services', 'limit' => 8 ),
		'1c28689f' => array( 'type' => 'featured_services', 'limit' => 8 ),
		'26eec478' => array( 'type' => 'all_services', 'slugs' => tc_get_servicios_slugs(), 'limit' => 12 ),
	);
}

function tc_resolve_product_grid_type( $element, $context = array() ) {
	$id     = $element['id'] ?? '';
	$config = tc_product_grid_config();

	if ( isset( $config[ $id ]['type'] ) ) {
		return $config[ $id ];
	}

	if ( ! empty( $context['home_page'] ) ) {
		return array(
			'type'  => 'featured_services',
			'limit' => 8,
		);
	}

	if ( ! empty( $context['shop_template'] ) ) {
		return array(
			'type'  => 'all_services',
			'slugs' => tc_get_servicios_slugs(),
			'limit' => 12,
		);
	}

	return null;
}

function tc_configure_jkit_product_grid( $element, $context = array() ) {
	$id   = $element['id'] ?? '';
	$grid = tc_resolve_product_grid_type( $element, $context );
	if ( ! $grid ) {
		return $element;
	}

	$type  = $grid['type'] ?? '';
	$limit = (int) ( $grid['limit'] ?? 4 );

	$pantalla_ids = tc_get_category_ids_string( tc_get_pantallas_slugs() );
	$servicio_ids = tc_get_category_ids_string( tc_get_servicios_slugs() );

	$element['settings']['sort_by'] = 'latest';
	if ( isset( $element['settings']['number_post']['size'] ) ) {
		$element['settings']['number_post']['size'] = $limit;
	}

	unset( $element['settings']['wc_include_post'], $element['settings']['wc_exclude_post'] );
	$element['settings']['st_category_inline_background'] = '';

	if ( 'featured_services' === $type ) {
		$featured_ids = tc_get_featured_service_product_ids();
		if ( $featured_ids ) {
			$element['settings']['wc_include_post']     = implode( ',', $featured_ids );
			$element['settings']['wc_include_category'] = '';
			$element['settings']['wc_exclude_category'] = $pantalla_ids;
		} elseif ( $servicio_ids ) {
			$element['settings']['wc_include_category'] = $servicio_ids;
			$element['settings']['wc_exclude_category'] = $pantalla_ids;
		}
	} elseif ( 'all_services' === $type ) {
		$cat_ids = tc_get_category_ids_string( $grid['slugs'] ?? tc_get_servicios_slugs() );
		if ( $cat_ids ) {
			$element['settings']['wc_include_category'] = $cat_ids;
		}
		if ( $pantalla_ids ) {
			$element['settings']['wc_exclude_category'] = $pantalla_ids;
		}
	} else {
		$cat_ids = tc_get_category_ids_string( $grid['slugs'] ?? tc_get_pantallas_slugs() );
		if ( $cat_ids ) {
			$element['settings']['wc_include_category'] = $cat_ids;
		}
		if ( $servicio_ids ) {
			$element['settings']['wc_exclude_category'] = $servicio_ids;
		}
	}

	$element['settings']['st_nocontent_text'] = 'No hay productos en esta categoría.';

	return $element;
}

function tc_replace_product_grid_with_shortcode( $element, $context = array() ) {
	$id     = $element['id'] ?? wp_generate_uuid4();
	$grid   = tc_resolve_product_grid_type( $element, $context );
	$config = tc_product_grid_config();
	$limit  = (int) ( ( $grid['limit'] ?? null ) ?? ( $config[ $id ]['limit'] ?? 4 ) );
	$slugs  = $grid['slugs'] ?? tc_get_pantallas_slugs();

	if ( $grid && 'featured_services' === ( $grid['type'] ?? '' ) ) {
		$featured_ids = tc_get_featured_service_product_ids();
		if ( $featured_ids ) {
			$limit  = count( $featured_ids );
			$shortcode = '[products limit="' . $limit . '" columns="4" orderby="post__in" ids="' . implode( ',', $featured_ids ) . '"]';
			return array(
				'id'         => $id,
				'elType'     => 'widget',
				'widgetType' => 'shortcode',
				'settings'   => array( 'shortcode' => $shortcode ),
				'elements'   => array(),
				'isInner'    => false,
			);
		}
		$slugs = tc_get_servicios_slugs();
	}

	$cols = ( 'all_services' === ( $grid['type'] ?? '' ) || '26eec478' === $id ) ? 3 : 4;

	return array(
		'id'         => $id,
		'elType'     => 'widget',
		'widgetType' => 'shortcode',
		'settings'   => array( 'shortcode' => tc_product_shortcode( $slugs, $limit, $cols ) ),
		'elements'   => array(),
		'isInner'    => false,
	);
}

function tc_elementor_context_for_post( $post_id ) {
	$post_id = (int) $post_id;
	$context = array();

	if ( function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'shop' ) === $post_id ) {
		$context['shop_template'] = true;
	}

	$contact = get_page_by_path( 'contactanos' );
	if ( $contact && (int) $contact->ID === $post_id ) {
		$context['contact_page'] = true;
	}

	if ( 'elementor-hf' === get_post_type( $post_id ) && 'type_header' === get_post_meta( $post_id, 'ehf_template_type', true ) ) {
		$context['header_template'] = true;
	}

	$home_id = tc_get_home_page_id();
	if ( $home_id && $home_id === $post_id ) {
		$context['home_page'] = true;
	}

	return $context;
}

function tc_get_contact_page_id() {
	$page = get_page_by_path( 'contactanos' );
	return $page ? (int) $page->ID : 0;
}

function tc_remove_elements_by_id( $elements, $ids ) {
	$out = array();
	foreach ( $elements as $element ) {
		if ( in_array( $element['id'] ?? '', $ids, true ) ) {
			continue;
		}
		if ( ! empty( $element['elements'] ) ) {
			$element['elements'] = tc_remove_elements_by_id( $element['elements'], $ids );
		}
		$out[] = $element;
	}
	return $out;
}

function tc_replace_widget_by_id( $elements, $widget_id, $replacement ) {
	foreach ( $elements as &$element ) {
		if ( ( $element['id'] ?? '' ) === $widget_id ) {
			$element = $replacement;
			continue;
		}
		if ( ! empty( $element['elements'] ) ) {
			$element['elements'] = tc_replace_widget_by_id( $element['elements'], $widget_id, $replacement );
		}
	}
	unset( $element );
	return $elements;
}

function tc_transform_contact_page_elements( $elements ) {
	$elements = tc_remove_elements_by_id(
		$elements,
		array(
			'36274f1',  // Formulario duplicado "Have an Idea".
		)
	);

	$elements = tc_replace_widget_by_id(
		$elements,
		'14bbb665',
		array(
			'id'         => '14bbb665',
			'elType'     => 'widget',
			'widgetType' => 'shortcode',
			'settings'   => array( 'shortcode' => '[tc_contact_whatsapp_card]' ),
			'elements'   => array(),
			'isInner'    => false,
		)
	);

	$elements = tc_replace_widget_by_id(
		$elements,
		'239ef11a',
		array(
			'id'         => '239ef11a',
			'elType'     => 'widget',
			'widgetType' => 'shortcode',
			'settings'   => array( 'shortcode' => '[tc_contact_form_panel]' ),
			'elements'   => array(),
			'isInner'    => false,
		)
	);

	$elements = tc_replace_widget_by_id(
		$elements,
		'68484bea',
		array(
			'id'         => '68484bea',
			'elType'     => 'widget',
			'widgetType' => 'shortcode',
			'settings'   => array(
				'shortcode' => '[tc_contact_whatsapp_card style="banner"]',
			),
			'elements'   => array(),
			'isInner'    => false,
		)
	);

	return tc_replace_widget_by_id(
		$elements,
		'5b4d0c0d',
		array(
			'id'         => '5b4d0c0d',
			'elType'     => 'widget',
			'widgetType' => 'shortcode',
			'settings'   => array( 'shortcode' => '[tc_contact_breadcrumb]' ),
			'elements'   => array(),
			'isInner'    => false,
		)
	);
}

function tc_apply_contact_image_box( $element ) {
	$map = array(
		'1f14c091' => array(
			'title' => 'Dirección',
			'desc'  => TC_ADDRESS,
			'icon'  => 'fas fa-map-marker-alt',
		),
		'5f316331' => array(
			'title' => 'Celular',
			'desc'  => TC_PHONE . "\n" . TC_EMAIL,
			'icon'  => 'fas fa-phone-alt',
		),
		'707df359' => array(
			'title' => 'Horario de atención',
			'desc'  => TC_HOURS,
			'icon'  => 'far fa-clock',
		),
	);

	$id = $element['id'] ?? '';
	if ( ! isset( $map[ $id ] ) ) {
		return $element;
	}

	$element['settings']['title_text']       = $map[ $id ]['title'];
	$element['settings']['description_text'] = $map[ $id ]['desc'];
	$element['settings']['selected_icon']    = array(
		'value'   => $map[ $id ]['icon'],
		'library' => str_starts_with( $map[ $id ]['icon'], 'far ' ) ? 'fa-regular' : 'fa-solid',
	);
	$element['settings']['position']         = 'left';
	$element['settings']['title_size']       = 'h4';
	$element['settings']['_css_classes']     = trim( ( $element['settings']['_css_classes'] ?? '' ) . ' tc-contact-info-box' );

	return $element;
}

function tc_is_contact_page() {
	return is_page() && tc_get_contact_page_id() === get_queried_object_id();
}

function tc_contact_breadcrumb_shortcode() {
	ob_start();
	?>
	<nav class="tc-contact-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'techcomputer' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'techcomputer' ); ?></a>
		<span aria-hidden="true"> / </span>
		<span><?php esc_html_e( 'Contáctanos', 'techcomputer' ); ?></span>
	</nav>
	<?php
	return ob_get_clean();
}

function tc_contact_form_panel_shortcode() {
	ob_start();
	?>
	<div class="tc-contact-form-panel">
		<h3 class="tc-contact-form-panel__title"><?php esc_html_e( 'Contáctanos', 'techcomputer' ); ?></h3>
		<?php echo tc_contact_form_shortcode(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	<?php
	return ob_get_clean();
}

function tc_contact_whatsapp_card_shortcode( $atts = array() ) {
	$atts = shortcode_atts(
		array(
			'style' => 'card',
		),
		$atts,
		'tc_contact_whatsapp_card'
	);

	$is_banner = ( 'banner' === $atts['style'] );
	$class     = $is_banner ? 'tc-contact-wa-card tc-contact-wa-card--banner' : 'tc-contact-wa-card';

	ob_start();
	?>
	<div class="<?php echo esc_attr( $class ); ?>">
		<div class="tc-contact-wa-card__icon" aria-hidden="true">
			<svg viewBox="0 0 32 32" focusable="false"><path d="M16.01 3C9.39 3 4 8.28 4 14.78c0 2.07.58 4.1 1.68 5.87L4 29l8.58-1.62A12.9 12.9 0 0 0 16.02 27C22.63 27 28 21.72 28 15.22 27.99 8.72 22.62 3 16.01 3Zm0 23.13c-1.66 0-3.29-.44-4.72-1.28l-.34-.2-5.09 1.06 1.08-4.9-.22-.35a9.86 9.86 0 0 1-1.52-5.28C5.2 9.9 10.07 5.2 16.01 5.2c5.93 0 10.79 4.7 10.79 10.48 0 5.78-4.86 10.48-10.79 10.48Zm5.92-7.84c-.32-.16-1.9-.94-2.2-1.05-.3-.1-.52-.16-.74.16-.22.32-.85 1.05-1.04 1.27-.19.22-.39.24-.71.08-.32-.16-1.36-.5-2.59-1.6-.96-.85-1.6-1.9-1.79-2.22-.19-.32-.02-.5.14-.66.14-.14.32-.39.48-.58.16-.19.22-.32.32-.54.1-.22.05-.4-.03-.58-.08-.16-.74-1.78-1.01-2.44-.27-.64-.54-.55-.74-.56h-.63c-.22 0-.58.08-.88.4-.3.32-1.15 1.12-1.15 2.74 0 1.62 1.18 3.18 1.34 3.4.16.22 2.32 3.54 5.62 4.96.79.34 1.4.54 1.88.69.79.25 1.51.22 2.08.13.63-.1 1.9-.78 2.17-1.53.27-.75.27-1.39.19-1.53-.08-.14-.3-.22-.62-.38Z"/></svg>
		</div>
		<div class="tc-contact-wa-card__body">
			<p class="tc-contact-wa-card__label">WhatsApp</p>
			<p class="tc-contact-wa-card__phone"><?php echo esc_html( TC_PHONE ); ?></p>
			<a class="tc-contact-wa-card__link" href="<?php echo esc_url( TC_WHATSAPP ); ?>" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Escríbenos', 'techcomputer' ); ?>
			</a>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

function tc_enqueue_contact_assets() {
	if ( ! tc_is_contact_page() ) {
		return;
	}

	$p = tc_brand_palette();
	$css = '
.tc-contact-breadcrumb{text-align:center;margin:0 0 8px;font-size:.95rem;color:#64748b}
.tc-contact-breadcrumb a{color:' . $p['primary'] . ';text-decoration:none;font-weight:600}
.tc-contact-breadcrumb a:hover{text-decoration:underline}
.tc-contact-info-box .elementor-image-box-title{font-size:1.05rem;margin-bottom:8px;color:' . $p['dark'] . '}
.tc-contact-info-box .elementor-image-box-description{white-space:pre-line;line-height:1.6}
.tc-contact-form-panel__title{margin:0 0 18px;font-size:1.5rem;color:' . $p['dark'] . '}
.tc-contact-wa-card{display:flex;align-items:center;gap:16px;padding:18px 22px;border:1px solid ' . $p['primary'] . ';border-radius:20px;background:' . $p['tint'] . '}
.tc-contact-wa-card__icon{width:52px;height:52px;border-radius:50%;background:#25D366;color:#fff;display:flex;align-items:center;justify-content:center;flex:0 0 auto}
.tc-contact-wa-card__icon svg{width:28px;height:28px;fill:currentColor}
.tc-contact-wa-card__label{margin:0 0 4px;font-weight:700;color:' . $p['dark'] . ';font-size:.9rem}
.tc-contact-wa-card__phone{margin:0 0 8px;font-size:1.1rem;font-weight:700;color:' . $p['dark'] . '}
.tc-contact-wa-card__link{display:inline-flex;font-weight:700;color:' . $p['primary'] . '!important;text-decoration:none}
.tc-contact-wa-card__link:hover{text-decoration:underline}
.tc-contact-wa-card--banner{justify-content:center;text-align:left;max-width:420px;margin:0 auto}
.tc-contact-directions-wrap{max-width:1140px;margin:0 auto;padding:20px}
@media(max-width:767px){.tc-contact-wa-card{flex-direction:column;text-align:center}}
';
	wp_register_style( 'tc-contact-page', false, array( 'tc-brand-colors' ), TC_SETUP_VERSION );
	wp_enqueue_style( 'tc-contact-page' );
	wp_add_inline_style( 'tc-contact-page', $css );
}

function tc_render_contact_page_directions() {
	if ( function_exists( 'tc_contact_page_is_active' ) && tc_contact_page_is_active() ) {
		return;
	}
	if ( ! tc_is_contact_page() || ! function_exists( 'tc_render_directions_section' ) ) {
		return;
	}
	echo '<div class="tc-contact-directions-wrap">';
	tc_render_directions_section();
	echo '</div>';
}

function tc_apply_header_icon_settings( $element ) {
	$icon = $element['settings']['selected_icon']['value'] ?? '';
	if ( ! str_contains( $icon, 'user' ) ) {
		return $element;
	}

	$element['settings']['link'] = array(
		'url'               => '',
		'is_external'       => '',
		'nofollow'          => '',
		'custom_attributes' => '',
	);
	$element['settings']['_css_classes'] = trim( ( $element['settings']['_css_classes'] ?? '' ) . ' tc-header-user-decor' );

	return $element;
}

function tc_apply_header_logo_element( $element, $context ) {
	unset( $context );
	// El logo lo gestiona la usuaria en Elementor; no sobrescribir desde código.
	return $element;
}

function tc_apply_header_container_element( $element, $context ) {
	if ( empty( $context['header_template'] ) || ! tc_header_element_in_group( $element['id'] ?? '', 'shell' ) ) {
		return $element;
	}

	$element['settings']['flex_wrap']              = 'wrap';
	$element['settings']['flex_wrap_tablet']       = 'nowrap';
	$element['settings']['flex_wrap_mobile']       = 'nowrap';
	$element['settings']['flex_align_items']       = 'center';
	$element['settings']['flex_align_items_tablet'] = 'center';
	$element['settings']['flex_align_items_mobile'] = 'center';
	$element['settings']['flex_direction_tablet']  = 'row';
	$element['settings']['flex_direction_mobile']  = 'row';
	$element['settings']['flex_justify_content_tablet'] = 'flex-start';
	$element['settings']['flex_justify_content_mobile'] = 'flex-start';
	$element['settings']['flex_gap_mobile']        = array(
		'unit'  => 'px',
		'size'  => 8,
		'sizes' => array(),
		'column' => '8',
		'row'    => '0',
		'isLinked' => true,
	);
	$element['settings']['padding']                = array(
		'unit'   => 'px',
		'top'    => '24',
		'right'  => '24',
		'bottom' => '24',
		'left'   => '24',
		'isLinked' => false,
	);
	$element['settings']['padding_tablet'] = array(
		'unit'   => 'px',
		'top'    => '20',
		'right'  => '20',
		'bottom' => '20',
		'left'   => '20',
		'isLinked' => true,
	);
	$element['settings']['padding_mobile'] = array(
		'unit'     => 'px',
		'top'      => '12',
		'right'    => '14',
		'bottom'   => '12',
		'left'     => '14',
		'isLinked' => false,
	);
	$element['settings']['_css_classes'] = trim( ( $element['settings']['_css_classes'] ?? '' ) . ' tc-header-shell' );

	return $element;
}

function tc_apply_header_menu_element( $element, $context ) {
	if ( empty( $context['header_template'] ) || ! tc_header_element_in_group( $element['id'] ?? '', 'nav' ) || 'navigation-menu' !== ( $element['widgetType'] ?? '' ) ) {
		return $element;
	}

	$element['settings']['layout']   = 'horizontal';
	$element['settings']['dropdown'] = 'tablet';
	$element['settings']['resp_align'] = 'right';
	$element['settings']['padding_horizontal_menu_item'] = array(
		'unit'  => 'px',
		'size'  => 14,
		'sizes' => array(),
	);
	$element['settings']['padding_horizontal_menu_item_tablet'] = array(
		'unit'  => 'px',
		'size'  => 10,
		'sizes' => array(),
	);
	$element['settings']['padding_horizontal_menu_item_mobile'] = array(
		'unit'  => 'px',
		'size'  => 0,
		'sizes' => array(),
	);
	$element['settings']['menu_typography_font_size_tablet'] = array(
		'unit'  => 'px',
		'size'  => 13,
		'sizes' => array(),
	);
	$element['settings']['menu_typography_font_size_mobile'] = array(
		'unit'  => 'px',
		'size'  => 15,
		'sizes' => array(),
	);
	$element['settings']['_css_classes'] = trim( ( $element['settings']['_css_classes'] ?? '' ) . ' tc-header-nav' );
	$element['settings']['width_tablet'] = array(
		'unit'  => 'custom',
		'size'  => 'auto',
		'sizes' => array(),
	);
	$element['settings']['width_mobile'] = array(
		'unit'  => 'custom',
		'size'  => 'auto',
		'sizes' => array(),
	);
	$element['settings']['_element_width_tablet'] = 'initial';
	$element['settings']['_element_width_mobile'] = 'initial';

	return $element;
}

function tc_apply_header_actions_element( $element, $context ) {
	if ( empty( $context['header_template'] ) || ! tc_header_element_in_group( $element['id'] ?? '', 'actions' ) ) {
		return $element;
	}

	$element['settings']['width'] = array(
		'unit'  => '%',
		'size'  => 7,
		'sizes' => array(),
	);
	$element['settings']['width_tablet'] = array(
		'unit'  => 'custom',
		'size'  => 'auto',
		'sizes' => array(),
	);
	$element['settings']['width_mobile'] = array(
		'unit'  => 'custom',
		'size'  => 'auto',
		'sizes' => array(),
	);
	$element['settings']['flex_direction']              = 'row';
	$element['settings']['flex_justify_content']        = 'flex-end';
	$element['settings']['flex_justify_content_tablet'] = 'flex-end';
	$element['settings']['flex_justify_content_mobile'] = 'flex-end';
	$element['settings']['flex_align_items']            = 'center';
	$element['settings']['_element_width_tablet']       = 'initial';
	$element['settings']['_element_width_mobile']       = 'initial';
	$element['settings']['_css_classes']                = trim( ( $element['settings']['_css_classes'] ?? '' ) . ' tc-header-actions' );

	return $element;
}

function tc_fix_kit_text( $settings, $context = array() ) {
	if ( empty( $settings['title'] ) ) {
		return $settings;
	}
	$map = array(
		'All Servicios'       => 'Todos los Servicios',
		'Nuestros Servicios'  => 'Pantallas para Notebook',
	);
	if ( ! empty( $context['shop_template'] ) ) {
		$map['Nuestros Servicios'] = 'Catálogo Techcomputer';
		$map['All Servicios']      = 'Catálogo Techcomputer';
	}
	if ( ! empty( $context['contact_page'] ) ) {
		$map["Have an Idea? Let's Make It Happen"] = 'Contáctanos';
	}
	if ( isset( $map[ $settings['title'] ] ) ) {
		$settings['title'] = $map[ $settings['title'] ];
	}
	return $settings;
}

function tc_is_home_hero_heading( $element ) {
	if ( 'heading' !== ( $element['widgetType'] ?? '' ) ) {
		return false;
	}
	$id = $element['id'] ?? '';
	if ( in_array( $id, array( '6577b81d', '4309a45' ), true ) ) {
		return true;
	}
	$title = (string) ( $element['settings']['title'] ?? '' );
	return false !== stripos( $title, 'Servicio Técnico Notebook' );
}

function tc_apply_home_hero_heading_settings( &$settings ) {
	unset( $settings['__globals__']['typography_typography'] );
	if ( empty( $settings['__globals__'] ) ) {
		unset( $settings['__globals__'] );
	}
	$settings['typography_typography']         = 'custom';
	$settings['typography_font_family']        = 'Plus Jakarta Sans';
	$settings['typography_font_weight']        = '600';
	$settings['typography_font_size']          = array(
		'unit'  => 'px',
		'size'  => 52,
		'sizes' => array(),
	);
	$settings['typography_font_size_tablet']   = array(
		'unit'  => 'px',
		'size'  => 38,
		'sizes' => array(),
	);
	$settings['typography_font_size_mobile']   = array(
		'unit'  => 'px',
		'size'  => 24,
		'sizes' => array(),
	);
	$settings['typography_line_height']        = array(
		'unit'  => 'em',
		'size'  => 1.15,
		'sizes' => array(),
	);
	$settings['typography_line_height_tablet'] = array(
		'unit'  => 'em',
		'size'  => 1.15,
		'sizes' => array(),
	);
	$settings['typography_line_height_mobile'] = array(
		'unit'  => 'em',
		'size'  => 1.35,
		'sizes' => array(),
	);
	$settings['align_mobile']                  = 'center';
	$settings['_margin_mobile']                = array(
		'unit'     => 'px',
		'top'      => '0',
		'right'    => '0',
		'bottom'   => '12',
		'left'     => '0',
		'isLinked' => false,
	);
}

function tc_service_section_element_ids() {
	return array(
		'cards'  => array( '7f7dfe2e', '52dca48e', '69952afa', '10107de4', '2ec7f5d9', '2056c38b', '5ccf4cab', '39201167' ),
		'text'   => array( '34204be7', '4845b9fb', '7dc815b0', '3d0e3628', '55d8e110', '1d7d48ee' ),
		'image'  => array( '7143a4d', '1528b3ab', '34c0c670', '5bf6c292', '4a1c26a0', '332d2f29', '7fece403', '2929e49b' ),
		'arrow'  => array( '7c50b16f', '54854d0d', '2617b167', '6a4db622', 'c04b819', 'b329287', '200ac722', '6edaa433' ),
		'wrap'   => array( '5b79c8ca', '16695222', '7703a015' ),
	);
}

function tc_apply_service_mobile_elementor_settings( $id, $settings ) {
	$groups = tc_service_section_element_ids();

	if ( in_array( $id, $groups['cards'], true ) ) {
		$settings['flex_direction_mobile']      = 'row';
		$settings['flex_wrap_mobile']           = 'nowrap';
		$settings['background_size_mobile']     = 'initial';
		$settings['background_position_mobile'] = 'top center';
		unset( $settings['background_bg_width_mobile'], $settings['background_bg_width_mobile_tablet'] );
		$settings['padding_mobile']             = array(
			'unit'     => 'px',
			'top'      => '0',
			'right'    => '0',
			'bottom'   => '18',
			'left'     => '0',
			'isLinked' => false,
		);
		$settings['margin_mobile']              = array(
			'unit'     => 'px',
			'top'      => '0',
			'right'    => '0',
			'bottom'   => '18',
			'left'     => '0',
			'isLinked' => false,
		);
	}

	if ( in_array( $id, $groups['text'], true ) ) {
		$settings['width_mobile']            = array(
			'unit'  => '%',
			'size'  => 56,
			'sizes' => array(),
		);
		$settings['flex_direction_mobile']   = 'column';
		$settings['min_height_mobile']       = array(
			'unit'  => 'px',
			'size'  => 0,
			'sizes' => array(),
		);
		$settings['padding_mobile']          = array(
			'unit'     => 'px',
			'top'      => '26',
			'right'    => '12',
			'bottom'   => '14',
			'left'     => '18',
			'isLinked' => false,
		);
		$settings['margin_mobile']           = array(
			'unit'     => 'px',
			'top'      => '0',
			'right'    => '0',
			'bottom'   => '0',
			'left'     => '0',
			'isLinked' => true,
		);
	}

	if ( in_array( $id, $groups['image'], true ) ) {
		$settings['width_mobile']               = array(
			'unit'  => '%',
			'size'  => 44,
			'sizes' => array(),
		);
		$settings['min_height_mobile']          = array(
			'unit'  => 'px',
			'size'  => 0,
			'sizes' => array(),
		);
		$settings['background_size_mobile']     = 'contain';
		$settings['background_position_mobile'] = 'center bottom';
		$settings['margin_mobile']              = array(
			'unit'     => 'px',
			'top'      => '0',
			'right'    => '0',
			'bottom'   => '0',
			'left'     => '0',
			'isLinked' => true,
		);
	}

	if ( in_array( $id, $groups['arrow'], true ) ) {
		unset( $settings['width_mobile'], $settings['margin_mobile'] );
	}

	if ( in_array( $id, $groups['wrap'], true ) ) {
		$settings['width_mobile']  = array(
			'unit'  => '%',
			'size'  => 100,
			'sizes' => array(),
		);
		$settings['margin_mobile'] = array(
			'unit'     => 'px',
			'top'      => '0',
			'right'    => '0',
			'bottom'   => '0',
			'left'     => '0',
			'isLinked' => true,
		);
		$settings['padding_mobile'] = array(
			'unit'     => 'px',
			'top'      => '0',
			'right'    => '0',
			'bottom'   => '0',
			'left'     => '0',
			'isLinked' => true,
		);
	}

	return $settings;
}

function tc_apply_home_element_settings( $element, $context ) {
	if ( empty( $context['home_page'] ) || empty( $element['settings'] ) || ! is_array( $element['settings'] ) ) {
		return $element;
	}

	$id       = $element['id'] ?? '';
	$widget   = $element['widgetType'] ?? '';
	$settings = &$element['settings'];

	if ( tc_is_home_hero_heading( $element ) ) {
		tc_apply_home_hero_heading_settings( $settings );
	}

	if ( in_array( $id, array( 'dbe225e', '3c2b444a' ), true ) ) {
		$settings['margin_mobile']              = array(
			'unit'     => 'px',
			'top'      => '0',
			'right'    => '0',
			'bottom'   => '0',
			'left'     => '0',
			'isLinked' => false,
		);
		$settings['padding_mobile']             = array(
			'unit'     => 'px',
			'top'      => '56',
			'right'    => '16',
			'bottom'   => '24',
			'left'     => '16',
			'isLinked' => false,
		);
		$settings['flex_direction_mobile']      = 'column';
		$settings['background_size_mobile']     = 'cover';
		$settings['background_position_mobile'] = 'center center';
	}

	if ( in_array( $id, array( '27241db2', '24a90c55' ), true ) && 'text-editor' === $widget ) {
		$settings['_element_custom_width_mobile'] = array(
			'unit'  => '%',
			'size'  => 100,
			'sizes' => array(),
		);
		$settings['align_mobile']                 = 'center';
		$settings['typography_font_size_mobile']  = array(
			'unit'  => 'px',
			'size'  => 15,
			'sizes' => array(),
		);
		$settings['typography_line_height_mobile'] = array(
			'unit'  => 'em',
			'size'  => 1.55,
			'sizes' => array(),
		);
	}

	$settings = tc_apply_service_mobile_elementor_settings( $id, $settings );

	if ( '3ddbcf02' === $id ) {
		$settings['flex_direction_mobile'] = 'column';
	}

	if ( in_array( $id, array( '6534553e', '5839ad66' ), true ) ) {
		$settings['width_mobile'] = array(
			'unit'  => '%',
			'size'  => 100,
			'sizes' => array(),
		);
	}

	if ( '73f4fd16' === $id ) {
		$settings['padding_mobile'] = array(
			'unit'     => 'px',
			'top'      => '24',
			'right'    => '16',
			'bottom'   => '32',
			'left'     => '16',
			'isLinked' => false,
		);
		$settings['margin_mobile'] = array(
			'unit'     => 'px',
			'top'      => '0',
			'right'    => '0',
			'bottom'   => '0',
			'left'     => '0',
			'isLinked' => true,
		);
	}

	if ( ! empty( $settings['width_mobile']['size'] ) && (int) $settings['width_mobile']['size'] > 100 && '%' !== ( $settings['width_mobile']['unit'] ?? '' ) ) {
		$settings['width_mobile'] = array(
			'unit'  => '%',
			'size'  => 100,
			'sizes' => array(),
		);
	}

	return $element;
}

/**
 * Elementos del hero de inicio que se ocultan (badge flotante con imagen pequeña).
 *
 * @return string[]
 */
function tc_home_removed_element_ids() {
	return array( '2657010a', '5d582024' );
}

/**
 * @param array<int, array<string, mixed>> $elements
 * @param string[]                         $remove_ids
 * @return array<int, array<string, mixed>>
 */
function tc_remove_elementor_elements_by_ids( $elements, $remove_ids ) {
	if ( empty( $elements ) || ! is_array( $elements ) ) {
		return $elements;
	}

	$filtered = array();
	foreach ( $elements as $element ) {
		$id = $element['id'] ?? '';
		if ( in_array( $id, $remove_ids, true ) ) {
			continue;
		}
		if ( ! empty( $element['elements'] ) ) {
			$element['elements'] = tc_remove_elementor_elements_by_ids( $element['elements'], $remove_ids );
		}
		$filtered[] = $element;
	}

	return array_values( $filtered );
}

function tc_process_elementor_elements( $elements, $context = array() ) {
	if ( ! empty( $context['shop_template'] ) ) {
		$elements = tc_transform_shop_template_elements( $elements );
	}
	if ( ! empty( $context['contact_page'] ) ) {
		$elements = tc_transform_contact_page_elements( $elements );
	}
	if ( ! empty( $context['home_page'] ) ) {
		$elements = tc_remove_elementor_elements_by_ids( $elements, tc_home_removed_element_ids() );
	}

	foreach ( $elements as &$element ) {
		if ( ! empty( $element['elements'] ) ) {
			$element['elements'] = tc_process_elementor_elements( $element['elements'], $context );
		}

		if ( ! empty( $context['header_template'] ) && tc_is_header_logo_slot_element( $element ) ) {
			continue;
		}

		$widget = $element['widgetType'] ?? '';

		if ( 'image-box' === $widget && ! empty( $element['settings']['title_text'] ) ) {
			if ( ! empty( $context['contact_page'] ) ) {
				$element = tc_apply_contact_image_box( $element );
			}
			$context['service_title'] = $element['settings']['title_text'];
		}

		if ( ! empty( $context['header_template'] ) && 'icon' === $widget ) {
			$element = tc_apply_header_icon_settings( $element );
		}

		if ( 'container' === ( $element['elType'] ?? '' ) ) {
			$element = tc_apply_header_container_element( $element, $context );
			$element = tc_apply_header_actions_element( $element, $context );
		}

		if ( 'navigation-menu' === $widget ) {
			$element = tc_apply_header_menu_element( $element, $context );
		}

		if ( 'jkit_product_grid' === $widget ) {
			if ( tc_jkit_is_available() ) {
				$element = tc_configure_jkit_product_grid( $element, $context );
			} else {
				$element = tc_replace_product_grid_with_shortcode( $element, $context );
			}
			continue;
		}

		if ( 'button' === $widget ) {
			$element['settings'] = tc_apply_link_to_button( $element['settings'] ?? array(), $context );
		}

		if ( 'heading' === $widget && ! empty( $element['settings'] ) ) {
			$element['settings'] = tc_fix_kit_text( $element['settings'], $context );
		}

		$element = tc_apply_home_element_settings( $element, $context );

		if ( ! empty( $element['settings'] ) && is_array( $element['settings'] ) ) {
			$element['settings'] = tc_apply_google_testimonial_settings( $element['settings'], $widget );
			tc_normalize_brand_settings( $element['settings'] );
			tc_localize_elementor_settings( $element['settings'], $widget );
		}
	}
	unset( $element );
	return $elements;
}

function tc_persist_elementor_data( $post_id ) {
	$raw = get_post_meta( $post_id, '_elementor_data', true );
	if ( empty( $raw ) ) {
		return false;
	}
	$data      = json_decode( $raw, true );
	if ( ! is_array( $data ) ) {
		return false;
	}
	$media_map = tc_collect_elementor_media_map( $data );
	$data      = tc_process_elementor_elements( $data, tc_elementor_context_for_post( $post_id ) );
	if ( tc_should_preserve_elementor_media() ) {
		tc_restore_elementor_media_map( $data, $media_map );
	}
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
	return true;
}

function tc_kit_path() {
	return trailingslashit( wp_upload_dir()['basedir'] ) . 'template-kits/' . TC_KIT_FOLDER . '/';
}

function tc_kit_register() {
	require_once ABSPATH . 'wp-content/plugins/template-kit-import/vendor/template-kit-import/template-kit-import.php';

	$kits = get_posts(
		array(
			'post_type'      => 'envato_tk_import',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		)
	);
	foreach ( $kits as $kit ) {
		if ( TC_KIT_FOLDER === get_post_meta( $kit->ID, 'envato_tk_folder_name', true ) ) {
			return (int) $kit->ID;
		}
	}

	$manifest_path = tc_kit_path() . 'manifest.json';
	if ( ! file_exists( $manifest_path ) ) {
		return new WP_Error( 'no_manifest', 'No se encontró el template kit.' );
	}
	$manifest = json_decode( file_get_contents( $manifest_path ), true );
	$post_id  = wp_insert_post(
		array(
			'post_title'  => $manifest['title'] ?? 'Techcomputer Kit',
			'post_type'   => 'envato_tk_import',
			'post_status' => 'publish',
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}
	update_post_meta( $post_id, 'envato_tk_manifest', $manifest );
	update_post_meta( $post_id, 'envato_tk_folder_name', TC_KIT_FOLDER );
	update_post_meta( $post_id, 'envato_tk_builder', 'elementor' );
	return (int) $post_id;
}

function tc_import_kit_template( $kit_id, $index ) {
	return \Envato_Template_Kit_Import\Importer::get_instance()->handle_template_import( $kit_id, $index );
}

function tc_find_library_template( $title ) {
	$posts = get_posts(
		array(
			'post_type'      => 'elementor_library',
			'post_status'    => 'publish',
			'title'          => $title,
			'posts_per_page' => 1,
		)
	);
	if ( $posts ) {
		return (int) $posts[0]->ID;
	}
	$posts = get_posts(
		array(
			'post_type'      => 'elementor_library',
			'post_status'    => 'publish',
			's'              => $title,
			'posts_per_page' => 5,
		)
	);
	foreach ( $posts as $post ) {
		if ( stripos( $post->post_title, $title ) !== false ) {
			return (int) $post->ID;
		}
	}
	return 0;
}

function tc_setup_hfe_template( $library_id, $type, $title ) {
	if ( ! $library_id ) {
		return new WP_Error( 'no_library', 'Plantilla de biblioteca no encontrada: ' . $title );
	}

	$existing = get_posts(
		array(
			'post_type'      => 'elementor-hf',
			'posts_per_page' => 1,
			'meta_key'       => 'ehf_template_type',
			'meta_value'     => $type,
			'post_status'    => 'publish',
		)
	);

	if ( $existing ) {
		$post_id = (int) $existing[0]->ID;
	} else {
		$post_id = wp_insert_post(
			array(
				'post_title'  => $title,
				'post_type'   => 'elementor-hf',
				'post_status' => 'publish',
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}
	}

	\Elementor\Plugin::$instance->db->safe_copy_elementor_meta( $library_id, $post_id );
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, 'ehf_template_type', $type );
	update_post_meta( $post_id, 'display-on-canvas-template', '1' );
	update_post_meta(
		$post_id,
		'ehf_target_include_locations',
		array(
			'rule'     => array( 'basic-global' ),
			'specific' => array(),
		)
	);
	update_post_meta( $post_id, 'ehf_target_exclude_locations', array() );
	update_post_meta( $post_id, 'ehf_target_user_roles', array() );

	tc_persist_elementor_data( $post_id );
	return $post_id;
}

function tc_setup_navigation_menu() {
	$menu_name = 'header';
	$menu      = wp_get_nav_menu_object( $menu_name );
	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $menu_name );
	} else {
		$menu_id = (int) $menu->term_id;
	}

	$items = wp_get_nav_menu_items( $menu_id );
	if ( $items ) {
		return $menu_id;
	}

	$links = array(
		array( 'Inicio', home_url( '/' ) ),
		array( 'Nosotros', tc_page_url( 'nosotros' ) ),
		array( 'Repuestos', function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ),
		array( 'Servicios', tc_cat_url( tc_get_servicios_slugs() ) ),
		array( 'Contáctanos', tc_page_url( 'contactanos' ) ),
	);

	$order = 1;
	foreach ( $links as $link ) {
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'  => $link[0],
				'menu-item-url'    => $link[1],
				'menu-item-status' => 'publish',
				'menu-item-type'   => 'custom',
				'menu-item-position' => $order++,
			)
		);
	}

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$locations['menu-1'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	return $menu_id;
}

function tc_create_page_from_template( $title, $slug, $template_id ) {
	$page = get_page_by_path( $slug );
	if ( ! $page ) {
		$page_id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '',
			),
			true
		);
	} else {
		$page_id = (int) $page->ID;
	}

	if ( is_wp_error( $page_id ) || ! $template_id ) {
		return $page_id;
	}

	$has_builder = ( 'builder' === get_post_meta( $page_id, '_elementor_edit_mode', true ) );
	if ( ! $has_builder ) {
		\Elementor\Plugin::$instance->db->safe_copy_elementor_meta( $template_id, $page_id );
		update_post_meta( $page_id, '_wp_page_template', 'elementor_canvas' );
	}
	tc_persist_elementor_data( $page_id );
	return $page_id;
}

/**
 * Aplica la plantilla Product del kit (product.json) a la página de tienda WooCommerce.
 */
function tc_apply_kit_json_to_page( $post_id, $json_relative_path, $force = false ) {
	if ( ! $force && tc_should_preserve_elementor_media() && 'builder' === get_post_meta( $post_id, '_elementor_edit_mode', true ) ) {
		return tc_persist_elementor_data( $post_id );
	}

	$path = tc_kit_path() . $json_relative_path;
	if ( ! file_exists( $path ) ) {
		return new WP_Error( 'no_template', 'No se encontró: ' . $json_relative_path );
	}

	$template = json_decode( file_get_contents( $path ), true );
	if ( empty( $template['content'] ) ) {
		return new WP_Error( 'bad_template', 'Plantilla JSON inválida.' );
	}

	$content = tc_process_elementor_elements( $template['content'], array( 'shop_template' => true ) );
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $content ) ) );
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $post_id, '_wp_page_template', 'elementor_canvas' );

	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	return true;
}

function tc_setup_shop_page() {
	if ( ! function_exists( 'wc_get_page_id' ) ) {
		return new WP_Error( 'no_wc', 'WooCommerce no está activo.' );
	}

	$shop_id = wc_get_page_id( 'shop' );
	if ( $shop_id <= 0 ) {
		return new WP_Error( 'no_shop', 'No hay página de tienda configurada.' );
	}

	return tc_apply_kit_json_to_page( $shop_id, 'templates/product.json', 'builder' !== get_post_meta( $shop_id, '_elementor_edit_mode', true ) );
}

function tc_shop_uses_elementor_kit() {
	$shop_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : 0;
	return $shop_id > 0 && 'builder' === get_post_meta( $shop_id, '_elementor_edit_mode', true );
}

add_action( 'woocommerce_before_main_content', 'tc_render_shop_elementor_kit', 1 );
add_action( 'woocommerce_before_main_content', 'tc_render_shop_catalog_fallback', 5 );
add_filter( 'woocommerce_products_will_display', 'tc_shop_hide_default_products_loop' );
add_filter( 'woocommerce_show_page_title', 'tc_hide_shop_default_title' );
add_action( 'wp', 'tc_shop_remove_default_notices' );

function tc_render_shop_elementor_kit() {
	if ( ! tc_shop_uses_elementor_kit() || ! function_exists( 'is_shop' ) || ! is_shop() ) {
		return;
	}
	if ( ! class_exists( '\Elementor\Plugin' ) ) {
		return;
	}

	$shop_id = wc_get_page_id( 'shop' );
	echo '<div class="tc-shop-elementor-kit">';
	echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $shop_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</div>';
}

function tc_render_shop_catalog_fallback() {
	if ( tc_shop_uses_elementor_kit() || ! function_exists( 'is_shop' ) || ! is_shop() ) {
		return;
	}
	echo do_shortcode( '[tc_shop_catalog]' );
}

function tc_shop_hide_default_products_loop( $will_display ) {
	if ( tc_shop_uses_elementor_kit() && function_exists( 'is_shop' ) && is_shop() ) {
		return false;
	}
	return $will_display;
}

function tc_shop_remove_default_notices() {
	if ( ! tc_shop_uses_elementor_kit() || ! function_exists( 'is_shop' ) || ! is_shop() ) {
		return;
	}
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_output_all_notices', 10 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
}

function tc_disable_store_coming_soon() {
	update_option( 'woocommerce_coming_soon', 'no' );
	update_option( 'woocommerce_store_pages_only', 'no' );
}

function tc_hide_shop_default_title( $show ) {
	if ( tc_shop_uses_elementor_kit() && function_exists( 'is_shop' ) && is_shop() ) {
		return false;
	}
	return $show;
}

function tc_ensure_jeg_elementor_kit() {
	if ( tc_jkit_is_available() ) {
		return true;
	}
	if ( ! function_exists( 'activate_plugin' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$plugin = 'jeg-elementor-kit/jeg-elementor-kit.php';
	if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin ) ) {
		return new WP_Error( 'no_jkit', 'Instala el plugin Jeg Elementor Kit (requerido por el template kit).' );
	}
	$result = activate_plugin( $plugin );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return true;
}

function tc_get_home_page_id() {
	$front = (int) get_option( 'page_on_front' );
	if ( $front > 0 ) {
		return $front;
	}
	$page = get_page_by_path( 'inicio' );
	if ( $page ) {
		return (int) $page->ID;
	}
	$page = get_page_by_title( 'Inicio Techcomputer' );
	return $page ? (int) $page->ID : 0;
}

function tc_run_full_setup() {
	if ( ! class_exists( '\Elementor\Plugin' ) ) {
		return new WP_Error( 'no_elementor', 'Activa Elementor primero.' );
	}
	if ( ! function_exists( 'hfe_header_enabled' ) ) {
		return new WP_Error( 'no_hfe', 'Activa Ultimate Addons for Elementor (Header Footer Elementor).' );
	}

	$jkit = tc_ensure_jeg_elementor_kit();
	if ( is_wp_error( $jkit ) ) {
		return $jkit;
	}

	tc_disable_store_coming_soon();

	$kit_id = tc_kit_register();
	if ( is_wp_error( $kit_id ) ) {
		return $kit_id;
	}

	tc_import_kit_template( $kit_id, TC_IDX_GLOBAL );
	tc_apply_techcomputer_brand_colors();

	$home_lib    = tc_import_kit_template( $kit_id, TC_IDX_HOME );
	$header_lib  = tc_import_kit_template( $kit_id, TC_IDX_HEADER );
	$footer_lib  = tc_import_kit_template( $kit_id, TC_IDX_FOOTER );
	$about_lib   = tc_import_kit_template( $kit_id, TC_IDX_ABOUT );
	$contact_lib = tc_import_kit_template( $kit_id, TC_IDX_CONTACT );

	if ( is_wp_error( $home_lib ) ) {
		$home_lib = tc_find_library_template( 'Inicio' ) ?: tc_find_library_template( 'Home' );
	}
	if ( is_wp_error( $header_lib ) ) {
		$header_lib = tc_find_library_template( 'Header' );
	}
	if ( is_wp_error( $footer_lib ) ) {
		$footer_lib = tc_find_library_template( 'Footer' );
	}

	$home_page = tc_create_page_from_template( 'Inicio Techcomputer', 'inicio', is_numeric( $home_lib ) ? $home_lib : 0 );
	if ( is_wp_error( $home_page ) ) {
		return $home_page;
	}

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $home_page );

	tc_create_page_from_template( 'Nosotros', 'nosotros', is_numeric( $about_lib ) ? $about_lib : 0 );
	tc_create_page_from_template( 'Contáctanos', 'contactanos', is_numeric( $contact_lib ) ? $contact_lib : 0 );

	tc_setup_hfe_template( is_numeric( $header_lib ) ? $header_lib : 0, 'type_header', 'Techcomputer Header' );
	tc_setup_hfe_template( is_numeric( $footer_lib ) ? $footer_lib : 0, 'type_footer', 'Techcomputer Footer' );

	tc_setup_navigation_menu();

	$shop_result = tc_setup_shop_page();
	if ( is_wp_error( $shop_result ) ) {
		return $shop_result;
	}

	tc_setup_repuesto_categories();
	tc_ensure_featured_service_products();

	// Re-aplicar home y tienda con categorías y grillas jkit actualizadas.
	$header_id = function_exists( 'get_hfe_header_id' ) ? get_hfe_header_id() : 0;
	if ( $header_id ) {
		tc_persist_elementor_data( $header_id );
	}
	$home_id = tc_get_home_page_id();
	if ( $home_id ) {
		tc_persist_elementor_data( $home_id );
	}
	$contact_id = tc_get_contact_page_id();
	if ( $contact_id ) {
		tc_persist_elementor_data( $contact_id );
	}
	$shop_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : 0;
	if ( $shop_id > 0 ) {
		tc_persist_elementor_data( $shop_id );
	}

	tc_apply_techcomputer_brand_colors();

	update_option( 'tc_setup_version', TC_SETUP_VERSION );

	return array(
		'home_id'  => $home_page,
		'home_url' => get_permalink( $home_page ),
		'shop_url' => wc_get_page_permalink( 'shop' ),
	);
}

/** Referencia: royalblue-dunlin-248831.hostingersite.com (Waze, Maps, Metro + iframe). */
function tc_directions_hostinger_uploads() {
	return 'https://royalblue-dunlin-248831.hostingersite.com/wp-content/uploads/2025/12';
}

function tc_directions_resolve_upload( $filename, $fallback_url ) {
	$dirs = array( '2025/12', '2024/10', '2026/06' );
	foreach ( $dirs as $dir ) {
		$path = WP_CONTENT_DIR . '/uploads/' . $dir . '/' . $filename;
		if ( file_exists( $path ) ) {
			return content_url( 'uploads/' . $dir . '/' . $filename );
		}
	}
	return $fallback_url;
}

function tc_directions_config() {
	$uploads = tc_directions_hostinger_uploads();
	$waze    = 'https://www.waze.com/en/live-map/directions/cl/region-metropolitana/santiago/techcomputer-spa-or-servicio-tecnico-de-notebooks-cambio-de-pantalla,-reparacion-de-bisagras,-ssd-y-mantencion-en-santiago?utm_source=footer&utm_medium=web&to=place.ChIJpfyXSt_OYpYRVHS8FPp_pek';

	return array(
		'title'     => '¿Cómo llegar a Techcomputer?',
		'subtitle'  => 'Los Militares 5620, Oficina 1801, Las Condes',
		'map_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3330.650457412012!2d-70.57546362463951!3d-33.406282373406924!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9662cedf4a97fca5%3A0xe9a57ffa14bc7454!2sTechcomputer%20SPA%20%7C%20Servicio%20T%C3%A9cnico%20de%20Notebooks%3A%20Cambio%20de%20Pantalla%2C%20Reparaci%C3%B3n%20de%20Bisagras%2C%20SSD%20y%20Mantenci%C3%B3n%20en%20Santiago!5e0!3m2!1ses!2scl!4v1762641876738!5m2!1ses!2scl',
		'cards'     => array(
			array(
				'id'    => 'waze',
				'label' => 'Llega con Waze',
				'icon'  => tc_directions_resolve_upload( 'waze_app_icon-logo_brandlogos.net_l82da1.png', $uploads . '/waze_app_icon-logo_brandlogos.net_l82da1.png' ),
				'type'  => 'external',
				'url'   => $waze,
			),
			array(
				'id'    => 'google-maps',
				'label' => 'Llega con Google Maps',
				'icon'  => tc_directions_resolve_upload( 'google-maps-icon-free-png1.png', $uploads . '/google-maps-icon-free-png1.png' ),
				'type'  => 'external',
				'url'   => 'https://www.google.com/maps/place/Techcomputer+SPA+%7C+Servicio+T%C3%A9cnico+de+Notebooks/@-33.4062824,-70.5754636,17z',
			),
			array(
				'id'    => 'metro',
				'label' => 'Llega en Metro',
				'icon'  => tc_directions_resolve_upload( 'logo1.png', $uploads . '/logo1.png' ),
				'type'  => 'video',
				'url'   => tc_directions_resolve_upload( 'Como-Llegar-Metro.mp4', $uploads . '/Como-Llegar-Metro.mp4' ),
			),
		),
	);
}

function tc_should_show_directions_section() {
	if ( is_singular( 'page' ) && is_front_page() ) {
		return true;
	}
	if ( function_exists( 'tc_contact_page_is_active' ) && tc_contact_page_is_active() ) {
		return true;
	}
	return false;
}

function tc_append_directions_to_home( $content ) {
	if ( ! tc_should_show_directions_section() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	if ( str_contains( $content, 'tc-directions-section' ) ) {
		return $content;
	}
	ob_start();
	tc_render_directions_section();
	return $content . ob_get_clean();
}

function tc_enqueue_directions_assets() {
	if ( ! tc_should_show_directions_section() ) {
		return;
	}
	$p   = tc_brand_palette();
	$css = '.tc-directions-section{padding:56px 20px;background:' . $p['section'] . ';font-family:inherit}
.tc-directions-section__inner{max-width:1140px;margin:0 auto}
.tc-directions-section__header{text-align:center;margin-bottom:36px}
.tc-directions-section__title{margin:0 0 8px;font-size:clamp(1.5rem,3vw,2rem);color:' . $p['dark'] . ';font-weight:700}
.tc-directions-section__subtitle{margin:0;color:' . $p['muted'] . ';font-size:1rem}
.tc-directions-cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:24px;margin-bottom:36px}
@media(max-width:767px){.tc-directions-section{padding:40px 16px}.tc-directions-cards{grid-template-columns:1fr;max-width:none;margin-left:0;margin-right:0}.tc-directions-map iframe{height:min(56vw,320px)}.tc-directions-modal{padding:12px}.tc-directions-modal__close{top:8px;right:8px}}
.tc-directions-card{display:flex;flex-direction:column;align-items:center;text-align:center;padding:28px 20px;background:#fff;border-radius:12px;box-shadow:0 8px 24px rgba(17,17,17,.06);transition:transform .2s ease,box-shadow .2s ease;text-decoration:none;color:inherit;border:0;cursor:pointer;font:inherit;width:100%}
.tc-directions-card:hover,.tc-directions-card:focus{transform:translateY(-4px);box-shadow:0 12px 32px rgba(82,138,49,.15);outline:none}
.tc-directions-card__icon{width:88px;height:88px;object-fit:contain;margin-bottom:16px}
.tc-directions-card__label{margin:0;font-size:1rem;font-weight:600;color:' . $p['dark'] . '}
.tc-directions-map{border-radius:12px;overflow:hidden;box-shadow:0 8px 24px rgba(17,17,17,.08)}
.tc-directions-map iframe{display:block;width:100%;height:450px;border:0}
.tc-directions-modal{position:fixed;inset:0;z-index:999999;display:none;align-items:center;justify-content:center;padding:20px;background:rgba(17,17,17,.82)}
.tc-directions-modal.is-open{display:flex}
.tc-directions-modal__dialog{position:relative;width:min(960px,100%);background:#000;border-radius:12px;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.45)}
.tc-directions-modal__close{position:absolute;top:12px;right:12px;z-index:2;width:40px;height:40px;border:0;border-radius:50%;background:rgba(255,255,255,.92);color:' . $p['dark'] . ';font-size:24px;line-height:1;cursor:pointer}
.tc-directions-modal__close:hover{background:#fff}
.tc-directions-modal__video{display:block;width:100%;max-height:80vh}';
	wp_register_style( 'tc-directions', false, array(), TC_SETUP_VERSION );
	wp_enqueue_style( 'tc-directions' );
	wp_add_inline_style( 'tc-directions', $css );

	wp_register_script( 'tc-directions', false, array(), TC_SETUP_VERSION, true );
	wp_enqueue_script( 'tc-directions' );
	wp_add_inline_script(
		'tc-directions',
		"(function(){var m=document.getElementById('tc-directions-modal');if(!m)return;var v=m.querySelector('video');var close=function(){m.classList.remove('is-open');m.setAttribute('aria-hidden','true');if(v){v.pause();v.currentTime=0;}};document.querySelectorAll('[data-tc-directions-video]').forEach(function(btn){btn.addEventListener('click',function(e){e.preventDefault();if(!v)return;v.src=btn.getAttribute('data-tc-directions-video');v.load();m.classList.add('is-open');m.setAttribute('aria-hidden','false');v.play().catch(function(){});});});m.querySelector('.tc-directions-modal__close').addEventListener('click',close);m.addEventListener('click',function(e){if(e.target===m)close();});document.addEventListener('keydown',function(e){if(e.key==='Escape'&&m.classList.contains('is-open'))close();});})();"
	);
}

function tc_render_directions_section( $force = false ) {
	if ( ! $force && ! tc_should_show_directions_section() ) {
		return;
	}
	$config = tc_directions_config();
	?>
	<section class="tc-directions-section" aria-labelledby="tc-directions-title">
		<div class="tc-directions-section__inner">
			<header class="tc-directions-section__header">
				<h2 id="tc-directions-title" class="tc-directions-section__title"><?php echo esc_html( $config['title'] ); ?></h2>
				<p class="tc-directions-section__subtitle"><?php echo esc_html( $config['subtitle'] ); ?></p>
			</header>
			<div class="tc-directions-cards">
				<?php foreach ( $config['cards'] as $card ) : ?>
					<?php if ( 'video' === $card['type'] ) : ?>
						<button type="button" class="tc-directions-card" data-tc-directions-video="<?php echo esc_url( $card['url'] ); ?>" aria-label="<?php echo esc_attr( $card['label'] ); ?>">
							<img class="tc-directions-card__icon" src="<?php echo esc_url( $card['icon'] ); ?>" alt="" width="88" height="88" loading="lazy" decoding="async">
							<p class="tc-directions-card__label"><?php echo esc_html( $card['label'] ); ?></p>
						</button>
					<?php else : ?>
						<a class="tc-directions-card" href="<?php echo esc_url( $card['url'] ); ?>" target="_blank" rel="noopener noreferrer">
							<img class="tc-directions-card__icon" src="<?php echo esc_url( $card['icon'] ); ?>" alt="" width="88" height="88" loading="lazy" decoding="async">
							<p class="tc-directions-card__label"><?php echo esc_html( $card['label'] ); ?></p>
						</a>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
			<div class="tc-directions-map">
				<iframe
					src="<?php echo esc_url( $config['map_embed'] ); ?>"
					width="100%"
					height="450"
					style="border:0;"
					allowfullscreen=""
					loading="lazy"
					referrerpolicy="no-referrer-when-downgrade"
					title="<?php echo esc_attr( $config['subtitle'] ); ?>"
				></iframe>
			</div>
		</div>
	</section>
	<div id="tc-directions-modal" class="tc-directions-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Video: cómo llegar en metro', 'techcomputer' ); ?>">
		<div class="tc-directions-modal__dialog">
			<button type="button" class="tc-directions-modal__close" aria-label="<?php esc_attr_e( 'Cerrar', 'techcomputer' ); ?>">&times;</button>
			<video class="tc-directions-modal__video" controls playsinline preload="none"></video>
		</div>
	</div>
	<?php
}

/** --- Catálogo con filtros (tienda) --- */

function tc_element_tree_has_widget( $element, $widget_type ) {
	if ( ( $element['widgetType'] ?? '' ) === $widget_type ) {
		return true;
	}
	foreach ( (array) ( $element['elements'] ?? array() ) as $child ) {
		if ( tc_element_tree_has_widget( $child, $widget_type ) ) {
			return true;
		}
	}
	return false;
}

function tc_replace_widget_in_tree( $element, $from_widget, $to_element ) {
	if ( ( $element['widgetType'] ?? '' ) === $from_widget ) {
		return $to_element;
	}
	if ( ! empty( $element['elements'] ) ) {
		foreach ( $element['elements'] as $i => $child ) {
			$element['elements'][ $i ] = tc_replace_widget_in_tree( $child, $from_widget, $to_element );
		}
	}
	return $element;
}

function tc_shop_strip_redundant_heading( $elements ) {
	$out = array();
	foreach ( $elements as $element ) {
		if ( 'heading' === ( $element['widgetType'] ?? '' ) ) {
			continue;
		}
		$out[] = $element;
	}
	return $out;
}

function tc_transform_shop_template_elements( $elements, $depth = 0 ) {
	if ( 0 !== $depth ) {
		$out = array();
		foreach ( $elements as $element ) {
			if ( ! empty( $element['elements'] ) ) {
				$element['elements'] = tc_transform_shop_template_elements( $element['elements'], $depth + 1 );
			}
			$out[] = $element;
		}
		return $out;
	}

	$out              = array();
	$catalog_injected = false;

	foreach ( $elements as $element ) {
		$element_id = $element['id'] ?? '';

		// Quitar banner hero vacío (generaba mucho espacio en blanco).
		if ( '21478b18' === $element_id ) {
			continue;
		}

		if ( tc_element_tree_has_widget( $element, 'jkit_product_grid' ) || tc_element_tree_has_widget( $element, 'shortcode' ) ) {
			if ( $catalog_injected ) {
				continue;
			}
			$catalog_injected = true;
			if ( tc_element_tree_has_widget( $element, 'jkit_product_grid' ) ) {
				$element = tc_replace_widget_in_tree(
					$element,
					'jkit_product_grid',
					array(
						'id'         => 'tcshopcatalog',
						'elType'     => 'widget',
						'widgetType' => 'shortcode',
						'settings'   => array( 'shortcode' => '[tc_shop_catalog]' ),
						'elements'   => array(),
						'isInner'    => false,
					)
				);
			}
			if ( isset( $element['settings'] ) ) {
				$element['settings']['margin']['top']    = '0';
				$element['settings']['margin']['bottom'] = '40';
				$element['settings']['padding']['top']   = '16';
				$element['settings']['padding']['bottom'] = '0';
			}
			if ( ! empty( $element['elements'] ) ) {
				$element['elements'] = tc_shop_strip_redundant_heading( $element['elements'] );
			}
		}

		if ( ! empty( $element['elements'] ) ) {
			$element['elements'] = tc_transform_shop_template_elements( $element['elements'], $depth + 1 );
		}
		$out[] = $element;
	}

	return $out;
}

function tc_catalog_brand_names() {
	return array( 'Asus', 'HP', 'Lenovo', 'Acer', 'Dell', 'Otros Modelos', 'Gaming' );
}

function tc_catalog_producto_root_slugs() {
	$slugs = array_merge(
		array( tc_catalog_repuesto_parent_slug() ),
		tc_catalog_repuesto_type_slugs(),
		tc_resolve_category_slugs( tc_get_pantallas_slugs() )
	);
	return array_values( array_unique( array_filter( $slugs ) ) );
}

function tc_catalog_servicio_root_slugs() {
	return tc_resolve_category_slugs( tc_get_servicios_slugs() );
}

function tc_catalog_resolve_product_tipo( $product_id ) {
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return null;
	}

	$terms = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'slugs' ) );
	if ( is_wp_error( $terms ) ) {
		$terms = array();
	}

	$servicio_roots = tc_catalog_servicio_root_slugs();
	$producto_roots = tc_catalog_producto_root_slugs();
	$has_servicio   = (bool) array_intersect( $terms, $servicio_roots );
	$has_producto   = (bool) array_intersect( $terms, $producto_roots );

	if ( $has_producto && ! $has_servicio ) {
		return 'productos';
	}
	if ( $has_servicio && ! $has_producto ) {
		return 'servicios';
	}
	if ( $has_producto && $has_servicio ) {
		if ( $product->is_virtual() ) {
			return 'servicios';
		}
		foreach ( $terms as $slug ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );
			if ( ! $term ) {
				continue;
			}
			$name = html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' );
			if ( in_array( $name, tc_catalog_brand_names(), true ) || preg_match( '/^\d+(?:\.\d+)?"/', $name ) ) {
				return 'productos';
			}
		}
		if ( preg_match( '/^Pantalla(\s|$)/i', $product->get_name() ) ) {
			return 'productos';
		}
		return 'servicios';
	}

	if ( preg_match( '/^(Pantalla|Display)\b/i', $product->get_name() ) ) {
		return 'productos';
	}
	if ( $product->is_virtual() ) {
		return 'servicios';
	}

	return null;
}

function tc_catalog_get_tipo_product_ids( $tipo ) {
	static $cache = null;

	if ( null === $cache ) {
		$cache = array(
			'productos' => array(),
			'servicios' => array(),
		);
		$product_ids = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		foreach ( $product_ids as $product_id ) {
			$resolved = tc_catalog_resolve_product_tipo( $product_id );
			if ( $resolved && isset( $cache[ $resolved ] ) ) {
				$cache[ $resolved ][] = (int) $product_id;
			}
		}
	}

	return $cache[ $tipo ] ?? array();
}

function tc_catalog_get_facet_terms( $filters = array() ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
		)
	);
	if ( is_wp_error( $terms ) ) {
		$terms = array();
	}

	$producto_roots = tc_catalog_producto_root_slugs();
	$servicio_roots = tc_catalog_servicio_root_slugs();
	$brand_names    = tc_catalog_brand_names();
	$repuesto_slug  = tc_catalog_normalize_repuesto_slug( $filters['repuesto'] ?? '' );

	$facets = array(
		'repuestos'    => array(),
		'marcas'       => array(),
		'tamanos'      => array(),
		'resoluciones' => array(),
		'servicios'    => array(),
	);

	$parent = get_term_by( 'slug', tc_catalog_repuesto_parent_slug(), 'product_cat' );
	if ( $parent && ! is_wp_error( $parent ) ) {
		foreach ( $terms as $term ) {
			if ( (int) $term->parent === (int) $parent->term_id ) {
				$facets['repuestos'][] = $term;
			}
		}
	}

	if ( ! $facets['repuestos'] ) {
		foreach ( tc_catalog_repuesto_config() as $slug => $cfg ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );
			if ( $term && ! is_wp_error( $term ) ) {
				$facets['repuestos'][] = $term;
			}
		}
	}

	usort(
		$facets['repuestos'],
		static function ( $a, $b ) {
			return strnatcasecmp( $a->name, $b->name );
		}
	);

	$allowed_marca_slugs = tc_catalog_marca_slugs_for_repuesto( $repuesto_slug );

	foreach ( $terms as $term ) {
		$name = html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' );

		if ( $repuesto_slug && $allowed_marca_slugs && in_array( $term->slug, $allowed_marca_slugs, true ) ) {
			$facets['marcas'][] = $term;
			continue;
		}

		if ( ! $repuesto_slug && in_array( $name, $brand_names, true ) ) {
			$facets['marcas'][] = $term;
			continue;
		}

		if ( $repuesto_slug && tc_catalog_repuesto_supports_screen_specs( $repuesto_slug ) && preg_match( '/^\d+(?:\.\d+)?"/', $name ) ) {
			$facets['tamanos'][] = $term;
			continue;
		}

		if ( ! $repuesto_slug && preg_match( '/^\d+(?:\.\d+)?"/', $name ) ) {
			$facets['tamanos'][] = $term;
			continue;
		}

		if ( $repuesto_slug && tc_catalog_repuesto_supports_screen_specs( $repuesto_slug ) && preg_match( '/\b(Full HD|HD 1366|2K 2560|4K 3840|QHD)\b/i', $name ) ) {
			$facets['resoluciones'][] = $term;
			continue;
		}

		if ( ! $repuesto_slug && preg_match( '/\b(Full HD|HD 1366|2K 2560|4K 3840|QHD)\b/i', $name ) ) {
			$facets['resoluciones'][] = $term;
			continue;
		}

		if ( in_array( $term->slug, $servicio_roots, true ) && ! in_array( $term->slug, $producto_roots, true ) ) {
			$facets['servicios'][] = $term;
		}
	}

	usort(
		$facets['marcas'],
		static function ( $a, $b ) {
			return strnatcasecmp( $a->name, $b->name );
		}
	);
	usort(
		$facets['tamanos'],
		static function ( $a, $b ) {
			return strnatcasecmp( $a->name, $b->name );
		}
	);
	usort(
		$facets['resoluciones'],
		static function ( $a, $b ) {
			return strnatcasecmp( $a->name, $b->name );
		}
	);
	usort(
		$facets['servicios'],
		static function ( $a, $b ) {
			return strnatcasecmp( $a->name, $b->name );
		}
	);

	return $facets;
}

function tc_catalog_get_request_filters() {
	$tipo = isset( $_GET['tc_tipo'] ) ? sanitize_key( wp_unslash( $_GET['tc_tipo'] ) ) : 'productos';
	if ( ! in_array( $tipo, array( 'productos', 'servicios' ), true ) ) {
		$tipo = 'productos';
	}

	$pick_term = static function ( $key ) {
		if ( empty( $_GET[ $key ] ) ) {
			return '';
		}
		$slug = sanitize_title( wp_unslash( $_GET[ $key ] ) );
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		return ( $term && ! is_wp_error( $term ) ) ? $term->slug : '';
	};

	$repuesto   = tc_catalog_normalize_repuesto_slug( $pick_term( 'tc_repuesto' ) );
	$marca      = $pick_term( 'tc_marca' );
	$tamano     = $pick_term( 'tc_tamano' );
	$resolucion = $pick_term( 'tc_resolucion' );

	if ( $repuesto ) {
		if ( ! tc_catalog_repuesto_supports_marca( $repuesto ) ) {
			$marca = '';
		} elseif ( $marca && ! in_array( $marca, tc_catalog_marca_slugs_for_repuesto( $repuesto ), true ) ) {
			$marca = '';
		}

		if ( ! tc_catalog_repuesto_supports_screen_specs( $repuesto ) ) {
			$tamano     = '';
			$resolucion = '';
		}
	} else {
		$marca      = '';
		$tamano     = '';
		$resolucion = '';
	}

	return array(
		'tipo'       => $tipo,
		'repuesto'   => $repuesto,
		'marca'      => $marca,
		'tamano'     => $tamano,
		'resolucion' => $resolucion,
		'servicio'   => $pick_term( 'tc_servicio' ),
		'q'          => isset( $_GET['tc_q'] ) ? sanitize_text_field( wp_unslash( $_GET['tc_q'] ) ) : '',
	);
}

function tc_catalog_filters_are_active( $filters ) {
	foreach ( array( 'repuesto', 'marca', 'tamano', 'resolucion', 'servicio', 'q' ) as $key ) {
		if ( ! empty( $filters[ $key ] ) ) {
			return true;
		}
	}
	return false;
}

function tc_catalog_repuesto_label( $repuesto_slug = '' ) {
	$repuesto_slug = tc_catalog_normalize_repuesto_slug( $repuesto_slug );
	if ( ! $repuesto_slug ) {
		return __( 'Repuestos para Notebook', 'techcomputer' );
	}
	$cfg = tc_catalog_repuesto_config()[ $repuesto_slug ] ?? array();
	return $cfg['label'] ?? $repuesto_slug;
}

function tc_catalog_build_query_args( $filters, $paged = 1, $limit = 12 ) {
	$tipo_ids = tc_catalog_get_tipo_product_ids( $filters['tipo'] );
	if ( empty( $tipo_ids ) ) {
		$tipo_ids = array( 0 );
	}

	$tax_query = array( 'relation' => 'AND' );

	foreach ( array( 'repuesto', 'marca', 'tamano', 'resolucion', 'servicio' ) as $facet ) {
		if ( empty( $filters[ $facet ] ) ) {
			continue;
		}
		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => array( $filters[ $facet ] ),
		);
	}

	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => max( 1, (int) $limit ),
		'paged'          => max( 1, (int) $paged ),
		'post__in'       => $tipo_ids,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	if ( count( $tax_query ) > 1 ) {
		$args['tax_query'] = $tax_query;
	}

	if ( ! empty( $filters['q'] ) ) {
		$args['s'] = $filters['q'];
	}

	return $args;
}

function tc_catalog_results_title( $filters ) {
	if ( is_string( $filters ) ) {
		$filters = array(
			'tipo'     => $filters,
			'repuesto' => '',
		);
	}

	if ( 'servicios' === ( $filters['tipo'] ?? '' ) ) {
		return __( 'Todos los Servicios', 'techcomputer' );
	}

	return tc_catalog_repuesto_label( $filters['repuesto'] ?? '' );
}

function tc_catalog_product_card_category( $product_id ) {
	$exclude_slugs = array_merge(
		tc_catalog_producto_root_slugs(),
		tc_catalog_servicio_root_slugs(),
		array( 'uncategorized', 'sin-categorizar' )
	);
	$terms = get_the_terms( $product_id, 'product_cat' );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return '';
	}

	$brand_names = array_map( 'strtolower', tc_catalog_brand_names() );
	$fallback    = '';

	foreach ( $terms as $term ) {
		if ( in_array( $term->slug, $exclude_slugs, true ) ) {
			continue;
		}

		$name = html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' );
		if ( in_array( strtolower( $name ), $brand_names, true ) ) {
			return strtoupper( $name );
		}
		if ( '' === $fallback ) {
			$fallback = $name;
		}
	}

	return '' !== $fallback ? strtoupper( $fallback ) : '';
}

function tc_catalog_render_product_card( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}

	$category_label = tc_catalog_product_card_category( $product->get_id() );
	?>
	<li <?php wc_product_class( 'jkit-product-block', $product ); ?>>
		<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="jkit-product">
			<div class="product-link">
				<?php if ( $product->is_on_sale() ) : ?>
					<span class="onsale text"><?php esc_html_e( 'Oferta', 'techcomputer' ); ?></span>
				<?php endif; ?>
				<?php echo $product->get_image( 'woocommerce_thumbnail', array( 'class' => 'wp-post-image product-image' ) ); ?>
			</div>
			<?php if ( $category_label ) : ?>
				<div class="product-categories"><object><span><?php echo esc_html( $category_label ); ?></span></object></div>
			<?php endif; ?>
			<h3 class="product-title"><?php echo esc_html( $product->get_name() ); ?></h3>
			<?php if ( $price_html = $product->get_price_html() ) : ?>
				<span class="price"><?php echo wp_kses_post( $price_html ); ?></span>
			<?php endif; ?>
		</a>
	</li>
	<?php
}

function tc_catalog_render_products_loop( $query ) {
	if ( ! $query->have_posts() ) {
		echo '<p class="tc-catalog-empty">' . esc_html__( 'No hay resultados con los filtros seleccionados.', 'techcomputer' ) . '</p>';
		return;
	}

	echo '<div class="woocommerce tc-catalog-woocommerce jeg-elementor-kit jkit-product-grid">';
	echo '<ul class="products jkit-products jkit-align-left tc-catalog-grid">';
	while ( $query->have_posts() ) {
		$query->the_post();
		global $product;
		$product = wc_get_product( get_the_ID() );
		tc_catalog_render_product_card( $product );
	}
	echo '</ul>';
	echo '</div>';

	if ( $query->max_num_pages > 1 ) {
		echo '<nav class="woocommerce-pagination tc-catalog-pagination">';
		echo paginate_links(
			array(
				'total'   => $query->max_num_pages,
				'current' => max( 1, (int) $query->get( 'paged' ) ),
				'format'  => '?paged=%#%',
				'add_args' => array_map(
					static function ( $value ) {
						return is_scalar( $value ) ? $value : '';
					},
					array_diff_key( $_GET, array( 'paged' => 1 ) )
				),
			)
		);
		echo '</nav>';
	}

	wp_reset_postdata();
}

function tc_catalog_render_filter_field( $id, $label, $terms, $current, $extra_class = '' ) {
	if ( empty( $terms ) ) {
		return;
	}
	$classes = 'tc-catalog-field';
	if ( $extra_class ) {
		$classes .= ' ' . $extra_class;
	}
	echo '<div class="' . esc_attr( $classes ) . '" data-tc-field="' . esc_attr( preg_replace( '/^tc_/', '', $id ) ) . '">';
	echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
	echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '">';
	echo '<option value="">' . esc_html__( 'Todos', 'techcomputer' ) . '</option>';
	foreach ( $terms as $term ) {
		printf(
			'<option value="%1$s" %2$s>%3$s (%4$d)</option>',
			esc_attr( $term->slug ),
			selected( $current, $term->slug, false ),
			esc_html( html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' ) ),
			(int) $term->count
		);
	}
	echo '</select></div>';
}

function tc_shop_catalog_shortcode() {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return '';
	}

	$filters    = tc_catalog_get_request_filters();
	$facets     = tc_catalog_get_facet_terms( $filters );
	$shop_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	$reset_url  = add_query_arg( 'tc_tipo', $filters['tipo'], $shop_url );
	$is_product = ( 'productos' === $filters['tipo'] );
	$show_marca = $is_product && $filters['repuesto'] && tc_catalog_repuesto_supports_marca( $filters['repuesto'] );
	$show_specs = $is_product && $filters['repuesto'] && tc_catalog_repuesto_supports_screen_specs( $filters['repuesto'] );

	ob_start();
	?>
	<div class="tc-shop-catalog" data-tc-catalog>
		<form class="tc-catalog-filters" method="get" action="<?php echo esc_url( $shop_url ); ?>">
			<div class="tc-catalog-filters__row tc-catalog-filters__tipo">
				<span class="tc-catalog-filters__label"><?php esc_html_e( 'Ver', 'techcomputer' ); ?></span>
				<div class="tc-catalog-tabs">
					<label class="tc-catalog-tab<?php echo $is_product ? ' is-active' : ''; ?>">
						<input type="radio" name="tc_tipo" value="productos" <?php checked( $filters['tipo'], 'productos' ); ?>>
						<span><?php esc_html_e( 'Productos', 'techcomputer' ); ?></span>
						<small><?php esc_html_e( 'Pantallas y repuestos', 'techcomputer' ); ?></small>
					</label>
					<label class="tc-catalog-tab<?php echo ! $is_product ? ' is-active' : ''; ?>">
						<input type="radio" name="tc_tipo" value="servicios" <?php checked( $filters['tipo'], 'servicios' ); ?>>
						<span><?php esc_html_e( 'Servicios', 'techcomputer' ); ?></span>
						<small><?php esc_html_e( 'Reparaciones y mantenciones', 'techcomputer' ); ?></small>
					</label>
				</div>
			</div>
			<div class="tc-catalog-filters__row tc-catalog-filters__fields">
				<div class="tc-catalog-field tc-catalog-field--search">
					<label for="tc_q"><?php esc_html_e( 'Buscar', 'techcomputer' ); ?></label>
					<input id="tc_q" type="search" name="tc_q" value="<?php echo esc_attr( $filters['q'] ); ?>" placeholder="<?php echo esc_attr( $is_product ? __( 'Modelo o pantalla…', 'techcomputer' ) : __( 'Tipo de servicio…', 'techcomputer' ) ); ?>">
				</div>
				<?php
				if ( $is_product ) {
					tc_catalog_render_filter_field( 'tc_repuesto', __( 'Tipo de repuesto', 'techcomputer' ), $facets['repuestos'], $filters['repuesto'] );
					tc_catalog_render_filter_field(
						'tc_marca',
						__( 'Marca', 'techcomputer' ),
						$facets['marcas'],
						$filters['marca'],
						'tc-catalog-field--marca' . ( $show_marca ? '' : ' tc-catalog-field--hidden' )
					);
					tc_catalog_render_filter_field(
						'tc_tamano',
						__( 'Tamaño', 'techcomputer' ),
						$facets['tamanos'],
						$filters['tamano'],
						'tc-catalog-field--tamano' . ( $show_specs ? '' : ' tc-catalog-field--hidden' )
					);
					tc_catalog_render_filter_field(
						'tc_resolucion',
						__( 'Resolución', 'techcomputer' ),
						$facets['resoluciones'],
						$filters['resolucion'],
						'tc-catalog-field--resolucion' . ( $show_specs ? '' : ' tc-catalog-field--hidden' )
					);
				} else {
					tc_catalog_render_filter_field( 'tc_servicio', __( 'Tipo de servicio', 'techcomputer' ), $facets['servicios'], $filters['servicio'] );
				}
				?>
			</div>
			<div class="tc-catalog-filters__actions">
				<button type="submit" class="button tc-catalog-submit"><?php esc_html_e( 'Filtrar', 'techcomputer' ); ?></button>
				<a class="tc-catalog-reset" href="<?php echo esc_url( $reset_url ); ?>"><?php esc_html_e( 'Limpiar filtros', 'techcomputer' ); ?></a>
			</div>
		</form>

		<div class="tc-catalog-results tc-catalog-results--<?php echo esc_attr( $filters['tipo'] ); ?>">
			<?php
			$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
			$query = new WP_Query( tc_catalog_build_query_args( $filters, $paged, 12 ) );
			echo '<h2 class="tc-catalog-results__title">' . esc_html( tc_catalog_results_title( $filters ) ) . '<span>' . esc_html( sprintf( _n( '%d resultado', '%d resultados', (int) $query->found_posts, 'techcomputer' ), (int) $query->found_posts ) ) . '</span></h2>';
			tc_catalog_render_products_loop( $query );
			?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

function tc_enqueue_shop_catalog_assets() {
	if ( ! function_exists( 'is_shop' ) || ! is_shop() ) {
		return;
	}

	if ( ! wp_style_is( 'jkit-elements-main', 'registered' ) ) {
		wp_register_style(
			'jkit-elements-main',
			plugins_url( 'jeg-elementor-kit/assets/css/elements/main.css' ),
			array(),
			defined( 'JEG_ELEMENTOR_KIT_VERSION' ) ? JEG_ELEMENTOR_KIT_VERSION : '1.0'
		);
	}
	if ( ! wp_style_is( 'jkit-elements-main', 'enqueued' ) ) {
		wp_enqueue_style( 'jkit-elements-main' );
	}

	$p   = tc_brand_palette();
	$css = 'body.woocommerce-shop .site-main,body.post-type-archive-product .site-main{padding-top:0}
body.woocommerce-shop .woocommerce-products-header,body.post-type-archive-product .woocommerce-products-header{display:none}
.tc-shop-elementor-kit .elementor-element-21478b18,.tc-shop-elementor-kit .elementor-element-3bf86c17{display:none!important}
.tc-shop-elementor-kit .elementor-element-26e412cd{margin-top:0!important;padding-top:8px!important;margin-bottom:0!important}
.tc-shop-elementor-kit .tc-shop-catalog{padding-top:0}
.tc-shop-catalog{max-width:1140px;margin:0 auto;padding:0 20px 80px}
.tc-catalog-filters{background:' . $p['section'] . ';border:1px solid rgba(17,17,17,.06);border-radius:20px;padding:28px;margin-bottom:48px;box-shadow:none}
.tc-catalog-filters__row{display:flex;flex-wrap:wrap;gap:16px;align-items:flex-end}
.tc-catalog-filters__tipo{align-items:stretch;margin-bottom:18px;flex-direction:column}
.tc-catalog-filters__label{font-weight:700;color:' . $p['dark'] . ';margin:0 0 10px;font-size:.95rem;letter-spacing:.02em}
.tc-catalog-tabs{display:grid;grid-template-columns:1fr 1fr;gap:12px;width:100%}
.tc-catalog-tab{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;padding:16px 12px;border-radius:16px;border:1px solid rgba(17,17,17,.08);background:#fff;cursor:pointer;text-align:center;transition:border-color .2s,background .2s,box-shadow .2s}
.tc-catalog-tab input{position:absolute;opacity:0;pointer-events:none}
.tc-catalog-tab span{font-size:1.05rem;font-weight:700;color:' . $p['dark'] . '}
.tc-catalog-tab small{font-size:.82rem;color:' . $p['muted'] . ';font-weight:500}
.tc-catalog-tab.is-active,.tc-catalog-tab:has(input:checked){border-color:' . $p['primary'] . ';background:' . $p['tint'] . ';box-shadow:0 4px 16px rgba(82,138,49,.08)}
.tc-catalog-pill{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border-radius:999px;background:' . $p['tint'] . ';cursor:pointer;font-weight:600;color:' . $p['dark'] . '}
.tc-catalog-pill input{accent-color:' . $p['primary'] . '}
.tc-catalog-filters__fields{width:100%;display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px}
.tc-catalog-field--hidden{display:none!important}
.tc-catalog-field label{display:block;margin-bottom:6px;font-size:.85rem;font-weight:600;color:' . $p['body'] . '}
.tc-catalog-field select,.tc-catalog-field input{width:100%;min-height:44px;border:1px solid rgba(17,17,17,.1);border-radius:12px;padding:0 12px;background:#fff}
.tc-catalog-field--search{grid-column:1/-1}
@media(min-width:768px){.tc-catalog-field--search{grid-column:span 2}}
.tc-catalog-filters__actions{display:flex;gap:12px;align-items:center;margin-top:18px;width:100%}
.tc-catalog-submit{background:' . $p['primary'] . '!important;border-color:' . $p['primary'] . '!important;color:#fff!important;border-radius:12px!important;padding:10px 22px!important;font-weight:600!important}
.tc-catalog-submit:hover{background:' . $p['primary_d'] . '!important;border-color:' . $p['primary_d'] . '!important}
.tc-catalog-reset{color:' . $p['primary'] . ';text-decoration:none;font-weight:600}
.tc-catalog-results{margin-top:8px}
.tc-catalog-results__title{margin:0 0 40px;text-align:center;font-size:clamp(1.75rem,4vw,3.125rem);line-height:1.2;font-weight:600;color:' . $p['dark'] . '}
.tc-catalog-results__title span{display:block;margin-top:10px;font-size:clamp(.85rem,1.6vw,1rem);font-weight:500;color:' . $p['muted'] . '}
.tc-catalog-section{margin-bottom:48px}
.tc-catalog-woocommerce .jkit-products.tc-catalog-grid{display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr));gap:30px;list-style:none!important;margin:0!important;padding:0!important;width:100%!important}
.tc-catalog-woocommerce .jkit-products.tc-catalog-grid::before,.tc-catalog-woocommerce .jkit-products.tc-catalog-grid::after{display:none!important;content:none!important}
.tc-catalog-woocommerce .jkit-product-block{list-style:none!important;margin:0!important;padding:0!important;width:100%!important;float:none!important;clear:none!important;border:0!important;box-shadow:none!important;background:transparent!important}
.tc-catalog-woocommerce .jkit-product{display:flex;flex-direction:column;height:100%;text-decoration:none;color:inherit}
.tc-catalog-woocommerce .product-link{position:relative;display:flex;align-items:center;justify-content:center;background:' . $p['tint'] . ';border-radius:20px;padding:32px;margin:0 0 16px;min-height:220px;transition:background .25s ease}
.tc-catalog-woocommerce .jkit-product:hover .product-link{background:' . $p['section'] . '}
.tc-catalog-woocommerce .product-link .product-image,.tc-catalog-woocommerce .product-link img{max-height:180px;width:auto!important;max-width:100%;object-fit:contain;margin:0 auto;display:block}
.tc-catalog-woocommerce .product-link .onsale{position:absolute;top:14px;left:14px;z-index:1;background:' . $p['primary'] . ';color:#fff;font-size:.72rem;font-weight:700;line-height:1;padding:6px 10px;border-radius:999px;text-transform:uppercase;letter-spacing:.04em}
.tc-catalog-woocommerce .product-categories{margin:0 0 8px;font-size:12px;font-weight:500;line-height:1.4;letter-spacing:.08em;text-transform:uppercase;color:' . $p['muted'] . '}
.tc-catalog-woocommerce .product-categories object{pointer-events:none}
.tc-catalog-woocommerce .product-title{margin:0 0 8px;font-size:18px;line-height:1.35;font-weight:600;color:' . $p['dark'] . '}
.tc-catalog-woocommerce .price{font-size:14px;font-weight:400;line-height:1.5;color:' . $p['muted'] . '}
.tc-catalog-woocommerce .price del{opacity:.75;margin-right:6px;font-weight:400}
.tc-catalog-woocommerce .price ins{text-decoration:none;color:' . $p['primary'] . ';font-weight:600}
.tc-catalog-empty{padding:32px 24px;background:' . $p['card'] . ';border-radius:20px;text-align:center;color:' . $p['muted'] . '}
.tc-catalog-pagination{margin-top:40px;text-align:center}
@media(max-width:1024px){.tc-catalog-woocommerce .jkit-products.tc-catalog-grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:24px}}
@media(max-width:767px){
body.woocommerce-shop .site-main,body.post-type-archive-product .site-main{padding-bottom:calc(72px + env(safe-area-inset-bottom,0))}
.tc-shop-catalog{padding:0 16px calc(64px + env(safe-area-inset-bottom,0))}
.tc-shop-elementor-kit .elementor-element-26e412cd{padding-left:16px!important;padding-right:16px!important}
.tc-catalog-filters{padding:18px 16px;margin-bottom:32px;border-radius:16px}
.tc-catalog-filters__fields{grid-template-columns:1fr!important}
.tc-catalog-field--search{grid-column:1!important}
.tc-catalog-filters__actions{flex-direction:column;align-items:stretch;gap:10px}
.tc-catalog-submit{width:100%!important;justify-content:center}
.tc-catalog-reset{text-align:center;padding:8px 0}
.tc-catalog-tab{padding:14px 10px}
.tc-catalog-tab span{font-size:.98rem}
.tc-catalog-tab small{font-size:.78rem}
.tc-catalog-results__title{margin-bottom:24px;font-size:clamp(1.45rem,6vw,2rem)}
.tc-catalog-woocommerce .jkit-products.tc-catalog-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.tc-catalog-woocommerce .product-link{min-height:170px;padding:20px 16px;border-radius:16px}
.tc-catalog-woocommerce .product-link .product-image,.tc-catalog-woocommerce .product-link img{max-height:130px}
.tc-catalog-woocommerce .product-title{font-size:1rem;line-height:1.3}
.tc-catalog-woocommerce .price{font-size:.88rem}
.tc-catalog-empty{padding:24px 16px}
.tc-catalog-pagination{margin-top:28px}
.tc-catalog-pagination .page-numbers{padding:8px 12px}
}
@media(max-width:480px){
.tc-catalog-tabs{grid-template-columns:1fr}
.tc-catalog-woocommerce .jkit-products.tc-catalog-grid{grid-template-columns:minmax(0,1fr);gap:20px}
.tc-catalog-woocommerce .product-link{min-height:200px}
}';
	wp_register_style( 'tc-shop-catalog', false, array( 'woocommerce-general', 'jkit-elements-main' ), TC_SETUP_VERSION );
	wp_enqueue_style( 'tc-shop-catalog' );
	wp_add_inline_style( 'tc-shop-catalog', $css );

	wp_register_script( 'tc-shop-catalog', false, array(), TC_SETUP_VERSION, true );
	wp_enqueue_script( 'tc-shop-catalog' );
	$repuesto_support = array();
	foreach ( tc_catalog_repuesto_config() as $slug => $cfg ) {
		$repuesto_support[ $slug ] = array(
			'marca'  => ! empty( $cfg['brands'] ) || ! empty( $cfg['legacy_brands'] ),
			'screen' => ! empty( $cfg['screen_specs'] ),
		);
	}

	wp_add_inline_script(
		'tc-shop-catalog',
		'(function(){var f=document.querySelector(".tc-catalog-filters");if(!f)return;var support=' . wp_json_encode( $repuesto_support ) . ';function toggleRepuestoFields(){var repuestoEl=f.querySelector("#tc_repuesto");if(!repuestoEl)return;var cfg=support[repuestoEl.value]||{};f.querySelectorAll("[data-tc-field=marca],[data-tc-field=tamano],[data-tc-field=resolucion]").forEach(function(field){var key=field.getAttribute("data-tc-field");var show=key==="marca"?!!cfg.marca:!!cfg.screen;field.classList.toggle("tc-catalog-field--hidden",!show);if(!show){var select=field.querySelector("select");if(select){select.value="";}}});}toggleRepuestoFields();f.querySelectorAll("select,input[type=radio]").forEach(function(el){el.addEventListener("change",function(){if(el.id==="tc_repuesto"){toggleRepuestoFields();}f.requestSubmit?f.requestSubmit():f.submit();});});})();'
	);
}

function tc_single_product_bootstrap() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
	add_action( 'woocommerce_before_main_content', 'tc_single_product_hero', 12 );
	add_action( 'woocommerce_single_product_summary', 'tc_single_product_summary_category', 4 );
	add_action( 'woocommerce_before_single_product_summary', 'tc_single_product_gallery_open', 9 );
	add_action( 'woocommerce_before_single_product_summary', 'tc_single_product_gallery_close', 21 );
	add_action( 'woocommerce_before_single_product', 'tc_single_product_tweak_summary', 5 );
}

function tc_single_product_tweak_summary() {
	global $product;

	if ( $product instanceof WC_Product && $product->get_average_rating() <= 0 ) {
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
	}
}

function tc_single_product_hero_banner_url() {
	return tc_directions_resolve_upload(
		'tc-product-hero-banner.png',
		content_url( 'uploads/2026/06/tc-product-hero-banner.png' )
	);
}

function tc_single_product_hero_label() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return __( 'Producto', 'techcomputer' );
	}

	$tipo = tc_catalog_resolve_product_tipo( $product->get_id() );
	if ( 'servicios' === $tipo ) {
		return __( 'Servicios', 'techcomputer' );
	}

	return __( 'Repuestos', 'techcomputer' );
}

function tc_single_product_hero() {
	$banner_url = tc_single_product_hero_banner_url();
	?>
	<div class="tc-product-hero">
		<div class="tc-product-hero__banner" style="background-image:linear-gradient(rgba(17,17,17,.4),rgba(17,17,17,.4)),url('<?php echo esc_url( $banner_url ); ?>')">
			<p class="tc-product-hero__label"><?php echo esc_html( tc_single_product_hero_label() ); ?></p>
		</div>
		<div class="tc-product-hero__crumbs">
			<?php woocommerce_breadcrumb(); ?>
		</div>
	</div>
	<?php
}

function tc_single_product_summary_category() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$label = tc_catalog_product_card_category( $product->get_id() );
	if ( ! $label ) {
		return;
	}

	echo '<div class="tc-product-category">' . esc_html( $label ) . '</div>';
}

function tc_single_product_gallery_open() {
	echo '<div class="tc-product-gallery-shell">';
}

function tc_single_product_gallery_close() {
	echo '</div>';
}

function tc_enqueue_single_product_assets() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	$p   = tc_brand_palette();
	$css = 'body.single-product{background:' . $p['section'] . '}
body.single-product .site-main{padding-top:0}
body.single-product .page-header{display:none!important}
.single-product .content-area,.single-product .site-main{max-width:none;width:100%}
.single-product .woocommerce{max-width:none;padding:0;margin:0}
.tc-product-hero{margin:0 auto 8px;max-width:1140px;padding:0 20px}
.tc-product-hero__banner{display:flex;align-items:center;justify-content:center;min-height:300px;background-color:' . $p['dark'] . ';background-size:cover;background-position:center bottom;background-repeat:no-repeat;border-radius:0 0 24px 24px;padding:44px 24px 52px;text-align:center;overflow:hidden}
.tc-product-hero__label{margin:0;color:#fff;font-size:clamp(2rem,5vw,3.25rem);font-weight:600;line-height:1.15;letter-spacing:-.02em;text-shadow:0 2px 18px rgba(0,0,0,.35)}
.tc-product-hero__crumbs{margin:20px auto 36px;max-width:1140px;padding:0 20px}
.tc-product-hero__crumbs .woocommerce-breadcrumb{margin:0;font-size:.9rem;color:' . $p['muted'] . '}
.tc-product-hero__crumbs .woocommerce-breadcrumb a{color:' . $p['primary'] . ';text-decoration:none;font-weight:600}
.tc-product-hero__crumbs .woocommerce-breadcrumb a:hover{color:' . $p['primary_d'] . '}
.single-product div.product.tc-product-kit{display:grid;grid-template-columns:minmax(0,1.05fr) minmax(0,.95fr);gap:48px;align-items:start;max-width:1140px;margin:0 auto;padding:0 20px 80px;width:100%;clear:both}
.single-product div.product.tc-product-kit::before,.single-product div.product.tc-product-kit::after{display:none!important;content:none!important}
.single-product div.product.tc-product-kit .woocommerce-product-gallery{width:100%!important;float:none!important;margin:0!important}
.single-product .tc-product-gallery-shell{background:' . $p['tint'] . ';border-radius:20px;padding:32px;overflow:hidden}
.single-product .tc-product-gallery-shell .woocommerce-product-gallery{margin:0!important}
.single-product .tc-product-gallery-shell .woocommerce-product-gallery__wrapper,.single-product .tc-product-gallery-shell .woocommerce-product-gallery__image{margin:0}
.single-product .tc-product-gallery-shell img{border-radius:12px}
.single-product .tc-product-gallery-shell .flex-control-thumbs{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(72px,1fr));gap:10px;margin:16px 0 0!important;padding:0!important;border:0!important}
.single-product .tc-product-gallery-shell .flex-control-thumbs li{list-style:none!important;width:auto!important;float:none!important;margin:0!important}
.single-product .tc-product-gallery-shell .flex-control-thumbs img{border-radius:10px;border:2px solid transparent;opacity:.75;transition:opacity .2s,border-color .2s}
.single-product .tc-product-gallery-shell .flex-control-thumbs img.flex-active,.single-product .tc-product-gallery-shell .flex-control-thumbs img:hover{opacity:1;border-color:' . $p['primary'] . '}
.single-product .tc-product-gallery-shell span.onsale{top:14px;left:14px;min-height:auto;min-width:0;border-radius:999px;padding:6px 10px;font-size:.72rem;font-weight:700;line-height:1;text-transform:uppercase;letter-spacing:.04em}
.single-product div.product.tc-product-kit .summary{float:none!important;width:100%!important;margin:0!important;padding:24px 0 0}
.single-product .tc-product-category{margin:0 0 10px;font-size:12px;font-weight:600;line-height:1.4;letter-spacing:.08em;text-transform:uppercase;color:' . $p['muted'] . '}
.single-product .product_title{margin:0 0 12px;font-size:clamp(1.5rem,3vw,2rem);font-weight:600;line-height:1.25;color:' . $p['dark'] . '}
.single-product .summary .price{margin:0 0 8px;font-size:clamp(1.35rem,2.5vw,1.75rem)!important;font-weight:700!important;color:' . $p['dark'] . '!important}
.single-product .summary .price del{color:' . $p['muted'] . ';font-size:1rem;font-weight:500;opacity:.85}
.single-product .summary .price ins{text-decoration:none;color:' . $p['primary'] . ';font-weight:700}
.single-product .woocommerce-product-details__short-description{margin:16px 0 0;color:' . $p['body'] . ';line-height:1.7;font-size:.98rem}
.single-product .woocommerce-product-details__short-description p:last-child{margin-bottom:0}
.single-product p.stock{margin:14px 0 0;font-weight:600;font-size:.92rem}
.single-product p.stock.in-stock{color:' . $p['primary'] . '}
.single-product form.cart{display:flex;flex-wrap:wrap;align-items:center;gap:12px;margin-top:24px;padding-top:24px;border-top:1px solid rgba(17,17,17,.08)}
.single-product form.cart .quantity .qty{min-height:48px;width:72px;border:1px solid rgba(17,17,17,.12);border-radius:12px;text-align:center;font-weight:600}
.single-product form.cart .single_add_to_cart_button{border-radius:12px!important;padding:14px 28px!important;min-height:48px;font-weight:600!important;font-size:.95rem!important}
.single-product .product_meta{margin-top:22px;padding-top:18px;border-top:1px solid rgba(17,17,17,.06);font-size:.88rem;line-height:1.8;color:' . $p['muted'] . '}
.single-product .product_meta>span{display:block;margin-bottom:4px}
.single-product .product_meta a{color:' . $p['primary'] . ';text-decoration:none;font-weight:600}
.single-product .product_meta a:hover{color:' . $p['primary_d'] . '}
.single-product div.product.tc-product-kit .woocommerce-tabs{grid-column:1/-1;margin-top:12px;padding-top:36px;border-top:1px solid rgba(17,17,17,.08)}
.single-product .woocommerce-tabs ul.tabs{display:flex;flex-wrap:wrap;gap:10px;padding:0;margin:0 0 24px;border:0;background:transparent}
.single-product .woocommerce-tabs ul.tabs::before{display:none}
.single-product .woocommerce-tabs ul.tabs li{border:0;background:transparent;margin:0;padding:0;border-radius:0}
.single-product .woocommerce-tabs ul.tabs li::before,.single-product .woocommerce-tabs ul.tabs li::after{display:none}
.single-product .woocommerce-tabs ul.tabs li a{display:block;padding:12px 20px;border-radius:12px;background:#fff;color:' . $p['muted'] . ';font-weight:600;text-decoration:none;transition:background .2s,color .2s}
.single-product .woocommerce-tabs ul.tabs li.active a,.single-product .woocommerce-tabs ul.tabs li a:hover{background:' . $p['tint'] . ';color:' . $p['primary'] . '}
.single-product .woocommerce-tabs .panel{margin:0;padding:24px;background:#fff;border-radius:20px;border:1px solid rgba(17,17,17,.06);box-shadow:none}
.single-product .woocommerce-tabs .panel h2:first-child{margin-top:0;font-size:1.15rem;color:' . $p['dark'] . '}
.single-product .woocommerce-tabs .shop_attributes{border:0;margin:0}
.single-product .woocommerce-tabs .shop_attributes th,.single-product .woocommerce-tabs .shop_attributes td{border:0;border-bottom:1px solid rgba(17,17,17,.06);padding:12px 0}
.single-product div.product.tc-product-kit .related.products,.single-product div.product.tc-product-kit .upsells.products{grid-column:1/-1;margin-top:48px;padding-top:48px;border-top:1px solid rgba(17,17,17,.08)}
.single-product .related.products>h2,.single-product .upsells.products>h2{margin:0 0 32px;text-align:center;font-size:clamp(1.5rem,3vw,2.25rem);font-weight:600;color:' . $p['dark'] . '}
.single-product .related.products ul.products,.single-product .upsells.products ul.products{display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr));gap:30px;margin:0!important;padding:0!important;list-style:none!important}
.single-product .related.products ul.products::before,.single-product .related.products ul.products::after,.single-product .upsells.products ul.products::before,.single-product .upsells.products ul.products::after{display:none!important;content:none!important}
.single-product .related.products ul.products li.product,.single-product .upsells.products ul.products li.product{float:none!important;width:100%!important;margin:0!important;padding:0!important;border:0!important;box-shadow:none!important;background:transparent!important}
.single-product .related.products ul.products li.product>a,.single-product .upsells.products ul.products li.product>a{display:flex;flex-direction:column;height:100%;text-decoration:none;color:inherit}
.single-product .related.products ul.products li.product img,.single-product .upsells.products ul.products li.product img{background:' . $p['tint'] . ';border-radius:20px;padding:28px;margin:0 0 14px;width:100%!important;object-fit:contain;aspect-ratio:1/1}
.single-product .related.products ul.products li.product .woocommerce-loop-product__title,.single-product .upsells.products ul.products li.product .woocommerce-loop-product__title{margin:0 0 8px;padding:0;font-size:1rem;font-weight:600;color:' . $p['dark'] . '}
.single-product .related.products ul.products li.product .price,.single-product .upsells.products ul.products li.product .price{margin:0;font-size:.92rem;color:' . $p['muted'] . '}
.single-product .related.products ul.products li.product .button,.single-product .upsells.products ul.products li.product .button{display:none!important}
@media(max-width:1024px){.single-product div.product.tc-product-kit{grid-template-columns:1fr;gap:28px}.single-product .related.products ul.products,.single-product .upsells.products ul.products{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:767px){
body.single-product{padding-bottom:calc(72px + env(safe-area-inset-bottom,0))}
.tc-product-hero{padding:0 16px}
.tc-product-hero__banner{min-height:220px;padding:24px 16px 32px;align-items:flex-start;justify-content:flex-start;background-position:center bottom;background-size:cover;border-radius:0 0 20px 20px}
.tc-product-hero__label{font-size:clamp(1.55rem,7vw,2.1rem);max-width:100%;word-wrap:break-word}
.tc-product-hero__crumbs{margin:14px auto 24px;padding:0 16px}
.tc-product-hero__crumbs .woocommerce-breadcrumb{font-size:.82rem;line-height:1.55;word-break:break-word}
.single-product div.product.tc-product-kit{padding:0 16px calc(64px + env(safe-area-inset-bottom,0));gap:20px}
.single-product .tc-product-gallery-shell{padding:16px;border-radius:16px}
.single-product .tc-product-gallery-shell .flex-viewport,.single-product .tc-product-gallery-shell .woocommerce-product-gallery__wrapper,.single-product .tc-product-gallery-shell .woocommerce-product-gallery__image{max-width:100%!important}
.single-product .tc-product-gallery-shell .flex-control-thumbs{grid-template-columns:repeat(auto-fill,minmax(56px,1fr))!important;gap:8px}
.single-product div.product.tc-product-kit .summary{padding-top:0}
.single-product .product_title{font-size:clamp(1.25rem,5.5vw,1.65rem);word-wrap:break-word;overflow-wrap:anywhere}
.single-product .summary .price{font-size:clamp(1.2rem,5vw,1.5rem)!important}
.single-product .woocommerce-product-details__short-description{font-size:.94rem}
.single-product form.cart{flex-direction:column;align-items:stretch;gap:10px}
.single-product form.cart .quantity{width:100%}
.single-product form.cart .quantity .qty{width:100%;max-width:none}
.single-product form.cart .single_add_to_cart_button{width:100%!important;flex:1 1 auto}
.single-product .product_meta{font-size:.84rem}
.single-product div.product.tc-product-kit .woocommerce-tabs{margin-top:4px;padding-top:28px}
.single-product .woocommerce-tabs ul.tabs{flex-wrap:nowrap;overflow-x:auto;-webkit-overflow-scrolling:touch;scrollbar-width:none;gap:8px;margin:0 -4px 18px;padding:0 4px 4px}
.single-product .woocommerce-tabs ul.tabs::-webkit-scrollbar{display:none}
.single-product .woocommerce-tabs ul.tabs li{flex:0 0 auto}
.single-product .woocommerce-tabs ul.tabs li a{padding:10px 14px;font-size:.86rem;white-space:nowrap}
.single-product .woocommerce-tabs .panel{padding:18px 16px;border-radius:16px}
.single-product .woocommerce-tabs .shop_attributes th,.single-product .woocommerce-tabs .shop_attributes td{display:block;width:100%;padding:10px 0}
.single-product .woocommerce-tabs .shop_attributes th{font-weight:700;padding-bottom:2px}
.single-product .related.products ul.products,.single-product .upsells.products ul.products{grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.single-product .related.products ul.products li.product img,.single-product .upsells.products ul.products li.product img{padding:18px;border-radius:16px}
.single-product .related.products ul.products li.product .woocommerce-loop-product__title,.single-product .upsells.products ul.products li.product .woocommerce-loop-product__title{font-size:.92rem;line-height:1.35}
.single-product .related.products>h2,.single-product .upsells.products>h2{margin-bottom:24px;font-size:clamp(1.25rem,5vw,1.75rem)}
}
@media(max-width:480px){
.single-product .related.products ul.products,.single-product .upsells.products ul.products{grid-template-columns:minmax(0,1fr);gap:20px}
.single-product .tc-product-hero__banner{min-height:200px}
}';

	wp_register_style( 'tc-single-product', false, array( 'woocommerce-general' ), TC_SETUP_VERSION );
	wp_enqueue_style( 'tc-single-product' );
	wp_add_inline_style( 'tc-single-product', $css );
}

function tc_is_home_context() {
	if ( is_front_page() ) {
		return true;
	}
	$home_id = tc_get_home_page_id();
	return $home_id > 0 && is_page( $home_id );
}

/**
 * CSS móvil del hero (inicio, nosotros y páginas con el mismo bloque del kit).
 */
function tc_shared_hero_mobile_css() {
	return '
@media(max-width:767px){
.elementor-element-dbe225e,.elementor-element-3c2b444a{margin-top:0!important;padding:20px 16px 16px!important;overflow:visible!important}
.elementor-element-4447161c,.elementor-element-62e22b01{width:100%!important;max-width:100%!important;padding:0!important}
.elementor-element-6577b81d,.elementor-element-4309a45{width:100%!important;max-width:100%!important;margin:0 auto 12px!important;padding:0 6px!important}
.elementor-element-6577b81d .elementor-heading-title,.elementor-element-4309a45 .elementor-heading-title,.elementor-element-3c2b444a .elementor-widget-heading .elementor-heading-title{font-size:1.55rem!important;line-height:1.28!important;letter-spacing:-.025em!important;text-align:center!important;white-space:normal!important;word-break:normal!important;overflow-wrap:break-word!important;hyphens:none!important;-webkit-hyphens:none!important;max-width:16ch!important;margin:0 auto!important;color:#111!important}
.elementor-element-27241db2,.elementor-element-24a90c55{width:100%!important;max-width:100%!important;margin:0 auto 14px!important;padding:0 8px!important}
.elementor-element-27241db2 .elementor-widget-container,.elementor-element-24a90c55 .elementor-widget-container{width:100%!important;max-width:100%!important;text-align:center!important;font-size:.94rem!important;line-height:1.6!important;color:#444!important}
.elementor-element-407266c8,.elementor-element-59b638bf{width:100%!important;justify-content:center!important;align-items:center!important;gap:10px!important;margin:0 auto 10px!important;padding:0 12px!important}
.elementor-element-407266c8 .elementor-button,.elementor-element-59b638bf .elementor-button{width:100%!important;max-width:280px!important;min-width:0!important;padding:13px 22px!important;font-size:.92rem!important;border-radius:999px!important;box-shadow:0 8px 20px rgba(82,138,49,.22)!important}
.elementor-element-407266c8 .elementor-button-wrapper,.elementor-element-59b638bf .elementor-button-wrapper{width:100%!important;display:flex!important;justify-content:center!important}
.elementor-element-373b3f08,.elementor-element-5291a5ef,.elementor-element-944ad80,.elementor-element-2657010a,.elementor-element-5d582024{display:none!important}
.elementor-element-2579d2ba{margin-top:4px!important;padding:0 8px!important}
.elementor-element-2579d2ba>.e-con-inner{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:10px 12px!important;justify-content:center!important;width:100%!important;max-width:340px!important;margin:0 auto!important}
.elementor-element-2579d2ba .elementor-counter{padding:10px 8px!important;border-radius:14px!important;background:rgba(255,255,255,.72)!important;box-shadow:0 4px 14px rgba(0,0,0,.04)!important}
.elementor-element-2579d2ba .elementor-counter-number-wrapper,.elementor-element-2579d2ba .elementor-counter-title{text-align:center!important}
.elementor-element-2579d2ba .elementor-counter-number-wrapper{font-size:1.35rem!important;line-height:1.1!important}
.elementor-element-2579d2ba .elementor-counter-title{font-size:.72rem!important;line-height:1.25!important;margin-top:4px!important}
.elementor-element-c1af7f3,.elementor-element-76fbfe65,.elementor-element-17eb095b,.elementor-element-40ab3dbb{display:none!important}
}
@media(max-width:380px){
.elementor-element-6577b81d .elementor-heading-title,.elementor-element-4309a45 .elementor-heading-title{font-size:1.35rem!important;line-height:1.3!important;max-width:100%!important}
.elementor-element-2579d2ba>.e-con-inner{max-width:100%!important}
}';
}

function tc_home_service_css_selectors( $scope, $group ) {
	$ids = tc_service_section_element_ids()[ $group ] ?? array();
	$parts = array();
	foreach ( $ids as $id ) {
		$parts[] = $scope . ' .elementor-element-' . $id;
	}
	return implode( ',', $parts );
}

function tc_home_product_grid_css( $scope ) {
	$base = $scope . ' .jeg-elementor-kit.jkit-product-grid .woocommerce .jkit-products .jkit-product-block';

	return $base . '{width:100%!important;min-width:0!important}
' . $scope . ' .jeg-elementor-kit.jkit-product-grid .jkit-product{display:flex!important;flex-direction:column!important;align-items:stretch!important;width:100%!important;text-align:left!important}
' . $base . ' .product-categories{display:block!important;width:100%!important;max-width:100%!important;min-width:0!important;margin:0 0 10px!important;padding:0!important;white-space:normal!important;text-align:left!important;letter-spacing:normal!important;word-spacing:normal!important;line-height:1.5!important;font-size:14px!important;text-transform:none!important}
' . $base . ' .product-categories object{display:block!important;width:100%!important;max-width:100%!important;pointer-events:none}
' . $base . ' .product-categories span{display:inline!important;margin:0!important;padding:0!important;white-space:normal!important;letter-spacing:normal!important;word-spacing:normal!important}
' . $base . ' .product-categories a{display:inline!important;margin:0!important;padding:0!important;white-space:normal!important;letter-spacing:normal!important;word-spacing:normal!important;text-transform:none!important;text-decoration:none!important}
' . $base . ' .product-title,' . $base . ' .product-title a{display:block!important;width:100%!important;max-width:100%!important;margin:0 0 8px!important;padding:0!important;white-space:normal!important;word-break:normal!important;overflow-wrap:break-word!important;letter-spacing:normal!important;word-spacing:normal!important;text-transform:none!important;text-align:left!important;line-height:1.35!important}
' . $base . ' .price,' . $base . ' .price *, ' . $base . ' .price del,' . $base . ' .price ins{display:inline!important;letter-spacing:normal!important;word-spacing:normal!important;text-align:left!important}';
}

function tc_home_responsive_css( $home_id ) {
	$home_id   = (int) $home_id;
	$scope     = '.elementor-' . $home_id;
	$grids     = tc_home_product_grid_css( $scope );
	$svc       = $scope . ' .elementor-element-73f4fd16';
	$svc_cards = tc_home_service_css_selectors( $svc, 'cards' );
	$svc_text  = tc_home_service_css_selectors( $svc, 'text' );
	$svc_image = tc_home_service_css_selectors( $svc, 'image' );
	$svc_arrow = tc_home_service_css_selectors( $svc, 'arrow' );
	$svc_wrap  = tc_home_service_css_selectors( $svc, 'wrap' );
	$svc_bg    = str_replace( ',', ':not(.elementor-motion-effects-element-type-background),', $svc_cards ) . ':not(.elementor-motion-effects-element-type-background)';
	$svc_bg   .= ',' . str_replace( ',', '>.elementor-motion-effects-container>.elementor-motion-effects-layer,', $svc_cards ) . '>.elementor-motion-effects-container>.elementor-motion-effects-layer';
	$svc_imgbg = str_replace( ',', ':not(.elementor-motion-effects-element-type-background),', $svc_image ) . ':not(.elementor-motion-effects-element-type-background)';
	$svc_imgbg .= ',' . str_replace( ',', '>.elementor-motion-effects-container>.elementor-motion-effects-layer,', $svc_image ) . '>.elementor-motion-effects-container>.elementor-motion-effects-layer';

	return $grids . '
' . $scope . ' .elementor-element-2657010a,
' . $scope . ' .elementor-element-5d582024{display:none!important}
@media(max-width:1024px){
' . $scope . ' .elementor-element-4309a45 .elementor-heading-title,
' . $scope . ' .elementor-element-6577b81d .elementor-heading-title{font-size:38px!important;line-height:1.2!important}
}
@media(max-width:767px){
' . $scope . ' .elementor-element-3c2b444a,
' . $scope . ' .elementor-element-dbe225e{margin-top:0!important;padding:24px 16px 12px!important;overflow:visible!important}
' . $scope . ' .elementor-element-62e22b01,
' . $scope . ' .elementor-element-4447161c{width:100%!important;max-width:100%!important;overflow:visible!important;padding-left:0!important;padding-right:0!important}
' . $scope . ' .elementor-element-4309a45,
' . $scope . ' .elementor-element-6577b81d{width:100%!important;max-width:100%!important;--e-global-typography-primary-font-size:24px;--e-global-typography-primary-line-height:1.32em}
' . $scope . ' .elementor-element-4309a45 .elementor-heading-title,
' . $scope . ' .elementor-element-6577b81d .elementor-heading-title,
' . $scope . ' .elementor-element-3c2b444a .elementor-widget-heading .elementor-heading-title{font-size:24px!important;line-height:1.32!important;letter-spacing:-.02em!important;max-width:20em!important;margin-left:auto!important;margin-right:auto!important;width:100%!important;text-align:center!important;white-space:normal!important;word-break:normal!important;overflow-wrap:break-word!important;hyphens:none!important;-webkit-hyphens:none!important}
' . $scope . ' .elementor-element-24a90c55,
' . $scope . ' .elementor-element-27241db2{width:100%!important;max-width:100%!important;--container-widget-width:100%!important}
' . $scope . ' .elementor-element-24a90c55 .elementor-widget-container,
' . $scope . ' .elementor-element-27241db2 .elementor-widget-container{width:100%!important;max-width:100%!important;text-align:center!important;font-size:15px!important;line-height:1.55!important}
' . $scope . ' .elementor-element-59b638bf,
' . $scope . ' .elementor-element-407266c8{width:100%!important;justify-content:center!important}
' . $scope . ' .elementor-element-59b638bf .elementor-button,
' . $scope . ' .elementor-element-407266c8 .elementor-button{width:100%;max-width:300px}
' . $svc . '{padding:20px 12px 28px!important;margin-top:0!important;overflow:visible!important}
' . $svc_wrap . '{width:100%!important;max-width:100%!important;margin:0!important;padding:0!important}
' . $svc_cards . '{position:relative!important;display:flex!important;flex-direction:row!important;flex-wrap:nowrap!important;align-items:stretch!important;width:100%!important;max-width:100%!important;margin:0 0 18px!important;padding:0 0 16px!important;overflow:hidden!important;border-radius:20px!important;min-height:190px!important;box-sizing:border-box!important}
' . $svc_cards . '>.e-con-inner{position:relative!important;display:flex!important;flex-direction:row!important;flex-wrap:nowrap!important;align-items:stretch!important;width:100%!important;max-width:100%!important}
' . $svc_bg . '{background-size:100% 100%!important;background-position:top center!important;background-repeat:no-repeat!important}
' . $svc_text . '{flex:1 1 56%!important;width:56%!important;max-width:56%!important;min-width:0!important;min-height:0!important;display:flex!important;flex-direction:column!important;justify-content:flex-start!important;align-items:flex-start!important;gap:6px!important;margin:0!important;padding:22px 44px 10px 16px!important;box-sizing:border-box!important}
' . $svc_text . ' .elementor-widget-button{margin-top:auto!important;width:100%!important;max-width:100%!important}
' . $svc_text . ' .elementor-button{width:auto!important;max-width:100%!important}
' . $svc_image . '{flex:0 0 44%!important;width:44%!important;max-width:44%!important;min-width:0!important;min-height:0!important;display:flex!important;flex-direction:column!important;justify-content:flex-end!important;align-items:center!important;align-self:stretch!important;margin:0!important;padding:8px 4px 4px 0!important;box-sizing:border-box!important}
' . $svc_imgbg . '{background-size:contain!important;background-position:center bottom!important;background-repeat:no-repeat!important}
' . $svc_image . ' .elementor-widget-spacer{width:100%!important;margin:0!important}
' . $svc_image . ' .elementor-widget-spacer .elementor-spacer-inner{height:clamp(105px,30vw,155px)!important;max-height:155px!important}
' . $svc_arrow . '{position:absolute!important;top:0!important;right:0!important;left:auto!important;bottom:auto!important;width:auto!important;max-width:none!important;margin:0!important;padding:0!important;z-index:6!important;flex:none!important;pointer-events:auto!important}
' . $svc . ' .elementor-widget-image-box{width:100%!important;max-width:100%!important;min-width:0!important;flex:0 1 auto!important}
' . $svc . ' .elementor-image-box-wrapper,' . $svc . ' .elementor-image-box-content{width:100%!important;max-width:100%!important;min-width:0!important;box-sizing:border-box!important}
' . $svc . ' .elementor-image-box-title{font-size:clamp(16px,3.9vw,23px)!important;line-height:1.12!important;max-width:100%!important;word-break:normal!important;overflow-wrap:break-word!important;hyphens:auto!important;--e-global-typography-a9a2c44-font-size:clamp(16px,3.9vw,23px);--e-global-typography-9f6bfa5-font-size:clamp(16px,3.9vw,23px)}
' . $svc . ' .elementor-image-box-description{font-size:12px!important;line-height:1.4!important;margin:0!important;max-width:100%!important;word-break:normal!important;overflow-wrap:break-word!important}
' . $scope . ' .elementor-element-5839ad66,' . $scope . ' .elementor-element-6534553e{width:100%!important;max-width:100%!important;padding-left:16px!important;padding-right:16px!important}
' . $scope . ' .elementor-element-3ddbcf02{flex-direction:column!important;width:100%!important}
}
@media(max-width:380px){
' . $scope . ' .elementor-element-4309a45 .elementor-heading-title,
' . $scope . ' .elementor-element-6577b81d .elementor-heading-title{font-size:21px!important;line-height:1.35!important;max-width:100%!important}
}';
}

function tc_enqueue_home_responsive_assets() {
	if ( ! tc_is_home_context() ) {
		return;
	}

	$home_id = (int) tc_get_home_page_id();
	if ( $home_id <= 0 ) {
		return;
	}

	$deps = array( 'tc-brand-colors' );
	if ( wp_style_is( 'elementor-frontend', 'registered' ) ) {
		$deps[] = 'elementor-frontend';
	}
	if ( ! wp_style_is( 'jkit-elements-main', 'registered' ) ) {
		wp_register_style(
			'jkit-elements-main',
			plugins_url( 'jeg-elementor-kit/assets/css/elements/main.css' ),
			array(),
			defined( 'JEG_ELEMENTOR_KIT_VERSION' ) ? JEG_ELEMENTOR_KIT_VERSION : '1.0'
		);
	}
	if ( wp_style_is( 'jkit-elements-main', 'registered' ) ) {
		$deps[] = 'jkit-elements-main';
	}
	$post_css_handle = 'elementor-post-' . $home_id;
	if ( wp_style_is( $post_css_handle, 'registered' ) ) {
		$deps[] = $post_css_handle;
	}

	wp_register_style( 'tc-home-responsive', false, $deps, TC_SETUP_VERSION );
	wp_enqueue_style( 'tc-home-responsive' );
	wp_add_inline_style( 'tc-home-responsive', tc_home_responsive_css( $home_id ) );
}

add_filter( 'term_links-product_cat', 'tc_product_grid_term_links', 10, 1 );

add_filter(
	'woocommerce_post_class',
	static function ( $classes ) {
		if ( function_exists( 'is_product' ) && is_product() ) {
			$classes[] = 'tc-product-kit';
		}
		return $classes;
	},
	20
);

function tc_render_whatsapp_float() {
	?>
	<a class="tc-wa-float" href="<?php echo esc_url( TC_WHATSAPP ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Escríbenos por WhatsApp', 'techcomputer' ); ?>">
		<svg viewBox="0 0 32 32" aria-hidden="true" focusable="false"><path d="M16.01 3C9.39 3 4 8.28 4 14.78c0 2.07.58 4.1 1.68 5.87L4 29l8.58-1.62A12.9 12.9 0 0 0 16.02 27C22.63 27 28 21.72 28 15.22 27.99 8.72 22.62 3 16.01 3Zm0 23.13c-1.66 0-3.29-.44-4.72-1.28l-.34-.2-5.09 1.06 1.08-4.9-.22-.35a9.86 9.86 0 0 1-1.52-5.28C5.2 9.9 10.07 5.2 16.01 5.2c5.93 0 10.79 4.7 10.79 10.48 0 5.78-4.86 10.48-10.79 10.48Zm5.92-7.84c-.32-.16-1.9-.94-2.2-1.05-.3-.1-.52-.16-.74.16-.22.32-.85 1.05-1.04 1.27-.19.22-.39.24-.71.08-.32-.16-1.36-.5-2.59-1.6-.96-.85-1.6-1.9-1.79-2.22-.19-.32-.02-.5.14-.66.14-.14.32-.39.48-.58.16-.19.22-.32.32-.54.1-.22.05-.4-.03-.58-.08-.16-.74-1.78-1.01-2.44-.27-.64-.54-.55-.74-.56h-.63c-.22 0-.58.08-.88.4-.3.32-1.15 1.12-1.15 2.74 0 1.62 1.18 3.18 1.34 3.4.16.22 2.32 3.54 5.62 4.96.79.34 1.4.54 1.88.69.79.25 1.51.22 2.08.13.63-.1 1.9-.78 2.17-1.53.27-.75.27-1.39.19-1.53-.08-.14-.3-.22-.62-.38Z"/></svg>
	</a>
	<?php
}

function tc_contact_form_shortcode() {
	$notice = '';
	if ( isset( $_GET['tc_contact'] ) ) {
		if ( 'sent' === $_GET['tc_contact'] ) {
			$notice = '<div class="tc-contact-form__notice tc-contact-form__notice--ok">' . esc_html__( 'Mensaje enviado. Te contactaremos pronto.', 'techcomputer' ) . '</div>';
		} elseif ( 'error' === $_GET['tc_contact'] ) {
			$notice = '<div class="tc-contact-form__notice tc-contact-form__notice--error">' . esc_html__( 'No se pudo enviar el mensaje. Revisa los campos e inténtalo de nuevo.', 'techcomputer' ) . '</div>';
		}
	}

	ob_start();
	echo $notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
	<form class="tc-contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="tc_contact_form">
		<?php wp_nonce_field( 'tc_contact_form', 'tc_contact_nonce' ); ?>
		<div class="tc-contact-form__row tc-contact-form__row--2">
			<div>
				<label for="tc_contact_name"><?php esc_html_e( 'Nombre', 'techcomputer' ); ?> *</label>
				<input id="tc_contact_name" type="text" name="tc_contact_name" required>
			</div>
			<div>
				<label for="tc_contact_email"><?php esc_html_e( 'Correo electrónico', 'techcomputer' ); ?> *</label>
				<input id="tc_contact_email" type="email" name="tc_contact_email" required>
			</div>
		</div>
		<div>
			<label for="tc_contact_phone"><?php esc_html_e( 'Teléfono', 'techcomputer' ); ?></label>
			<input id="tc_contact_phone" type="tel" name="tc_contact_phone">
		</div>
		<div>
			<span class="tc-contact-form__label"><?php esc_html_e( '¿Cómo desea ser contactado?', 'techcomputer' ); ?> *</span>
			<div class="tc-contact-form__choices">
				<label><input type="radio" name="tc_contact_method" value="Whatsapp" required> WhatsApp</label>
				<label><input type="radio" name="tc_contact_method" value="Email"> Email</label>
				<label><input type="radio" name="tc_contact_method" value="Llamado"> <?php esc_html_e( 'Llamado', 'techcomputer' ); ?></label>
			</div>
		</div>
		<div>
			<label for="tc_contact_message"><?php esc_html_e( 'Comentario o mensaje', 'techcomputer' ); ?></label>
			<textarea id="tc_contact_message" name="tc_contact_message"></textarea>
		</div>
		<button type="submit" class="button tc-contact-form__submit"><?php esc_html_e( 'Enviar', 'techcomputer' ); ?></button>
	</form>
	<?php
	return ob_get_clean();
}

function tc_handle_contact_form() {
	$redirect = tc_page_url( 'contactanos' );
	if ( ! $redirect ) {
		$redirect = home_url( '/contactanos/' );
	}

	if ( ! isset( $_POST['tc_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tc_contact_nonce'] ) ), 'tc_contact_form' ) ) {
		wp_safe_redirect( add_query_arg( 'tc_contact', 'error', $redirect ) );
		exit;
	}

	$name    = isset( $_POST['tc_contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['tc_contact_name'] ) ) : '';
	$email   = isset( $_POST['tc_contact_email'] ) ? sanitize_email( wp_unslash( $_POST['tc_contact_email'] ) ) : '';
	$phone   = isset( $_POST['tc_contact_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['tc_contact_phone'] ) ) : '';
	$method  = isset( $_POST['tc_contact_method'] ) ? sanitize_text_field( wp_unslash( $_POST['tc_contact_method'] ) ) : '';
	$message = isset( $_POST['tc_contact_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tc_contact_message'] ) ) : '';

	if ( '' === $name || '' === $email || '' === $method ) {
		wp_safe_redirect( add_query_arg( 'tc_contact', 'error', $redirect ) );
		exit;
	}

	$body  = "Nombre: {$name}\n";
	$body .= "Email: {$email}\n";
	$body .= "Teléfono: {$phone}\n";
	$body .= "Contacto preferido: {$method}\n\n";
	$body .= "Mensaje:\n{$message}\n";

	$sent = wp_mail(
		TC_EMAIL,
		'Contacto web Techcomputer - ' . $name,
		$body,
		array(
			'Reply-To: ' . $name . ' <' . $email . '>',
			'Content-Type: text/plain; charset=UTF-8',
		)
	);

	wp_safe_redirect( add_query_arg( 'tc_contact', $sent ? 'sent' : 'error', $redirect ) );
	exit;
}
