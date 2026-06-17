<?php
/**
 * Plugin Name: Techcomputer - Panel de configuración
 * Description: Interfaz admin para aplicar plantillas, header/footer, productos y enlaces.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/techcomputer-site-setup.php';

add_action( 'admin_menu', 'tc_admin_menu' );
add_action( 'admin_notices', 'tc_admin_notice' );

function tc_admin_menu() {
	add_management_page(
		'Techcomputer Setup',
		'Techcomputer',
		'manage_options',
		'techcomputer-setup',
		'tc_admin_page'
	);
}

function tc_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( get_option( 'tc_setup_version' ) !== TC_SETUP_VERSION ) {
		$url = admin_url( 'tools.php?page=techcomputer-setup' );
		echo '<div class="notice notice-info"><p><strong>Techcomputer:</strong> Hay actualizaciones pendientes. Se aplican solas al cargar el sitio (logo y header). Tus imágenes en Elementor <strong>no se borran</strong>. Opcional: <a href="' . esc_url( $url ) . '">Herramientas → Techcomputer</a>.</p></div>';
	}
}

function tc_count_products() {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return 0;
	}
	$query = wc_get_products( array( 'limit' => 1, 'return' => 'ids', 'status' => 'publish' ) );
	return (int) wp_count_posts( 'product' )->publish;
}

function tc_admin_page() {
	$result = null;
	if ( isset( $_POST['tc_setup_full'] ) && check_admin_referer( 'tc_setup_action' ) ) {
		$result = tc_run_full_setup();
	} elseif ( isset( $_POST['tc_refresh_content'] ) && check_admin_referer( 'tc_setup_action' ) ) {
		$updated = 0;
		$home_id = tc_get_home_page_id();
		if ( $home_id && tc_persist_elementor_data( $home_id ) ) {
			++$updated;
		}
		foreach ( array( 'nosotros', 'contactanos' ) as $slug ) {
			$p = get_page_by_path( $slug );
			if ( $p && tc_persist_elementor_data( $p->ID ) ) {
				++$updated;
			}
		}
		if ( function_exists( 'get_hfe_header_id' ) && get_hfe_header_id() ) {
			tc_persist_elementor_data( get_hfe_header_id() );
			++$updated;
		}
		if ( function_exists( 'get_hfe_footer_id' ) && get_hfe_footer_id() ) {
			tc_persist_elementor_data( get_hfe_footer_id() );
			++$updated;
		}
		if ( function_exists( 'tc_disable_store_coming_soon' ) ) {
			tc_disable_store_coming_soon();
		}
		if ( function_exists( 'wc_get_page_id' ) ) {
			$shop_id = wc_get_page_id( 'shop' );
			if ( $shop_id > 0 && tc_persist_elementor_data( $shop_id ) ) {
				++$updated;
			}
		}
		if ( function_exists( 'tc_apply_techcomputer_brand_colors' ) ) {
			tc_apply_techcomputer_brand_colors();
			++$updated;
		}
		if ( function_exists( 'tc_setup_repuesto_categories' ) ) {
			tc_setup_repuesto_categories();
			++$updated;
		}
		if ( function_exists( 'tc_ensure_featured_service_products' ) ) {
			tc_ensure_featured_service_products();
			++$updated;
		}
		update_option( 'tc_setup_version', TC_SETUP_VERSION );
		$result = array( 'refresh' => $updated );
	}

	$product_count = tc_count_products();
	$pantallas     = tc_resolve_category_slugs( tc_get_pantallas_slugs() );
	$servicios     = tc_resolve_category_slugs( tc_get_servicios_slugs() );
	$hfe_header    = function_exists( 'get_hfe_header_id' ) ? get_hfe_header_id() : 0;
	$hfe_footer    = function_exists( 'get_hfe_footer_id' ) ? get_hfe_footer_id() : 0;
	$jkit_ok       = function_exists( 'tc_jkit_is_available' ) && tc_jkit_is_available();
	$shop_elementor = function_exists( 'tc_shop_uses_elementor_kit' ) && tc_shop_uses_elementor_kit();
	$coming_soon    = 'yes' === get_option( 'woocommerce_coming_soon', 'no' );
	?>
	<div class="wrap">
		<h1>Configuración Techcomputer</h1>

		<?php if ( is_wp_error( $result ) ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $result->get_error_message() ); ?></p></div>
		<?php elseif ( is_array( $result ) && ! empty( $result['home_url'] ) ) : ?>
			<div class="notice notice-success">
				<p><strong>Sitio configurado.</strong></p>
				<p>
					<a class="button button-primary" href="<?php echo esc_url( $result['home_url'] ); ?>" target="_blank">Ver inicio</a>
					<a class="button" href="<?php echo esc_url( $result['shop_url'] ); ?>" target="_blank">Ver tienda</a>
				</p>
			</div>
		<?php elseif ( is_array( $result ) && isset( $result['refresh'] ) ) : ?>
			<div class="notice notice-success"><p>Contenido actualizado en <?php echo (int) $result['refresh']; ?> página(s).</p></div>
		<?php endif; ?>

		<table class="widefat" style="max-width:760px;margin:20px 0">
			<tbody>
				<tr><th>Productos publicados</th><td><?php echo (int) $product_count; ?></td></tr>
				<tr><th>Categoría pantallas</th><td><?php echo $pantallas ? esc_html( implode( ', ', $pantallas ) ) : 'No encontrada — revisa el import CSV'; ?></td></tr>
				<tr><th>Categoría servicios</th><td><?php echo $servicios ? esc_html( implode( ', ', $servicios ) ) : 'No encontrada — revisa el import CSV'; ?></td></tr>
				<tr><th>Header HFE</th><td><?php echo $hfe_header ? 'Activo (ID ' . (int) $hfe_header . ')' : 'No configurado'; ?></td></tr>
				<tr><th>Footer HFE</th><td><?php echo $hfe_footer ? 'Activo (ID ' . (int) $hfe_footer . ')' : 'No configurado'; ?></td></tr>
				<tr><th>Jeg Elementor Kit</th><td><?php echo $jkit_ok ? 'Activo (grillas del kit)' : 'No activo — necesario para el diseño de tienda'; ?></td></tr>
				<tr><th>Tienda (/shop)</th><td><?php echo $shop_elementor ? 'Plantilla Product del kit (product.json)' : 'Pendiente — ejecuta configuración completa'; ?></td></tr>
				<tr><th>Modo tienda WC</th><td><?php echo $coming_soon ? 'Próximamente (desactiva con Configurar sitio)' : 'Publicada'; ?></td></tr>
			</tbody>
		</table>

		<?php if ( 0 === $product_count ) : ?>
			<div class="notice notice-warning inline" style="margin:16px 0;max-width:760px">
				<p><strong>Sin productos en WooCommerce.</strong> Importa el CSV desde <em>Productos → Importar</em> (archivo en <code>wp-content/uploads/wc-imports/</code>). Sin productos, las grillas del kit quedan vacías.</p>
			</div>
		<?php endif; ?>

		<p>Este proceso:</p>
		<ul style="list-style:disc;padding-left:20px">
			<li>Importa Home, Header, Footer, Nosotros y Contacto</li>
			<li>Aplica la plantilla <strong>Product</strong> del kit a la página de tienda WooCommerce</li>
			<li>Activa Jeg Elementor Kit y configura grillas por categoría (pantallas + servicios)</li>
			<li>Activa header y footer en todo el sitio (incluye plantilla canvas)</li>
			<li>Enlaza botones: Cotiza Aquí → WhatsApp, Ver Servicio → categorías, Ver Más → tienda</li>
			<li>Aplica la paleta de colores de techcomputer.cl (verde #528A31)</li>
			<li>Aplica reseñas de Google (testimonios) y enlaces de botones</li>
			<li>Crea menú de navegación</li>
		</ul>

		<form method="post" style="margin-bottom:16px">
			<?php wp_nonce_field( 'tc_setup_action' ); ?>
			<?php submit_button( 'Configurar sitio completo', 'primary', 'tc_setup_full', false ); ?>
		</form>

		<form method="post">
			<?php wp_nonce_field( 'tc_setup_action' ); ?>
			<?php submit_button( 'Solo actualizar productos y botones', 'secondary', 'tc_refresh_content', false ); ?>
			<p class="description"><strong>Seguro para tus imágenes:</strong> conserva todas las fotos que subiste en Elementor. Solo actualiza enlaces, botones, colores, header y catálogo. No uses &laquo;Configurar sitio completo&raquo; si ya personalizaste páginas.</p>
			<p class="description"><strong>Logo del header:</strong> ve a <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=elementor-hf' ) ); ?>">UAE → Plantillas Header/Footer</a>, abre &laquo;Techcomputer Header&raquo; y edita el recuadro de imagen. No copies el header dentro de la página Inicio.</p>
		</form>
	</div>
	<?php
}
