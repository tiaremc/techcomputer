<?php
/**
 * Activa diagnóstico de WordPress. Incluir en wp-config.php del servidor ANTES de:
 * require_once ABSPATH . 'wp-settings.php';
 *
 * require __DIR__ . '/config/wp-debug-snippet.php';
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', true );
}
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true );
@ini_set( 'display_errors', 1 );
